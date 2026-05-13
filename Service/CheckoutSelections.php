<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Service;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DB\Select;
use Magento\Framework\DB\Sql\Expression;
use Magento\Framework\Serialize\SerializerInterface;
use Paazl\CheckoutWidget\Helper\General;
use Paazl\CheckoutWidget\Model\Config;

class CheckoutSelections
{

    private ResourceConnection $resource;
    private General $generalHelper;
    private SerializerInterface $json;
    private Config $config;

    private array $referencePrefix = [];

    public function __construct(
        ResourceConnection $resource,
        General $generalHelper,
        SerializerInterface $json,
        Config $config
    ) {
        $this->resource = $resource;
        $this->generalHelper = $generalHelper;
        $this->json = $json;
        $this->config = $config;
    }

    public function getUnsent(int $limit = 100): array
    {
        $settleSeconds = (int)round($this->config->getCheckoutSelectionsSettleHours() * 3600);
        $select = $this->buildQuery();
        $select->where('p.was_sent = ?', 0);
        $select->where(
            new Expression(
                '(p.is_final = 1 OR p.updated_at < (NOW() - INTERVAL ' . $settleSeconds . ' SECOND))'
            )
        );
        $select->limit($limit);
        return $this->hydrateRows($select);
    }

    /**
     * Build payload rows for the given quote IDs, regardless of was_sent flag.
     *
     * @param int[] $quoteIds
     * @return array
     */
    public function getByQuoteIds(array $quoteIds): array
    {
        if (empty($quoteIds)) {
            return [];
        }
        $select = $this->buildQuery();
        $select->where('p.quote_id IN (?)', $quoteIds);
        return $this->hydrateRows($select);
    }

    private function hydrateRows(Select $select): array
    {
        $connection = $this->resource->getConnection();
        $rows = $connection->fetchAll($select);
        $data = [];

        foreach ($rows as $row) {
            $quoteId = (int)$row['quote_id'];
            $extShippingInfo = $this->json->unserialize($row['ext_shipping_info']);
            $selectedOption = $row['selected_option'] ? $this->json->unserialize($row['selected_option']) : null;
            $isGuest = $row['customer_is_guest'] ?? '';
            $storeId = isset($row['store_id']) && $row['store_id'] ? (int)$row['store_id'] : null;
            $prefix  = $this->getReferencePrefix($storeId);
            $incrementId = !empty($row['increment_id']) ? $prefix . $row['increment_id'] : '';
            $excludedFields = $this->config->getExcludedCustomerFields($storeId);

            if (!is_array($extShippingInfo) || !isset($extShippingInfo['shippingOptions'])) {
                $this->generalHelper->addTolog(
                    'Export Checkout selections',
                    'Skipped row with invalid JSON, quote_id ' . $quoteId
                );
                continue;
            }

            $customer = [
                'email'      => $row['customer_email'] ?? null,
                'firstname'  => $row['customer_firstname'] ?? null,
                'lastname'   => $row['customer_lastname'] ?? null,
            ];

            $shippingAddress = [
                'firstname'  => $row['qa_firstname'] ?? null,
                'lastname'   => $row['qa_lastname'] ?? null,
                'company'    => $row['qa_company'] ?? null,
                'street'     => $this->parseStreet($row['qa_street'] ?? null),
                'city'       => $row['qa_city'] ?? null,
                'postcode'   => $row['qa_postcode'] ?? null,
                'region'     => $row['qa_region'] ?? null,
                'region_id'  => $row['qa_region_id'] ?? null,
                'country_id' => $row['qa_country_id'] ?? null,
                'telephone'  => $row['qa_telephone'] ?? null,
            ];

            $customer = $this->removeExcludedFields($customer, $excludedFields);
            $shippingAddress = $this->removeExcludedFields($shippingAddress, $excludedFields);

            $data[] = [
                'quote_id' => $quoteId,
                'token' => $row['token'] ?? null,
                'order_reference' => $incrementId,
                'shipping_options' => $extShippingInfo['shippingOptions'],
                'selected_option' => $selectedOption,
                'selection_updated_at' => $this->formatUtcTimestamp($row['selection_updated_at'] ?? null),
                'next_to_payment' => (bool)($row['next_to_payment'] ?? false),
                'is_final' => (bool)($row['is_final'] ?? false),
                'pickuplocation_shown' => (bool)($row['pickup_offered'] ?? false),
                'captured_at' => $this->formatUtcTimestamp($row['captured_at'] ?? null),
                'max_delivery_options' => $this->config->getShippingOptionsLimit($storeId),
                'max_pickup_options' => $this->config->getPickupLocationsPageLimit($storeId),
                'nominated_date_enabled' => $this->config->getNominatedDateEnabled($storeId),
                'green' => $this->isGreenOption($extShippingInfo['shippingOptions']),
                'customer_is_guest' => $isGuest,
                'customer' => $customer,
                'shipping_address' => $shippingAddress,
            ];
        }

        return $data;
    }

