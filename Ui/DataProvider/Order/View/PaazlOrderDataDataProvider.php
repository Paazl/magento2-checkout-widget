<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Ui\DataProvider\Order\View;

use Magento\Framework\View\Element\UiComponent\DataProvider\DataProviderInterface;

/**
 * DataProvider for Paazl order data form
 *
 * @api
 */
class PaazlOrderDataDataProvider implements DataProviderInterface
{
    /**
     * @var string
     */
    private $requestFieldName;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $primaryFieldName;

    /**
     * @var array
     */
    private $meta;

    /**
     * @var array
     */
    private $data;

    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->name = $name;
        $this->primaryFieldName = $primaryFieldName;
        $this->requestFieldName = $requestFieldName;
        $this->meta = $meta;
        $this->data = $data;
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritDoc}
     */
    public function getConfigData()
    {
        return [];
    }

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function setConfigData($config)
    {

    }
    // phpcs:enable

    /**
     * {@inheritDoc}
     */
    public function getMeta()
    {
        return $this->meta;
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldMetaInfo($fieldSetName, $fieldName)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldSetMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getFieldsMetaInfo($fieldSetName)
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getPrimaryFieldName()
    {
        return $this->primaryFieldName;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequestFieldName()
    {
        return $this->requestFieldName;
    }

    /**
     * {@inheritDoc}
     */
    public function getData()
    {
        return [];
    }

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function addFilter(\Magento\Framework\Api\Filter $filter)
    {

    }
    // phpcs:enable

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function addOrder($field, $direction)
    {

    }
    // phpcs:enable

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function setLimit($offset, $size)
    {

    }
    // phpcs:enable

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function getSearchCriteria()
    {

    }
    // phpcs:enable

    // phpcs:disable
    /**
     * {@inheritDoc}
     */
    public function getSearchResult()
    {

    }
    // phpcs:enable
}
