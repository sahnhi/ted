<?php

declare(strict_types=1);

namespace App\Channable\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;

/**
 * @ORM\Table(name="app_channable_file_log")
 *
 * @ORM\Entity
 */
class ChannableFileLog implements ResourceInterface, ChannableFileLogStatesInterface
{
    use TimestampableEntity;

    /**
     * @ORM\Id
     *
     * @ORM\GeneratedValue(strategy="IDENTITY")
     *
     * @ORM\Column(type="integer")
     */
    private int $id;

    /**
     * @ORM\Column(name="file_name", type="string", length=255)
     */
    private string $fileName;

    /**
     * @ORM\Column(type="string")
     */
    private string $state;

    /**
     * @ORM\Column(name="last_transmission", type="datetime", nullable=true)
     */
    private ?\DateTime $lastTransmission = null;

    /**
     * @ORM\Column(name="transmission_counter", type="integer")
     */
    private int $transmissionCounter;

    /**
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(name="created_at",    type="datetime")
     */
    protected $createdAt;

    /**
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(name="updated_at",    type="datetime")
     */
    protected $updatedAt;

    public function __construct()
    {
        $this->fileName = 'channable_2024.csv';
        $this->transmissionCounter = 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFileName(): string
    {
        return $this->fileName;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function setState(string $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getLastTransmission(): ?\DateTime
    {
        return $this->lastTransmission;
    }

    public function setLastTransmission(?\DateTime $lastTransmission): self
    {
        $this->lastTransmission = $lastTransmission;

        return $this;
    }

    public function getTransmissionCounter(): int
    {
        return $this->transmissionCounter;
    }

    public function incrementTransmissionCount(): self
    {
        $this->transmissionCounter = ++$this->transmissionCounter;

        return $this;
    }

    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setUpdatedAt(\DateTime $updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }
}
