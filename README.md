# Sertlab_LifeTimeRevenue

A Magento 2 module that adds a **Customer Lifetime Revenue** column to the Admin Sales Order Grid.

## What it does

- Displays the total value of all `complete` and `closed` orders per customer, across all websites
- The column is sortable and supports min/max range filtering
- Values are formatted in the store's base currency

## How it works

The module pre-calculates and stores the lifetime revenue in a dedicated `sertlab_lifetime_revenue`
table (one row per customer). The value is recalculated whenever an order transitions to `complete`
or `closed`, or when a refund is applied. The grid reads the value via a simple JOIN — no
aggregations happen at grid load time.

For a full explanation of the data strategy, performance considerations, and design decisions,
read [DESIGN_NOTE.md](DESIGN_NOTE.md).

## Installation

Clone the repository into your Magento installation:

```bash
git clone <repository-url> app/code/Sertlab/LifeTimeRevenue
bin.magento module:enable Sertlab_LifeTimeRevenue
bin/magento setup:upgrade
```

## Requirements

- Magento 2.4+
- PHP 8.1+

## Running Tests

```bash
vendor/bin/phpunit app/code/Sertlab/LifeTimeRevenue/Test/Unit
```