    /**
     * Mark checkout selections as sent by quote IDs.
     */
    public function markAsSent(array $quoteIds): void
    {
        if (empty($quoteIds)) {
            return;
        }
        $connection = $this->resource->getConnection();
        $table = $this->resource->getTableName('mm_paazl_checkout_selection');
        // Explicitly assign updated_at to its own value to bypass the
        // ON UPDATE CURRENT_TIMESTAMP behaviour, so the export does not
        // appear as customer activity in the "Last Activity" column.
        $connection->update(
            $table,
            [
                'was_sent'   => 1,
                'sent_at'    => new Expression('NOW()'),
                'updated_at' => new Expression($connection->quoteIdentifier('updated_at')),
            ],
            ['quote_id IN (?)' => $quoteIds]
        );
    }

    /**
     * Remove excluded fields from data array.
     * The 'region' canonical field also removes 'region_id'.
     */
    private function removeExcludedFields(array $data, array $excludedFields): array
    {
        foreach ($excludedFields as $field) {
            unset($data[$field]);
            if ($field === 'region') {
                unset($data['region_id']);
            }
        }
        return $data;
    }

    private function buildQuery(): Select
    {
        $connection = $this->resource->getConnection();
        $paazlTable = $this->resource->getTableName('mm_paazl_checkout_selection');
        $paazlQuoteTable = $this->resource->getTableName('mm_paazl_quote');
        $quoteTable = $this->resource->getTableName('quote');
        $orderTable = $this->resource->getTableName('sales_order');
        $quoteAddressTbl = $this->resource->getTableName('quote_address');

        return $connection->select()
            ->from(
                ['p' => $paazlTable],
                [
                    'quote_id',
                    'ext_shipping_info',
                    'next_to_payment',
                    'is_final',
                    'pickup_offered',
                    'captured_at' => 'created_at',
                    'selection_updated_at' => 'updated_at',
                ]
            )
            ->joinLeft(
                ['q' => $quoteTable],
                'q.entity_id = p.quote_id',
                [
                    'customer_is_guest',
                    'customer_email',
                    'customer_firstname',
                    'customer_lastname'
                ]
            )
            ->joinLeft(['so' => $orderTable], 'so.quote_id = p.quote_id', ['increment_id', 'store_id'])
            ->joinLeft(
                ['pq' => $paazlQuoteTable],
                'pq.quote_id = p.quote_id',
                ['selected_option' => 'ext_shipping_info', 'token']
            )
            ->joinLeft(
                ['qa' => $quoteAddressTbl],
                "qa.quote_id = p.quote_id AND qa.address_type = 'shipping'",
                [
                    'qa_firstname'  => 'firstname',
                    'qa_middlename' => 'middlename',
                    'qa_lastname'   => 'lastname',
                    'qa_company'    => 'company',
                    'qa_street'     => 'street',
                    'qa_city'       => 'city',
                    'qa_postcode'   => 'postcode',
                    'qa_region'     => 'region',
                    'qa_region_id'  => 'region_id',
                    'qa_country_id' => 'country_id',
                    'qa_telephone'  => 'telephone'
                ]
            )
            ->where('p.ext_shipping_info IS NOT NULL')
            ->where('p.ext_shipping_info != ?', '')
            ->order('p.quote_id DESC');
    }

    private function formatUtcTimestamp($value): string
    {
        if (!$value) {
            return '';
        }
        try {
            $dt = new \DateTimeImmutable((string)$value, new \DateTimeZone('UTC'));
            return $dt->format('Y-m-d\TH:i:s\Z');
        } catch (\Throwable $e) {
            return (string)$value;
        }
    }

    private function isGreenOption(array $shippingOptions): bool
    {
        foreach ($shippingOptions as $shippingOption) {
            if (isset($shippingOption['deliveryDates']) && is_array($shippingOption['deliveryDates'])) {
                foreach ($shippingOption['deliveryDates'] as $deliveryDate) {
                    if (isset($deliveryDate['rateReason']) && $deliveryDate['rateReason'] === 'GREEN') {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    private function parseStreet(?string $street): array
    {
        if (empty($street)) {
            return [];
        }
        return preg_split('/\r\n|\r|\n/', $street);
    }

    /**
     * Return reference prefix for a store.
     */
    private function getReferencePrefix(?int $storeId): string
    {
        if ($storeId === null) {
            return '';
        }
        if (!array_key_exists($storeId, $this->referencePrefix)) {
            $this->referencePrefix[$storeId] = (string)($this->config->getReferencePrefix($storeId) ?? '');
        }
        return $this->referencePrefix[$storeId];
    }
}
