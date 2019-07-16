<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Quote;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Api\QuoteReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Quote\QuoteReferenceFactory;

/**
 * Class QuoteReferenceRepository
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Quote
 */
class QuoteReferenceRepository implements QuoteReferenceRepositoryInterface
{

    /**
     * @var QuoteReference
     */
    private $quoteReference;

    /**
     * @var QuoteReferenceFactory
     */
    private $quoteReferenceFactory;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * QuoteReferenceRepository constructor.
     *
     * @param QuoteReference        $quoteReference
     * @param QuoteReferenceFactory $quoteReferenceFactory
     * @param General               $generalHelper
     */
    public function __construct(
        QuoteReference $quoteReference,
        QuoteReferenceFactory $quoteReferenceFactory,
        General $generalHelper
    ) {
        $this->quoteReference = $quoteReference;
        $this->quoteReferenceFactory = $quoteReferenceFactory;
        $this->generalHelper = $generalHelper;
    }

    /**
     * @param int $quoteId
     *
     * @return QuoteReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId($quoteId)
    {
        /** @var \Paazl\CheckoutWidget\Model\Quote\QuoteReference $model */
        $model = $this->quoteReferenceFactory->create();
        $this->quoteReference->load($model, $quoteId, QuoteReferenceInterface::QUOTE_ID);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Reference for the quote %1 not found', $quoteId));
        }

        return $model;
    }

    /**
     * @param QuoteReferenceInterface $quoteReference
     *
     * @return QuoteReferenceInterface|\Paazl\CheckoutWidget\Model\Quote\QuoteReference
     * @throws CouldNotSaveException
     */
    public function save(QuoteReferenceInterface $quoteReference)
    {
        try {
            /** @var \Paazl\CheckoutWidget\Model\Quote\QuoteReference $quoteReference */
            $this->quoteReference->save($quoteReference);
        } catch (\Exception $exception) {
            $this->generalHelper->addTolog('exception', $exception->getMessage());
            throw new CouldNotSaveException(__('Cannot save quote reference'), $exception);
        }

        return $quoteReference;
    }
}
