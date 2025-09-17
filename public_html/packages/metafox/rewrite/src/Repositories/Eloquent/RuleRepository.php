<?php

namespace MetaFox\Rewrite\Repositories\Eloquent;

use MetaFox\Platform\Repositories\AbstractRepository;
use MetaFox\Rewrite\Models\Rule;
use MetaFox\Rewrite\Repositories\RuleRepositoryInterface;

/**
 * Class RuleRepository.
 */
class RuleRepository extends AbstractRepository implements RuleRepositoryInterface
{
    public function model()
    {
        return Rule::class;
    }
}
