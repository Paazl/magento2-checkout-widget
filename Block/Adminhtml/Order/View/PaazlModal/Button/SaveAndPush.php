<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Adminhtml\Order\View\PaazlModal\Button;

/**
 * Button "Save and Push to Paazl" in "Available Paazl Methods" slide-out panel of a order view page
 */
class SaveAndPush extends Generic
{
    /**
     * @return array
     */
    public function getButtonData()
    {
        return [
            'label' => __('Save and Push to Paazl'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'paazl_order_data_form.paazl_order_data_form',
                                'actionName' => 'saveAndPush'
                            ],
                        ]
                    ]
                ],
                'form-role' => 'saveAndPush',
            ],
            'on_click' => '',
            'sort_order' => 30
        ];
    }
}
