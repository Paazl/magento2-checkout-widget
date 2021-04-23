<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Processor;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Helper\Order as OrderHelper;
use Paazl\CheckoutWidget\Model\Api\Builder\Order;
use Paazl\CheckoutWidget\Model\Api\PaazlApi;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;

/**
 * Class SendToService
 *
 * @package Paazl\CheckoutWidget\Model\Api\Processor
 */
class SendToService
{

    /**
     * @var Order
     */
    private $orderBuilder;

    /**
     * @var PaazlApiFactory
     */
    private $paazlApiFactory;

    /**
     * @var OrderHelper
     */
    private $orderHelper;

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var MarkOrderAsSent
     */
    private $markOrderAsSent;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * SendToService constructor.
     *
     * @param Order                             $orderBuilder
     * @param PaazlApiFactory                   $paazlApiFactory
     * @param OrderHelper                       $orderHelper
     * @param GeneralHelper                     $generalHelper
     * @param MarkOrderAsSent                   $markOrderAsSent
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     */
    public function __construct(
        Order $orderBuilder,
        PaazlApiFactory $paazlApiFactory,
        OrderHelper $orderHelper,
        GeneralHelper $generalHelper,
        MarkOrderAsSent $markOrderAsSent,
        OrderReferenceRepositoryInterface $orderReferenceRepository
    ) {
        $this->orderBuilder = $orderBuilder;
        $this->paazlApiFactory = $paazlApiFactory;
        $this->orderHelper = $orderHelper;
        $this->generalHelper = $generalHelper;
        $this->markOrderAsSent = $markOrderAsSent;
        $this->orderReferenceRepository = $orderReferenceRepository;
    }

    /**
     * @param OrderInterface $order
     * @param bool           $force
     *
     * @return void
     * @throws LocalizedException
     */
    public function process(OrderInterface $order, $force = false)
    {
        if (!$this->orderHelper->isPaazlOrder($order)) {
            throw new LocalizedException(__('Not a Paazl order.'));
        }

        try {
            $reference = $this->orderReferenceRepository->getByOrderId($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            throw new LocalizedException(__('Reference information not found'));
        }

        if ($reference->isSent() && !$force && !$reference->getInvalid()) {
            throw new LocalizedException(__('Order was sent to Paazl already'));
        }

        try {
            $orderInfo = $this->orderBuilder->getCreateOrderData($order);
            /** @var PaazlApi $paazlApi */
            $paazlApi = $this->paazlApiFactory->create($order->getStoreId());
            if ($reference->isSent() && $reference->getInvalid()) {
                $modify = true;
            } else {
                $modify = false;
            }
            $paazlApi->addOrder($orderInfo, $modify);
            $this->markOrderAsSent->process($order);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
            throw new LocalizedException(__('Error sending order data to Paazl: %1', $e->getMessage()));
        }
    }
}
