<?php

namespace MetaFox\Platform\ApiDoc\BodyParameters;

use Knuckles\Scribe\Extracting\Strategies\BodyParameters\GetFromFormRequest as BodyParametersGetFromFormRequest;
use MetaFox\Platform\Traits\ApiDoc\HasCustomValidationRules;

class GetFromFormRequest extends BodyParametersGetFromFormRequest
{
    use HasCustomValidationRules;
}
