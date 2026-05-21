<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Ui\Component\DeferredQueue\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Renders the order increment_id as a link to the order view page.
 */
class OrderLink extends Column
{
    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $fieldName = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            $orderId = $item['order_id'] ?? null;
            $incrementId = $item[$fieldName] ?? '';
            if (!$orderId || $incrementId === '') {
                continue;
            }
            $item[$fieldName . '_raw'] = (string)$incrementId;
            $url = $this->urlBuilder->getUrl('sales/order/view', ['order_id' => (int)$orderId]);
            $item[$fieldName] = sprintf(
                '<a href="%s">%s</a>',
                htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
                htmlspecialchars((string)$incrementId, ENT_QUOTES, 'UTF-8')
            );
        }

        return $dataSource;
    }
}
