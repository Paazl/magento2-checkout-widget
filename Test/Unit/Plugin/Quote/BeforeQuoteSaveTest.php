<?php
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Paazl\CheckoutWidget\Test\Unit\Plugin\Quote;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\ScopeInterface;
use Paazl\CheckoutWidget\Model\Config;
use Paazl\CheckoutWidget\Plugin\Quote\BeforeQuoteSave;
use Paazl\CheckoutWidget\Test\Unit\UnitTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Stub;

class BeforeQuoteSaveTest extends UnitTestCase
{
    private const STORE_ID = 3;

    /**
     * @var BeforeQuoteSave
     */
    private $entity;

    /**
     * @var ScopeConfigInterface|MockObject
     */
    private $scopeConfigMock;

    /**
     * @var Config|MockObject
     */
    private $configMock;

    /**
     * @var QuoteRepository|Stub
     */
    private $subjectMock;

    /**
     * @var Quote|Stub
     */
    private $quoteMock;

    /**
     * @var Address|MockObject
     */
    private $shippingAddressMock;

    /**
     * @var Address|MockObject
     */
    private $billingAddressMock;

    protected function setUpWithoutVoid()
    {
        $this->scopeConfigMock = $this->createMock(ScopeConfigInterface::class);
        $this->configMock = $this->createMock(Config::class);
        $this->subjectMock = $this->createStub(QuoteRepository::class);
        $this->shippingAddressMock = $this->createMock(Address::class);
        $this->billingAddressMock = $this->createMock(Address::class);

        $this->quoteMock = $this->createStub(Quote::class);
        $this->quoteMock->method('getStoreId')->willReturn(self::STORE_ID);
        $this->quoteMock->method('getShippingAddress')->willReturn($this->shippingAddressMock);
        $this->quoteMock->method('getBillingAddress')->willReturn($this->billingAddressMock);

        $this->entity = $this->objectManager->getObject(
            BeforeQuoteSave::class,
            [
                'scopeConfig' => $this->scopeConfigMock,
                'config' => $this->configMock
            ]
        );
    }

    public function testSetsOriginCountryFromQuoteStoreScope()
    {
        $this->shippingAddressMock->method('getCountryId')->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->with(self::STORE_ID)
            ->willReturn(true);
        $this->scopeConfigMock->expects($this->once())
            ->method('getValue')
            ->with(BeforeQuoteSave::ORIGIN, ScopeInterface::SCOPE_STORE, self::STORE_ID)
            ->willReturn('DE');

        $this->shippingAddressMock->expects($this->once())->method('setCountryId')->with('DE');
        $this->billingAddressMock->expects($this->once())->method('setCountryId')->with('DE');

        $this->assertSame([$this->quoteMock], $this->entity->beforeSave($this->subjectMock, $this->quoteMock));
    }

    public function testKeepsExistingShippingCountry()
    {
        $this->shippingAddressMock->method('getCountryId')->willReturn('BE');
        $this->configMock->expects($this->never())->method('isEnabled');
        $this->scopeConfigMock->expects($this->never())->method('getValue');

        $this->shippingAddressMock->expects($this->never())->method('setCountryId');
        $this->billingAddressMock->expects($this->never())->method('setCountryId');

        $this->assertSame([$this->quoteMock], $this->entity->beforeSave($this->subjectMock, $this->quoteMock));
    }

    public function testDoesNothingWhenDisabledForQuoteStore()
    {
        $this->shippingAddressMock->method('getCountryId')->willReturn(null);
        $this->configMock->expects($this->once())
            ->method('isEnabled')
            ->with(self::STORE_ID)
            ->willReturn(false);
        $this->scopeConfigMock->expects($this->never())->method('getValue');

        $this->shippingAddressMock->expects($this->never())->method('setCountryId');
        $this->billingAddressMock->expects($this->never())->method('setCountryId');

        $this->assertSame([$this->quoteMock], $this->entity->beforeSave($this->subjectMock, $this->quoteMock));
    }
}
