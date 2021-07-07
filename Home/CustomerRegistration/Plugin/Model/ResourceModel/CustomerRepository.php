<?php
declare(strict_types=1);

namespace Home\CustomerRegistration\Plugin\Model\ResourceModel;

use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Editing the username field
 */
class CustomerRepository
{
    /**
     * @param CustomerRepositoryInterface $subject
     * @param CustomerInterface $customer
     * @param null $passwordHash
     */
    public function beforeSave(CustomerRepositoryInterface $subject, CustomerInterface $customer, $passwordHash = null)
    {
        $customer->setFirstname(trim($customer->getFirstname()));

        return [$customer, $passwordHash];
    }
}
