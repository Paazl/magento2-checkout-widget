<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class ExcludeCustomerFields implements OptionSourceInterface
{

    public const EMAIL = 'email';
    public const FIRSTNAME = 'firstname';
    public const LASTNAME = 'lastname';
    public const COMPANY = 'company';
    public const STREET = 'street';
    public const CITY = 'city';
    public const POSTCODE = 'postcode';
    public const REGION = 'region';
    public const COUNTRY_ID = 'country_id';
    public const TELEPHONE = 'telephone';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            ['value' => self::EMAIL, 'label' => __('Email')],
            ['value' => self::FIRSTNAME, 'label' => __('First Name')],
            ['value' => self::LASTNAME, 'label' => __('Last Name')],
            ['value' => self::COMPANY, 'label' => __('Company')],
            ['value' => self::STREET, 'label' => __('Street Address')],
            ['value' => self::CITY, 'label' => __('City')],
            ['value' => self::POSTCODE, 'label' => __('Postal/Zip Code')],
            ['value' => self::REGION, 'label' => __('Province/Region')],
            ['value' => self::COUNTRY_ID, 'label' => __('Country')],
            ['value' => self::TELEPHONE, 'label' => __('Phone Number')],
        ];
    }
}
