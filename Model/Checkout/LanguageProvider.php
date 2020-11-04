<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Checkout;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Locale\ResolverInterface;

/**
 * Class LanguageProvider
 * @package Paazl\CheckoutWidget\Model\Checkout
 */
class LanguageProvider implements ConfigProviderInterface
{

    /**
     * @var ResolverInterface
     */
    private $resolver;

    private $allowedCodes = [
        'en' => 'eng',
        'nl' => 'nld',
        'de' => 'deu',
        'pl' => 'pol'
    ];

    /**
     * LanguageProvider constructor.
     *
     * @param ResolverInterface $resolver
     */
    public function __construct(
        ResolverInterface $resolver
    ) {
        $this->resolver = $resolver;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        $languageCode = 'eng';
        if ($this->resolver->getLocale()) {
            $locale = $this->resolver->getLocale();
            $languageCode = explode('_', $locale)[0];
            if (isset($this->allowedCodes[$languageCode])) {
                $languageCode = $this->allowedCodes[$languageCode];
            }
        }

        return [
            'language' => $languageCode,
        ];
    }
}
