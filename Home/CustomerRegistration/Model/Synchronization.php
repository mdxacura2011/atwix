<?php
declare(strict_types=1);

namespace Home\CustomerRegistration\Model;

use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Synchronization
 */
class Synchronization
{
    /**
     * @var PsrLoggerInterface
     */
    protected $logger;

    /**
     * @param PsrLoggerInterface $logger
     */
    public function __construct(
        PsrLoggerInterface $logger
    ) {
        $this->logger = $logger;
    }

    /**
     * @param $message
     */
    public function setPersonalCustomerData($message)
    {
        $this->logger->info($message);
    }
}
