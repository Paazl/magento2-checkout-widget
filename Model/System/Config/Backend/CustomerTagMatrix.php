<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Model\System\Config\Backend;

use Magento\Config\Model\Config\Backend\Serialized\ArraySerialized;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Paazl\CheckoutWidget\Model\DeliveryMatrix\Letter;

/**
 * Validates and normalizes the customer tag -> matrix position rows before they are stored.
 */
class CustomerTagMatrix extends ArraySerialized
{
    public const COLUMN_TAG = 'tag';
    public const COLUMN_LETTER = 'matrix_letter';

    /**
     * @var Letter
     */
    private $letter;

    /**
     * CustomerTagMatrix constructor.
     *
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param Letter $letter
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        Letter $letter,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        array $data = [],
        ?Json $serializer = null
    ) {
        $this->letter = $letter;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data,
            $serializer
        );
    }

    /**
     * @inheritdoc
     *
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            unset($value['__empty']);
            $this->setValue($this->normalizeRows($value));
        }

        return parent::beforeSave();
    }

    /**
     * Trim tags, upper-case letters and reject empty, invalid or duplicate rows.
     *
     * @param array $rows
     * @return array
     * @throws LocalizedException
     */
    private function normalizeRows(array $rows): array
    {
        $normalized = [];
        $seen = [];
        foreach ($rows as $key => $row) {
            if (!is_array($row)) {
                continue;
            }
            $tag = trim((string)($row[self::COLUMN_TAG] ?? ''));
            $letter = $this->letter->normalize($row[self::COLUMN_LETTER] ?? null);

            if ($tag === '') {
                throw new LocalizedException(__('Customer Tag Matrix: the customer tag cannot be empty.'));
            }
            if (!$this->letter->isValid($letter)) {
                throw new LocalizedException(__(
                    'Customer Tag Matrix: "%1" is not a valid matrix position for tag "%2". '
                    . 'Use one or two capital letters (from "A" to "Z", followed by "AA" to "ZZ").',
                    $row[self::COLUMN_LETTER] ?? '',
                    $tag
                ));
            }

            $lookup = mb_strtolower($tag);
            if (isset($seen[$lookup])) {
                throw new LocalizedException(__(
                    'Customer Tag Matrix: the customer tag "%1" is mapped more than once.',
                    $tag
                ));
            }
            $seen[$lookup] = true;

            $normalized[$key] = [
                self::COLUMN_TAG => $tag,
                self::COLUMN_LETTER => $letter,
            ];
        }

        return $normalized;
    }
}
