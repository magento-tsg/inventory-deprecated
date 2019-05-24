<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfiguration\Test\Integration;

use Magento\InventoryConfigurationApi\Api\Data\SourceItemConfigurationInterface;
use Magento\InventoryConfigurationApi\Api\GetBackorderStatusConfigurationValueInterface;
use Magento\InventoryConfigurationApi\Api\SetBackorderStatusConfigurationValueInterface;
use Magento\TestFramework\Helper\Bootstrap;
use PHPUnit\Framework\TestCase;

class GetBackorderStatusConfigurationValueTest extends TestCase
{
    /**
     * @var SetBackorderStatusConfigurationValueInterface
     */
    private $setBackorderStatusConfigurationValue;

    /**
     * @var GetBackorderStatusConfigurationValueInterface
     */
    private $getBackorderStatusConfigurationValue;

    protected function setUp()
    {
        parent::setUp();

        $this->setBackorderStatusConfigurationValue = Bootstrap::getObjectManager()->get(
            SetBackorderStatusConfigurationValueInterface::class
        );

        $this->getBackorderStatusConfigurationValue = Bootstrap::getObjectManager()->get(
            GetBackorderStatusConfigurationValueInterface::class
        );
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForSourceItem()
    {
        $this->setBackorderStatusConfigurationValue->forSourceItem(
            'SKU-1',
            'eu-1',
            SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY
        );
        $backorders = $this->getBackorderStatusConfigurationValue->forSourceItem('SKU-1', 'eu-1');
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY, $backorders);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForSourceItemWithFallbackOnSource()
    {
        $this->setBackorderStatusConfigurationValue->forSourceItem('SKU-1', 'eu-1', null);
        $this->setBackorderStatusConfigurationValue->forSource(
            'eu-1',
            SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY
        );
        $backorders = $this->getBackorderStatusConfigurationValue->forSourceItem('SKU-1', 'eu-1');
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY, $backorders);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForSourceItemWithFallbackOnGlobal()
    {
        $this->setBackorderStatusConfigurationValue->forSourceItem('SKU-1', 'eu-1', null);
        $this->setBackorderStatusConfigurationValue->forSource('eu-1', null);
        $this->setBackorderStatusConfigurationValue->forGlobal(SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY);
        $backorders = $this->getBackorderStatusConfigurationValue->forSourceItem('SKU-1', 'eu-1');
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY, $backorders);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForSource()
    {
        $this->setBackorderStatusConfigurationValue->forSource(
            'eu-1',
            SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY
        );
        $backorders = $this->getBackorderStatusConfigurationValue->forSource('eu-1');
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY, $backorders);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForSourceWithFallbackOnGlobal()
    {
        $this->setBackorderStatusConfigurationValue->forSource('eu-1', null);
        $this->setBackorderStatusConfigurationValue->forGlobal(SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY);
        $backorders = $this->getBackorderStatusConfigurationValue->forSource('eu-1');
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NOTIFY, $backorders);
    }

    /**
     * @magentoDbIsolation enabled
     */
    public function testForGlobal()
    {
        $this->setBackorderStatusConfigurationValue->forGlobal(SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY);
        $backorders = $this->getBackorderStatusConfigurationValue->forGlobal();
        self::assertEquals(SourceItemConfigurationInterface::BACKORDERS_YES_NONOTIFY, $backorders);
    }
}