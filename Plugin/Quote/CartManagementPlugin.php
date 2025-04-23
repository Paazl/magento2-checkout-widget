<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Quote;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Quote\Api\CartManagementInterface;
use Magento\Quote\Api\Data\PaymentInterface;
use Paazl\CheckoutWidget\Helper\Order;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;

class CartManagementPlugin
{
    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var Order
     */
    private $orderHelper;

    /**
     * @var ExtInfoHandler
     */
    private $infoHandler;

    /**
     * CartManagementPlugin constructor.
     *
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param Order                                      $orderHelper
     * @param ExtInfoHandler                             $infoHandler
     */
    public function __construct(
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        Order $orderHelper,
        ExtInfoHandler $infoHandler
    ) {
        $this->quoteRepository = $quoteRepository;
        $this->orderHelper = $orderHelper;
        $this->infoHandler = $infoHandler;
    }

    /**
     * @param CartManagementInterface $subject
     * @param int                     $cartId
     * @param PaymentInterface|null   $paymentMethod
     *
     * @return null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws CouldNotSaveException
     */
    public function beforePlaceOrder(
        CartManagementInterface $subject,
        $cartId,
        ?PaymentInterface $paymentMethod = null
    ) {
        $quote = $this->quoteRepository->getActive($cartId);
        if ($quote->getIsVirtual()) {
            return null;
        }

        $shippingMethod = $quote->getShippingAddress()->getShippingMethod();
        if (!$this->orderHelper->isPaazlShippingMethod((string)$shippingMethod)) {
            return null;
        }

        $info = $this->infoHandler->getInfoFromQuote($quote);
        if (!$info) {
            throw new CouldNotSaveException(__('Shipping information not found'));
        }

        return null;
    }
}
