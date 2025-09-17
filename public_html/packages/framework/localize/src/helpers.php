<?php

if (!function_exists('csv_to_multi_array')) {
    /**
     * @return array<int, mixed>
     */
    function csv_to_multi_array(string $path): array
    {
        $handle = @fopen($path, 'r');
        if (!$handle) {
            return [];
        }

        /** @var string[] $header */
        $header = null;
        $result = [];
        while (($line = fgetcsv($handle, null, ',')) !== false) {
            if (!$header) { // split first row.
                $nameField = [];
                foreach ($line as $value) {
                    $value = trim($value);
                    if (empty($value)) {
                        continue;
                    }

                    $nameField[] = $value;
                }

                $header = $nameField;
                continue;
            }

            $row = [];
            foreach ($line as $index => $value) {
                $value = trim($value);
                if (empty($value)) {
                    continue;
                }

                if (!isset($header[$index])) {
                    continue;
                }

                $row[$header[$index]] = trim($value);
            }

            if (empty($row)) {
                continue;
            }

            $result[] = $row;
        }

        fclose($handle);

        return $result;
    }
}

if (!function_exists('toTranslationKey')) {
    /**
     * Get translation key.
     *
     * @param string $namespace
     * @param string $group
     * @param string $name
     *
     * @return string
     */
    function toTranslationKey(string $namespace, string $group, string $name): string
    {
        if ($group === '*') {
            return $name;
        }

        if ($namespace === '*') {
            return sprintf('%s.%s', $group, $name);
        }

        return sprintf('%s::%s.%s', $namespace, $group, $name);
    }
}

if (!function_exists('__translation_wrapper')) {
    /**
     * @param  string $phrase
     * @return string
     */
    function __translation_wrapper(string $phrase): string
    {
        if (!config('localize.disable_translation')) {
            return $phrase;
        }

        if (\Illuminate\Support\Str::match('/^[\w\d_-]+::[\w\d_-]+(\.[\w\d_-]+)+$/', $phrase)) {
            return $phrase;
        }

        if (\Illuminate\Support\Str::match('/^\[.*\]$/s', $phrase)) {
            return $phrase;
        }

        // For some phrases which are wrapped with html tag
        if (\Illuminate\Support\Str::match('/^<html>.+<\/html>$/m', $phrase)) {
            $replaced = preg_replace('/^(<html>)(\[?)(.+[^\]])(\]?)(<\/html>)$/m', '$1[$3]$5', $phrase);

            return is_string($replaced) ? $replaced : $phrase;
        }

        // Skip cases of empty string
        if (empty($phrase)) {
            return $phrase;
        }

        return sprintf('[%s]', $phrase);
    }
}

if (!function_exists('__p')) {
    /**
     * Translate the given message.
     *
     * @param string|null          $key
     * @param array<string, mixed> $replace
     * @param string|null          $locale
     *
     * @return string
     */
    function __p(string $key = null, array $replace = [], ?string $locale = null): string
    {
        if (null === $key || $key === '') {
            return '';
        }

        /**
         * NOTE: must override locale value with app locale.
         * Since there are somepoint of time, the translator locale is not updated with app locale
         * => causing some phrases using incorrect locale.
         */
        $locale = $locale ?? app()->getLocale();

        $phrase = app('translator')->get($key, $replace, $locale);

        if (!is_string($phrase)) {
            return $key;
        }

        if ($key === $phrase) {
            $isStandardSyntax = preg_match('#^[\w\d_-]+::[\w\d_-]+(\.[\w\d_-]+)+$#', $phrase);

            return $isStandardSyntax ? __translation_wrapper(__translation_prefix($phrase)) : $key;
        }

        $result = MessageFormatter::formatMessage($locale, $phrase, $replace);

        if (false === $result) {
            return $phrase;
        }

        return $result;
    }
}

if (!function_exists('__type_key')) {
    /**
     * Translate the given message.
     *
     * @param  string|null $entityType
     * @return string
     */
    function __type_key(?string $entityType = null): string
    {
        if (null === $entityType || $entityType === '') {
            return '';
        }

        $entities = resolve(MetaFox\Core\Repositories\DriverRepositoryInterface::class)->loadEntityModuleMap();
        $moduleId = $entities[$entityType] ?? null;

        if (!$moduleId) {
            return $entityType;
        }

        return sprintf('%s::phrase.entity_type_%s', $moduleId, $entityType);
    }
}

if (!function_exists('__p_type_key')) {
    /**
     * Translate the given message.
     *
     * @param string|null          $key
     * @param array<string, mixed> $replace
     * @param string|null          $locale
     *
     * @return string
     */
    function __p_type_key(?string $entityType = null, array $replace = [], ?string $locale = null): string
    {
        $key        = __type_key($entityType);
        $translated = __p($key, $replace, $locale);

        if ($translated === $key) {
            return $entityType;
        }

        return $translated;
    }
}

if (!function_exists('__translation_prefix')) {
    /**
     * Translate the given message.
     *
     * @param string|null          $key
     * @param array<string, mixed> $replace
     * @param string|null          $locale
     *
     * @return string
     */
    function __translation_prefix(string $key, ?string $phrase = null): string
    {
        if (!config('localize.display_translation_key')) {
            return is_string($phrase) ? $phrase : $key;
        }

        $key = preg_replace('#^[\w\d_-]+::(web(\.[\w\d_-]+))+$#', '$1', $key);

        $prefix = sprintf('%s: ', $key);

        if (!is_string($phrase)) {
            return $prefix;
        }

        return str($phrase)->prepend($prefix)->toString();
    }
}

if (!function_exists('__is_phrase')) {
    /**
     * Used for checking if a string is a phrase key or not?
     * NOTE: This method allows only ASCII characters, numbers, underscore and a dot.
     */
    function __is_phrase(mixed $key = null): bool
    {
        if (!is_string($key)) {
            return false;
        }

        if (empty($key)) {
            return false;
        }

        if (!preg_match('@^(?:[a-zA-Z0-9-_]+::)?(?:\w+\.)\w[\w\.]*$@D', $key)) {
            return false;
        }

        [$namespace, $group, $name] = app('translator')->parseKey($key);

        if (empty($namespace) || empty($group) || empty($name)) {
            return false;
        }

        return true;
    }
}
