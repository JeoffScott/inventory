<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShippingAdminUi\Ui\DataProvider;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryShippingAdminUi\Model\SourceSelectionResultToArray;
use Magento\InventorySourceSelectionApi\Api\Data\ItemRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\Data\InventoryRequestInterfaceFactory;
use Magento\InventorySourceSelectionApi\Api\SourceSelectionServiceInterface;
use Magento\InventorySourceSelectionApi\Api\GetDefaultSourceSelectionAlgorithmCodeInterface;
use Magento\InventoryApi\Api\SourceRepositoryInterface;

/**
 * Class GetSourcesByStockIdSkuAndQty
 * @package Magento\InventoryShippingAdminUi\Ui\DataProvider
 * @deprecated (see \Magento\InventoryShippingAdminUi\Ui\DataProvider\GetSourcesByOrderIdStockIdSkuAndQty)
 */
class GetSourcesByStockIdSkuAndQty
{
    /**
     * @var ItemRequestInterfaceFactory
     */
    private $itemRequestFactory;

    /**
     * @var InventoryRequestInterfaceFactory
     */
    private $inventoryRequestFactory;

    /**
     * @var SourceSelectionServiceInterface
     */
    private $sourceSelectionService;

    /**
     * @var GetDefaultSourceSelectionAlgorithmCodeInterface
     */
    private $getDefaultSourceSelectionAlgorithmCode;

    /**
     * @var SourceRepositoryInterface
     */
    private $sourceRepository;

    /**
     * @var SourceSelectionResultToArray
     */
    private $sourceSelectionResultToArray;

    /**
     * GetSourcesByStockIdSkuAndQty constructor.
     *
     * @param ItemRequestInterfaceFactory $itemRequestFactory
     * @param InventoryRequestInterfaceFactory $inventoryRequestFactory
     * @param SourceSelectionServiceInterface $sourceSelectionService
     * @param GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode
     * @param SourceRepositoryInterface $sourceRepository
     * @param SourceSelectionResultToArray $sourceSelectionResultToArray
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function __construct(
        ItemRequestInterfaceFactory $itemRequestFactory,
        InventoryRequestInterfaceFactory $inventoryRequestFactory,
        SourceSelectionServiceInterface $sourceSelectionService,
        GetDefaultSourceSelectionAlgorithmCodeInterface $getDefaultSourceSelectionAlgorithmCode,
        SourceRepositoryInterface $sourceRepository,
        SourceSelectionResultToArray $sourceSelectionResultToArray
    ) {
        $this->itemRequestFactory = $itemRequestFactory;
        $this->inventoryRequestFactory = $inventoryRequestFactory;
        $this->sourceSelectionService = $sourceSelectionService;
        $this->getDefaultSourceSelectionAlgorithmCode = $getDefaultSourceSelectionAlgorithmCode;
        $this->sourceRepository = $sourceRepository;
        $this->sourceSelectionResultToArray = $sourceSelectionResultToArray;
    }

    /**
     * @param int $stockId
     * @param string $sku
     * @param float $qty
     * @return array
     * @throws NoSuchEntityException
     * @SuppressWarnings(PHPMD.LongVariable)
     */
    public function execute(int $stockId, string $sku, float $qty): array
    {
        $algorithmCode = $this->getDefaultSourceSelectionAlgorithmCode->execute();

        $requestItem = $this->itemRequestFactory->create([
            'sku' => $sku,
            'qty' => $qty
        ]);
        $inventoryRequest = $this->inventoryRequestFactory->create([
            'stockId' => $stockId,
            'items' => [$requestItem]
        ]);

        return $this->sourceSelectionResultToArray->execute($inventoryRequest, $algorithmCode);
    }
}
