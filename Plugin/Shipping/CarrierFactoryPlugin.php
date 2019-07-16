<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Shipping;

use Magento\Checkout\Model\Session\Proxy as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Shipping\Model\Carrier\AbstractCarrier;
use Magento\Shipping\Model\CarrierFactory;
use Paazl\CheckoutWidget\Api\QuoteReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Model\Carrier\Paazlshipping;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class CarrierFactoryPlugin
 *
 * @package Paazl\CheckoutWidget\Plugin\Shipping
 */
class CarrierFactoryPlugin
{

    /**
     * @var Config
     */
    private $config;

    /**
     * @var CheckoutSession
     */
    private $checkout;

    /**
     * @var QuoteReferenceRepositoryInterface
     */
    private $quoteReferenceRepository;

    /**
     * CarrierFactoryPlugin constructor.
     *
     * @param Config                            $config
     * @param CheckoutSession                   $checkout
     * @param QuoteReferenceRepositoryInterface $quoteReferenceRepository
     */
    public function __construct(
        Config $config,
        CheckoutSession $checkout,
        QuoteReferenceRepositoryInterface $quoteReferenceRepository
    ) {
        $this->config = $config;
        $this->checkout = $checkout;
        $this->quoteReferenceRepository = $quoteReferenceRepository;
    }

    /**
     * @param CarrierFactory $subject
     * @param \Closure       $proceed
     * @param                $carrierCode
     * @param null           $storeId
     *
     * @return bool|AbstractCarrier
     */
    public function aroundCreateIfActive(
        CarrierFactory $subject,
        \Closure $proceed,
        $carrierCode,
        $storeId = null
    ) {
        $paazlActive = $this->config->isCarrierActive();

        try {
            $reference = $this->quoteReferenceRepository->getByQuoteId($this->checkout->getQuoteId());
            $paazlActive = $paazlActive && !empty($reference->getToken());
        } catch (NoSuchEntityException $e) {
            $paazlActive = false;
        }

        $carrier = $proceed($carrierCode, $storeId);
        if (!$carrier) {
            return $carrier;
        }

        if ($paazlActive && $this->config->isHideOtherShippingMethods() && ($carrierCode != Paazlshipping::CODE)) {
            // This is not Paazl, but Paazl is active and hides other methods - deactivating this method
            return false;
        }

        if (!$paazlActive && ($carrierCode == Paazlshipping::CODE)) {
            return false;
        }

        return $carrier;
    }
}
