<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryInStorePickupQuote\Model;

use Magento\InventoryInStorePickup\Model\ExtractPickupLocationShippingAddressData;
use Magento\InventoryInStorePickupApi\Api\Data\PickupLocationInterface;
use Magento\Quote\Api\Data\AddressInterface;

/**
 * Check if provided Shipping Address is address of Pickup Location.
 */
class IsPickupLocationShippingAddress
{
    /**
     * @var ExtractPickupLocationShippingAddressData
     */
    private $extractPickupLocationShippingAddressData;

    /**
     * @var ExtractQuoteAddressShippingAddressData
     */
    private $extractQuoteAddressShippingAddressData;

    /**
     * @var BuildShippingAddressData
     */
    private $buildShippingAddressData;

    /**
     * @param ExtractPickupLocationShippingAddressData $extractPickupLocationShippingAddressData
     * @param ExtractQuoteAddressShippingAddressData $extractQuoteAddressShippingAddressData
     * @param BuildShippingAddressData $buildShippingAddressData
     */
    public function __construct(
        ExtractPickupLocationShippingAddressData $extractPickupLocationShippingAddressData,
        ExtractQuoteAddressShippingAddressData $extractQuoteAddressShippingAddressData,
        BuildShippingAddressData $buildShippingAddressData
    ) {
        $this->extractPickupLocationShippingAddressData = $extractPickupLocationShippingAddressData;
        $this->extractQuoteAddressShippingAddressData = $extractQuoteAddressShippingAddressData;
        $this->buildShippingAddressData = $buildShippingAddressData;
    }

    /**
     * Check if Address is Pickup Location address.
     *
     * @param PickupLocationInterface $pickupLocation
     * @param AddressInterface $shippingAddress
     *
     * @return bool
     */
    public function execute(PickupLocationInterface $pickupLocation, AddressInterface $shippingAddress): bool
    {
        $data = $this->buildShippingAddressData->execute(
            $this->extractPickupLocationShippingAddressData->execute($pickupLocation)
        );

        if (!$shippingAddress->getExtensionAttributes() ||
            !$shippingAddress->getExtensionAttributes()->getPickupLocationCode()
        ) {
            return false;
        }

        $shippingAddressData = $this->extractQuoteAddressShippingAddressData->execute($shippingAddress);

        foreach ($data as $key => $value) {
            if (!array_key_exists($key, $shippingAddressData) || $shippingAddressData[$key] != $value) {
                return false;
            }
        }

        return true;
    }
}
