<?php

namespace Srinivas\PasswordResetter\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Model\Customer;
use Magento\Eav\Model\Entity\Attribute\SetFactory as AttributeSetFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;

class PasswordUpdateAttribute implements DataPatchInterface
{
    const PASS_UPDATE_DATE = 'sv_password_updated_at';
    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var CustomerSetupFactory
     */
    protected $customerSetupFactory;

    /**
     * @var AttributeSetFactory
     */
    private $attributeSetFactory;
    private CustomerRepositoryInterface $customerRepository;
    private SearchCriteriaInterface $searchCriteria;
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeSetFactory $attributeSetFactory
     */
    public function __construct(
        ModuleDataSetupInterface    $moduleDataSetup,
        CustomerSetupFactory        $customerSetupFactory,
        AttributeSetFactory         $attributeSetFactory,
        CustomerRepositoryInterface $customerRepository,
        SearchCriteriaInterface     $searchCriteria,
        SearchCriteriaBuilder       $searchCriteriaBuilder
    )
    {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeSetFactory = $attributeSetFactory;
        $this->customerRepository = $customerRepository;
        $this->searchCriteria = $searchCriteria;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }
    /**
     * Add eav attributes
     */
    public function apply()
    {
        $this->createDateAttribute();
        $this->updateDateAttribute();
    }

    private function updateDateAttribute(){
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

    private function createDateAttribute()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $customerEntity = $customerSetup->getEavConfig()->getEntityType('customer');
        $attributeSetId = $customerEntity->getDefaultAttributeSetId();

        /** @var $attributeSet AttributeSet */
        $attributeSet = $this->attributeSetFactory->create();
        $attributeGroupId = $attributeSet->getDefaultGroupId($attributeSetId);
        $customerSetup->addAttribute(Customer::ENTITY, self::PASS_UPDATE_DATE, [
            'type' => 'datetime',
            'label' => 'password Updated At',
            'input' => 'date',
            'required' => false,
            'visible' => true,
            'user_defined' => true,
            'position' => 999,
            'system' => 0
        ]);
        $attribute = $customerSetup->getEavConfig()->getAttribute(Customer::ENTITY, self::PASS_UPDATE_DATE)
            ->addData([
                'attribute_set_id' => $attributeSetId,
                'attribute_group_id' => $attributeGroupId,
                'used_in_forms' => ['adminhtml_customer'],//you can use other forms also ['adminhtml_customer_address', 'customer_account_edit', 'customer_address_edit', 'customer_register_address', 'customer_account_create']
            ]);

        $attribute->save();

    }

    /**
     * Get dependencies
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get Aliases
     */
    public function getAliases()
    {
        return [];
    }
}
