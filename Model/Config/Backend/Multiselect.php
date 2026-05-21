<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

/**
 * Backend model for multiselect config fields
 *
 * Ensures that deselecting all options properly saves an empty value
 */
class Multiselect extends Value
{

    /**
     * @return Multiselect
     */
    public function beforeSave(): self
    {
        $value = $this->getValue();
        if (is_array($value)) {
            $value = array_filter($value, 'strlen');
            $this->setValue(implode(',', $value));
        }
        if (!$this->getValue()) {
            $this->setValue('');
        }
        return parent::beforeSave();
    }
}
