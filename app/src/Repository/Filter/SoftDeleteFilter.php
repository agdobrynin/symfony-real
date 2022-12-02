<?php
declare(strict_types=1);

namespace App\Repository\Filter;

use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;

class SoftDeleteFilter extends SQLFilter
{
    public const NAME = 'soft_delete';
    // For an HTTP request, the name of the parameter in query_string that disables this filter
    public const GET_PARAMETER_SOFT_DELETE_DISABLED = 'with-soft-deleted';

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
