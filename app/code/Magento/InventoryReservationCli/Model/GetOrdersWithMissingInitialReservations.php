<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryReservationCli\Model;

use Magento\Framework\Serialize\SerializerInterface;
use Magento\InventoryReservationCli\Model\ResourceModel\GetReservationsList;
use Magento\Sales\Api\Data\OrderInterface;

/**
 * Filter orders for missing initial reservation
 */
class GetOrdersWithMissingInitialReservations
{
    /**
     * @var GetReservationsList
     */
    private $getReservationsList;

    /**
     * @var SerializerInterface
     */
    private $serialize;

    /**
     * @param GetReservationsList $getReservationsList
     * @param SerializerInterface $serialize
     */
    public function __construct(
        GetReservationsList $getReservationsList,
        SerializerInterface $serialize
    ) {
        $this->getReservationsList = $getReservationsList;
        $this->serialize = $serialize;
    }

    /**
     * Get list of reservations for Order entity.
     *
     * @param OrderInterface[] $orders
     * @return array
     */
    public function execute(array $orders): array
    {
        $entityIdAndSkuList = $this->getEntityIdAndSkuList($orders);

        $reservationList = $this->getReservationsList->execute();
        foreach ($reservationList as $reservation) {
            $metadata = $this->serialize->unserialize($reservation['metadata']);
            $objectId = $metadata['object_id'];
            $eventType = $metadata['event_type'];

            if ($eventType === 'order_placed' && in_array($objectId, array_keys($entityIdAndSkuList))) {
                $entityIdAndSkuList = $this->filterReservedSkus((int)$objectId, $entityIdAndSkuList, $reservation);
            }
        }

        $entityIdAndSkuList = array_filter($entityIdAndSkuList, function ($order) {
            return !empty($order['skus']);
        });

        return $entityIdAndSkuList;
    }

    /**
     * @param int $objectId
     * @param array $entityIdAndSkuList
     * @param array $reservation
     * @return array
     */
    private function filterReservedSkus(int $objectId, array $entityIdAndSkuList, array $reservation): array
    {
        $reservedSku = $reservation['sku'];
        if (!in_array($reservedSku, array_keys($entityIdAndSkuList[$objectId]['skus']))) {
            return $entityIdAndSkuList;
        }

        $reservedQuantity = $reservation['quantity'];
        $entityIdAndSkuList[$objectId]['skus'][$reservedSku] += (float)$reservedQuantity;

        $entityIdAndSkuList[$objectId]['skus'] = array_filter($entityIdAndSkuList[$objectId]['skus']);

        return $entityIdAndSkuList;
    }

    /**
     * @param OrderInterface[] $orders
     * @return array
     */
    private function getEntityIdAndSkuList(array $orders): array
    {
        $list = [];
        foreach ($orders as $order) {
            $entityId = $order->getEntityId();
            $list[$entityId] = [
                'increment_id' => $order->getIncrementId(),
                'skus' => []
            ];
            foreach ($order->getItems() as $item) {
                $list[$entityId]['skus'][$item->getSku()] = (float)$item->getQtyOrdered();
            }
        }
        return $list;
    }
}
