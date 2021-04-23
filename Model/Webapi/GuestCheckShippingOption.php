<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Paazl\CheckoutWidget\Api\Data\CheckQuoteResultInterface;
use Paazl\CheckoutWidget\Api\Webapi\GuestCheckShippingOptionInterface;

class GuestCheckShippingOption implements GuestCheckShippingOptionInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var CheckShippingOption
     */
    private $checkShippingOption;

    /**
     * GuestCheckShippingOption constructor.
     *
     * @param QuoteIdMaskFactory  $quoteIdMaskFactory
     * @param CheckShippingOption $checkShippingOption
     */
    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        CheckShippingOption $checkShippingOption
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->checkShippingOption = $checkShippingOption;
    }

    /**
     * {@inheritDoc}
     */
    public function get($cartId)
    {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->checkShippingOption->get($quoteIdMask->getQuoteId());
    }
}
