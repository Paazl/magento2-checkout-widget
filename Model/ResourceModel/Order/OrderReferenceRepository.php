<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\ResourceModel\Order;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Order\OrderReferenceFactory;

/**
 * Class OrderReferenceRepository
 *
 * @package Paazl\CheckoutWidget\Model\ResourceModel\Order
 */
class OrderReferenceRepository implements OrderReferenceRepositoryInterface
{

    /**
     * @var OrderReference
     */
    private $resource;

    /**
     * @var OrderReferenceFactory
     */
    private $orderReferenceFactory;

    /**
     * @var General
     */
    private $generalHelper;

    /**
     * OrderReferenceRepository constructor.
     *
     * @param OrderReference        $orderReference
     * @param OrderReferenceFactory $orderReferenceFactory
     * @param General               $generalHelper
     */
    public function __construct(
        OrderReference $orderReference,
        OrderReferenceFactory $orderReferenceFactory,
        General $generalHelper
    ) {
        $this->resource = $orderReference;
        $this->orderReferenceFactory = $orderReferenceFactory;
        $this->generalHelper = $generalHelper;
    }

    /**
     * @param int $orderId
     *
     * @return OrderReferenceInterface
     * @throws NoSuchEntityException
     */
    public function getByOrderId($orderId)
    {
        /** @var \Paazl\CheckoutWidget\Model\Order\OrderReference $model */
        $model = $this->orderReferenceFactory->create();
        $this->resource->load($model, $orderId, OrderReferenceInterface::ORDER_ID);

        if (!$model->getId()) {
            throw new NoSuchEntityException(__('Reference for the order %1 not found', $orderId));
        }

        return $model;
    }

    /**
     * @param OrderReferenceInterface $orderReference
     *
     * @return OrderReferenceInterface
     * @throws CouldNotSaveException
     */
    public function save(OrderReferenceInterface $orderReference)
    {
        try {
            /** @var \Paazl\CheckoutWidget\Model\Order\OrderReference $orderReference */
            $this->resource->save($orderReference);
        } catch (\Exception $exception) {
            $this->generalHelper->addTolog('exception', $exception->getMessage());
            throw new CouldNotSaveException(__('Cannot save order reference'), $exception);
        }

        return $orderReference;
    }
}
