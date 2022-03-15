<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\ViewModel\PaazlStatus;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\LocalizedException;
use Paazl\CheckoutWidget\Block\Adminhtml\Order\View\ShippingAndHandling\PaazlStatus as ParentBlock;
use Paazl\CheckoutWidget\Model\ExtInfoHandler;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Model\ShippingInfoFactory;
use Paazl\CheckoutWidget\Model\Api\Field\DeliveryType;
use Paazl\CheckoutWidget\Api\Data\Order\OrderReferenceInterface;

/**
 * View model for Paazl Status block.
 */
class DeliveryDate implements ArgumentInterface
{
    public const PARENT_BLOCK_NAME = 'shipping_and_handling_info.paazl_status';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var ShippingInfoFactory
     */
    protected $shippingInfoFactory;

    /**
     * @var bool|ParentBlock
     */
    private $parentBlock = null;

    /**
     * @var null|OrderReferenceInterface
     */
    private $paazlOrder = null;

    /**
     * @var null|ShippingInfo
     */
    private $extShippingInfo = null;

    /**
     * @var ExtInfoHandler
     */
    private $extInfoHandler;

    /**
     * ViewModel constructor
     *
     * @param DataObjectHelper    $dataObjectHelper
     * @param ExtInfoHandler      $extInfoHandler
     * @param Context             $context
     * @param ShippingInfoFactory $shippingInfoFactory
     */
    public function __construct(
        DataObjectHelper $dataObjectHelper,
        ExtInfoHandler $extInfoHandler,
        Context $context,
        ShippingInfoFactory $shippingInfoFactory
    ) {
        $this->dataObjectHelper = $dataObjectHelper;
        $this->shippingInfoFactory = $shippingInfoFactory;
        $this->context = $context;
        $this->extInfoHandler = $extInfoHandler;
    }

    /**
     * Retrieves parent block using proxy pattern
     *
     * @return bool|ParentBlock
     */
    protected function getParentBlock()
    {
        if (null === $this->parentBlock) {
            $layout = $this->context->getLayout();
            $this->parentBlock = $layout->getBlock(self::PARENT_BLOCK_NAME);
        }

        return $this->parentBlock;
    }

    /**
     * Retrieves Paazl Order from parent block
     *
     * @return OrderReferenceInterface|null
     * @throws LocalizedException
     */
    protected function getPaazlOrder()
    {
        if (null === $this->paazlOrder) {
            $this->paazlOrder = $this->getParentBlock()->getPaazlOrder();
        }

        return $this->paazlOrder;
    }

    /**
     * Retrieves ExtShippingInfo
     *
     * @return ShippingInfo|null
     * @throws LocalizedException
     */
    public function getExtShippingInfo()
    {
        if (!$this->getPaazlOrder()) {
            return null;
        }

        if (null === $this->extShippingInfo) {
            $this->extShippingInfo = $this->extInfoHandler->getInfoFromOrderReference($this->getPaazlOrder());
        }

        return $this->extShippingInfo;
    }

    /**
     * Checks if type is delivery
     *
     * @return bool
     * @throws LocalizedException
     */
    public function isTypeDelivery()
    {
        return $this->getExtShippingInfo() && $this->getExtShippingInfo()->getType() === DeliveryType::DELIVERY;
    }
}
