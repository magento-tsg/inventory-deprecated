<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Checks product qty by product sku and stock id.
 */
class ProductQtyLeftChecker
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var StockItemConfigurationInterface
     */
    private $stockItemConfig;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param StockItemConfigurationInterface $stockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        StockItemConfigurationInterface $stockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->stockItemConfig = $stockItemConfiguration;
    }

    /**
     * Get salable qty if it is possible.
     *
     * @param string $productSku
     * @param int $stockId
     * @return float|null
     */
    public function getProductQtyLeftBySku(string $productSku, int $stockId): ?float
    {
        $productSalableQty = $this->getProductSalableQty->execute($productSku, $stockId);
        if ($this->isSalableQtyAvailableForDisplaying((float)$productSalableQty)) {
            return $productSalableQty;
        }

        return null;
    }

    /**
     * Is salable quantity available for displaying.
     *
     * @param float $productSalableQty
     * @return bool
     */
    public function isSalableQtyAvailableForDisplaying(float $productSalableQty): bool
    {
        return ($this->stockItemConfig->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO
                || $this->stockItemConfig->getBackorders() !== StockItemConfigurationInterface::BACKORDERS_NO
                && $this->stockItemConfig->getMinQty() < 0)
            && bccomp((string)$productSalableQty, (string)$this->stockItemConfig->getStockThresholdQty(), 12) !== 1
            && $productSalableQty > 0;
    }
}
