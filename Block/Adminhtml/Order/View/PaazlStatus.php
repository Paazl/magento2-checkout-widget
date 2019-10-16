<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\View;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Helper\Admin as AdminHelper;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;

/**
 * PaazlStatus Block
 */
class PaazlStatus extends AbstractOrder
{
    /** @var OrderReferenceRepositoryInterface */
    private $orderReferenceRepository;

    /** @var null|OrderReferenceInterface|false  */
    private $paazlOrder = null;

    /**
     * Constructor
     *
     * @param OrderReferenceRepositoryInterface $orderReferenceRepository
     * @param Context $context
     * @param Registry $registry
     * @param AdminHelper $adminHelper
     * @param array $data
     */
    public function __construct(
        OrderReferenceRepositoryInterface $orderReferenceRepository,
        Context $context,
        Registry $registry,
        AdminHelper $adminHelper,
        array $data = []
    ) {
        $this->orderReferenceRepository = $orderReferenceRepository;
        parent::__construct($context, $registry, $adminHelper, $data);
    }

    /**
     * Preparing global layout
     *
     * @return $this
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        if (!$this->getPaazlOrder()) {
            return parent::_prepareLayout();
        }
        $onclick = "submitAndReloadArea($('paazl-order-status'), '" . $this->getSubmitUrl() . "')";
        $label = $this->getPaazlOrder()->isSent() ? __('Resend') : __('Retry');
        $button = $this->getLayout()->createBlock(
            Button::class
        )->setData(
            ['label' => $label, 'class' => 'action-send action-secondary', 'onclick' => $onclick]
        );
        $this->setChild('send_button', $button);

        return parent::_prepareLayout();
    }

    /**
     * Button URL getter
     *
     * @return string
     * @throws LocalizedException
     */
    public function getSubmitUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order_data/send',
            ['order_id' => $this->getOrder()->getEntityId()]
        );
    }

    /**
     * Retrieves orderReferenceRepository
     *
     * @return OrderReferenceRepositoryInterface
     */
    public function getOrderReferenceRepository()
    {
        return $this->orderReferenceRepository;
    }

    /**
     * Retrieves Paazl Order
     *
     * @return OrderReferenceInterface|false
     * @throws LocalizedException
     */
    public function getPaazlOrder()
    {
        if (null !== $this->paazlOrder) {
            return $this->paazlOrder;
        }

        try {
            $this->paazlOrder = $this->getOrderReferenceRepository()->getByOrderId($this->getOrder()->getEntityId());
        } catch (NoSuchEntityException $exception) {
            $this->paazlOrder = false;
        }

        return $this->paazlOrder;
    }
}
