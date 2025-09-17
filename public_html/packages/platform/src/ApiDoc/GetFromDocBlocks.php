<?php

namespace MetaFox\Platform\ApiDoc;

use Illuminate\Support\Str;
use Knuckles\Camel\Extraction\ExtractedEndpointData;
use Knuckles\Scribe\Extracting\RouteDocBlocker;
use Knuckles\Scribe\Extracting\Strategies\Metadata\GetFromDocBlocks as Block;
use MetaFox\App\Models\Package;
use MetaFox\App\Repositories\Eloquent\PackageRepository;

class GetFromDocBlocks extends Block
{


    public static function &getNamespaceMap()
    {
        static $namespaceMap;

        if (!$namespaceMap) {
            /** @var Package[] $apps */
            $apps = resolve(PackageRepository::class)->getModel()->newQuery()->get();
            foreach ($apps as $app) {
                $namespaceMap[$app->namespace] = $app;
            }
        }

        return $namespaceMap;

    }

    public static function estimateData($controllerName)
    {
        $updatedAt = date('M d, Y');
        $namespaceMap = static::getNamespaceMap();
        $namespace = preg_replace('/^(.+)\\\\Http\\\\Controllers.+/m', "$1", $controllerName);
        /** @var \MetaFox\App\Models\Package $info */
        $info = null;

        if (array_key_exists($namespace, $namespaceMap)) {
            $info = $namespaceMap[$namespace];
        }

        $fullSubgroupName = preg_replace('/^(.+)\\\\(\w+)Controller$/m', "$2", $controllerName);
        $fullSubgroupName = Str::headline(Str::snake($fullSubgroupName, ' '));

        $subgroupName = explode(' ', $fullSubgroupName);
        $subgroupName = Str::headline(array_pop($subgroupName));

        return [
            'defaults'  => [
                'subgroup'         => $subgroupName,
                'groupDescription' => implode(PHP_EOL, [
                    sprintf('App name: %s', $info?->title),
                    sprintf('Version: %s', $info?->version),
                    sprintf('Author: %s', $info?->author),
                    sprintf('Updated at: %s', $updatedAt),
                ]),
                'subgroupDescription' => implode(PHP_EOL, [
                    sprintf('App name: %s',  $info?->title),
                    sprintf('Resource name: %s', $fullSubgroupName),
                    sprintf('Version: %s', $info?->version),
                    sprintf('Author: [%s](%s)', $info?->author, $info->author_url),
                    sprintf('Updated at: %s', $updatedAt),
                ]),
            ],
            'overrides' => [
                'groupName' => $info?->title,
            ]
        ];
    }

    public function __invoke(ExtractedEndpointData $endpointData, array $routeRules = []): array
    {
        $data = static::estimateData($endpointData->controller->name);
        $docBlocks = RouteDocBlocker::getDocBlocksFromRoute($endpointData->route);
        $methodDocBlock = $docBlocks['method'];
        $classDocBlock = $docBlocks['class'];

        $response = array_merge($this->getMetadataFromDocBlock($methodDocBlock, $classDocBlock), $data['overrides']);

        foreach ($data['defaults'] as $name => $value) {
            if (!$response[$name]) {
                $response[$name] = $value;
            }
        }

        return $response;
    }
}