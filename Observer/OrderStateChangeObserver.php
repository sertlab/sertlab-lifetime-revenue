<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;
use Sertlab\LifeTimeRevenue\Model\CustomerLifetimeRevenueUpdater;

class OrderStateChangeObserver implements ObserverInterface
{
    /**
     * Constructor.
     *
     * @param CustomerLifetimeRevenueUpdater $customerLifetimeRevenueUpdater
     */
    public function __construct(
        private readonly CustomerLifetimeRevenueUpdater $customerLifetimeRevenueUpdater
    ) {
    }

    /**
     * Execute method.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if (!$order->getCustomerId()) {
            return; // guest order, skip
        }

        if (!$order->dataHasChangedFor('state') && !$order->dataHasChangedFor('total_refunded')) {
            return;
        }

        $allowedStates = [Order::STATE_COMPLETE, Order::STATE_CLOSED];

        if (in_array($order->getState(), $allowedStates, true)) {
            $this->customerLifetimeRevenueUpdater->recalculate((int) $order->getCustomerId());
        }
    }
}
