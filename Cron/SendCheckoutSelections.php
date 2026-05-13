<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Cron;

use Paazl\CheckoutWidget\Helper\General as GeneralHelper;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Service\CheckoutSelections;
use Paazl\CheckoutWidget\Service\SendToPaazl;

class SendCheckoutSelections
{

    private CheckoutSelections $checkoutSelections;
    private SendToPaazl $sendToPaazl;
    private GeneralHelper $generalHelper;
    private Config $config;

    public function __construct(
        CheckoutSelections $checkoutSelections,
        SendToPaazl $sendToPaazl,
        GeneralHelper $generalHelper,
        Config $config
    ) {
        $this->checkoutSelections = $checkoutSelections;
        $this->sendToPaazl = $sendToPaazl;
        $this->generalHelper = $generalHelper;
        $this->config = $config;
    }

    public function execute(): void
    {
        if (!$this->config->saveCheckoutSelections()) {
            return;
        }

        $batchSize = $this->config->getCheckoutSelectionsBatchSize();
        $selections = $this->checkoutSelections->getUnsent($batchSize);
        if (empty($selections)) {
            return;
        }

        try {
            $this->sendToPaazl->send($selections);
        } catch (\Exception $e) {
            $this->generalHelper->addTolog('SendCheckoutSelections', $e->getMessage());
        }
    }
}
