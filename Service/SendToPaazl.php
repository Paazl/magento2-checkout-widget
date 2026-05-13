<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Service;

use Magento\Framework\HTTP\Client\CurlFactory;
use Magento\Framework\Serialize\SerializerInterface;
use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Api\UrlProviderFactory;
use Paazl\CheckoutWidget\Model\Config;

class SendToPaazl
{

    private CurlFactory $curlFactory;
    private SerializerInterface $json;
    private GeneralHelper $generalHelper;
    private Config $config;
    private UrlProviderFactory $urlProviderFactory;
    private CheckoutSelections $checkoutSelections;

    public function __construct(
        CheckoutSelections $checkoutSelections,
        CurlFactory $curlFactory,
        SerializerInterface $json,
        GeneralHelper $generalHelper,
        Config $config,
        UrlProviderFactory $urlProviderFactory
    ) {
        $this->checkoutSelections = $checkoutSelections;
        $this->curlFactory = $curlFactory;
        $this->json = $json;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
        $this->urlProviderFactory = $urlProviderFactory;
    }

    /**
     * Send the provided selections to Paazl and mark them as sent on success.
     *
     * @param array $selections Output of CheckoutSelections::getUnsent/getByQuoteIds
     * @return int Number of selections successfully sent
     * @throws \RuntimeException when the remote call fails
     */
    public function send(array $selections): int
    {
        if (empty($selections)) {
            return 0;
        }

        $quoteIds = array_column($selections, 'quote_id');
        $payload = $this->json->serialize($selections);

        $urlProvider = $this->urlProviderFactory->create();
        $url = $urlProvider->getBaseUrl() . 'checkout/selection';

        $apiKey = $this->config->getApiKey();
        $apiSecret = $this->config->getApiSecret();

        $curl = $this->curlFactory->create();
        $curl->setHeaders([
            'Content-Type' => 'application/json',
            'Authorization' => 'Bearer ' . $apiKey . ':' . $apiSecret
        ]);
        $curl->post($url, $payload);

        $status = $curl->getStatus();
        if ($status >= 200 && $status < 300) {
            $this->checkoutSelections->markAsSent($quoteIds);
            $this->generalHelper->addTolog(
                'SendToPaazl',
                'Sent ' . count($selections) . ' selections successfully'
            );
            return count($selections);
        }

        $message = 'Failed with HTTP ' . $status . ': ' . $curl->getBody();
        $this->generalHelper->addTolog('SendToPaazl', $message);
        throw new \RuntimeException($message);
    }
}
