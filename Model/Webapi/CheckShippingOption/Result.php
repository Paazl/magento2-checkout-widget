<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Webapi\CheckShippingOption;

use Magento\Framework\Api\AbstractSimpleObject;
use Paazl\CheckoutWidget\Api\Data\CheckQuoteResultInterface;

class Result extends AbstractSimpleObject implements CheckQuoteResultInterface
{
    /**
     * @inheritDoc
     */
    public function getReloadWidget()
    {
        return (bool)$this->_get(self::RELOAD_WIDGET);
    }

    /**
     * @inheritDoc
     */
    public function setReloadWidget($value)
    {
        return $this->setData(self::RELOAD_WIDGET, (bool)$value);
    }

    /**
     * @inheritDoc
     */
    public function getTotals()
    {
        return $this->_get(self::TOTALS);
    }

    /**
     * @inheritDoc
     */
    public function setTotals($value)
    {
        return $this->setData(self::TOTALS, $value);
    }

    /**
     * @inheritDoc
     */
    public function getReloadWidgetConfigJson(): string
    {
        return (string)$this->_get(self::RELOAD_WIDGET_CONFIG_JSON);
    }

    /**
     * @inheritDoc
     */
    public function setReloadWidgetConfigJson(string $value)
    {
        return $this->setData(self::RELOAD_WIDGET_CONFIG_JSON, $value);
    }
}
