<?php

namespace ProtoneMedia\ApiHealth\Tests\TestCheckers;

use Pbmedia\ApiHealth\Checkers\AbstractChecker;
use Pbmedia\ApiHealth\Checkers\CheckerHasFailed;

class BasedOnIdChecker extends AbstractChecker
{
    private $shouldPass;

    public function id(): string
    {
        return md5(static::class) . ($this->shouldPass ? 'pass' : 'fail');
    }

    public function __construct(bool $shouldPass)
    {
        $this->shouldPass = $shouldPass;
    }

    public function run()
    {
        if (!$this->shouldPass) {
            throw new CheckerHasFailed("TestChecker fails!");
        }
    }

    public function isDue(): bool
    {
        return true;
    }

    public static function create()
    {
        return new static(true);
    }
};
