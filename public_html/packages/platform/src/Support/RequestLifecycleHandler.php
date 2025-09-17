<?php

namespace MetaFox\Platform\Support;

class RequestLifecycleHandler
{
    protected array $terminatedCallbacks = [];

    public function onTerminated($callback): void
    {
        $this->terminatedCallbacks[] = $callback;
    }

    public function handleTerminated(): void
    {
        foreach ($this->terminatedCallbacks as $callback) {
            $callback();
        }

        $this->terminatedCallbacks = [];
    }
}
