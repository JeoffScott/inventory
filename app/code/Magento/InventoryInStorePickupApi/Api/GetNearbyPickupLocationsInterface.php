<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupApi\Api;

use Magento\InventoryInStorePickupApi\Api\Data\SearchCriteria\GetNearbyLocationsCriteriaInterface;

/**
 * Find nearest Pickup Locations by requested address, radius, and affiliation to Sales Channel.
 * Default locations sort order - ascending distance to request address.
 *
 * @api
 */
interface GetNearbyPickupLocationsInterface
{
    /**
     * @param GetNearbyLocationsCriteriaInterface $searchCriteria
     * @param string $salesChannelType
     * @param string $salesChannelCode
     *
     * @return \Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface[]
     */
    public function execute(
        GetNearbyLocationsCriteriaInterface $searchCriteria,
        string $salesChannelType,
        string $salesChannelCode
    ): array;
}
