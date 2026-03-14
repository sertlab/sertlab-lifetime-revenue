<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Sertlab\LifeTimeRevenue\Api\Data\LifetimeRevenueInterface;
use Sertlab\LifeTimeRevenue\Api\LifetimeRevenueRepositoryInterface;

class LifetimeRevenueRepository implements LifetimeRevenueRepositoryInterface
{
    /**
     * Constructor.
     *
     * @param LifetimeRevenueFactory $factory
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        private readonly LifetimeRevenueFactory $factory,
        private readonly ResourceConnection $resourceConnection,
    ) {
    }

    /**
     * Get by customer id.
     *
     * @param int $customerId
     * @return LifetimeRevenueInterface
     * @throws NoSuchEntityException
     */
    public function getByCustomerId(int $customerId): LifetimeRevenueInterface
    {
        $connection = $this->resourceConnection->getConnection();
        $table = $this->resourceConnection->getTableName('sertlab_lifetime_revenue');

        $select = $connection->select()
            ->from($table)
            ->where('customer_id = ?', $customerId)
            ->limit(1);

        $row = $connection->fetchRow($select);

        if (!$row) {
            throw new NoSuchEntityException(
                __('No lifetime revenue record found for customer id "%1".', $customerId)
            );
        }

        $model = $this->factory->create();
        $model->setData($row);

        return $model;
    }

    /**
     * Save.
     *
     * @param LifetimeRevenueInterface $lifetimeRevenue
     * @return LifetimeRevenueInterface
     * @throws CouldNotSaveException
     */
    public function save(LifetimeRevenueInterface $lifetimeRevenue): LifetimeRevenueInterface
    {
        try {
            $this->resourceConnection->getConnection()->insertOnDuplicate(
                $this->resourceConnection->getTableName('sertlab_lifetime_revenue'),
                [
                    'customer_id' => $lifetimeRevenue->getCustomerId(),
                    'customer_lifetime_revenue' => $lifetimeRevenue->getCustomerLifetimeRevenue(),
                ],
                ['customer_lifetime_revenue']
            );
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()), $e);
        }

        return $lifetimeRevenue;
    }
}
