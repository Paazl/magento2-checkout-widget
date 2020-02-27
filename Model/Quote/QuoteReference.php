<?php
/**
 * Copyright © 2019 Paazl. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Paazl\CheckoutWidget\Model\Quote;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Stdlib\DateTime;
use Paazl\CheckoutWidget\Api\Data\Quote\QuoteReferenceInterface;
use Paazl\CheckoutWidget\Model\ResourceModel\Quote\QuoteReference as QuoteReferenceResource;

/**
 * Class QuoteReference
 *
 * @package Paazl\CheckoutWidget\Model\Quote
 */
class QuoteReference extends AbstractModel implements QuoteReferenceInterface
{
    /**
     * Init resource model.
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init(QuoteReferenceResource::class);
    }

    /**
     * @return int|null
     */
    public function getQuoteId()
    {
        return $this->getData(self::QUOTE_ID);
    }

    /**
     * @param int $value
     *
     * @return $this
     */
    public function setQuoteId(int $value)
    {
        return $this->setData(self::QUOTE_ID, $value);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setExtShippingInfo($value)
    {
        return $this->setData(self::EXT_SHIPPING_INFO, $value);
    }

    /**
     * @return string
     */
    public function getExtShippingInfo()
    {
        return $this->getData(self::EXT_SHIPPING_INFO);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setToken($value)
    {
        return $this->setData(self::TOKEN, $value);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * @param string $value
     *
     * @return $this
     */
    public function setTokenExpiresAt($value)
    {
        return $this->setData(self::TOKEN_EXPIRES_AT, $value);
    }

    /**
     * @return string
     */
    public function getTokenExpiresAt()
    {
        return $this->getData(self::TOKEN_EXPIRES_AT);
    }

    /**
     * @inheritDoc
     */
    public function isTokenExpired(\DateTime $now = null)
    {
        $result = true;
        $expiresAt = $this->getTokenExpiresAt();
        if (empty($expiresAt)) {
            return $result;
        }

        $expires = \DateTime::createFromFormat(
            DateTime::DATETIME_PHP_FORMAT,
            $expiresAt,
            new \DateTimeZone('UTC')
        );

        try {
            $now = $now ?: new \DateTime('now', new \DateTimeZone('UTC'));
            // phpcs:ignore
        } catch (\Exception $e) {
        }

        if ($now && $expires && ($now < $expires)) {
            $result = false;
        }

        return $result;
    }
}
