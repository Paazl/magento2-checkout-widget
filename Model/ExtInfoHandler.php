<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\QuoteReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Helper\General as Helper;

/**
 * Class ExtInfoHandler
 *
 * @package Paazl\CheckoutWidget\Model
 */
class ExtInfoHandler
{

    /**
     * @var ShippingInfoFactory
     */
    private $shippingInfoFactory;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var Helper
     */
    private $helper;

    /**
     * @var QuoteReferenceRepositoryInterface
     */
    private $quoteReferenceRepository;

    /**
     * ExtInfoHandler constructor.
     *
     * @param ShippingInfoFactory               $shippingInfoFactory
     * @param Json                              $json
     * @param Helper                            $helper
     * @param QuoteReferenceRepositoryInterface $quoteReferenceRepository
     */
    public function __construct(
        ShippingInfoFactory $shippingInfoFactory,
        Json $json,
        Helper $helper,
        QuoteReferenceRepositoryInterface $quoteReferenceRepository
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->json = $json;
        $this->helper = $helper;
        $this->quoteReferenceRepository = $quoteReferenceRepository;
    }

    /**
     * @param Quote $quote
     *
     * @return \Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface|null
     */
    private function getQuoteReference(Quote $quote)
    {
        try {
            return $this->quoteReferenceRepository->getByQuoteId($quote->getId());
        } catch (NoSuchEntityException $e) {
            $this->helper->addTolog('exception', $e->getMessage());
        }

        return null;
    }

    /**
     * @param ShippingInfo $info
     * @param Quote        $quote
     */
    public function setInfoToQuote(ShippingInfo $info, Quote $quote)
    {
        try {
            $reference = $this->getQuoteReference($quote);
            if ($reference) {
                $reference->setExtShippingInfo($info->toJson());
                $this->quoteReferenceRepository->save($reference);
            }
        } catch (CouldNotSaveException $e) {
            $this->helper->addTolog('exception', $e->getMessage());
        }
    }

    /**
     * @param Quote $quote
     *
     * @return null|ShippingInfo
     */
    public function getInfoFromQuote(Quote $quote)
    {
        $reference = $this->getQuoteReference($quote);
        if (!$reference) {
            return null;
        }

        $info = $reference->getExtShippingInfo();
        if (empty($info)) {
            return null;
        }

        /** @var ShippingInfo $shippingInfo */
        $shippingInfo = $this->shippingInfoFactory->create();

        try {
            $data = $this->json->unserialize($info);
            if (is_array($data)) {
                $shippingInfo->setData($data);
            }
            return $shippingInfo;
        } catch (\Exception $e) {
            $this->helper->addTolog('exception', $e->getMessage());
        }

        return null;
    }

    /**
     * @param OrderReferenceInterface $reference
     *
     * @return ShippingInfo|null
     */
    public function getInfoFromOrderReference(OrderReferenceInterface $reference)
    {
        $info = $reference->getExtShippingInfo();

        /** @var ShippingInfo $shippingInfo */
        $shippingInfo = $this->shippingInfoFactory->create();

        try {
            $data = $this->json->unserialize($info);
            if (is_array($data)) {
                $shippingInfo->setData($data);
            }
            return $shippingInfo;
        } catch (\Exception $e) {
            $this->helper->addTolog('exception', $e->getMessage());
        }

        return null;
    }
}
