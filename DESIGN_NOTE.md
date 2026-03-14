# Design Note — Customer Lifetime Revenue Module

## Overview

This module adds a **Customer Lifetime Revenue** column to the Magento Admin Order Grid.
The value shows the total of all completed and closed orders for each customer, across all websites.

---

## Data Strategy

I created a separate table `sertlab_lifetime_revenue` with one row per customer. It stores
the pre-calculated revenue value so the grid never has to calculate anything at load time.

The value is updated by an observer on `sales_order_save_after`. When an order becomes `complete`
or `closed`, the observer recalculates the total for that customer and saves it. The save uses
`INSERT ON DUPLICATE KEY UPDATE` so it works for both new and existing records.

The observer also handles partial refunds. When a credit memo is created on a complete order,
the order state does not change but `total_refunded` does. The observer watches for changes to
both `state` and `total_refunded`, so a partial refund will correctly trigger a recalculation.
The revenue formula already accounts for refunds: `SUM(base_grand_total - IFNULL(base_total_refunded, 0))`.

The grid reads the value with a simple `LEFT JOIN` on the pre-calculated table. No aggregations
happen at read time. The column is sortable and supports range-based filtering (min/max) directly
in the admin grid.

---

## Why Full Recalculation Instead of Incremental Updates

I chose to always recalculate the full value from scratch instead of just adding or subtracting
the current order amount.

An incremental approach would be faster but it can go wrong easily. If an event fires twice, or
gets missed, or someone edits data manually, the stored value becomes wrong and there is no way
to fix it automatically. With a full recalculation, the value is always correct because it is
always read fresh from the database.

---

## Scalability

The heavy work happens when an order is saved, not when the grid loads. So no matter how many
orders exist in the system, the grid query stays simple and fast — it is always just a join on
a primary key.

The recalculation query does get slower as a customer has more orders. For most stores this is
fine. If needed, this could be moved to a message queue so it runs in the background and does
not slow down the order save. I kept it synchronous for now to keep things simple, but the async
path is straightforward to add.

---

## Performance Over Time

The `sertlab_lifetime_revenue` table always has one row per customer. It does not grow with
order volume. No cleanup or reindexing is needed over time.

The foreign key to `customer_entity` with `CASCADE DELETE` means the row is automatically
removed when a customer is deleted.

The revenue calculation uses a direct SQL aggregate query instead of loading order objects through
the ORM. This keeps things fast and avoids loading large collections into memory. The query also
benefits from the existing `SALES_ORDER_CUSTOMER_ID` index on `sales_order.customer_id`:

```sql
SHOW INDEX FROM sales_order WHERE Column_name = 'customer_id';
+-------------+------------+-------------------------+--------------+-------------+-----------+
| Table       | Key_name                | Column_name | Index_type |
+-------------+------------+-------------------------+--------------+-------------+-----------+
| sales_order | SALES_ORDER_CUSTOMER_ID | customer_id | BTREE      |
+-------------+------------+-------------------------+--------------+-------------+-----------+
```

This means MySQL can filter rows for a specific customer efficiently without a full table scan.
The trade-off is that if another module changes order totals at the model level using plugins,
those changes would not be reflected here since we read directly from the database table.

---

## Extensibility

Adding more metrics like Average Order Value, Total Orders, or Last Order Date is straightforward:

1. Add the new columns to `sertlab_lifetime_revenue` in `db_schema.xml`
2. Update `CustomerLifetimeRevenueUpdater::recalculate()` to calculate and save the new values

Everything else the observer, the repository, the grid plugin — stays the same. Because we
already query `sales_order` once per recalculation, extra metrics can be added to the same
query at no extra cost.

---

## Multi-Website & Currency

I used `base_grand_total` and `base_total_refunded` instead of `grand_total` and
`total_refunded`. This is because `base_grand_total` is always stored in the base
currency of the installation, so orders from different stores can be added together.

This works well when all websites share the same base currency. If each website has
a different base currency, the numbers would not be directly comparable. In that case
a future improvement could be to convert everything to one currency before saving,
or to store the revenue separately per website.

## Trade-offs and Possible Improvements

- **Backfill**: customers who already exist will have no record until their next order. A CLI
  command or an admin button per customer could be added to handle this. I left it out of scope
  for now but it is a natural next step.

- **Async processing**: on high-traffic stores, running the recalculation synchronously adds
  a small delay to the order save. Moving it to a queue would remove that risk.

- **Direct SQL**: using `ResourceConnection` instead of the ORM is faster but it means
  third-party plugins that modify order totals at the model level would be bypassed.

- **Multi-currency**: the current implementation assumes a single base currency
  across all websites. See Multi-Website & Currency section above.
