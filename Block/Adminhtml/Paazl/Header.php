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
 * Class Header
 *
 * @package Paazl\CheckoutWidget\Block\Adminhtml\Paazl
 */
class Header extends Field
{

    /**
     * @var string
     */
    protected $_template = 'Paazl_CheckoutWidget::system/config/fieldset/header.phtml';

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * Header constructor.
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
    public function render(AbstractElement $element)
    {
        $element->addClass('paazl');

        return $this->toHtml();
    }
}
