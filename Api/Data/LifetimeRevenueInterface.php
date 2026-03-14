<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Api\Data;

interface LifetimeRevenueInterface
{
    public const CUSTOMER_ID = 'customer_id';
    public const CUSTOMER_LIFETIME_REVENUE = 'customer_lifetime_revenue';

    /**
     * Get customer ID.
     *
     * @return int
     */
    public function getCustomerId(): int;

    /**
     * Set customer ID.
     *
     * @param int $customerId
     * @return self
     */
    public function setCustomerId(int $customerId): self;

    /**
     * Get customer lifetime revenue.
     *
     * @return float
     */
    public function getCustomerLifetimeRevenue(): float;

    /**
     * Set customer lifetime revenue.
     *
     * @param float $revenue
     * @return self
     */
    public function setCustomerLifetimeRevenue(float $revenue): self;
}
