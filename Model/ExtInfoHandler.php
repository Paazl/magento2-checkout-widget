<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
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
     * ExtInfoHandler constructor.
     *
     * @param ShippingInfoFactory $shippingInfoFactory
     * @param Json                $json
     * @param Helper              $helper
     */
    public function __construct(
        ShippingInfoFactory $shippingInfoFactory,
        Json $json,
        Helper $helper
    ) {
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->json = $json;
        $this->helper = $helper;
    }

    /**
     * @param ShippingInfo $info
     * @param Quote        $quote
     */
    public function setInfoToQuote(ShippingInfo $info, Quote $quote)
    {
        $quote->setExtShippingInfo($info->toJson());
    }

    /**
     * @param Quote $quote
     *
     * @return null|ShippingInfo
     */
    public function getInfoFromQuote(Quote $quote)
    {
        $info = $quote->getExtShippingInfo();

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
