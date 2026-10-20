<?php

declare(strict_types=1);

namespace App\Doctrine\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Sylius\Component\Resource\Model\ResourceInterface;

#[ORM\Entity]
#[ORM\Table(name: 'product_feed_file_importer')]
class ProductFeedFileImporter implements ResourceInterface
{
    use TimestampableEntity;

    public const STATE_UPLOADED = 'uploaded';
    public const STATE_PROCESSING = 'processing';
    public const STATE_PUBLISHED = 'published';
    public const STATE_REJECTED = 'rejected';

    public const TRANSITION_PROCESS = 'process';
    public const TRANSITION_PUBLISH = 'publish';
    public const TRANSITION_REJECT = 'reject';

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[ORM\Column(name: 'filename', type: 'string', length: 255)]
    private string $filename;

    #[ORM\Column(name: 'author', type: 'string', length: 255)]
    private string $author;

    #[ORM\Column(name: 'validated', type: 'boolean')]
    private ?bool $validated = null;

    #[ORM\Column(name: 'state', type: 'string', length: 255)]
    private string $state = self::STATE_UPLOADED;

    #[Gedmo\Timestampable(on: 'create')]
    #[ORM\Column(name: 'created_at', type: Types::DATETIME_MUTABLE)]
    protected $createdAt;

    #[Gedmo\Timestampable(on: 'update')]
    #[ORM\Column(name: 'updated_at', type: Types::DATETIME_MUTABLE)]
    protected $updatedAt;

    public function __construct()
    {
        $this->setFilename('file' . date('Y-m-d-H-i-s') . '.csv');
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function setFilename(string $filename): self
    {
        $this->filename = $filename;

        return $this;
    }

    public function getAuthor(): string
    {
        return $this->author;
    }

    public function setAuthor(string $author): self
    {
        $this->author = $author;

        return $this;
    }

    public function getValidated(): ?bool
    {
        return $this->validated;
    }

    public function setValidated(?bool $validated): self
    {
        $this->validated = $validated;

        return $this;
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
}
