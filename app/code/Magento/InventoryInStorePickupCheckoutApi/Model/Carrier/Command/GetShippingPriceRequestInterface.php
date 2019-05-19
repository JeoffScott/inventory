<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupCheckoutApi\Model\Carrier\Command;

use Magento\InventoryInStorePickupCheckoutApi\Api\Data\ShippingPriceRequestExtensionInterface;
use Magento\InventoryInStorePickupCheckoutApi\Api\Data\ShippingPriceRequestInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;

/**
 * Create Shipping Price Request Data Transfer Object.
 *
 * @api
 */
interface GetShippingPriceRequestInterface
{
    /**
     * @param RateRequest $rateRequest
     * @param float $defaultPrice
     * @param float $freePackages
     *
     * @param ShippingPriceRequestExtensionInterface|null $shippingPriceRequestExtension
     *
     * @return ShippingPriceRequestInterface
     */
    public function execute(
        RateRequest $rateRequest,
        float $defaultPrice,
        float $freePackages,
        ?ShippingPriceRequestExtensionInterface $shippingPriceRequestExtension = null
    ): ShippingPriceRequestInterface;
}
