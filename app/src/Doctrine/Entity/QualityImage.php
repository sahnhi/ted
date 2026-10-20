<?php

namespace App\Doctrine\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Image;

#[ORM\Table(name: 'quality_image')]
#[ORM\Entity]
class QualityImage extends Image
{
    #[ORM\JoinColumn(name: 'owner_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: Quality::class, inversedBy: 'images')]
    protected $owner;

    public function getOwner(): ?Quality
    {
        return parent::getOwner();
    }
}
