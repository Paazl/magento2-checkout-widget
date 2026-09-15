<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Model\CustomerTag;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\AttributeMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\OptionInterface;
use Magento\Framework\Api\AttributeInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\Quote;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Model\CustomerTag\MatrixResolver;
use Paazl\CheckoutWidget\Model\DeliveryMatrix\Letter;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;

class MatrixResolverTest extends UnitTestCase
{
    private const ATTRIBUTE_CODE = 'paazl_customer_tag';

    /**
     * @var MatrixResolver
     */
    private $resolver;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var CustomerRepositoryInterface|MockObject
     */
    private $customerRepositoryMock;

    /**
     * @var CustomerMetadataInterface|MockObject
     */
    private $customerMetadataMock;

    /**
     * @var Quote|MockObject
     */
    private $quoteMock;

    protected function setUpWithoutVoid()
    {
        $this->configMock = $this->createMock(Config::class);
        $this->customerRepositoryMock = $this->createMock(CustomerRepositoryInterface::class);
        $this->customerMetadataMock = $this->createMock(CustomerMetadataInterface::class);

        $this->quoteMock = $this->createMock(Quote::class);
        $this->quoteMock->method('getStoreId')->willReturn(1);

        $this->configMock->method('isCustomerTagMatrixEnabled')->willReturn(true);
        $this->configMock->method('getCustomerTagAttribute')->willReturn(self::ATTRIBUTE_CODE);
        $this->configMock->method('getCustomerTagMatrix')->willReturn([
            'vip' => 'B',
            'dhl1' => 'D',
            'loyalty' => 'E',
            'gold' => 'AA',
        ]);

        $this->resolver = new MatrixResolver(
            $this->configMock,
            $this->customerRepositoryMock,
            $this->customerMetadataMock,
            new Letter()
        );
    }

    public function testDisabledReturnsNull()
    {
        $config = $this->createMock(Config::class);
        $config->method('isCustomerTagMatrixEnabled')->willReturn(false);
        $repository = $this->createMock(CustomerRepositoryInterface::class);
        $repository->expects($this->never())->method('getById');

        $resolver = new MatrixResolver($config, $repository, $this->customerMetadataMock, new Letter());

        $this->assertNull($resolver->resolve($this->quoteMock));
    }

    public function testGuestReturnsNull()
    {
        $this->givenQuoteCustomerId(null);
        $this->customerRepositoryMock->expects($this->never())->method('getById');

        $this->assertNull($this->resolver->resolve($this->quoteMock));
    }

    public function testMissingCustomerReturnsNull()
    {
        $this->givenQuoteCustomerId(42);
        $this->customerRepositoryMock->method('getById')
            ->willThrowException(new NoSuchEntityException(__('gone')));

        $this->assertNull($this->resolver->resolve($this->quoteMock));
    }

    /**
     * @param mixed $storedValue
     * @param string|null $expected
     * @dataProvider textAttributeDataProvider
     */
    public function testResolvesFromTextAttribute($storedValue, $expected)
    {
        $this->givenCustomerWithValue($storedValue);
        $this->givenAttributeOptions([]);

        $this->assertSame($expected, $this->resolver->resolve($this->quoteMock));
    }

    /**
     * @return array[]
     */
    public function textAttributeDataProvider()
    {
        return [
            'single tag' => ['vip', 'B'],
            'case-insensitive' => ['VIP', 'B'],
            'last letter wins across tags' => ['dhl1,loyalty', 'E'],
            'order does not matter' => ['loyalty, dhl1', 'E'],
            'two letters outrank one' => ['loyalty,gold', 'AA'],
            'unmapped tags are ignored' => ['unknown,vip', 'B'],
            'nothing mapped' => ['unknown', null],
            'empty value' => ['', null],
            'null value' => [null, null],
            'array value' => [['dhl1', 'vip'], 'D'],
        ];
    }

    public function testResolvesMultiselectOptionIdsThroughLabels()
    {
        $this->givenCustomerWithValue('12,15');
        $this->givenAttributeOptions(['12' => 'VIP', '15' => 'Loyalty']);

        $this->assertSame('E', $this->resolver->resolve($this->quoteMock));
    }

    public function testCustomerWithoutAttributeReturnsNull()
    {
        $this->givenQuoteCustomerId(7);
        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with(self::ATTRIBUTE_CODE)->willReturn(null);
        $this->customerRepositoryMock->method('getById')->with(7)->willReturn($customer);

        $this->assertNull($this->resolver->resolve($this->quoteMock));
    }

    /**
     * @param mixed $value
     */
    private function givenCustomerWithValue($value)
    {
        $this->givenQuoteCustomerId(7);

        $attribute = $this->createMock(AttributeInterface::class);
        $attribute->method('getValue')->willReturn($value);

        $customer = $this->createMock(CustomerInterface::class);
        $customer->method('getCustomAttribute')->with(self::ATTRIBUTE_CODE)->willReturn($attribute);

        $this->customerRepositoryMock->method('getById')->with(7)->willReturn($customer);
    }

    /**
     * @param int|null $customerId
     */
    private function givenQuoteCustomerId($customerId)
    {
        $quoteCustomer = $this->createMock(CustomerInterface::class);
        $quoteCustomer->method('getId')->willReturn($customerId);
        $this->quoteMock->method('getCustomer')->willReturn($quoteCustomer);
    }

    /**
     * @param array $options value => label
     */
    private function givenAttributeOptions(array $options)
    {
        $optionMocks = [];
        foreach ($options as $value => $label) {
            $option = $this->createMock(OptionInterface::class);
            $option->method('getValue')->willReturn((string)$value);
            $option->method('getLabel')->willReturn($label);
            $optionMocks[] = $option;
        }

        $metadata = $this->createMock(AttributeMetadataInterface::class);
        $metadata->method('getOptions')->willReturn($optionMocks);
        $this->customerMetadataMock->method('getAttributeMetadata')
            ->with(self::ATTRIBUTE_CODE)
            ->willReturn($metadata);
    }
}
