<?php

namespace MetaFox\Platform\ApiDoc\UrlParameters;

use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\Strategies\UrlParameters\GetFromLaravelAPI as UrlParametersGetFromLaravelAPI;

class GetFromLaravelAPI extends UrlParametersGetFromLaravelAPI
{
    protected function setTypesAndExamplesForOthers(array $parameters, ExtractedEndpointData $endpointData): array
    {
        $version = config('scribe.routes.match.versions', 'v1');

        foreach ($parameters as $name => $parameter) {
            if (empty($parameter['type'])) {
                $parameters[$name]['type'] = "string";
            }

            if ($name == 'id') {
                $parameters[$name]['type'] = "integer";
            }

            if (($parameter['example'] ?? null) === null) {
                // If the user explicitly set a `where()` constraint, use that to refine examples
                $parameterRegex               = $endpointData->route->wheres[$name] ?? null;
                $parameters[$name]['example'] = $parameterRegex
                    ? $this->castToType($this->getFaker()->regexify($parameterRegex), $parameters[$name]['type'])
                    : $this->generateDummyValue($parameters[$name]['type'], hints: ['name' => $name]);
            }

            // fix: override ver parameter
            if ($name == 'ver') {
                $parameters[$name]['example'] = $version;
            }
        }

        return $parameters;
    }
}
