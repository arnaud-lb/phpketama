<?php
declare(strict_types=1);

namespace Ketama;

class Bucket
{
    public function __construct(private int $point, private string $ip)
    {
    }

    public function getPoint(): int
    {
        return $this->point;
    }

    public function getIp(): string
    {
        return $this->ip;
    }
}
