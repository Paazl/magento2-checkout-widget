<?php

namespace Paazl\CheckoutWidget\Plugin\Hyva\ReactCheckout\ViewModel;

use Hyva\ReactCheckout\ViewModel\CheckoutConfigProvider;
use Magento\Framework\Serialize\SerializerInterface;
use Paazl\CheckoutWidget\Model\Checkout\PaazlConfigProvider;

class CheckoutConfigProviderPlugin
{
    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var PaazlConfigProvider
     */
    private $paazlConfigProvider;

    public function __construct(
        SerializerInterface $serializer,
        PaazlConfigProvider $paazlConfigProvider
    ) {
        $this->serializer = $serializer;
        $this->paazlConfigProvider = $paazlConfigProvider;
    }

    public function afterGetConfig(CheckoutConfigProvider $subject, string $config): string
    {
        $config = $this->serializer->unserialize($config);

        return $this->serializer->serialize(
            array_merge($config, $this->paazlConfigProvider->getConfig())
        );
    }
}
