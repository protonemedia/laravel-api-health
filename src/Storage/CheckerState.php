<?php

namespace Pbmedia\ApiHealth\Storage;

use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Cache;
use Pbmedia\ApiHealth\Checkers\Checker;

class CheckerState
{
    private $checker;
    private $cache;

    public function __construct(Checker $checker)
    {
        $this->checker = $checker;

        $this->cache = Cache::driver(config('api-health.cache_driver'));
    }

    private function key(): string
    {
        return 'laravel-api-checker.' . md5(get_class($this->checker));
    }

    public function data(): array
    {
        return $this->cache->get($this->key());
    }

    public function shouldSentFailedNotification(): bool
    {
        return $this->data()['notifications_sent'] == [];
    }

    public function setToFailed()
    {
        return $this->cache->forever($this->key(), [
            'failed_at'          => now()->getTimestamp(),
            'notifications_sent' => [],
        ]);
    }

    public function markSentFailedNotification(Notification $notification)
    {
        $data = $this->data();

        $data['notifications_sent'][] = [
            'notification_type' => get_class($notification),
            'sent_at'           => now()->getTimestamp(),
        ];

        $this->cache->forever($this->key(), $data);
    }

    public function isFailed(): bool
    {
        return $this->cache->has($this->key());
    }

    public function undoFailed()
    {
        return $this->cache->pull($this->key());
    }
}
