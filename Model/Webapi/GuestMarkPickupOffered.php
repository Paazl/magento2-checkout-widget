<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi;

use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;
use Paazl\CheckoutWidget\Api\Webapi\GuestMarkPickupOfferedInterface;

class GuestMarkPickupOffered implements GuestMarkPickupOfferedInterface
{
    private QuoteIdMaskFactory $quoteIdMaskFactory;
    private MarkPickupOffered $markPickupOffered;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        MarkPickupOffered $markPickupOffered
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->markPickupOffered = $markPickupOffered;
    }

    /**
     * @inheritDoc
     */
    public function execute($cartId, $offered): bool
    {
        /** @var QuoteIdMask $quoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');
        return $this->markPickupOffered->execute((string)$quoteIdMask->getQuoteId(), $offered);
    }
}
