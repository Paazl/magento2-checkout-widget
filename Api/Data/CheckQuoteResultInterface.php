<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Api\Data;

/**
 * Interface CheckQuoteResultInterface
 *
 * @package Paazl\CheckoutWidget\Api\Data
 */
interface CheckQuoteResultInterface
{
    const RELOAD_WIDGET = 'reload_widget';
    const RELOAD_WIDGET_CONFIG_JSON = 'reload_widget_config';
    const TOTALS = 'totals';

    /**
     * @return boolean
     */
    public function getReloadWidget();

    /**
     * @param boolean $value
     * @return $this
     */
    public function setReloadWidget($value);

    /**
     * @return string
     */
    public function getReloadWidgetConfigJson(): string;

    /**
     * @param string $value
     * @return $this
     */
    public function setReloadWidgetConfigJson(string $value);

    /**
     * @return \Magento\Quote\Api\Data\TotalsInterface|null
     */
    public function getTotals();

    /**
     * @param \Magento\Quote\Api\Data\TotalsInterface $value
     * @return $this
     */
    public function setTotals($value);
}
