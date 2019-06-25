<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Paazl;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;

/**
 * Class Heading
 *
 * @package Paazl\CheckoutWidget\Block\Adminhtml\Paazl
 */
class Heading extends Field
{

    /**
     * Styles heading sperator.
     *
     * @param AbstractElement $element
     *
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $html = '<tr id="row_' . $element->getHtmlId() . '">';
        $html .= ' <td class="label"></td>';
        $html .= ' <td class="value">';
        $html .= '  <div class="mm-heading-paazl">' . $element->getData('label') . '</div>';
        $html .= '	<div class="mm-comment-paazl">';
        $html .= '   <div id="content">' . $element->getData('comment') . '</div>';
        $html .= '  </div>';
        $html .= ' </td>';
        $html .= ' <td></td>';
        $html .= '</tr>';

        return $html;
    }
}
