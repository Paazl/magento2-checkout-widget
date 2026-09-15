<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Block\Adminhtml\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Paazl\CheckoutWidget\Model\System\Config\Backend\CustomerTagMatrix as Backend;

/**
 * Renders the customer tag -> matrix position rows in the Paazl configuration.
 */
class CustomerTagMatrix extends AbstractFieldArray
{
    /**
     * @inheritdoc
     */
    protected function _prepareToRender()
    {
        $this->addColumn(Backend::COLUMN_TAG, [
            'label' => __('Customer Tag'),
            'class' => 'required-entry',
        ]);
        $this->addColumn(Backend::COLUMN_LETTER, [
            'label' => __('Matrix Position'),
            'class' => 'required-entry validate-paazl-delivery-matrix-code',
            'style' => 'width: 80px',
        ]);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add matrix line');
    }
}
