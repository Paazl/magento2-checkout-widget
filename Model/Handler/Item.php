<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Handler;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Sales\Model\Order\Item as OrderItem;
use Magento\Quote\Model\Quote\Item as QuoteItem;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;

/**
 * Handler Quote or Order Item
 */
class Item
{
    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * Item constructor.
     *
     * @param GeneralHelper $generalHelper
     */
    public function __construct(GeneralHelper $generalHelper)
    {
        $this->generalHelper = $generalHelper;
    }

    /**
     * Gets formatted price from quote or order item
     * When price value = 0 method will return value equivalent 0.01
     *
     * @param AbstractExtensibleModel $item
     * @return float
     * @throws LocalizedException
     */
    public function getPriceValue(AbstractExtensibleModel $item)
    {
        if (!($item instanceof OrderItem) && !($item instanceof QuoteItem)) {
            $exception = new LocalizedException(
                __('Cannot get price value from the item. Item must be Item from Quote or Item from Order.')
            );
            $this->generalHelper->addTolog('exception', $exception->getMessage());
            throw $exception;
        }

        if ($item->getParentItemId() > 0) {
            $priceValue = $item->getParentItem()->getPriceInclTax();
        } else {
            $priceValue = $item->getPriceInclTax();
        }

        return $priceValue <= 0.01 ? 0.01 : $priceValue;
    }
}
