<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\Inventory\Model\ResourceModel;

use Magento\Framework\App\ResourceConnection;
use Magento\Inventory\Model\ResourceModel\SourceItem;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Inventory\Model\ResourceModel\StockSourceLink;
use Magento\InventoryApi\Api\Data\StockSourceLinkInterface;

/**
 * Get all stocks Ids for this product.
 */
class GetAssignedStockIdsBySku
{
    /**
     * @var ResourceConnection
     */
    private $resource;

    /**
     * @param ResourceConnection $resource
     */
    public function __construct(
        ResourceConnection $resource
    ) {
        $this->resource = $resource;
    }

    /**
     * @param string $productSku
     * @return array
     */
    public function execute(string $productSku): array
    {
        $connection = $this->resource->getConnection();
        $sourceItemTable = $this->resource->getTableName(SourceItem::TABLE_NAME_SOURCE_ITEM);
        $stockSourceLinkTable = $this->resource->getTableName(StockSourceLink::TABLE_NAME_STOCK_SOURCE_LINK);

        $select = $connection->select()
            ->from(
                ['source_item' => $sourceItemTable],
                []
            )->join(
                ['stock_source_link' => $stockSourceLinkTable],
                'source_item.'. SourceItemInterface::SOURCE_CODE .' = stock_source_link.'
                . StockSourceLinkInterface::SOURCE_CODE,
                [StockSourceLinkInterface::STOCK_ID]
            )->where(
                'source_item.' . SourceItemInterface::SKU . ' = ?',
                $productSku
            )->distinct(true);

        return $connection->fetchCol($select);
    }
}
