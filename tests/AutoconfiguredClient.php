<?php

declare(strict_types=1);

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Tests;

use GuzzleHttp\ClientInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

final class AutoconfiguredClient implements ClientInterface
{
    public function send(RequestInterface $request, array $options = []): ResponseInterface
    {
    }

    public function sendAsync(RequestInterface $request, array $options = []): PromiseInterface
    {
    }

    public function request($method, $uri, array $options = []): ResponseInterface
    {
    }

    public function requestAsync($method, $uri, array $options = []): PromiseInterface
    {
    }

    public function getConfig($option = null)
    {
    }
}
