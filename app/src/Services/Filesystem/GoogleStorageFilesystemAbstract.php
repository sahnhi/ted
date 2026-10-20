<?php

namespace App\Services\Filesystem;

use App\Enum\EmailConfigurationName;
use App\Exception\EmailConfigurationNotFoundException;
use App\Registry\EmailConfigurationRegistryInterface;
use Gaufrette\Filesystem;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Twig\Environment;

class GoogleStorageFilesystemAbstract implements GoogleStorageFilesystemInterface
{
    public function __construct(
        protected Filesystem $filesystemStorage,
        protected Filesystem $filesystemLocal,
        protected MailerInterface $mailer,
        protected Environment $twig,
        protected EmailConfigurationRegistryInterface $emailConfigurationRegistry
    ) {
    }

    public function upload(string $path, string $key): bool
    {
        try {
            $this->filesystemStorage->write(
                $key,
                file_get_contents($path),
                true
            );

            return true;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            $this->sendErrorMail(
                '[ERROR] Upload to Google Cloud Bucket' . $key,
                'Error while uploading to GCS ' . $key . ', type: ' . $exception->getMessage()
            );
        }

        return false;
    }

    public function download(string $key, string $path): ?string
    {
        try {
            $this->filesystemLocal->write(
                $path,
                $this->filesystemStorage->read($key),
                true
            );

            return $path;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            $this->sendErrorMail(
                '[ERROR] access object on Google Cloud Bucket' . $key,
                'Error cannot access file on GCS ' . $key . ', type: ' . $exception->getMessage()
            );
        }

        return null;
    }

    public function read(string $key): ?string
    {
        try {
            return $this->filesystemStorage->read($key);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            $this->sendErrorMail(
                '[ERROR] read object on Google Cloud Bucket' . $key,
                'Error cannot read object on GCS ' . $key . ', type: ' . $exception->getMessage()
            );
        }

        return null;
    }

    public function readTemporaryFile(string $key): string
    {
        $tmpDirectory = rtrim(sys_get_temp_dir(), \DIRECTORY_SEPARATOR);

        $filepath = $tmpDirectory . \DIRECTORY_SEPARATOR . basename($key);

        if (!file_put_contents($filepath, $this->read($key))) {
            throw new \RuntimeException(sprintf('Could not create file "%s"!', $filepath));
        }

        return $filepath;
    }

    public function listKeys(string $prefix): array
    {
        try {
            return $this->filesystemStorage->listKeys($prefix);
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            $this->sendErrorMail(
                '[ERROR] list object on Google Cloud Bucket' . $prefix,
                'Error cannot read directory on GCS ' . $prefix . ', de type: ' . $exception->getMessage()
            );
        }

        return [];
    }

    public function delete(string $key): bool
    {
        if (!$this->filesystemStorage->delete($key)) {
            return false;
        }

        return true;
    }

    public function move(string $keyFrom, string $keyTo): bool
    {
        return $this->filesystemStorage->rename($keyFrom, $keyTo);
    }

    public function exists(string $key): bool
    {
        return $this->filesystemStorage->has($key);
    }

    public function retrieve(string $key)
    {
        return $this->filesystemStorage->get($key);
    }

    public function mkdir(string $key): bool
    {
        try {
            $this->filesystemStorage->write(
                $key,
                'CDE !',
                true
            );

            return true;
        } catch (\Exception $exception) {
            error_log($exception->getMessage());
            $this->sendErrorMail(
                '[ERROR] Mkdir in google drive bucket' . $key,
                'Error while creating folder in GCS ' . $key . ', type: ' . $exception->getMessage()
            );
        }

        return false;
    }

    public function getFilename(string $key): string
    {
        if ($this->filesystemStorage->isDirectory($key)) {
            return '';
        }

        $exploded = explode('/', $key);

        return end($exploded);
    }

    /**
     * @throws TransportExceptionInterface
     * @throws EmailConfigurationNotFoundException
     */
    protected function sendErrorMail(string $object, string $body): void
    {
        $supportAddresses = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::SUPPORT);
        $devAddresses = $this->emailConfigurationRegistry->getEmailConfigurationByName(EmailConfigurationName::DEV);

        $message = (new Email())
            ->subject($object)
            ->from(new Address($supportAddresses->getFrom(), $supportAddresses->getFromName()))
            ->to(...$devAddresses->getPrincipals())
            ->text($body);

        $this->mailer->send($message);
    }
}
