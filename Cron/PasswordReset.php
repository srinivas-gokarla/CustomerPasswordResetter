<?php

namespace Srinivas\PasswordResetter\Cron;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Area;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Math\Random;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Store\Model\ScopeInterface;
use  Srinivas\PasswordResetter\Helper\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Customer\Model\CustomerRegistry;
use Magento\Customer\Helper\View as CustomerViewHelper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Intl\DateTimeFactory;
use Magento\Framework\Reflection\DataObjectProcessor;
class PasswordReset
{
    public const XML_PATH_FORGOT_EMAIL_IDENTITY = 'customer/password/forgot_email_identity';

    const EMAIL_TEMPLATE = 'sv_password_template';
    const PASS_UPDATE_DATE = 'sv_password_updated_at';
    private SearchCriteriaBuilder $searchCriteriaBuilder;
    private DateTime $dateTime;
    private CustomerRepositoryInterface $customerRepository;
    private Config $config;
    private ScopeConfigInterface $scopeConfig;
    private TransportBuilder $transportBuilder;
    private StoreManagerInterface $storeManager;
    private Random $mathRandom;
    private StoreManagerInterface $storeManager1;
    private CustomerRegistry $customerRegistry;
    private CustomerViewHelper $customerViewHelper;
    private DateTimeFactory $dateTimeFactory;
    private DataObjectProcessor $dataProcessor;
    public function __construct(
        SearchCriteriaBuilder       $searchCriteriaBuilder,
        DateTime                    $dateTime,
        CustomerRepositoryInterface $customerRepository,
        Config                      $config,
        ScopeConfigInterface        $scopeConfig,
        TransportBuilder            $transportBuilder,
        StoreManagerInterface       $storeManager,
        Random                      $mathRandom,
        StoreManagerInterface       $storeManager1,
        CustomerRegistry            $customerRegistry,
        CustomerViewHelper          $customerViewHelper,
        DateTimeFactory             $dateTimeFactory,
        DataObjectProcessor         $dataProcessor

    )
    {
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->storeManager = $storeManager;
        $this->mathRandom = $mathRandom;
        $this->storeManager1 = $storeManager1;
        $this->customerRegistry = $customerRegistry;
        $this->customerViewHelper = $customerViewHelper;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->dataProcessor = $dataProcessor;
    }
    private function disableAddressValidation($customer)
    {
        foreach ($customer->getAddresses() as $address) {
            $addressModel = $this->customerRegistry->retrieve($address->getId());
            $addressModel->setShouldIgnoreValidation(true);
        }
    }
    public function changeResetPasswordLinkToken(CustomerInterface $customer, string $passwordLinkToken): bool
    {
        if (!is_string($passwordLinkToken) || empty($passwordLinkToken)) {
            throw new InputException(
                __(
                    'Invalid value of "%value" provided for the %fieldName field.',
                    ['value' => $passwordLinkToken, 'fieldName' => 'password reset token']
                )
            );
        } else {
            $customerSecure = $this->customerRegistry->retrieveSecureData($customer->getId());
            $customerSecure->setRpToken($passwordLinkToken);
            $customerSecure->setRpTokenCreatedAt(
                $this->dateTimeFactory->create()->format(\Magento\Framework\Stdlib\DateTime::DATETIME_PHP_FORMAT)
            );
            $this->customerRepository->save($customer);
        }
        return true;
    }
    protected function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            reset($storeIds);
            $defaultStoreId = current($storeIds);
        }
        return $defaultStoreId;
    }
    protected function getFullCustomerObject($customer)
    {
        $mergedCustomerData = $this->customerRegistry->retrieveSecureData($customer->getId());
        $customerData = $this->dataProcessor->buildOutputDataArray(
            $customer,
            CustomerInterface::class
        );
        $mergedCustomerData->addData($customerData);
        $mergedCustomerData->setData('name', $this->customerViewHelper->getCustomerName($customer));
        return $mergedCustomerData;
    }
    public function sendPasswordReminderEmail($customer)
    {
        $storeId = $this->storeManager->getStore()->getId();
        if (!$storeId) {
            $storeId = $this->getWebsiteStoreId($customer);
        }

        $customerEmailData = $this->getFullCustomerObject($customer);

        $this->sendEmailTemplate(
            $customer,
            self::EMAIL_TEMPLATE,
            self::XML_PATH_FORGOT_EMAIL_IDENTITY,
            ['customer' => $customerEmailData, 'store' => $this->storeManager->getStore($storeId)],
            $storeId
        );
    }
    protected function sendEmailTemplate(
        $customer,
        $template,
        $sender,
        $templateParams = [],
        $storeId = null,
        $email = null
    ) {
        $templateId = $this->scopeConfig->getValue(
            $template,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($email === null) {
            $email = $customer->getEmail();
        }

        $transport = $this->transportBuilder->setTemplateIdentifier(self::EMAIL_TEMPLATE)
            ->setTemplateOptions(
                [
                    'area' => Area::AREA_FRONTEND,
                    'store' => $storeId
                ]
            )
            ->setTemplateVars($templateParams)
            ->setFrom(
                $this->scopeConfig->getValue(
                    $sender,
                    ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            )
            ->addTo($email, $this->customerViewHelper->getCustomerName($customer))
            ->getTransport();

        $transport->sendMessage();

        return $this;
    }


    /**
     * @throws NoSuchEntityException
     * @throws InputException
     * @throws LocalizedException
     */
    public function execute()
    {
        $enabled = $this->scopeConfig->getValue(
            'sv/password/enabled',
            ScopeInterface::SCOPE_STORE,
        );
        if ($enabled) {
            $date = $this->config->getDate();
            $searchCriteria = $this->searchCriteriaBuilder->addFilter(
                'sv_password_updated_at',
                '%' . $this->dateTime->gmtDate('y-m-d', strtotime('-' . $date . 'day')) . '%',
                'like'
            )->create();
            $customers = $this->customerRepository->getList($searchCriteria)->getItems();
            foreach ($customers as $customer) {

                $websiteId = $customer->getWebsiteId();
                $email = $customer->getEmail();
                if ($websiteId === null) {
                    $websiteId = $this->storeManager->getStore()->getWebsiteId();
                }
                $customer1 = $this->customerRepository->get($email, $websiteId);
                $this->disableAddressValidation($customer1);
                $newPasswordToken = $this->mathRandom->getUniqueHash();
                $this->changeResetPasswordLinkToken($customer1, $newPasswordToken);
                $this->sendPasswordReminderEmail($customer1);
            $customer->setCustomAttribute(self::PASS_UPDATE_DATE, $this->dateTime->gmtDate('y-m-d'));
            $this->customerRepository->save($customer);
            }
        }
    }
}
