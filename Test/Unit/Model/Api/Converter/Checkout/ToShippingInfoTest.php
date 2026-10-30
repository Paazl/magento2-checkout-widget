<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\Api\Converter\Checkout;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\ArrayManager;
use Paazl\CheckoutWidget\Model\Api\Converter\Checkout\ToShippingInfo;
use Paazl\CheckoutWidget\Model\ShippingInfo;
use Paazl\CheckoutWidget\Model\ShippingInfoFactory;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;

class ToShippingInfoTest extends UnitTestCase
{
    /**
     * @var ToShippingInfo
     */
    private $entity;

    protected function setUpWithoutVoid()
    {
        $shippingInfoFactory = $this->createStub(ShippingInfoFactory::class);
        $shippingInfoFactory->method('create')->willReturnCallback(static fn () => new ShippingInfo());

        $this->entity = new ToShippingInfo($shippingInfoFactory, new Json(), new ArrayManager());
    }

    public function testConvertsSelectedShippingOption()
    {
        $info = $this->entity->convert(json_encode([
            'deliveryType' => 'HOME',
            'shippingOption' => [
                'identifier' => 'AVG',
                'name' => 'Delivery during the day',
                'rate' => 4.95
            ]
        ]));

        $this->assertSame('AVG', $info->getIdenfifier());
        $this->assertSame('HOME', $info->getType());
        $this->assertSame(4.95, $info->getPrice());
    }

    public function testResponseWithoutShippingOptionGivesEmptySelection()
    {
        $info = $this->entity->convert(json_encode(['deliveryType' => 'HOME']));

        $this->assertNull($info->getIdenfifier());
    }

    public function testShippingOptionWithoutIdentifierGivesEmptySelection()
    {
        $info = $this->entity->convert(
            json_encode(['deliveryType' => 'HOME', 'shippingOption' => ['identifier' => null]])
        );

        $this->assertNull($info->getIdenfifier());
    }

    public function testEmptyResponseGivesEmptySelection()
    {
        $info = $this->entity->convert('');

        $this->assertNull($info->getIdenfifier());
        $this->assertNull($info->getType());
    }
}
