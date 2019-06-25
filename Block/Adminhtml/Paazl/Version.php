<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Paazl;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Magento\Backend\Block\Template\Context;

/**
 * Class Version
 *
 * @package Paazl\CheckoutWidget\Block\Adminhtml\Paazl
 */
class Version extends Field
{

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * Version constructor.
     *
     * @param Context       $context
     * @param GeneralHelper $generalHelper
     */
    public function __construct(
        Context $context,
        GeneralHelper $generalHelper
    ) {
        $this->generalHelper = $generalHelper;
        parent::__construct($context);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _getElementHtml(AbstractElement $element)
    {
        $html = sprintf(
            '<strong>%s</strong><br/>%s: %s',
            __('Paazl Checkout Widget'),
            __('Module Version'),
            $this->generalHelper->getExtensionVersion()
        );
        $element->setData('text', $html);
        return parent::_getElementHtml($element);
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _renderScopeLabel(AbstractElement $element)
    {
        return '';
    }

    /**
     * @param AbstractElement $element
     *
     * @return string
     */
    public function _renderInheritCheckbox(AbstractElement $element)
    {
        return '';
    }
}
