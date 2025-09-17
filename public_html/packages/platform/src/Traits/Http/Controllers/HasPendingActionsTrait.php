<?php

namespace MetaFox\Platform\Traits\Http\Controllers;

trait HasPendingActionsTrait
{
    /**
     * Dispatch and collect all actions currently pending for the related model.
     * These can be later be utilized by clients to display prompts or warning messages to attract user's attention.
     * @param mixed      $model
     * @param array|null $params
     *
     * @return array
     */
    public function collectPendingActions($model, ?array $params = []): array
    {
        $responses = app('events')->dispatch('models.actions.pending', $model, $params) ?? [];
        if (!is_array($responses)) {
            return [];
        }

        return array_values(array_filter($responses, fn ($response) => is_array($response)));
    }
}
