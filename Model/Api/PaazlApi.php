<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Magento\Framework\HTTP\ClientInterface;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Api\Http\ClientFactory;
use Paazl\CheckoutWidget\Model\Api\Response\Data\Token;
use Paazl\CheckoutWidget\Model\Api\Response\Data\TokenBuilder;

/**
 * Class PaazlApi
 *
 * @package Paazl\CheckoutWidget\Model\Api
 */
class PaazlApi
{

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var array
     */
    private $request = [];

    /**
     * @var GeneralHelper
     */
    private $generalHelper;

    /**
     * @var ClientFactory
     */
    private $httpClientFactory;

    /**
     * @var TokenBuilder
     */
    private $tokenBuilder;

    /**
     * @var UrlProvider
     */
    private $urlProvider;

    /**
     * PaazlApi constructor.
     *
     * @param Configuration $configuration
     * @param GeneralHelper $generalHelper
     * @param ClientFactory $httpClientFactory
     * @param TokenBuilder  $tokenBuilder
     * @param UrlProvider   $urlProvider
     */
    public function __construct(
        Configuration $configuration,
        GeneralHelper $generalHelper,
        ClientFactory $httpClientFactory,
        TokenBuilder $tokenBuilder,
        UrlProvider $urlProvider
    ) {
        $this->configuration = $configuration;
        $this->generalHelper = $generalHelper;
        $this->httpClientFactory = $httpClientFactory;
        $this->tokenBuilder = $tokenBuilder;
        $this->urlProvider = $urlProvider;
    }

    /**
     * Returns API token
     *
     * @param string $reference
     * @return Token $response
     * @throws ApiException
     */
    public function getApiToken($reference)
    {
        $url = $this->urlProvider->getCheckoutTokenUrl();

        $httpClient = $this->getAuthorizedClient();

        try {
            $this->request['reference'] = $reference;
            $this->generalHelper->addTolog('Token request: ', $this->request);

            $httpClient->addHeader('Content-Type', 'application/json;charset=UTF-8');
            $httpClient->addHeader('Accept', 'application/json;charset=UTF-8');

            $httpClient->post($url, json_encode($this->request));
            $body = $httpClient->getBody();
            $status = $httpClient->getStatus();

            $this->generalHelper->addTolog('Token response status: ', $status);
            $this->generalHelper->addTolog('Token response: ', $body);
            if ($status >= 200 && $status < 300) {
                /** @var Token $token */
                $token = $this->tokenBuilder->setResponse($body)->create();
                return $token;
            }
        } catch (\Exception $e) {
            throw ApiException::error($e);
        }

        throw ApiException::error();
    }

    /**
     * Sends order to Paazl
     *
     * @param array $orderData
     * @return boolean
     * @throws ApiException
     */
    public function addOrder(array $orderData)
    {
        $url = $this->urlProvider->getOrderUrl();

        $httpClient = $this->getAuthorizedClient();

        $this->generalHelper->addTolog('AddOrder request: ', $orderData);

        $httpClient->addHeader('Content-Type', 'application/json;charset=UTF-8');
        $httpClient->addHeader('Accept', 'application/json;charset=UTF-8');

        $httpClient->post($url, json_encode($orderData));
        $body = $httpClient->getBody();
        $status = $httpClient->getStatus();

        $this->generalHelper->addTolog('AddOrder response status: ', $status);
        $this->generalHelper->addTolog('AddOrder response body: ', $body);
        if ($status >= 400 && $status < 500) {
            throw ApiException::fromErrorResponse($body, $status);
        }

        if ($status >= 200 && $status < 300) {
            return true;
        }

        throw ApiException::error();
    }

    /**
     * @param array $orderData
     *
     * @return string
     * @throws ApiException
     */
    public function getShippingOptions(array $orderData)
    {
        $url = $this->urlProvider->getShippingOptionsUrl();

        $httpClient = $this->getAuthorizedClient();

        $this->generalHelper->addTolog('getShippingOptions request: ', $orderData);

        $httpClient->addHeader('Content-Type', 'application/json;charset=UTF-8');
        $httpClient->addHeader('Accept', 'application/json;charset=UTF-8');

        $httpClient->post($url, json_encode($orderData));
        $body = $httpClient->getBody();
        $status = $httpClient->getStatus();

        $this->generalHelper->addTolog('getShippingOptions response status: ', $status);
        $this->generalHelper->addTolog('getShippingOptions response: ', $body);
        if ($status >= 400 && $status < 500) {
            throw ApiException::fromErrorResponse($body, $status);
        }

        if ($status >= 200 && $status < 300) {
            return $body;
        }

        throw ApiException::error();
    }

    /**
     * @param string $reference
     *
     * @return mixed|null
     * @throws ApiException
     */
    public function fetchCheckoutData($reference)
    {
        $url = $this->urlProvider->getCheckoutUrl();

        $httpClient = $this->getAuthorizedClient();
        $result = null;
        try {
            $url .= '?' . http_build_query([
                    'reference' => $reference
                ]);

            $httpClient->addHeader('Accept', 'application/json;charset=UTF-8');

            $this->generalHelper->addTolog('fetchCheckoutData URL: ', $url);

            $httpClient->get($url);
            $status = $httpClient->getStatus();
            $body = $httpClient->getBody();
            $this->generalHelper->addTolog('fetchCheckoutData response status: ', $body);
            $this->generalHelper->addTolog('fetchCheckoutData response: ', $body);
            if ($status !== 200) {
                // @codingStandardsIgnoreLine
                throw new \Exception('Cannot obtain checkout info');
            }
            $result = json_decode($body, true);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('exception', $e->getMessage());
            throw ApiException::error($e);
        }

        return $result;
    }

    /**
     * @return string
     */
    private function buildAuthorizationHeader()
    {
        return 'Bearer ' . $this->configuration->getKey() . ':' . $this->configuration->getSecret();
    }

    /**
     * @return ClientInterface
     */
    private function getAuthorizedClient(): ClientInterface
    {
        $httpClient = $this->httpClientFactory->create();
        $httpClient->setHeaders([
            'Authorization' => $this->buildAuthorizationHeader()
        ]);

        $timeout = $this->configuration->getTimeout();
        if ($timeout > 0) {
            $httpClient->setTimeout($timeout);
        }

        return $httpClient;
    }
}
