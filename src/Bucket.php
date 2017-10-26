<?php
declare(strict_types=1);

namespace Ketama;

class Bucket
{
    private $point;
    private $ip;

    public function __construct(int $point, string $ip)
    {
        $this->point = $point;
        $this->ip = $ip;
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
