<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

class GuzzleExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            new TwigFilter('csa_guzzle_pretty_print', [$this, 'prettyPrint']),
            new TwigFilter('csa_guzzle_status_code_class', [$this, 'statusCodeClass']),
            new TwigFilter('csa_guzzle_format_duration', [$this, 'formatDuration']),
            new TwigFilter('csa_guzzle_short_uri', [$this, 'shortenUri']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('csa_guzzle_detect_lang', [$this, 'detectLang']),
        ];
    }

    public function detectLang($body): string
    {
        return match (true) {
            \str_starts_with($body, '<?xml') => 'xml',
            \str_starts_with($body, '{'), \str_starts_with($body, '[') => 'json',
            default => 'markup',
        };
    }

    public function prettyPrint($code, $lang): bool|string
    {
        switch ($lang) {
            case 'json':
                return \json_encode(\json_decode($code), \JSON_PRETTY_PRINT);
            case 'xml':
                $xml = new \DomDocument('1.0');
                $xml->preserveWhiteSpace = false;
                $xml->formatOutput = true;
                $xml->loadXml($code, LIBXML_NOWARNING);

                return $xml->saveXml();
            default:
                return $code;
        }
    }

    public function statusCodeClass($statusCode): string
    {
        return match (true) {
            $statusCode >= 500 => 'server-error',
            $statusCode >= 400 => 'client-error',
            $statusCode >= 300 => 'redirection',
            $statusCode >= 200 => 'success',
            $statusCode >= 100 => 'informational',
            default => 'unknown',
        };
    }

    public function formatDuration($seconds): string
    {
        $formats = ['%.2f s', '%d ms', '%d µs'];

        while ($format = \array_shift($formats)) {
            if ($seconds > 1) {
                break;
            }

            $seconds *= 1000;
        }

        return \sprintf($format, $seconds);
    }

    public function shortenUri($uri): string
    {
        $parts = \parse_url($uri);

        return \sprintf(
            '%s://%s%s',
            $parts['scheme'] ?? 'http',
            $parts['host'],
            isset($parts['port']) ? (':'.$parts['port']) : ''
        );
    }

    public function getName(): string
    {
        return 'csa_guzzle';
    }
}
