<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurationAdminUi\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;
use Magento\CatalogInventory\Ui\DataProvider\Product\Form\Modifier\AdvancedInventory as AdvancedInventoryModifier;

/**
 * Remove "Notify For Quantity Below" field from "Advanced Inventory" modal panel.
 */
class NotifyForQtyBelow extends AbstractModifier
{
    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * @param ArrayManager $arrayManager
     */
    public function __construct(ArrayManager $arrayManager)
    {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $stockDataPath = $this->arrayManager->findPath(
            AdvancedInventoryModifier::STOCK_DATA_FIELDS,
            $meta,
            null,
            'children'
        );

        if (null === $stockDataPath) {
            return $meta;
        }

        $backordersPath = $stockDataPath . '/children/container_notify_stock_qty/arguments/data/config';
        $meta = $this->arrayManager->set(
            $backordersPath,
            $meta,
            [
                'visible' => 0,
                'imports' => '',
            ]
        );

        return $meta;
    }
}