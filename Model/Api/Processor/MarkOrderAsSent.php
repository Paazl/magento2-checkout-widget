<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api\Processor;

use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;

/**
 * Class MarkOrderAsSent
 *
 * @package Paazl\CheckoutWidget\Model\Api\Processor
 */
class MarkOrderAsSent
{

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * MarkOrderAsSent constructor.
     *
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param DateTime                          $dateTime
     */
    public function __construct(
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        DateTime $dateTime
    ) {
        $this->dateTime = $dateTime;
        $this->orderReferenceRepository = $orderReferenceRepository;
    }

    /**
     * @param OrderInterface $order
     *
     * @throws LocalizedException
     */
    public function process(OrderInterface $order)
    {
        try {
            $reference = $this->orderReferenceRepository->getByOrderId($order->getEntityId());
        } catch (NoSuchEntityException $exception) {
            throw new LocalizedException(__('Reference information not found'), $exception);
        }

        $reference->setExtSentAt($this->dateTime->gmtDate());

        try {
            $this->orderReferenceRepository->save($reference);
        } catch (CouldNotSaveException $exception) {
            throw new LocalizedException(__('Cannot update reference'), $exception);
        }
    }
}
