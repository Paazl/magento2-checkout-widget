<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Block\Checkout;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Registry;

/**
 * Class Summary
 *
 * @package Paazl\CheckoutWidget\Block\Checkout
 */
class Summary extends Template
{

    /**
     * @var Registry
     */
    private $registry;

    /**
     * Summary constructor.
     *
     * @param Context  $context
     * @param Registry $registry
     * @param array    $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = []
    ) {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->registry->registry('summaryTotals');
    }
}
