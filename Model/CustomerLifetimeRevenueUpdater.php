<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Sales\Model\Order;
use Sertlab\LifeTimeRevenue\Api\LifetimeRevenueRepositoryInterface;

class CustomerLifetimeRevenueUpdater
{
    /**
     * Constructor.
     *
     * @param ResourceConnection $resourceConnection
     * @param LifetimeRevenueRepositoryInterface $repository
     * @param LifetimeRevenueFactory $lifetimeRevenueFactory
     */
    public function __construct(
        private readonly ResourceConnection $resourceConnection,
        private readonly LifetimeRevenueRepositoryInterface $repository,
        private readonly LifetimeRevenueFactory $lifetimeRevenueFactory
    ) {
    }

    /**
     * Recalculate and persist lifetime revenue for a customer.
     *
     * @param int $customerId
     * @return void
     */
    public function recalculate(int $customerId): void
    {
        $connection = $this->resourceConnection->getConnection();

        $select = $connection->select()
            ->from(
                $this->resourceConnection->getTableName('sales_order'),
                ['lifetime_revenue' => new \Zend_Db_Expr('SUM(base_grand_total - IFNULL(base_total_refunded, 0))')]
            )
            ->where('customer_id = ?', $customerId)
            ->where('state IN (?)', [Order::STATE_COMPLETE, Order::STATE_CLOSED]);

        $revenue = (float) $connection->fetchOne($select);

        $record = $this->lifetimeRevenueFactory->create();
        $record->setCustomerId($customerId);
        $record->setCustomerLifetimeRevenue($revenue);

        $this->repository->save($record);
    }
}
