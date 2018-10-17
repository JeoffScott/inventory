<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventorySourceSelectionApi\Api\Data;

use Magento\Framework\Api\ExtensibleDataInterface;

/**
 * Data Interface representing particular Source Selection Algorithm
 *
 * @api
 */
interface SourceSelectionAlgorithmInterface extends ExtensibleDataInterface
{
    /**
     * @return string
     */
    public function getCode(): string;

    /**
     * @return string
     */
    public function getTitle(): string;

    /**
     * @return string
     */
    public function getDescription(): string;

    /**
     * Retrieve existing extension attributes object
     *
     * @return \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmExtensionInterface|null
     */
    public function getExtensionAttributes(): ?SourceSelectionAlgorithmExtensionInterface;

    /**
     * Set an extension attributes object
     *
     * @codingStandardsIgnoreStart
     * @param \Magento\InventorySourceSelectionApi\Api\Data\SourceSelectionAlgorithmExtensionInterface $extensionAttributes
     * @codingStandardsIgnoreEnd
     * @return void
     */
    public function setExtensionAttributes(SourceSelectionAlgorithmExtensionInterface $extensionAttributes): void;
}
