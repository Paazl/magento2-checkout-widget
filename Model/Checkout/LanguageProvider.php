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
        $languageCode = 'en';
        if ($this->resolver->getLocale()) {
            $locale = $this->resolver->getLocale();
            $languageCode = explode('_', $locale)[0];
        }

        return [
            'language' => $languageCode,
        ];
    }
}
