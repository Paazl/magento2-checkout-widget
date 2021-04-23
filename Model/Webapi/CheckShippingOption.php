<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Paazl\CheckoutWidget\Api\Data\CheckQuoteResultInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProviderFactory;
use Paazl\CheckoutWidget\Model\Quote\CheckQuoteShippingOption;
use Paazl\CheckoutWidget\Model\Webapi\CheckShippingOption\Result;
use Paazl\CheckoutWidget\Model\Webapi\CheckShippingOption\ResultFactory;
use Paazl\CheckoutWidget\Api\Webapi\CheckShippingOptionInterface;

class CheckShippingOption implements CheckShippingOptionInterface
{
    /**
     * @var CheckQuoteShippingOption
     */
    private $checkQuoteShippingOption;

    /**
     * @var CartRepositoryInterface
     */
    private $cartRepository;

    /**
     * @var CheckShippingOption\ResultFactory
     */
    private $resultFactory;

    /**
     * @var CartTotalRepositoryInterface
     */
    private $totalRepository;

    /**
     * @var WidgetConfigProvider
     */
    private $widgetConfigProviderFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * CheckShippingOption constructor.
     *
     * @param CheckQuoteShippingOption          $checkQuoteShippingOption
     * @param CartRepositoryInterface           $cartRepository
     * @param CheckShippingOption\ResultFactory $resultFactory
     * @param CartTotalRepositoryInterface      $totalRepository
     * @param WidgetConfigProviderFactory       $widgetConfigProviderFactory
     * @param Json                              $json
     * @param General                           $generalHelper
     */
    public function __construct(
        CheckQuoteShippingOption $checkQuoteShippingOption,
        CartRepositoryInterface $cartRepository,
        ResultFactory $resultFactory,
        CartTotalRepositoryInterface $totalRepository,
        WidgetConfigProviderFactory $widgetConfigProviderFactory,
        Json $json,
        General $generalHelper
    ) {
        $this->checkQuoteShippingOption = $checkQuoteShippingOption;
        $this->cartRepository = $cartRepository;
        $this->resultFactory = $resultFactory;
        $this->totalRepository = $totalRepository;
        $this->widgetConfigProviderFactory = $widgetConfigProviderFactory;
        $this->json = $json;
        $this->generalHelper = $generalHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        $quote = $this->cartRepository->getActive($cartId);
        $checkResult = $this->checkQuoteShippingOption->validate($quote);

        /** @var Result $result */
        $result = $this->resultFactory->create();

        if ($checkResult->getReloadWidget()) {
            $result->setReloadWidget(true);
        }

        if ($checkResult->getRecollectTotals()) {
            $quote->collectTotals();
            $this->cartRepository->save($quote);
            $result->setTotals($this->totalRepository->get($cartId));
        }

        try {
            /** @var WidgetConfigProvider $config */
            $config = $this->widgetConfigProviderFactory->create();
            $config->setQuote($quote);
            $result->setReloadWidgetConfigJson($this->json->serialize($config->getConfig()));
        } catch (LocalizedException $e) {
            $this->generalHelper->addTolog('exception', $e->getLogMessage());
        }

        return $result;
    }
}
