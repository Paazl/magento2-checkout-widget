<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\CheckoutSelection;

use Magento\Framework\Exception\CouldNotSaveException;
use Paazl\CheckoutWidget\Api\CheckoutSelection\DataInterface;
use Paazl\CheckoutWidget\Api\CheckoutSelection\RepositoryInterface;
use Paazl\CheckoutWidget\Model\CheckoutSelection\DataFactory;

/**
 * Checkout Selection repository
 */
class Repository implements RepositoryInterface
{

    private DataFactory $dataFactory;
    private ResourceModel $resource;

    public function __construct(
        DataFactory $dataFactory,
        ResourceModel $resource
    ) {
        $this->dataFactory = $dataFactory;
        $this->resource = $resource;
    }

    /**
     * @inheritDoc
     */
    public function create(): DataInterface
    {
        return $this->dataFactory->create();
    }

    /**
     * @inheritDoc
     */
    public function save(
        DataInterface $checkoutSelection
    ) : DataInterface {
        try {
            $checkoutSelection->setWasSent(false);
            $this->resource->save($checkoutSelection);
        } catch (\Throwable $exception) {
            $exceptionMsg = self::COULD_NOT_SAVE_EXCEPTION;
            throw new CouldNotSaveException(__(
                $exceptionMsg,
                $exception->getMessage()
            ));
        }
        return $checkoutSelection;
    }

    /**
     * @inheritDoc
     */
    public function getByQuoteId(int $quoteId): ?DataInterface
    {
        $checkoutSelection = $this->dataFactory->create();
        $this->resource->load($checkoutSelection, $quoteId, DataInterface::QUOTE_ID);
        if (!$checkoutSelection->getEntityId()) {
            return null;
        }
        return $checkoutSelection;
    }

    /**
     * @inheritDoc
     */
    public function markNextToPayment(int $quoteId): DataInterface
    {
        $checkoutSelection = $this->getByQuoteId($quoteId);
        if ($checkoutSelection === null) {
            $checkoutSelection = $this->create();
            $checkoutSelection->setQuoteId($quoteId);
        }
        $checkoutSelection->setNextToPayment(true);
        return $this->save($checkoutSelection);
    }

    /**
     * @inheritDoc
     */
    public function markAsFinal(int $quoteId): ?DataInterface
    {
        $checkoutSelection = $this->getByQuoteId($quoteId);
        if ($checkoutSelection === null) {
            return null;
        }
        $checkoutSelection->setIsFinal(true);
        $checkoutSelection->setNextToPayment(true);
        return $this->save($checkoutSelection);
    }

    /**
     * @inheritDoc
     */
    public function setPickupOffered(int $quoteId, bool $offered): DataInterface
    {
        $checkoutSelection = $this->getByQuoteId($quoteId);
        if ($checkoutSelection === null) {
            if (!$offered) {
                // Nothing to record — default for new rows is already false.
                $checkoutSelection = $this->create();
                $checkoutSelection->setQuoteId($quoteId);
                return $checkoutSelection;
            }
            $checkoutSelection = $this->create();
            $checkoutSelection->setQuoteId($quoteId);
        } elseif ($checkoutSelection->getPickupOffered() === $offered) {
            return $checkoutSelection;
        }
        $checkoutSelection->setPickupOffered($offered);
        return $this->save($checkoutSelection);
    }
}
