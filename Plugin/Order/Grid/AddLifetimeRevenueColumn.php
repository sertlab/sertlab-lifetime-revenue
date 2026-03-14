<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Plugin\Order\Grid;

use Magento\Sales\Model\ResourceModel\Order\Grid\Collection;

class AddLifetimeRevenueColumn
{
    /**
     * Before load plugin method.
     *
     * @param Collection $subject
     * @param bool $printQuery
     * @param bool $logQuery
     * @return bool[]
     */
    public function beforeLoad(Collection $subject, bool $printQuery = false, bool $logQuery = false): array
    {
        if (!$subject->isLoaded()) {
            $subject->getSelect()->joinLeft(
                ['slr' => $subject->getTable('sertlab_lifetime_revenue')],
                'slr.customer_id = main_table.customer_id',
                ['customer_lifetime_revenue']
            );
        }

        return [$printQuery, $logQuery];
    }
}
