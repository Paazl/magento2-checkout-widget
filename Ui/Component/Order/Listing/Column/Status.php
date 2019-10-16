<?php

namespace Paazl\CheckoutWidget\Ui\Component\Order\Listing\Column;

use Magento\Ui\Component\Listing\Columns\Column;
use Paazl\CheckoutWidget\Ui\Component\Order\Listing\Column\Status\Options;

/**
 * Class Status
 */
class Status extends Column
{
    /**
     * {@inheritdoc}
     */
    public function prepareDataSource(array $dataSource)
    {
        $dataSource = parent::prepareDataSource($dataSource);

        if (empty($dataSource['data']['items'])) {
            return $dataSource;
        }

        foreach ($dataSource['data']['items'] as &$item) {
            if ($item[$this->getData('name')] === Options::VALUE_NOT_PAAZL) {
                $item[$this->getData('name')] = '';

                continue;
            }

            $class = 'paazl-status ';
            $class .= $item[$this->getData('name')] == Options::VALUE_SUCCESSFULLY_UPDATED
                ? 'successfully-updated'
                : 'need-to-be-update';
            $item[$this->getData('name')] = '<div class="' . $class . '"></div>';
        }

        return $dataSource;
    }
}
