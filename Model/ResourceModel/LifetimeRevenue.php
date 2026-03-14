<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class LifetimeRevenue extends AbstractDb
{
    /**
     * Initialize resource model.
     *
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init('sertlab_lifetime_revenue', 'customer_id');
    }
}
