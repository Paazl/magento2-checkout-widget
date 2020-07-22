<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Plugin\Framework\App;

use Magento\Framework\App\Action\AbstractAction;
use Magento\Framework\Controller\AbstractResult;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Paazl\CheckoutWidget\Logger\PaazlLogger;
use Paazl\CheckoutWidget\Model\Checkout\WidgetConfigProvider;
use ReflectionClass;
use ReflectionException;

/**
 * Appends configuration of the widget to the result if Onestepcheckout is active.
 */
class ActionPlugin
{
    /**
     * @var JsonSerializer
     */
    private $serializer;

    /**
     * @var WidgetConfigProvider
     */
    private $paazlConfigProvider;

    /**
     * @var PaazlLogger
     */
    private $logger;

    /**
     * ActionPlugin constructor.
     *
     * @param JsonSerializer       $serializer
     * @param WidgetConfigProvider $paazlConfigProvider
     * @param PaazlLogger          $logger
     */
    public function __construct(
        JsonSerializer $serializer,
        WidgetConfigProvider $paazlConfigProvider,
        PaazlLogger $logger
    ) {
        $this->serializer = $serializer;
        $this->paazlConfigProvider = $paazlConfigProvider;
        $this->logger = $logger;
    }

    /**
     * @param AbstractAction $subject
     * @param AbstractResult $result
     * @return AbstractResult
     */
    public function afterExecute(
        AbstractAction $subject,
        $result
    ) {
        $class = get_class($subject);
        //phpcs:ignore
        if (strpos($class, 'Onestepcheckout\Iosc\Controller\Onepage\Update') === false
            || (!is_object($result))
            || (!$result instanceof Json)
        ) {
            return $result;
        }

        $jsonValue = $this->getJsonData($result);

        try {
            if (!empty($jsonValue['data']) && is_array($jsonValue['data'])) {
                $jsonValue['data']['paazlshipping_widgetConfig'] = $this->paazlConfigProvider->getConfig();
                $result->setData($jsonValue);
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e->getLogMessage(), ['exception' => $e]);
        }
        return $result;
    }

    /**
     * @param Json $result
     * @return array|bool|float|int|mixed|string|null
     */
    private function getJsonData(Json $result)
    {
        $jsonValue = null;

        // Getting the property "json"
        try {
            $reflection = new ReflectionClass($result);
            foreach ($reflection->getProperties() as $property) {
                if ($property->getName() != 'json') {
                    continue;
                }

                $property->setAccessible(true);
                $jsonValue = $this->serializer->unserialize((string)$property->getValue($result));
                $property->setAccessible(false);
                break;
            }
        } catch (ReflectionException $e) {
            $this->logger->critical($e->getMessage(), ['exception' => $e]);
        }

        return $jsonValue;
    }
}
