<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\View\ShippingAndHandling;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PaazlEdit
 */
class PaazlEdit extends Template
{
    /**
     * Path to template file in theme.
     *
     * @var string
     */
    protected $_template = 'Paazl_CheckoutWidget::order/view/shipping-and-handling-info/paazl-edit.phtml';

    /** @var StoreManagerInterface */
    protected $storeManager;

    /** @var UrlInterface */
    protected $urlBuilder;

    /** @var RequestInterface */
    protected $request;

    /**
     * Constructor
     *
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param RequestInterface $request
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        RequestInterface $request,
        Template\Context $context,
        array $data = []
    ) {
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->request = $request;
        parent::__construct($context, $data);
    }

    /**
     * Gets Update URL
     *
     * @return string
     */
    public function getUpdateUrl()
    {
        return $this->getUrl('mui/index/render');
    }

    /**
     * Gets Render URL
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getRenderUrl()
    {
        return $this->getUrl(
            'mui/index/render_handle',
            [
                'handle' => 'paazl_order_data_edit_form',
                'store' => $this->storeManager->getStore()->getId(),
                'buttons' => 1,
                'order_id'  => $this->request->getParam('order_id')
            ]
        );
    }

    /**
     * Gets Validate URL
     *
     * @return string
     */
    public function getValidateUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order_data/validate',
            [
                'order_id'  => $this->request->getParam('order_id')
            ]
        );
    }

    /**
     * Gets Save URL
     *
     * @return string
     */
    public function getSaveUrl()
    {
        return $this->getUrl(
            'paazl_checkoutwidget/order_data/save',
            [
                'order_id'  => $this->request->getParam('order_id')
            ]
        );
    }
}