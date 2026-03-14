<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Model;

use Magento\Framework\Model\AbstractModel;
use Sertlab\LifeTimeRevenue\Api\Data\LifetimeRevenueInterface;
use Sertlab\LifeTimeRevenue\Model\ResourceModel\LifetimeRevenue as LifetimeRevenueResource;

class LifetimeRevenue extends AbstractModel implements LifetimeRevenueInterface
{
    /**
     * Initialize model.
     *
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _construct(): void
    {
        $this->_init(LifetimeRevenueResource::class);
    }

    /**
     * Get customer id.
     *
     * @return int
     */
    public function getCustomerId(): int
    {
        return (int) $this->getData(self::CUSTOMER_ID);
    }

    /**
     * Set customer id.
     *
     * @param int $customerId
     * @return self
     */
    public function setCustomerId(int $customerId): self
    {
        return $this->setData(self::CUSTOMER_ID, $customerId);
    }

    /**
     * Get customer lifetime revenue.
     *
     * @return float
     */
    public function getCustomerLifetimeRevenue(): float
    {
        return (float) $this->getData(self::CUSTOMER_LIFETIME_REVENUE);
    }

    /**
     * Set customer lifetime revenue.
     *
     * @param float $revenue
     * @return self
     */
    public function setCustomerLifetimeRevenue(float $revenue): self
    {
        return $this->setData(self::CUSTOMER_LIFETIME_REVENUE, $revenue);
    }
}
