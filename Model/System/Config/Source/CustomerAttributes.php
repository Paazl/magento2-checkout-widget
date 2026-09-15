<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Paazl\CheckoutWidget\Setup\Patch\Data\AddCustomerTagAttribute;

/**
 * Customer attributes that can hold customer tags.
 */
class CustomerAttributes implements OptionSourceInterface
{
    /**
     * Frontend inputs whose values can be read as a list of tags.
     */
    private const SUPPORTED_INPUTS = ['text', 'textarea', 'select', 'multiselect'];

    /**
     * @var CustomerMetadataInterface
     */
    private $customerMetadata;

    /**
     * @var array|null
     */
    private $options;

    /**
     * @param CustomerMetadataInterface $customerMetadata
     */
    public function __construct(CustomerMetadataInterface $customerMetadata)
    {
        $this->customerMetadata = $customerMetadata;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [['value' => '', 'label' => __('None')]];
            foreach ($this->getAttributes() as $attribute) {
                $this->options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => sprintf(
                        '%s (%s)',
                        $attribute->getFrontendLabel() ?: $attribute->getAttributeCode(),
                        $attribute->getAttributeCode()
                    ),
                ];
            }
        }

        return $this->options;
    }

    /**
     * User-defined customer attributes with a supported input, plus the module's own.
     *
     * @return AttributeMetadataInterface[]
     */
    private function getAttributes(): array
    {
        try {
            $attributes = $this->customerMetadata->getAllAttributesMetadata();
        } catch (LocalizedException $e) {
            return [];
        }

        $result = [];
        foreach ($attributes as $attribute) {
            $isOwn = $attribute->getAttributeCode() === AddCustomerTagAttribute::ATTRIBUTE_CODE;
            if (!$isOwn && !$attribute->isUserDefined()) {
                continue;
            }
            if (!in_array($attribute->getFrontendInput(), self::SUPPORTED_INPUTS, true)) {
                continue;
            }
            $result[] = $attribute;
        }

        usort($result, function (AttributeMetadataInterface $a, AttributeMetadataInterface $b) {
            return strcmp((string)$a->getFrontendLabel(), (string)$b->getFrontendLabel());
        });

        return $result;
    }
}
