<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryCatalogConfiguration\Plugin\CatalogInventory\Model\ResourceModel\Stock;

use Magento\CatalogInventory\Model\ResourceModel\Stock\Item as ItemResourceModel;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractModel;
use Magento\InventoryCatalogApi\Model\GetSkusByProductIdsInterface;
use Magento\InventoryCatalogConfiguration\Model\SaveSourceItemConfiguration;
use Magento\InventoryCatalogConfiguration\Model\SaveStockItemConfiguration;
use Magento\InventoryCatalogApi\Api\DefaultStockProviderInterface;
use Magento\Framework\App\ResourceConnection;

/**
 * Save inventory configuration for given product and default source/stock after stock item was saved successfully.
 */
class SaveInventoryConfigurationPlugin
{
    /**
     * @var GetSkusByProductIdsInterface
     */
    private $getSkusByProductIds;

    /**
     * @var DefaultStockProviderInterface
     */
    private $defaultStockProvider;

    /**
     * @var SaveSourceItemConfiguration
     */
    private $saveSourceItemConfiguration;

    /**
     * @var SaveStockItemConfiguration
     */
    private $saveStockItemConfiguration;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @param GetSkusByProductIdsInterface $getSkusByProductIds
     * @param DefaultStockProviderInterface $defaultStockProvider
     * @param SaveSourceItemConfiguration $saveSourceItemConfiguration
     * @param SaveStockItemConfiguration $saveStockItemConfiguration
     * @param ResourceConnection $resourceConnection
     */
    public function __construct(
        GetSkusByProductIdsInterface $getSkusByProductIds,
        DefaultStockProviderInterface $defaultStockProvider,
        SaveSourceItemConfiguration $saveSourceItemConfiguration,
        SaveStockItemConfiguration $saveStockItemConfiguration,
        ResourceConnection $resourceConnection
    ) {
        $this->getSkusByProductIds = $getSkusByProductIds;
        $this->defaultStockProvider = $defaultStockProvider;
        $this->saveSourceItemConfiguration = $saveSourceItemConfiguration;
        $this->saveStockItemConfiguration = $saveStockItemConfiguration;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param ItemResourceModel $subject
     * @param callable $proceed
     * @param AbstractModel $stockItem
     * @return ItemResourceModel
     * @throws \Exception
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function aroundSave(
        ItemResourceModel $subject,
        callable $proceed,
        AbstractModel $stockItem
    ): ItemResourceModel {
        $connection = $this->resourceConnection->getConnection();
        $connection->beginTransaction();
        try {
            $proceed($stockItem);

            if ($stockItem->getStockId() !== $this->defaultStockProvider->getId()) {
                throw new LocalizedException(__('error'));
            }

            $productId = $stockItem->getProductId();
            $skus = $this->getSkusByProductIds->execute([$productId]);
            $productSku = $skus[$productId];

            $this->saveSourceItemConfiguration->execute($productSku, $stockItem);

            $this->saveStockItemConfiguration->execute($productSku, $stockItem);

            $connection->commit();

            return $subject;
        } catch (\Exception $e) {
            $connection->rollBack();
            throw $e;
        }
    }
}
