<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryShipping\Plugin\Sales\ResourceModel\Order\Shipment;

use Magento\InventoryShipping\Model\ResourceModel\ShipmentSource\SaveShipmentSource;
use Magento\Sales\Model\ResourceModel\Order\Shipment as ShipmentResource;
use Magento\Sales\Model\Order\Shipment;

class SaveSourceForShipmentPlugin
{
    /**
     * @var SaveShipmentSource
     */
    private $saveShipmentSource;

    /**
     * @param SaveShipmentSource $saveShipmentSource
     */
    public function __construct(
        SaveShipmentSource $saveShipmentSource
    ) {
        $this->saveShipmentSource = $saveShipmentSource;
    }

    /**
     * @param ShipmentResource $subject
     * @param ShipmentResource $result
     * @param Shipment $shipment
     * @return ShipmentResource
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterSave(
        ShipmentResource $subject,
        ShipmentResource $result,
        Shipment $shipment) {
        if ($shipment->getExtensionAttributes() && $shipment->getExtensionAttributes()->getSourceCode()) {
            $sourceCode = $shipment->getExtensionAttributes()->getSourceCode();
            $this->saveShipmentSource->execute((int)$shipment->getId(), $sourceCode);
        }
        return $result;
    }
}
