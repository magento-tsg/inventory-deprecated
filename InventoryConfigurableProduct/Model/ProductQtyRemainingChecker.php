<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Model;

use Magento\InventoryConfigurationApi\Api\Data\StockItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetStockItemConfigurationInterface;
use Magento\InventorySalesApi\Api\GetProductSalableQtyInterface;

/**
 * Checks product qty by product sku and stock id.
 */
class ProductQtyRemainingChecker
{
    /**
     * @var GetProductSalableQtyInterface
     */
    private $getProductSalableQty;

    /**
     * @var GetStockItemConfigurationInterface
     */
    private $getStockItemConfiguration;

    /**
     * @param GetProductSalableQtyInterface $getProductSalableQty
     * @param GetStockItemConfigurationInterface $getStockItemConfiguration
     */
    public function __construct(
        GetProductSalableQtyInterface $getProductSalableQty,
        GetStockItemConfigurationInterface $getStockItemConfiguration
    ) {
        $this->getProductSalableQty = $getProductSalableQty;
        $this->getStockItemConfiguration = $getStockItemConfiguration;
    }

    /**
     * Get salable qty if it is possible.
     *
     * @param string $productSku
     * @param int $stockId
     * @return float|null
     */
    public function getProductQtyRemainingBySku(string $productSku, int $stockId): ?float
    {
        $productSalableQty = $this->getProductSalableQty->execute($productSku, $stockId);
        $stockItemConfiguration = $this->getStockItemConfiguration->execute($productSku, $stockId);

        return $this->isSalableQtyDisplayable($stockItemConfiguration, $productSalableQty) ? $productSalableQty : null;
    }

    /**
     * Is salable quantity available for displaying.
     *
     * @param StockItemConfigurationInterface $stockItemConfiguration
     * @param float $productSalableQty
     * @return bool
     */
    private function isSalableQtyDisplayable(
        StockItemConfigurationInterface $stockItemConfiguration,
        float $productSalableQty
    ): bool {
        return ($stockItemConfiguration->getBackorders() === StockItemConfigurationInterface::BACKORDERS_NO
                || $stockItemConfiguration->getMinQty() < 0)
            && $productSalableQty > 0
            && $productSalableQty <= $stockItemConfiguration->getStockThresholdQty();
    }
}
