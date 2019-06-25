<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Api;

use Magento\Framework\HTTP\Adapter\CurlFactory;
use Magento\Framework\HTTP\ClientFactory;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Api\Response\Data\Token;
use Paazl\CheckoutWidget\Model\Api\Response\Data\TokenBuilder;
use Paazl\CheckoutWidget\Model\Config;

/**
 * Class PaazlApi
 *
 * @package Paazl\CheckoutWidget\Model\Api
 */
class PaazlApi
{

    /**
     * @var Config
     */
    private $scopeConfig;

    /**
     * @var CurlFactory
     */
    private $adapter;

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
     * @param Config        $scopeConfig
     * @param GeneralHelper $generalHelper
     * @param CurlFactory   $adapter
     * @param ClientFactory $httpClientFactory
     * @param TokenBuilder  $tokenBuilder
     * @param UrlProvider   $urlProvider
     */
    public function __construct(
        Config $scopeConfig,
        GeneralHelper $generalHelper,
        CurlFactory $adapter,
        ClientFactory $httpClientFactory,
        TokenBuilder $tokenBuilder,
        UrlProvider $urlProvider
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->adapter = $adapter;
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

        $httpAdapter = $this->adapter->create();

        try {
            $this->request['reference'] = $reference;
            $this->generalHelper->addTolog('Token request', $this->request);
            $httpAdapter->write(
                \Zend_Http_Client::POST,
                $url,
                '1.1',
                [
                    'Content-Type: application/json;charset=UTF-8',
                    'Accept: application/json;charset=UTF-8',
                    'Authorization: ' . $this->buildAuthorizationHeader()
                ],
                json_encode($this->request)
            );

            $response = $httpAdapter->read();
            $body = \Zend_Http_Response::extractBody($response);
            $status = \Zend_Http_Response::extractCode($response);
            $this->generalHelper->addTolog('Token response', $body);
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

        $httpAdapter = $this->adapter->create();
        $this->generalHelper->addTolog('Order request', $orderData);
        $httpAdapter->write(
            \Zend_Http_Client::POST,
            $url,
            '1.1',
            [
                'Content-Type: application/json;charset=UTF-8',
                'Accept: application/json;charset=UTF-8',
                'Authorization: ' . $this->buildAuthorizationHeader()
            ],
            json_encode($orderData)
        );
        $response = $httpAdapter->read();
        $body = \Zend_Http_Response::extractBody($response);
        $status = \Zend_Http_Response::extractCode($response);
        $this->generalHelper->addTolog('debug', $body);
        if ($status >= 400 && $status < 500) {
            throw ApiException::fromErrorResponse($body, $status);
        }

        if ($status >= 200 && $status < 300) {
            return true;
        }

        throw ApiException::error();
    }

    /**
     * @param $reference
     *
     * @return mixed|null
     * @throws ApiException
     */
    public function fetchCheckoutData($reference)
    {
        $url = $this->urlProvider->getCheckoutUrl();

        $httpClient = $this->httpClientFactory->create();
        $result = null;
        try {
            $url .= '?' . http_build_query([
                    'reference' => $reference
                ]);
            $httpClient->setHeaders([
                'Accept' => 'application/json;charset=UTF-8',
                'Authorization' => $this->buildAuthorizationHeader()
            ]);
            $httpClient->get($url);
            $status = $httpClient->getStatus();
            $body = $httpClient->getBody();
            if ($status !== 200) {
                // @codingStandardsIgnoreLine
                throw new \Exception('Cannot obtain checkout info');
            }
            $result = json_decode($body, true);
            $this->generalHelper->addTolog('debug', $body);
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
        return 'Bearer ' . $this->getApiKey() . ':' . $this->getApiSecret();
    }

    /**
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getApiKey();
    }

    /**
     * @return mixed
     */
    public function getApiSecret()
    {
        return $this->scopeConfig->getApiSecret();
    }
}
