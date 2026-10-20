<?php

declare(strict_types=1);

namespace App\Registry;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Model\EmailConfiguration;

final class EmailConfigurationRegistry implements EmailConfigurationRegistryInterface
{
    public function __construct(
        private iterable $emailConfigurations = []
    ) {
    }

    final public function addEmailConfiguration(string $name, array $emailConfiguration): void
    {
        $this->emailConfigurations[$name] = $this->createEmailConfigurationModel($name, $emailConfiguration);
    }

    public function getEmailConfigurations(): iterable
    {
        return $this->emailConfigurations;
    }

    /**
     * @throws EmailConfigurationNotFoundException
     */
    public function getEmailConfigurationByName(EmailConfigurationName $emailConfigurationName): EmailConfiguration
    {
        return $this->emailConfigurations[$emailConfigurationName->value] ?? throw new EmailConfigurationNotFoundException(sprintf('No Email configuration found for "%s"', $emailConfigurationName->value));
    }

    private function createEmailConfigurationModel(string $name, array $emailConfiguration): EmailConfiguration
    {
        return new EmailConfiguration(
            EmailConfigurationName::from($name),
            is_array($emailConfiguration['principals']) ? $emailConfiguration['principals'] : [$emailConfiguration['principals']],
            array_key_exists('from', $emailConfiguration) ? $emailConfiguration['from'] : null,
            array_key_exists('from_name', $emailConfiguration) ? $emailConfiguration['from_name'] : null,
            array_key_exists('ccs', $emailConfiguration) ? $emailConfiguration['ccs'] : null,
        );
    }
}
