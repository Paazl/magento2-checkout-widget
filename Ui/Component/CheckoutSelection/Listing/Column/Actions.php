<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Ui\Component\CheckoutSelection\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Actions extends Column
{

    private UrlInterface $urlBuilder;

    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        $this->urlBuilder = $urlBuilder;
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    public function prepareDataSource(array $dataSource)
    {
        if (!isset($dataSource['data']['items'])) {
            return $dataSource;
        }

        $name = $this->getData('name');
        foreach ($dataSource['data']['items'] as &$item) {
            if (!isset($item['entity_id']) || !empty($item['was_sent'])) {
                continue;
            }
            $item[$name]['send'] = [
                'href' => $this->urlBuilder->getUrl(
                    'paazl_checkoutwidget/checkoutselection/send',
                    ['id' => $item['entity_id']]
                ),
                'label' => __('Send to Paazl'),
                'confirm' => [
                    'title' => __('Send selection to Paazl'),
                    'message' => __('Send this checkout selection to Paazl?')
                ]
            ];
        }

        return $dataSource;
    }
}
