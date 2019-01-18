<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Model;

use Magento\Framework\App\ObjectManager;
use Magento\InventorySalesApi\Model\GetSkuFromOrderItemInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Model\GetInventoryRequestFromOrderBuilder;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\Sales\Api\Data\InvoiceInterface;
use Magento\Sales\Api\Data\InvoiceItemInterface;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionResultInterface;

/**
 * Creates instance of InventoryRequestInterface by given InvoiceInterface object.
 * Only virtual type items will be used.
 */
class GetSourceSelectionResultFromInvoice
{
    /**
     * @var GetSkuFromOrderItemInterface
     */
    private $getSkuFromOrderItem;

    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetInventoryRequestFromOrderBuilder
     */
    private $getInventoryRequestFromOrderBuilder;

    /**
     * GetSourceSelectionResultFromInvoice constructor.
     *
     * @param GetSkuFromOrderItemInterface $getSkuFromOrderItem
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetInventoryRequestFromOrderBuilder|null $getInventoryRequestFromOrderBuilder
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(
        GetSkuFromOrderItemInterface $getSkuFromOrderItem,
        ItemRequestInterfaceFactory $itemRequestFactory,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetInventoryRequestFromOrderBuilder $getInventoryRequestFromOrderBuilder = null
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getSkuFromOrderItem = $getSkuFromOrderItem;
        $this->getInventoryRequestFromOrderBuilder = $getInventoryRequestFromOrderBuilder ?:
            ObjectManager::getInstance()->get(GetInventoryRequestFromOrderBuilder::class);
    }

    /**
     * Get source selection result from invoice
     *
     * @param InvoiceInterface $invoice
     * @return SourceSelectionResultInterface
     */
    public function execute(InvoiceInterface $invoice): SourceSelectionResultInterface
    {
        $order = $invoice->getOrder();
        $websiteId = (int) $order->getStore()->getWebsiteId();
        $stockId = (int) $this->stockByWebsiteIdResolver->execute($websiteId)->getStockId();

        $selectionAlgorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        $inventoryRequestBuilder = $this->getInventoryRequestFromOrderBuilder->execute($selectionAlgorithmCode);

        $inventoryRequest = $inventoryRequestBuilder->execute(
            $stockId,
            (int) $order->getEntityId(),
            $this->getSelectionRequestItems($invoice->getItems())
        );

        $selectionAlgorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();
        return $this->sourceSelectionService->execute($inventoryRequest, $selectionAlgorithmCode);
    }

    /**
     * Get selection request items
     *
     * @param InvoiceItemInterface[]|iterable $invoiceItems
     * @return array
     */
    private function getSelectionRequestItems(iterable $invoiceItems): array
    {
        $selectionRequestItems = [];
        foreach ($invoiceItems as $invoiceItem) {
            $orderItem = $invoiceItem->getOrderItem();

            if ($orderItem->isDummy() || !$orderItem->getIsVirtual()) {
                continue;
            }

            $itemSku = $this->getSkuFromOrderItem->execute($orderItem);
            $qty = (int)$invoiceItem->getQty();

            if ($invoiceItem->getOrderItem()->getIsQtyDecimal()) {
                $qty = (float)$invoiceItem->getQty();
            }

            $qty = $qty > 0 ? $qty : 0;

            $selectionRequestItems[] = $this->itemRequestFactory->create([
                'sku' => $itemSku,
                'qty' => $qty,
            ]);
        }

        return $selectionRequestItems;
    }
}
