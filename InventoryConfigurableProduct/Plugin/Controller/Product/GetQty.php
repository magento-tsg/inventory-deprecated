<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\InventoryConfigurableProduct\Plugin\Controller\Product;

use Magento\ConfigurableProduct\Controller\Product\GetQty as Subject;
use Magento\InventoryConfigurableProduct\Model\ProductQtyLeftChecker;
use Magento\InventorySalesApi\Api\Data\SalesChannelInterface;
use Magento\InventorySalesApi\Api\StockResolverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Plugin for configurable product option qty verification controller.
 */
class GetQty
{
    /**
     * @var ResultFactory
     */
    private $resultPageFactory;

    /**
     * @var ProductQtyLeftChecker
     */
    private $productQtyLeftChecker;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StockResolverInterface
     */
    private $stockResolver;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param ResultFactory $resultPageFactory
     * @param ProductQtyLeftChecker $productQtyLeftChecker
     * @param StoreManagerInterface $storeManager
     * @param StockResolverInterface $stockResolver
     * @param ProductRepositoryInterface $productRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        ResultFactory $resultPageFactory,
        ProductQtyLeftChecker $productQtyLeftChecker,
        StoreManagerInterface $storeManager,
        StockResolverInterface $stockResolver,
        ProductRepositoryInterface $productRepository,
        LoggerInterface $logger
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->productQtyLeftChecker = $productQtyLeftChecker;
        $this->storeManager = $storeManager;
        $this->stockResolver = $stockResolver;
        $this->productRepository = $productRepository;
        $this->logger = $logger;
    }

    /**
     * Get configurable product option qty by sku
     *
     * @param Subject $subject
     * @return Json
     */
    public function aroundExecute(Subject $subject)
    {
        $productId = (int)$subject->getRequest()->getParam('id');
        $response = [
            'errors' => false,
            'qty' => null
        ];

        try {
            if ($productId) {
                $websiteCode = $this->storeManager->getWebsite()->getCode();
                $stockId = (int)$this->stockResolver
                    ->execute(SalesChannelInterface::TYPE_WEBSITE, $websiteCode)->getStockId();
                $optionSku = $this->productRepository->getById($productId)->getSku();
                $response['qty'] = $this->productQtyLeftChecker->getProductQtyLeftBySku($optionSku, $stockId);
            }
        } catch (NoSuchEntityException | LocalizedException $exception) {
            $this->logger->error($exception->getMessage());
            $response['errors'] = true;
        }

        /** @var Json $resultJson */
        $resultJson = $this->resultPageFactory->create(ResultFactory::TYPE_JSON);

        return $resultJson->setData($response);
    }
}
