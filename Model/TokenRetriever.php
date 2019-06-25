<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Model\Api\Builder\Reference;
use Paazl\CheckoutWidget\Model\Api\PaazlApiFactory;

/**
 * Class TokenRetriever
 *
 * @package Paazl\CheckoutWidget\Model
 */
class TokenRetriever
{

    /**
     * @var Reference
     */
    private $referenceBuilder;

    /**
     * @var PaazlApiFactory
     */
    private $apiFactory;

    /**
     * @var null|string
     */
    private $token = null;

    /**
     * TokenRetriever constructor.
     *
     * @param Reference       $referenceBuilder
     * @param PaazlApiFactory $apiFactory
     */
    public function __construct(
        Reference $referenceBuilder,
        PaazlApiFactory $apiFactory
    ) {
        $this->referenceBuilder = $referenceBuilder;
        $this->apiFactory = $apiFactory;
    }

    /**
     * @param Quote $quote
     *
     * @return string
     * @throws LocalizedException
     */
    public function retrieve(Quote $quote)
    {
        if (!$this->token) {
            try {
                $api = $this->apiFactory->create();

                $token = $api->getApiToken($this->referenceBuilder->getQuoteReference($quote));
                $this->token = $token->getToken();
            } catch (\Exception $exception) {
                throw new LocalizedException(__($exception->getMessage()), $exception);
            }
        }

        return $this->token;
    }
}
