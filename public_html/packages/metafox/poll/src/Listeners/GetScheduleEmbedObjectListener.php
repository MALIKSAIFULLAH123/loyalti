<?php

namespace MetaFox\Poll\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Platform\Contracts\Entity;

class GetScheduleEmbedObjectListener
{
    public function handle(Entity $schedule, ?bool $toForm = false)
    {
        if (!$schedule->data) {
            return null;
        }
        $data = $schedule->data;
        if (Arr::get($data, 'post_type') != 'poll') {
            return null;
        }
        $schedule->data = $this->transformToEmbed($data, $toForm);

        return true;
    }

    /**
     * @param  array $data
     * @param  bool  $toForm
     * @return array
     */
    private function transformToEmbed(array $data, bool $toForm): array
    {
        $data['unset_privacy'] = true;
        $data['schedule_type'] = 'poll';
        if ($toForm) {
            $answers        = collect(Arr::get($data, 'answers'));
            $currentAnswers = $answers->map(function ($answer) {
                return [
                    'answer' => $answer['answer'],
                    'order'  => $answer['ordering'],
                ];
            });
            $data = array_merge($data, [
                'poll_question'   => Arr::get($data, 'question'),
                'poll_answers'    => $currentAnswers->toArray(),
                'poll_close_time' => Arr::get($data, 'closed_at'),
                'poll_public'     => Arr::get($data, 'public_vote'),
                'poll_multiple'   => Arr::get($data, 'is_multiple'),
            ]);
            unset($data['question'], $data['closed_at'], $data['public_vote'], $data['is_multiple'], $data['answers']);

            return $data;
        }
        $data['extra'] = [
            'can_edit'   => false,
            'can_delete' => false,
            'can_vote'   => false,
        ];

        return $data;
    }
}
