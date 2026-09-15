<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\CustomerTag;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\DeliveryMatrix\Letter;

/**
 * Resolves the delivery matrix start position for the customer on a quote,
 * based on the customer tag -> matrix position mapping in configuration.
 */
class MatrixResolver
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var Letter
     */
    private $letter;

    /**
     * Option value => label per attribute code, loaded on first use.
     *
     * @var array
     */
    private $optionLabels = [];

    /**
     * @param Config                      $config
     * @param CustomerRepositoryInterface $customerRepository
     * @param CustomerMetadataInterface   $customerMetadata
     * @param Letter                      $letter
     */
    public function __construct(
        Config $config,
        CustomerRepositoryInterface $customerRepository,
        CustomerMetadataInterface $customerMetadata,
        Letter $letter
    ) {
        $this->config = $config;
        $this->customerRepository = $customerRepository;
        $this->customerMetadata = $customerMetadata;
        $this->letter = $letter;
    }

    /**
     * Highest mapped matrix position across the customer's tags; null when none applies.
     *
     * @param Quote $quote
     * @return string|null
     */
    public function resolve(Quote $quote): ?string
    {
        $storeId = $quote->getStoreId();
        if (!$this->config->isCustomerTagMatrixEnabled($storeId)) {
            return null;
        }

        $mapping = $this->config->getCustomerTagMatrix($storeId);
        if (!$mapping) {
            return null;
        }

        $letters = [];
        foreach ($this->getCustomerTags($quote) as $tag) {
            $key = mb_strtolower($tag);
            if (isset($mapping[$key])) {
                $letters[] = $mapping[$key];
            }
        }

        return $this->letter->highest($letters);
    }

    /**
     * Tags on the quote's customer. Guests have none.
     *
     * Multiselect/select values are stored as option IDs, so both the raw value
     * and its option label are returned; the mapping can then use either.
     *
     * @param Quote $quote
     * @return string[]
     */
    public function getCustomerTags(Quote $quote): array
    {
        $customerId = (int)$quote->getCustomer()->getId();
        $attributeCode = $this->config->getCustomerTagAttribute($quote->getStoreId());
        if (!$customerId || $attributeCode === '') {
            return [];
        }

        // Reload through the repository so custom attributes are guaranteed to be
        // populated regardless of how the customer was attached to the quote.
        try {
            $customer = $this->customerRepository->getById($customerId);
        } catch (LocalizedException $e) {
            return [];
        }

        $attribute = $customer->getCustomAttribute($attributeCode);
        $raw = $attribute ? $attribute->getValue() : null;
        if ($raw === null || $raw === '' || $raw === []) {
            return [];
        }

        $values = is_array($raw) ? $raw : explode(',', (string)$raw);
        $labels = $this->getOptionLabels($attributeCode);

        $tags = [];
        foreach ($values as $value) {
            $value = trim((string)$value);
            if ($value === '') {
                continue;
            }
            $tags[] = $value;
            if (isset($labels[$value])) {
                $tags[] = $labels[$value];
            }
        }

        return $tags;
    }

    /**
     * Option value => label for the attribute; empty for attributes without options.
     *
     * @param string $attributeCode
     * @return string[]
     */
    private function getOptionLabels(string $attributeCode): array
    {
        if (!isset($this->optionLabels[$attributeCode])) {
            $this->optionLabels[$attributeCode] = [];
            try {
                $options = $this->customerMetadata->getAttributeMetadata($attributeCode)->getOptions();
            } catch (LocalizedException $e) {
                $options = [];
            }
            foreach ($options as $option) {
                $value = (string)$option->getValue();
                $label = trim((string)$option->getLabel());
                if ($value !== '' && $label !== '') {
                    $this->optionLabels[$attributeCode][$value] = $label;
                }
            }
        }

        return $this->optionLabels[$attributeCode];
    }
}
