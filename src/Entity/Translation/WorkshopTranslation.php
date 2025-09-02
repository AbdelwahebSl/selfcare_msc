<?php
// src/Entity/Translation/WorkshopTranslation.php

namespace App\Entity\Translation;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Translatable\Entity\MappedSuperclass\AbstractTranslation;

/**
 * @ORM\Table(name="workshop_translations", indexes={
 *      @ORM\Index(name="workshop_translation_idx", columns={"locale", "object_class", "field", "foreign_key"})
 * })
 * @ORM\Entity
 */
class WorkshopTranslation extends AbstractTranslation
{
    // tout est hérité de AbstractTranslation
}
