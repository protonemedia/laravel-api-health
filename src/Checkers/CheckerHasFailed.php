<?php

namespace Pbmedia\ApiHealth\Checkers;

use Exception;

class CheckerHasFailed extends Exception
{
    private $checker;

    public function getChecker(): Checker
    {
        return $this->checker;
    }

    public function setChecker(Checker $checker)
    {
        $this->checker = $checker;

        return $this;
    }

    public static function create(Checker $checker, string $message): self
    {
        return (new static($message))->setChecker($checker);
    }
}
