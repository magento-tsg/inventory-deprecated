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
class ProductQtyRemainingChecker
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
     * Is salable quantity available for displaying.
     *
     * @param string $productSku
     * @param int $stockId
     * @return float|null
     */
    public function getProductQtyRemainingBySku(string $productSku, int $stockId): ?float
    {
        $productSalableQty = $this->getProductSalableQty->execute($productSku, $stockId);

        if ($productSalableQty > 0 && $productSalableQty <= $this->stockItemConfig->getStockThresholdQty()) {
            return $productSalableQty;
        }

        return null;
    }
}
