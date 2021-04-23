<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Sales\Adminhtml\Order;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Controller\Adminhtml\Order\AddressSave;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\App\RequestInterface;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Paazl\CheckoutWidget\Logger\PaazlLogger;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;

/**
 * Class AddressSavePlugin
 * Plugin for AddressSave controller
 */
class AddressSavePlugin
{
    /**
     * @var RequestInterface
     */
    private $request;
    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressRepository;
    /**
     * @var PaazlLogger
     */
    private $logger;
    /**
     * @var OrderReferenceRepositoryInterface
     */
    private $orderReferenceRepository;

    /**
     * AddressSavePlugin constructor.
     *
     * @param RequestInterface $request
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param PaazlLogger $logger
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     */
    public function __construct(
        RequestInterface $request,
        OrderAddressRepositoryInterface $orderAddressRepository,
        PaazlLogger $logger,
        OrderReferenceRepositoryInterface $orderReferenceRepository
    ) {
        $this->request = $request;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->logger = $logger;
        $this->orderReferenceRepository = $orderReferenceRepository;
    }

    /**
     * @param AddressSave $subject
     * @param Redirect $result
     *
     * @return Redirect
     */
    public function afterExecute(AddressSave $subject, Redirect $result)
    {
        $addressId = $this->request->getParam('address_id');
        try {
            $orderAddress = $this->orderAddressRepository->get($addressId);
            $paazlOrder = $this->orderReferenceRepository->getByOrderId($orderAddress->getParentId());
            $paazlOrder->setInvalid(1)->save();
        } catch (NoSuchEntityException $e) {
            $this->logger->add('exception', $e->getMessage());
        }

        return $result;
    }
}
