<?php
declare(strict_types=1);

namespace Ketama;

class Continuum
{
    const OFFSET_MODTIME = 0;
    const OFFSET_NUMSERVERS = 4;
    const OFFSET_POINTS = 8;

    /**
     * Bin is a binary serialization of the buckets:
     *
     * 0..4:        modtime
     * 4..8:        bucket count
     * 8..8+N:      bucket points (N = bucket count)
     * 8+N..8+N*2:  bucket ip offsets
     * 8+N*2..?:    bucket ips
     *
     * @var bin string
     */
    private $bin;

    public static function create(array $buckets, int $modtime): Continuum
    {
        $bin = pack('VV', $modtime, count($buckets));
        assert(strlen($bin) === 8);

        $points = array_map(function (Bucket $bucket) {
            return $bucket->getPoint();
        }, $buckets);

        $bin .= pack('V*', ...$points);
        assert(strlen($bin) === 8+count($points)*4);

        [, $offsets] = array_reduce($buckets, function ($carry, Bucket $bucket) {
            [$offset, $offsets] = $carry;
            $len = strlen($bucket->getIp());

            if ($len > 255) {
                throw new \Exception(sprintf("IP is larger than 255 bytes, serialization would fail"));
            }
            if ($offset >= 0xffffff) {
                throw new \Exception(sprintf("Offset is larger or equal to 0xffffff, serialization would fail"));
            }

            $offsets[] = ($len << 24) | $offset;
            return [$offset + $len, $offsets];
        }, [0, []]);

        $bin .= pack('V*', ...$offsets);
        assert(strlen($bin) === 8+count($points)*4*2);

        $ips = array_map(function (Bucket $bucket) {
            return $bucket->getIp();
        }, $buckets);

        $bin .= pack(str_repeat('a*', count($ips)), ...$ips);

        $continuum = new Continuum();
        $continuum->bin = $bin;

        return $continuum;
    }

    public static function unserialize(string $bin): Continuum
    {
        $continuum = new Continuum();
        $continuum->bin = $bin;
        return $continuum;
    }

    public function serialize(): string
    {
        return $this->bin;
    }

    public function getModtime(): int
    {
        [, $modtime] = unpack('V', $this->bin, self::OFFSET_MODTIME);
        return $modtime;
    }

    public function getServer(string $key): string
    {
        [, $numservers] = unpack('V', $this->bin, self::OFFSET_NUMSERVERS);

        $h = $this->hashi($key);
        $highp = $numservers;
        $lowp = 0;

        while (true) {
            $midp = intval(($lowp + $highp) / 2);

            if ($midp === $numservers) {
                return $buckets[0];
            }

            [, $midval] = unpack('V', $this->bin, self::OFFSET_POINTS + $midp*4);

            if ($midp === 0) {
                $midval1 = 0;
            } else {
                [, $midval1] = unpack('V', $this->bin, self::OFFSET_POINTS + ($midp-1)*4);
            }

            if ($h <= $midval && $h > $midval1) {
                return $this->readIp($midp);
            }

            if ($midval < $h) {
                $lowp = $midp + 1;
            } else {
                $highp = $midp - 1;
            }

            if ($lowp > $highp) {
                return $this->readIp(0);
            }
        }
    }

    private function readIp($idx): string
    {
        [, $numservers] = unpack('V', $this->bin, self::OFFSET_NUMSERVERS);
        [, $ipoffset] = unpack('V', $this->bin, self::OFFSET_POINTS + $numservers*4 + $idx*4);

        $len = $ipoffset >> 24;
        $ipoffset = $ipoffset & 0xffffff;

        return substr($this->bin, self::OFFSET_POINTS + $numservers*4*2 + $ipoffset, $len);
    }

    public function printContinuum(): void
    {
        printf("Numpoints in continuum: %d\n", count($this->continuum));

        foreach ($this->continuum as $bucket) {
            printf("%s (%u)\n", $bucket->getIp(), $bucket->getPoint());
        }
    }

    private function hashi(string $str): int
    {
        $digest = hash('md5', $str, true);
        $values = unpack('V', $digest);
        return $values[1];
    }
}
