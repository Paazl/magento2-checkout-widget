<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;

/**
 * Interface QuoteReferenceRepositoryInterface
 * @package Paazl\CheckoutWidget\Api
 */
interface QuoteReferenceRepositoryInterface
{

    /**
     * @param $quoteId
     *
     * @return QuoteReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId($quoteId);

    /**
     * @param QuoteReferenceInterface $reference
     *
     * @return QuoteReferenceInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteReferenceInterface $reference);
}
