<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\HttpFoundation;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Symfony\Component\HttpFoundation\Response;

class StreamResponse extends Response
{
    public const BUFFER_SIZE = 4096;

    private int $bufferSize;

    public function __construct(ResponseInterface $response, $bufferSize = self::BUFFER_SIZE)
    {
        parent::__construct(null, $response->getStatusCode(), $response->getHeaders());

        $this->content = $response->getBody();
        $this->bufferSize = $bufferSize;
    }

    public function sendContent(): static
    {
        $chunked = $this->headers->has('Transfer-Encoding');
        if (!$this->content instanceof StreamInterface) {
            return $this;
        }
        $this->content->seek(0);

        for (; ;) {
            $chunk = $this->content->read($this->bufferSize);

            if ($chunked) {
                echo \sprintf("%x\r\n", \strlen($chunk));
            }

            echo $chunk;

            if ($chunked) {
                echo "\r\n";
            }

            \flush();

            if (!$chunk) {
                break;
            }
        }

        return $this;
    }

    public function getContent(): string|false
    {
        return false;
    }
}
