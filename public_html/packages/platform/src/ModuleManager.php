<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Platform;

/**
 * WARNING:
 * In this time, it saves data to Redis/file, it's not good performance as it should be.
 *
 * ModuleManager should be improved:
 * - Prefer Object Cache than network base caching may save 10ms/req.
 * - Flush cache content based on .env or something else.
 * - Do not cache on Dev mode.
 * - Prefer export than use as Modules:: facade instead of calling in the instance.
 *
 * Class ModuleManager
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity) @todo consider to reduce complexity.
 */
class ModuleManager extends PackageManager
{
}
