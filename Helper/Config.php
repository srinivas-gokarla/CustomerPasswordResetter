<?php

namespace Srinivas\PasswordResetter\Helper;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Config
{
    const IS_ENABLED = 'sv/password/enabled';

    const RESET_PASSWORD_DATE = 'sv/password/date';




    private ScopeConfigInterface $ScopeConfigInterface;
    private StoreManagerInterface $StoreManagerInterface;

    public function __construct(
        ScopeConfigInterface  $ScopeConfigInterface,
        StoreManagerInterface $StoreManagerInterface,
    ) {
        $this->ScopeConfigInterface = $ScopeConfigInterface;
        $this->StoreManagerInterface = $StoreManagerInterface;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEnable()
    {
        return $this->ScopeConfigInterface->getValue(self::IS_ENABLED);
        ScopeInterface::SCOPE_STORE;
        $this->StoreManagerInterface->getStore()->getStoreId();
    }
    public function getDate()
    {
        return $this->ScopeConfigInterface->getValue(self::RESET_PASSWORD_DATE);
        ScopeInterface::SCOPE_STORE;
        $this->StoreManagerInterface->getStore()->getStoreId();
    }

}
