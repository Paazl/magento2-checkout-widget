<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Quote\Model\Quote;
use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterfaceFactory;
use Paazl\CheckoutWidget\Api\QuoteReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterfaceFactory;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;

/**
 * Class TokenRetriever
 *
 * @package Paazl\CheckoutWidget\Model
 */
class TokenRetriever
{

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * @var PaazlApiFactory
     */
    private $apiFactory;

    /**
     * @var null|string
     */
    private $token = null;

    /**
     * @var QuoteReferenceRepositoryInterface
     */
    private $quoteReferenceRepository;

    /**
     * @var QuoteReferenceInterfaceFactory
     */
    private $quoteReferenceInterfaceFactory;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * @var OrderReferenceInterfaceFactory
     */
    private $orderReferenceInterfaceFactory;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * TokenRetriever constructor.
     *
     * @param Reference                         $referenceBuilder
     * @param PaazlApiFactory                   $apiFactory
     * @param QuoteReferenceRepositoryInterface $quoteReferenceRepository
     * @param QuoteReferenceInterfaceFactory    $quoteReferenceInterfaceFactory
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param OrderReferenceInterfaceFactory    $orderReferenceInterfaceFactory
     * @param TimezoneInterface                 $timezone
     * @param DateTime                          $dateTime
     */
    public function __construct(
        Reference $referenceBuilder,
        PaazlApiFactory $apiFactory,
        QuoteReferenceRepositoryInterface $quoteReferenceRepository,
        QuoteReferenceInterfaceFactory $quoteReferenceInterfaceFactory,
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        OrderReferenceInterfaceFactory $orderReferenceInterfaceFactory,
        TimezoneInterface $timezone,
        DateTime $dateTime
    ) {
        $this->referenceBuilder = $referenceBuilder;
        $this->apiFactory = $apiFactory;
        $this->quoteReferenceRepository = $quoteReferenceRepository;
        $this->quoteReferenceInterfaceFactory = $quoteReferenceInterfaceFactory;
        $this->orderReferenceRepository = $orderReferenceRepository;
        $this->orderReferenceInterfaceFactory = $orderReferenceInterfaceFactory;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
    }

    /**
     * @param Quote $quote
     *
     * @return string
     * @throws LocalizedException
     */
    public function retrieveByQuote(Quote $quote)
    {
        if (!$this->token) {
            $reference = $this->getQuoteReference($quote);

            if ($reference->getToken() && !$reference->isTokenExpired()) {
                $this->token = $reference->getToken();
                return $this->token;
            }

            try {
                $api = $this->apiFactory->create($quote->getStoreId());
                $token = $api->getApiToken($this->referenceBuilder->getQuoteReference($quote));
                $gmtNow = $this->timezone->date(null, null, false);
                // @codingStandardsIgnoreLine
                $gmtNow->add(new \DateInterval('P30D'));
                $reference
                    ->setToken($token->getToken())
                    ->setTokenExpiresAt($this->dateTime->gmtDate(null, $gmtNow));
                $this->quoteReferenceRepository->save($reference);
                $this->token = $reference->getToken();
            } catch (\Exception $exception) {
                throw new LocalizedException(__($exception->getMessage()), $exception);
            }
        }

        return $this->token;
    }

    /**
     * @param OrderInterface $order
     *
     * @return string
     * @throws LocalizedException
     */
    public function retrieveByOrder(OrderInterface $order)
    {
        if (!$this->token) {
            try {
                $api = $this->apiFactory->create($order->getStoreId());
                $token = $api->getApiToken($this->referenceBuilder->getOrderReference($order));
                $this->token = $token->getToken();
            } catch (\Exception $exception) {
                throw new LocalizedException(__($exception->getMessage()), $exception);
            }
        }

        return $this->token;
    }

    /**
     * Retrieves Quote Reference
     *
     * @param Quote $quote
     * @return QuoteReferenceInterface
     */
    protected function getQuoteReference(Quote $quote)
    {
        try {
            $reference = $this->quoteReferenceRepository->getByQuoteId($quote->getId());
        } catch (NoSuchEntityException $e) {
            $reference = $this->quoteReferenceInterfaceFactory->create(
                ['data' => [
                    QuoteReferenceInterface::QUOTE_ID => $quote->getId(),
                ]]
            );
        }

        return $reference;
    }
}
