<?php

namespace App\Service\MicroPost\User;

interface GetOriginalEntityDataInterface
{
    /**
     * This method use UnitOfWork from EntityManagerInterface.
     *
     * @param mixed $entity Entity for find original data before persist and flush
     * @param string $field Field name in entity
     * @return mixed
     */
    public function getOriginalValue($entity, string $field);
}
