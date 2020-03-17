<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Test\Integration\Plugin\Controller\Product;

use Magento\CatalogInventory\Model\Configuration;
use Magento\Config\Model\ResourceModel\Config;
use Magento\Framework\App\Config\ReinitableConfigInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\TestFramework\TestCase\AbstractController;

/**
 * Test configurable option qty
 */
class GetQtyTest extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private $json;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @inheritdoc
     */
    protected function setUp()
    {
        parent::setUp();

        $this->json = $this->_objectManager->get(SerializerInterface::class);
        $this->storeManager = $this->_objectManager->get(StoreManagerInterface::class);
        $this->config = $this->_objectManager->get(Config::class);
        $this->_objectManager->get(ReinitableConfigInterface::class)->reinit();
    }

    /**
     * Test for configurable product option qty verification
     *
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/websites_with_stores.php
     * @magentoDataFixture Magento/ConfigurableProduct/_files/configurable_attribute.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/product_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/sources.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stocks.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryApi/Test/_files/stock_source_links.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/source_items_configurable.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryConfigurableProduct/Test/_files/set_product_configurable_out_of_stock.php
     * @magentoDataFixture ../../../../app/code/Magento/InventorySalesApi/Test/_files/stock_website_sales_channels.php
     * @magentoDataFixture ../../../../app/code/Magento/InventoryIndexer/Test/_files/reindex_inventory.php
     * @magentoDbIsolation disabled
     * @dataProvider ajaxQtyDataProvider
     * @param int|null $expectedQty
     * @param int|null $productId
     * @param string|null $storeCode
     * @param int $thresholdQty
     */
    public function testExecute(?int $expectedQty, ?int $productId, ?string $storeCode, int $thresholdQty = 100): void
    {
        $this->config->saveConfig(Configuration::XML_PATH_STOCK_THRESHOLD_QTY, $thresholdQty);
        $this->storeManager->setCurrentStore($storeCode);
        $this->getRequest()->setParam('id', $productId);

        $this->dispatch('catalog/product/getQty');
        $actual = $this->json->unserialize($this->getResponse()->getBody());

        $this->assertEquals($expectedQty, $actual['qty']);
    }

    /**
     * DataProvider for testExecute()
     *
     * @return array
     */
    public function ajaxQtyDataProvider(): array
    {
        return [
            'missing_product_id' => [null, null, null],
            'product_with_zero_qty' => [null, 10, 'store_for_us_website'],
            '100_products_in_stock' => [100, 20, 'store_for_us_website'],
            'zero_qty_for_given_stock' => [null, 20, 'store_for_eu_website'],
            'stock_greater_than_threshold' => [null, 20, 'store_for_us_website', 99],
        ];
    }
}
