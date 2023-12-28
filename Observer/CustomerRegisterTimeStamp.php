<?php

namespace Srinivas\PasswordResetter\Observer;

use Magento\Customer\Model\Customer;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;

class CustomerRegisterTimeStamp implements ObserverInterface

{
    const PASS_UPDATE_DATE = 'sv_password_updated_at';

    protected $customerRepository;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    public function __construct(

        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    )

    {

        $this->customerRepository = $customerRepository;

        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    public function execute(\Magento\Framework\Event\Observer $observer)

    {
        $searchCriteria = $this->searchCriteriaBuilder->create();
        /** @var SearchCriteriaInterface $searchCriteria */
        try {
            $customers = $this->customerRepository->getList($searchCriteria)->getItems();
            /** @var Customer $customer */
            foreach ($customers as $customer) {
                $customer->setCustomAttribute(self::PASS_UPDATE_DATE, $customer->getUpdatedAt());
                $this->customerRepository->save($customer);
            }
        } catch (LocalizedException $e) {
            echo $e->getMessage();
        }

    }

}
