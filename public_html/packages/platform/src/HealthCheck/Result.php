<?php

namespace MetaFox\Platform\HealthCheck;

use DateTime;

class Result
{
    public DateTime $startedAt;

    public string $severity = 'error';

    private array $reports = [];

    public function __construct()
    {
        $this->startedAt = now();
    }

    /**
     * @return array
     */
    public function getReports(): array
    {
        return $this->reports;
    }

    /**
     * @param string $msg
     */
    public function success(string $msg, ?string $title = '', ?array $actions = []): void
    {
        $this->reports[] = [
            'severity' => 'success',
            'message'  => $msg,
            'id'       => md5($msg),
            'title'    => $title,
            'actions'  => $actions,
        ];
    }

    /**
     * @param string      $msg
     * @param string|null $title
     */
    public function error(string $msg, ?string $title = '', ?array $actions = []): void
    {
        $this->reports[] = [
            'severity' => 'error',
            'message'  => $msg,
            'id'       => md5($msg),
            'title'    => $title,
            'actions'  => $actions,
        ];
    }

    /**
     * @param string $msg
     */
    public function warn(string $msg, ?string $title = '', ?array $actions = []): void
    {
        $this->reports[] = [
            'severity' => 'warning',
            'message'  => $msg,
            'id'       => md5($msg),
            'title'    => $title,
            'actions'  => $actions,
        ];
    }

    /**
     * @param string $msg
     */
    public function debug(string $msg): void
    {
        $this->reports[] = ['severity' => 'debug', 'message' => $msg];
    }

    public function okay(): bool
    {
        foreach ($this->reports as $msg) {
            if ($msg['severity'] == 'error') {
                return false;
            }
        }

        return true;
    }
}
