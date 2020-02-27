<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\Create\Shipping\Method\Paazl\Widget;

use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Backend\Model\Session\Quote as SessionQuote;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Quote\Model\Quote;
use Magento\Store\Model\Store;

/**
 * Adminhtml sales order create abstract block
 *
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class Modal extends Widget
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Paazl_CheckoutWidget::order/create/shipping/method/paazl/widget/modal.phtml';

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * Session quote
     *
     * @var SessionQuote
     */
    private $sessionQuote;

    /**
     * @param Context $context
     * @param SerializerInterface $serializer
     * @param SessionQuote $sessionQuote
     * @param array $data
     */
    public function __construct(
        Context $context,
        SerializerInterface $serializer,
        SessionQuote $sessionQuote,
        array $data = []
    ) {
        $this->sessionQuote = $sessionQuote;
        $this->serializer = $serializer;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve quote session object
     *
     * @return SessionQuote
     */
    protected function getSession()
    {
        return $this->sessionQuote;
    }

    /**
     * Retrieve quote model object
     *
     * @return Quote
     */
    public function getQuote()
    {
        return $this->getSession()->getQuote();
    }

    /**
     * Retrieve customer identifier
     *
     * @return int
     */
    public function getCustomerId()
    {
        return $this->getSession()->getCustomerId();
    }

    /**
     * Retrieve store model object
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->getSession()->getStore();
    }

    /**
     * Retrieve store identifier
     *
     * @return int
     */
    public function getStoreId()
    {
        return $this->getSession()->getStoreId();
    }

    /**
     * Initialize paazl modal widget
     *
     * @return string
     */
    public function getPaazlWidgetModalJson()
    {
        return $this->serializer->serialize(
            [
                'paazlWidgetModal' => [
                    'modalClass' => 'ui-dialog-active ui-popup paazl-widget-modal'
                ],
            ]
        );
    }
}
