<?php

namespace App\Doctrine\Entity;

use ApiPlatform\Action\NotFoundAction;
use ApiPlatform\Core\Annotation\ApiProperty;
use ApiPlatform\Core\Annotation\ApiResource;
use App\ApiPlatform\State\Familiarity\FamiliarityCollectionProvider;
use App\Doctrine\Repository\FamiliarityRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Resource\Model\ResourceInterface;
use Sylius\Component\Resource\Model\TranslatableInterface;
use Sylius\Component\Resource\Model\TranslatableTrait;

#[ORM\Table(name: 'sylius_familiarity')]
#[ORM\Entity(repositoryClass: FamiliarityRepository::class)]
#[ApiResource(
    collectionOperations: [
        'familiarities_get' => [
           'method' => 'GET',
            'path' => '/familiarities',
            'provider' => FamiliarityCollectionProvider::class,
        ],
    ],
    itemOperations: [
        'shop_get' => [
            'method' => 'GET',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
        'shop_post' => [
            'method' => 'POST',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
        'shop_delete' => [
            'method' => 'DELETE',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
        'shop_put' => [
            'method' => 'PUT',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
        'shop_patch' => [
            'method' => 'PATCH',
            'controller' => NotFoundAction::class,
            'read' => false,
            'output' => false,
        ],
    ],
    normalizationContext: [
        'groups' => [
            'familiarity:read',
        ],
    ],
)]
class Familiarity implements ResourceInterface, TranslatableInterface, \Stringable
{
    use TranslatableTrait {
        __construct as initializeTranslationsCollection;
        getTranslation as private doGetTranslation;
    }

    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'IDENTITY')]
    #[ORM\SequenceGenerator(sequenceName: 'tablename_seq', allocationSize: 100, initialValue: 1)]
    #[ApiProperty(identifier: false)]
    private ?int $id = null;

    #[ORM\Column(name: 'code', type: 'string', length: 100, nullable: false)]
    #[ApiProperty(identifier: true)]
    private ?string $code = null;

    #[ORM\Column(name: 'enabled', type: 'boolean', nullable: true)]
    private bool $enabled = false;

    public function __construct()
    {
        $this->initializeTranslationsCollection();
    }

    public function __toString(): string
    {
        return (string) $this->getWording();
    }

    public function getWording(?string $locale = null): ?string
    {
        return $this->getTranslation($locale)->getWording();
    }

    public function getTranslation(?string $locale = null): FamiliarityTranslationInterface
    {
        /** @var FamiliarityTranslationInterface $translation */
        $translation = $this->doGetTranslation($locale);

        return $translation;
    }

    public function getName(): string
    {
        return $this->getTranslation()->getWording();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setWording(string $wording): void
    {
        $this->getTranslation()->setWording($wording);
    }

    public function getTranslations(): Collection
    {
        return $this->translations;
    }

    public function getCode(): ?string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled($enabled): static
    {
        $this->enabled = $enabled;

        return $this;
    }

    protected function createTranslation(): FamiliarityTranslationInterface
    {
        return new FamiliarityTranslation();
    }
}
