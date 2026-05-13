<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Paazl\CheckoutWidget\Api\Webapi\GuestMarkNextToPaymentInterface;

class GuestMarkNextToPayment implements GuestMarkNextToPaymentInterface
{
    private QuoteIdMaskFactory $quoteIdMaskFactory;
    private MarkNextToPayment $markNextToPayment;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        MarkNextToPayment $markNextToPayment
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->markNextToPayment = $markNextToPayment;
    }

    /**
     * @inheritDoc
     */
    public function execute($cartId): bool
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->markNextToPayment->execute((string)$quoteIdMask->getQuoteId());
    }
}
