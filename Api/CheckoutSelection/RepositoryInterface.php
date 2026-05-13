<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Api\CheckoutSelection;

use Magento\Framework\Exception\LocalizedException;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface
    as CheckoutSelection;

interface RepositoryInterface
{

    public const COULD_NOT_SAVE_EXCEPTION = 'Could not save the checkout selection: %1';

    /**
     * Return new entity object
     *
     * @return CheckoutSelection
     */
    public function create(): CheckoutSelection;

    /**
     * Register entity to save
     *
     * @param CheckoutSelection $checkoutSelection
     *
     * @return CheckoutSelection
     * @throws LocalizedException
     */
    public function save(
        CheckoutSelection $checkoutSelection
    ) : CheckoutSelection;

    /**
     * Get checkout selection by quote ID
     *
     * @param int $quoteId
     * @return CheckoutSelection|null
     */
    public function getByQuoteId(int $quoteId): ?CheckoutSelection;

    /**
     * Flag the checkout selection for the given quote as having reached the payment step.
     * Creates the row if it does not yet exist.
     *
     * @param int $quoteId
     * @return CheckoutSelection
     * @throws LocalizedException
     */
    public function markNextToPayment(int $quoteId): CheckoutSelection;

    /**
     * Flag the checkout selection for the given quote as final (e.g. order placed).
     *
     * @param int $quoteId
     * @return CheckoutSelection|null Null when no selection row exists for the quote.
     * @throws LocalizedException
     */
    public function markAsFinal(int $quoteId): ?CheckoutSelection;

    /**
     * Set whether pickup locations are currently being offered for the
     * customer's address on the given quote. Tracks the latest state, so
     * a change of address that removes pickup availability flips it back.
     * The row is created if it does not yet exist.
     *
     * @param int $quoteId
     * @param bool $offered
     * @return CheckoutSelection
     * @throws LocalizedException
     */
    public function setPickupOffered(int $quoteId, bool $offered): CheckoutSelection;

}
