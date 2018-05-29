<?php

/**
 * This file is part of the GuzzleStereo package.
 *
 * (c) Christophe Willemsen <willemsen.christophe@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ikwattro\GuzzleStereo\Formatter;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class ResponseFormatter
{
    protected $markerHeader;

    public function __construct($markerHeader = false)
    {
        $this->markerHeader = $markerHeader;
    }

    public function formatResponse(ResponseInterface $response)
    {
        $format = [
            'code' => $response->getStatusCode(),
            'headers' => array_map(function($value) {
                return array_map('base64_encode',$value);
            }, $response->getHeaders()),
            'body' => base64_encode((string) $response->getBody()),
        ];

        if ($this->markerHeader) {
            $format['headers']['X-Guzzle-Stereo'] = true;
        }

        return $format;
    }

    public function rebuildTrack(array $trackContent)
    {
        $headers = array_map(function($value) {
            return array_map('base64_decode',$value);
        }, $trackContent['headers']);
        return new Response($trackContent['code'], $headers, base64_decode($trackContent['body']));
    }

    public function encodeResponsesCollection(array $responses)
    {
        $formatted = [];
        foreach ($responses as $response) {
            $formatted[] = $this->formatResponse($response);
        }
        return json_encode($formatted, JSON_PRETTY_PRINT);
    }

    public function rebuildFromTape($tapeContent)
    {
        $decoded = json_decode($tapeContent, true);
        $tracks = [];
        foreach ($decoded as $track) {
            $tracks[] = $this->rebuildTrack($track);
        }

        return $tracks;
    }
}
