<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Ui\Component\DeferredQueue\Listing\Column;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

/**
 * Class Actions
 *
 * @package Paazl\CheckoutWidget\Ui\Component\DeferredQueue\Listing\Column
 */
class Actions extends Column
{
    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
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

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                $name = $this->getData('name');
                if (isset($item['entity_id'])) {
                    if (($item['deferred_status'] ?? null) === 'pending') {
                        $item[$name]['process'] = [
                            'href' => $this->urlBuilder->getUrl(
                                'paazl_checkoutwidget/deferredqueue/process',
                                ['id' => $item['entity_id']]
                            ),
                            'label' => __('Process Now'),
                            'confirm' => [
                                'title' => __('Process Order #${ $.$data.increment_id }'),
                                'message' => __('Move this deferred order to Processing immediately?')
                            ]
                        ];
                    }
                    $item[$name]['delete'] = [
                        'href' => $this->urlBuilder->getUrl(
                            'paazl_checkoutwidget/deferredqueue/delete',
                            ['id' => $item['entity_id']]
                        ),
                        'label' => __('Delete'),
                        'confirm' => [
                            'title' => __('Delete "${ $.$data.customer_name }"'),
                            'message' => __('Are you sure you want to delete this entry?')
                        ]
                    ];
                }
            }
        }

        return $dataSource;
    }
}
