<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Plugin\Quote;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Quote\Api\CartTotalRepositoryInterface;
use Magento\Quote\Api\Data\TotalsInterface;
use Paazl\CheckoutWidget\Model\Quote\Totals\AppendShippingMethods;
use Paazl\CheckoutWidget\Model\Config;

class CartTotalRepositoryPlugin
{
    /**
     * @var Config
     */
    private $config;

    /**
     * @var AppendShippingMethods
     */
    private $appendShippingMethods;

    /**
     * CartTotalRepositoryPlugin constructor.
     *
     * @param AppendShippingMethods $appendShippingMethods
     */
    public function __construct(
        AppendShippingMethods $appendShippingMethods,
        Config $config
    ) {
        $this->appendShippingMethods = $appendShippingMethods;
        $this->config = $config;
    }

    /**
     * @param CartTotalRepositoryInterface $subject
     * @param TotalsInterface              $totals
     * @param int                          $cartId
     * @return TotalsInterface
     * @throws NoSuchEntityException
     * @throws StateException
     */
    public function afterGet(
        CartTotalRepositoryInterface $subject,
        TotalsInterface $totals,
        $cartId
    ) {
        if ($this->config->isEnabled()) {
            $this->appendShippingMethods->append($totals, $cartId);
        }
        
        return $totals;
    }
}
