<?php
declare(strict_types=1);

namespace App\Repository\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeleteFilter extends SQLFilter
{
    public const NAME = 'soft_delete';

    /** @var <string, bool>[] */
    protected $disabled = [];

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasField('deleteAt')) {
            return $targetTableAlias . '.delete_at IS NULL';
        }

        return '';
    }
}
