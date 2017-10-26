<?php
declare(strict_types=1);

namespace Ketama;

class Serverinfo
{
    private $addr;
    private $memory;

    public function __construct(string $addr, int $memory)
    {
        $this->addr = $addr;
        $this->memory = $memory;
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
        return strlen($this->addr) > 0 && $this->memory > 0;
    }
}
