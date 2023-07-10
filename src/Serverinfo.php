<?php
declare(strict_types=1);

namespace Ketama;

class Serverinfo
{
    public function __construct(private string $addr, private int $memory)
    {
    }

    public function getAddr(): string
    {
        return $this->addr;
    }

    public function getMemory(): int
    {
        return $this->memory;
    }

    public function valid(): bool
    {
        return $this->addr !== '' && $this->memory > 0;
    }
}
