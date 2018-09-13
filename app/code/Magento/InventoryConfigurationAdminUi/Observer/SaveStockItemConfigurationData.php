<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationAdminUi\Observer;

use Magento\Framework\Api\SimpleDataObjectConverter;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\InventoryConfigurationAdminUi\Model\ResourceModel\GetStockIdsBySourceCode;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterfaceFactory;
use Magento\InventoryConfigurationApi\Api\SaveSourceConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\SaveStockConfigurationInterface;

/**
 * Save stock item configuration for given product.
 */
class SaveStockItemConfigurationData implements ObserverInterface
{
    /**
     * @var StockItemConfigurationInterfaceFactory
     */
    private $stockItemConfigurationFactory;

    /**
     * @var SaveSourceConfigurationInterface
     */
    private $saveStockConfiguration;

    /**
     * @var GetStockIdsBySourceCode
     */
    private $getStockIdsBySourceCode;

    /**
     * @var array
     */
    private $fields = [
        StockItemConfigurationInterface::MANAGE_STOCK => 'bool',
        StockItemConfigurationInterface::MIN_QTY => 'float',
        StockItemConfigurationInterface::STOCK_THRESHOLD_QTY => 'float',
        StockItemConfigurationInterface::MIN_SALE_QTY => 'float',
        StockItemConfigurationInterface::MAX_SALE_QTY => 'float',
        StockItemConfigurationInterface::ENABLE_QTY_INCREMENTS => 'bool',
        StockItemConfigurationInterface::QTY_INCREMENTS => 'float',
        StockItemConfigurationInterface::LOW_STOCK_DATE => 'string',

    ];

    /**
     * @param StockItemConfigurationInterfaceFactory $stockItemConfigurationFactory
     * @param SaveStockConfigurationInterface $saveStockConfiguration
     * @param GetStockIdsBySourceCode $getStockIdsBySourceCodes
     */
    public function __construct(
        StockItemConfigurationInterfaceFactory $stockItemConfigurationFactory,
        SaveStockConfigurationInterface $saveStockConfiguration,
        GetStockIdsBySourceCode $getStockIdsBySourceCodes
    ) {
        $this->stockItemConfigurationFactory = $stockItemConfigurationFactory;
        $this->saveStockConfiguration = $saveStockConfiguration;
        $this->getStockIdsBySourceCode = $getStockIdsBySourceCodes;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer): void
    {
        $sku = $observer->getProduct()->getSku();
        $request = $observer->getController()->getRequest();
        $product = $request->getParam('product', []);
        $stockData = $product['stock_data'] ?? [];

        if (!$stockData || empty($stockData['stock_id'])) {
            return;
        }

        $stockItemConfiguration = $this->stockItemConfigurationFactory->create();
        foreach ($this->fields as $field => $type) {
            $method = 'set' . SimpleDataObjectConverter::snakeCaseToUpperCamelCase($field);
            if ($stockData['use_config_' . $field]) {
                $stockItemConfiguration->$method(null);
            } else {
                $value = $this->getValue($stockData[$field], $type);
                $stockItemConfiguration->$method($value);
            }
        }

        $isQtyDecimal = (bool)$stockData[StockItemConfigurationInterface::IS_QTY_DECIMAL];
        $stockItemConfiguration->setIsQtyDecimal($isQtyDecimal);
        $isDecimalDivided = (bool)$stockData[StockItemConfigurationInterface::IS_DECIMAL_DIVIDED];
        $stockItemConfiguration->setIsDecimalDivided($isDecimalDivided);

        $this->saveStockConfiguration->forStockItem((string)$sku, (int)$stockData['stock_id'], $stockItemConfiguration);
    }

    /**
     * @param mixed $value
     * @param string $type
     * @return mixed
     */
    private function getValue($value, $type)
    {
        switch ($type) {
            case 'float':
                $value = (float)$value;
                break;
            case 'bool':
                $value = (bool)$value;
                break;
            case 'string':
                $value = (string)$value;
                break;
        }

        return $value;
    }
}
