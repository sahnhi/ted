<?php

namespace App\Registry;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Model\EmailConfiguration;

interface EmailConfigurationRegistryInterface
{
    /**
     * @return array<EmailConfigurationName>
     */
    public function getEmailConfigurations(): iterable;

    /**
     * @throws EmailConfigurationNotFoundException
     */
    public function getEmailConfigurationByName(EmailConfigurationName $emailConfigurationName): EmailConfiguration;
}
