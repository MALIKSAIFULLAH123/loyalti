<?php

namespace MetaFox\Platform\ApiDoc\QueryParameters;

use Knuckles\Scribe\Extracting\Strategies\QueryParameters\GetFromFormRequest as QueryParametersGetFromFormRequest;
use MetaFox\Platform\Traits\ApiDoc\HasCustomValidationRules;

/**
 * Class GetFromFormRequest.
 * @ignore
 * @codeCoverageIgnore
 */
class GetFromFormRequest extends QueryParametersGetFromFormRequest
{
    use HasCustomValidationRules;
}
