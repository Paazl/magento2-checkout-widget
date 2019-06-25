<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Console\Command;

use Magento\Framework\App\Area;
use Magento\Framework\App\State;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Sales\Api\OrderRepositoryInterface;
use Paazl\CheckoutWidget\Model\Api\Processor\SendToService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Sends order to Paazl
 *
 * @package Paazl\CheckoutWidget\Console\Command
 */
class OrderSendCommand extends Command
{

    /**
     * @var string
     */
    const ARG_ORDER = 'order';

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var SendToService
     */
    private $sendToService;

    /**
     * @var State
     */
    private $state;

    /**
     * OrderSendCommand constructor.
     *
     * @param OrderRepositoryInterface $orderRepository
     * @param SendToService            $sendToService
     * @param State                    $state
     */
    public function __construct(
        OrderRepositoryInterface $orderRepository,
        SendToService $sendToService,
        State $state
    ) {
        $this->orderRepository = $orderRepository;
        $this->sendToService = $sendToService;
        parent::__construct();
        $this->state = $state;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('paazl:order:send')
            ->setDescription('Sends selected order to Paazl')
            ->setDefinition($this->getInputList());

        parent::configure();
    }

    /**
     * @return array
     */
    public function getInputList()
    {
        return [
            new InputArgument(
                OrderSendCommand::ARG_ORDER,
                InputArgument::REQUIRED | InputArgument::IS_ARRAY,
                'Space-separated list of order increment IDs.'
            ),
        ];
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|void|null
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $this->state->setAreaCode(Area::AREA_ADMINHTML);
        } catch (LocalizedException $e) {
        }

        $orderIds = $input->getArgument(OrderSendCommand::ARG_ORDER);

        if (!is_array($orderIds)) {
            $orderIds = [$orderIds];
        }

        foreach ($orderIds as $item) {
            try {
                $order = $this->orderRepository->get($item);
                $this->sendToService->process($order);

                $output->writeln("<info>Order ID {$item}: OK</info>");
            } catch (LocalizedException $e) {
                $output->writeln("<error>Order ID {$item}: {$e->getMessage()}</error>");
            }
        }
    }
}
