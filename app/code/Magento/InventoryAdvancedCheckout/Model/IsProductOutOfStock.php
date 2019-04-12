<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryAdvancedCheckout\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\IsProductSalableInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;

class IsProductOutOfStock implements \Magento\AdvancedCheckout\Model\IsProductOutOfStockInterface
{
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var IsProductSalableInterface
     */
    private $isProductSalable;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var WebsiteRepositoryInterface
     */
    private $websiteRepository;

    /**
     * IsProductOutOfStock constructor
     *
     * @param ProductRepositoryInterface $productRepository
     * @param IsProductSalableInterface $isProductSalable
     * @param StockResolverInterface $stockResolver
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        IsProductSalableInterface $isProductSalable,
        StockResolverInterface $stockResolver,
        WebsiteRepositoryInterface $websiteRepository
    ) {
        $this->productRepository = $productRepository;
        $this->isProductSalable = $isProductSalable;
        $this->stockResolver = $stockResolver;
        $this->websiteRepository = $websiteRepository;
    }

    /**
     * Get is product out of stock for given Product id in a given Website id in MSI contest
     *
     * @param int $productId
     * @param int $websiteId
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute(int $productId, int $websiteId): bool
    {
        $product = $this->productRepository->getById($productId);

        $website = $this->websiteRepository->getById($websiteId);

        $stock = $this->stockResolver->execute(SalesChannelInterface::TYPE_WEBSITE, $website->getCode());

        return !$this->isProductSalable->execute($product->getSku(), $stock->getStockId());
    }
}
