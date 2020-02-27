<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;
use Magento\Catalog\Model\Product\Attribute\Repository;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Attributes
 *
 * @package Paazl\CheckoutWidget\Model\System\Config\Source
 */
class Attributes implements ArrayInterface
{

    /**
     * @var array
     */
    public $options;

    /**
     * @var Repository
     */
    private $attributeRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * Attributes constructor.
     *
     * @param Repository            $attributeRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Repository $attributeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->attributeRepository = $attributeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->options) {
            $this->options[] = ['value' => null, 'label' => __('None')];
            $attributes = $this->getAttributeCollection();
            foreach ($attributes as $attribute) {
                $this->options[] = [
                    'value' => $attribute->getAttributeCode(),
                    'label' => $this->getLabel($attribute->getFrontendLabel())
                ];
            }
        }

        return $this->options;
    }

    /**
     * @return \Magento\Catalog\Api\Data\ProductAttributeInterface[]|\Magento\Eav\Api\Data\AttributeInterface[]
     */
    private function getAttributeCollection()
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('is_visible', 1)->create();
        return $this->attributeRepository->getList($searchCriteria)->getItems();
    }

    /**
     * @param string $attributeLabel
     *
     * @return string
     */
    private function getLabel($attributeLabel)
    {
        return str_replace("'", '', $attributeLabel);
    }
}
