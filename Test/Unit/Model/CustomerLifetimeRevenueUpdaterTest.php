<?php

declare(strict_types=1);

namespace Sertlab\LifeTimeRevenue\Test\Unit\Model;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Select;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Sertlab\LifeTimeRevenue\Api\LifetimeRevenueRepositoryInterface;
use Sertlab\LifeTimeRevenue\Model\CustomerLifetimeRevenueUpdater;
use Sertlab\LifeTimeRevenue\Model\LifetimeRevenue;
use Sertlab\LifeTimeRevenue\Model\LifetimeRevenueFactory;

class CustomerLifetimeRevenueUpdaterTest extends TestCase
{
    /** @var ResourceConnection&MockObject */
    private ResourceConnection $resourceConnection;

    /** @var LifetimeRevenueRepositoryInterface&MockObject */
    private LifetimeRevenueRepositoryInterface $repository;

    /** @var LifetimeRevenueFactory&MockObject */
    private LifetimeRevenueFactory $factory;

    /** @var AdapterInterface&MockObject */
    private AdapterInterface $connection;

    /** @var Select&MockObject */
    private Select $select;

    /** @var CustomerLifetimeRevenueUpdater */
    private CustomerLifetimeRevenueUpdater $updater;

    protected function setUp(): void
    {
        $this->select = $this->createMock(Select::class);
        $this->select->method('from')->willReturnSelf();
        $this->select->method('where')->willReturnSelf();

        $this->connection = $this->createMock(AdapterInterface::class);
        $this->connection->method('select')->willReturn($this->select);

        $this->resourceConnection = $this->createMock(ResourceConnection::class);
        $this->resourceConnection->method('getConnection')->willReturn($this->connection);
        $this->resourceConnection->method('getTableName')->with('sales_order')->willReturn('sales_order');

        $this->repository = $this->createMock(LifetimeRevenueRepositoryInterface::class);
        $this->factory = $this->createMock(LifetimeRevenueFactory::class);

        $this->updater = new CustomerLifetimeRevenueUpdater(
            $this->resourceConnection,
            $this->repository,
            $this->factory
        );
    }

    public function testRecalculateSavesCalculatedRevenue(): void
    {
        $customerId = 1;
        $revenue = 250.75;

        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->with($this->select)
            ->willReturn((string) $revenue);

        $record = $this->createMock(LifetimeRevenue::class);
        $record->expects($this->once())->method('setCustomerId')->with($customerId);
        $record->expects($this->once())->method('setCustomerLifetimeRevenue')->with($revenue);

        $this->factory->expects($this->once())->method('create')->willReturn($record);

        $this->repository->expects($this->once())->method('save')->with($record);
        $this->repository->expects($this->never())->method('getByCustomerId');

        $this->updater->recalculate($customerId);
    }

    public function testRecalculateWithZeroRevenueWhenNoOrdersFound(): void
    {
        $customerId = 2;

        $this->connection->expects($this->once())
            ->method('fetchOne')
            ->willReturn(false);

        $record = $this->createMock(LifetimeRevenue::class);
        $record->expects($this->once())->method('setCustomerId')->with($customerId);
        $record->expects($this->once())->method('setCustomerLifetimeRevenue')->with(0.0);

        $this->factory->expects($this->once())->method('create')->willReturn($record);
        $this->repository->expects($this->once())->method('save')->with($record);

        $this->updater->recalculate($customerId);
    }
}
