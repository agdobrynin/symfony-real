<?php
declare(strict_types=1);

namespace App\Service\MicroPost\User;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @codeCoverageIgnore
 */
class GetOriginalEntityData implements GetOriginalEntityDataInterface
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function getOriginalValue($entity, string $field)
    {
        return $this->entityManager->getUnitOfWork()->getOriginalEntityData($entity)[$field] ?? null;
    }
}
