<?php
declare(strict_types=1);

namespace Home\CustomerRegistration\Observer;

use Exception;
use Home\CustomerRegistration\Model\Synchronization;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\Store;
use Psr\Log\LoggerInterface;

/**
 * Saving to logs and sending a letter admin
 */
class DataProcessingCustomer implements ObserverInterface
{
    /**
     * Email admin support customer
     */
    const XML_PATH_EMAIL_SUPPORT = 'trans_email/ident_support/email';

    /** @var Synchronization */
    private $synchronization;

    /** @var DateTime */
    private $date;

    /** @var TransportBuilder */
    private $transportBuilder;

    /** @var ScopeConfigInterface */
    private $scopeConfig;

    /** @var LoggerInterface */
    private $logger;

    /**
     * @param Synchronization $synchronization
     * @param DateTime $date
     * @param TransportBuilder $transportBuilder
     * @param ScopeConfigInterface $scopeConfig
     * @param LoggerInterface $logger
     */
    public function __construct(
        Synchronization $synchronization,
        DateTime $date,
        TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        LoggerInterface $logger
    ) {
        $this->synchronization = $synchronization;
        $this->date = $date;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->logger = $logger;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $event = $observer->getEvent();
        $customer = $event->getData(CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER);
        $this->saveLogDataCustomer($customer);
        $this->sendEmailToAdmin($customer);
    }

    /**
     * Save in log customer name, last name, email
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function saveLogDataCustomer(CustomerInterface $customer): void
    {
        $dataCustomer = $this->date->gmtDate() . ' ' .
            $customer->getFirstname() . ' ' . $customer->getLastname() . ' ' . $customer->getEmail();
        $this->synchronization->setPersonalCustomerData($dataCustomer);
    }

    /**
     * Send customer data to admin email
     *
     * @param CustomerInterface $customer
     * @return void
     */
    private function sendEmailToAdmin(CustomerInterface $customer): void
    {
        $dataCustomer = 'Firstname: ' . $customer->getFirstname() .
            ' Lastname: ' . $customer->getLastname() .
            ' Email: ' . $customer->getEmail();

        $vars = [
            'customerData' => $dataCustomer,
        ];

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('customer_data_after_registration_email_template')
                ->setTemplateOptions(['area' => Area::AREA_FRONTEND, 'store' => Store::DEFAULT_STORE_ID])
                ->setTemplateVars($vars)
                ->addTo($this->scopeConfig->getValue(self::XML_PATH_EMAIL_SUPPORT, ScopeInterface::SCOPE_STORE))
                ->getTransport();
            $transport->sendMessage();
        } catch (Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }
}
