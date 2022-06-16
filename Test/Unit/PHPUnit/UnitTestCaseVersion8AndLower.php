<?php
// phpcs:disable Generic.Classes.DuplicateClassName.Found
/**
 * Copyright © Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Test\Unit;

use Magento\Framework\TestFramework\Unit\Helper\ObjectManager;
use PHPUnit\Framework\TestCase;

class UnitTestCase extends TestCase
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    protected function setUp()
    {
        parent::setUp();

        $this->objectManager = new ObjectManager($this);
        $this->setUpWithoutVoid();
    }

    protected function setUpWithoutVoid()
    {
    }
}
