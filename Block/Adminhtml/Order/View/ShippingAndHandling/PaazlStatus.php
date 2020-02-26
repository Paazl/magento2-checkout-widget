<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\View\ShippingAndHandling;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Block\Adminhtml\Order\AbstractOrder;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Helper\Admin as AdminHelper;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;
use Paazl\CheckoutWidget\Api\OrderReferenceRepositoryInterface;
use Paazl\CheckoutWidget\Block\Adminhtml\Order\View\PaazlModal;

/**
 * PaazlStatus Block
 */
class PaazlStatus extends AbstractOrder
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Paazl_CheckoutWidget::order/view/shipping-and-handling-info/paazl-status.phtml';

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
     * @return AbstractOrder
     * @throws LocalizedException
     */
    protected function _prepareLayout()
    {
        if (!$this->getPaazlOrder()) {
            return parent::_prepareLayout();
        }

        if ($this->getPaazlOrder()->canResend()) {
            $onclick = "submitAndReloadArea($('paazl-order-status').parentNode.parentNode, '" .
                $this->getResendUrl() . "')";
            $resendButton = $this->getLayout()
                ->createBlock(Button::class)
                ->setData([
                    'label' => __('Resend'),
                    'class' => 'action-resend action-secondary',
                    'onclick' => $onclick
                ]);
            $this->setChild('resend_button', $resendButton);

            return parent::_prepareLayout();
        }
        if ($this->getPaazlOrder()->canEdit()) {
            $editBlock = $this->getLayout()->createBlock(PaazlEdit::class);
            $this->setChild('edit_block', $editBlock);
        }

        if ($this->getPaazlOrder()->canRetry()) {
            $onclick = "submitAndReloadArea($('paazl-order-status').parentNode.parentNode, '" .
                $this->getRetryUrl() . "')";
            $retryButton = $this->getLayout()
                ->createBlock(Button::class)
                ->setData([
                    'label' => __('Retry'),
                    'class' => 'action-retry action-secondary',
                    'onclick' => $onclick
                ]);

            $this->setChild('retry_button', $retryButton);
        }

        return parent::_prepareLayout();
    }

    /**
     * Gets Resend URL
     *
     * @return string
     * @throws LocalizedException
     */
    public function getResendUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order/resend',
            ['order_id' => $this->getOrder()->getEntityId()]
        );
    }

    /**
     * Gets Retry URL
     *
     * @return string
     * @throws LocalizedException
     */
    public function getRetryUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order/retry',
            ['order_id' => $this->getOrder()->getEntityId()]
        );
    }

    /**
     * Gets Edit URL
     *
     * @return string
     * @throws LocalizedException
     */
    public function getEditUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order_data/edit',
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
        if (null === $this->paazlOrder) {
            try {
                $this->paazlOrder =
                    $this->getOrderReferenceRepository()->getByOrderId($this->getOrder()->getEntityId());
            } catch (NoSuchEntityException $exception) {
                $this->paazlOrder = false;
            }
        }

        return $this->paazlOrder;
    }
}
