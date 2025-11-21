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
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterfaceFactory;
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
     * @var QuoteReferenceInterfaceFactory
     */
    private $quoteReferenceInterfaceFactory;

    /**
     * ExtInfoHandler constructor.
     *
     * @param ShippingInfoFactory               $shippingInfoFactory
     * @param Json                              $json
     * @param Helper                            $helper
     * @param QuoteReferenceRepositoryInterface $quoteReferenceRepository
     * @param QuoteReferenceInterfaceFactory    $quoteReferenceInterfaceFactory
     */
    public function __construct(
        ShippingInfoFactory $shippingInfoFactory,
        Json $json,
        Helper $helper,
        QuoteReferenceRepositoryInterface $quoteReferenceRepository,
        QuoteReferenceInterfaceFactory $quoteReferenceInterfaceFactory
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->json = $json;
        $this->helper = $helper;
        $this->quoteReferenceRepository = $quoteReferenceRepository;
        $this->quoteReferenceInterfaceFactory = $quoteReferenceInterfaceFactory;
    }

    /**
     * @param Quote $quote
     *
     * @return \Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface
     */
    private function getQuoteReference(Quote $quote)
    {
        $quoteId = $quote->getId();
        try {
            $reference = $this->quoteReferenceRepository->getByQuoteId($quoteId);
            $this->helper->addTolog('info', sprintf(
                'Loaded existing quote reference for quote ID: %s (reference ID: %s)',
                $quoteId,
                $reference->getId()
            ));
        } catch (NoSuchEntityException $e) {
            $reference = $this->quoteReferenceInterfaceFactory->create(
                ['data' => [
                    QuoteReferenceInterface::QUOTE_ID => $quoteId,
                ]]
            );
            $this->helper->addTolog('info', sprintf(
                'Created new quote reference for quote ID: %s',
                $quoteId
            ));
        }

        return $reference;
    }

    /**
     * @param ShippingInfo $info
     * @param Quote        $quote
     */
    public function setInfoToQuote(ShippingInfo $info, Quote $quote)
    {
        $quoteId = $quote->getId();

        try {
            $reference = $this->getQuoteReference($quote);
            $isNewReference = !$reference->getId();

            $shippingInfoData = $info->toJson();
            $reference->setExtShippingInfo($shippingInfoData);

            $this->helper->addTolog('info', sprintf(
                'Saving shipping info to quote ID: %s | Operation: %s | Data: %s',
                $quoteId,
                $isNewReference ? 'CREATE' : 'UPDATE',
                $shippingInfoData
            ));

            $this->quoteReferenceRepository->save($reference);

            $this->helper->addTolog('info', sprintf(
                'Successfully saved quote reference for quote ID: %s (reference ID: %s)',
                $quoteId,
                $reference->getId()
            ));
        } catch (CouldNotSaveException $e) {
            $this->helper->addTolog('exception', sprintf(
                'Failed to save quote reference for quote ID: %s | Error: %s',
                $quoteId,
                $e->getMessage()
            ));
        } catch (\Exception $e) {
            $this->helper->addTolog('exception', sprintf(
                'Unexpected error saving quote reference for quote ID: %s | Error: %s | Trace: %s',
                $quoteId,
                $e->getMessage(),
                $e->getTraceAsString()
            ));
        }
    }

    /**
     * @param Quote $quote
     *
     * @return null|ShippingInfo
     */
    public function getInfoFromQuote(Quote $quote)
    {
        $quoteId = $quote->getId();
        $reference = $this->getQuoteReference($quote);
        $info = $reference->getExtShippingInfo();

        if (empty($info)) {
            $this->helper->addTolog('info', sprintf(
                'No shipping info found for quote ID: %s (reference ID: %s)',
                $quoteId,
                $reference->getId() ?: 'N/A'
            ));
            return null;
        }

        $this->helper->addTolog('info', sprintf(
            'Retrieved shipping info for quote ID: %s | Data: %s',
            $quoteId,
            $info
        ));

        /** @var ShippingInfo $shippingInfo */
        $shippingInfo = $this->shippingInfoFactory->create();

        try {
            $data = $this->json->unserialize($info);
            if (is_array($data)) {
                $shippingInfo->setData($data);
            }
            return $shippingInfo;
        } catch (\Exception $e) {
            $this->helper->addTolog('exception', sprintf(
                'Failed to unserialize shipping info for quote ID: %s | Error: %s',
                $quoteId,
                $e->getMessage()
            ));
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
