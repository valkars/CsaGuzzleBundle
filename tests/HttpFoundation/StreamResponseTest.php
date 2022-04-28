<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests\HttpFoundation;

use Csa\Bundle\GuzzleBundle\HttpFoundation\StreamResponse;
use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;

class StreamResponseTest extends TestCase
{
    public function testNormalOutput(): void
    {
        $this->expectOutputString('this should not be streamed');

        $mock = new Response(200, [], 'this should not be streamed');
        $response = new StreamResponse($mock);
        $response->send();
    }

    public function testChunkedOutput(): void
    {
        $this->expectOutputString("a\r\nthis shoul\r\na\r\nd not be s\r\n7\r\ntreamed\r\n0\r\n\r\n");

        $mock = new Response(200, ['Transfer-Encoding' => 'chunked'], 'this should not be streamed');
        $response = new StreamResponse($mock, 10);
        $response->send();
    }
}
