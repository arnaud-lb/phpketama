<?php

use PHPUnit\Framework\TestCase;
use Symfony\Component\Cache\Simple\ArrayCache;
use Ketama\Ketama;

class KetamaTest extends TestCase
{
    public function testPhpketamaIsCompatibleWithCKetama(): void
    {
        $cache = new ArrayCache();
        $ketama = new Ketama($cache);
        $continuum = $ketama->createContinuum(__DIR__."/fixtures/continuum0");

        $expectations = [
            'Austria' => '10.0.0.10',
            'Belgium' => '10.0.0.10',
            'Bulgaria' => '10.0.0.2',
            'Croatia' => '10.0.0.10',
            'Cyprus' => '10.0.0.1',
            'Czech' => '10.0.0.10',
            'Denmark' => '10.0.0.1',
            'Estonia' => '10.0.0.2',
            'Finland' => '10.0.0.2',
            'France' => '10.0.0.1',
            'Germany' => '10.0.0.10',
            'Greece' => '10.0.0.2',
            'Hungary' => '10.0.0.2',
            'Ireland' => '10.0.0.10',
            'Italy' => '10.0.0.10',
            'Latvia' => '10.0.0.2',
            'Lithuania' => '10.0.0.2',
            'Luxembourg' => '10.0.0.1',
            'Malta' => '10.0.0.10',
            'Poland' => '10.0.0.2',
            'Portugal' => '10.0.0.10',
            'Republic' => '10.0.0.1',
            'Romania' => '10.0.0.10',
            'Slovakia' => '10.0.0.10',
            'Slovenia' => '10.0.0.1',
            'Spain' => '10.0.0.2',
            'Sweden' => '10.0.0.10',
        ];

        foreach ($expectations as $key => $expectedIp) {
            $ip = $continuum->getServer($key);
            $this->assertSame($expectedIp, $ip, $key);
        }
    }
}
