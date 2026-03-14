<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Api;

use Sertlab\LifeTimeRevenue\Api\Data\LifetimeRevenueInterface;

interface LifetimeRevenueRepositoryInterface
{
    /**
     * Get lifetime revenue record by customer ID.
     *
     * @param int $customerId
     * @return LifetimeRevenueInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCustomerId(int $customerId): LifetimeRevenueInterface;

    /**
     * Save lifetime revenue record.
     *
     * @param LifetimeRevenueInterface $lifetimeRevenue
     * @return LifetimeRevenueInterface
     */
    public function save(LifetimeRevenueInterface $lifetimeRevenue): LifetimeRevenueInterface;
}
