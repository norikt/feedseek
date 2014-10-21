<?php

namespace Feedseek;

use Goutte\Client;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Feedseek
 * @package Feedseek
 */
class Feedseek
{
    const W3C_FEED_VALIDATOR_URL = 'http://validator.w3.org/feed/check.cgi';

    /**
     * @var bool
     */
    protected static $strict = true;

    /**
     * @var \Goutte\Client
     */
    protected static $client = null;

    /**
     * @var array
     */
    protected static $feeds = [];

    /**
     * @param string|array $url
     * @param bool $strict
     * @return array
     * @throw InvalidArgumentException
     * @throw RuntimeException
     */
    public static function find($url, $strict = false)
    {
        if (is_array($url)) {
            static $results;
            foreach ($url as $u) {
                if (isset($results[$u])) continue;
                $results[$u] = static::find($u, $strict);
            }
            return $results;
        }

        if (!$url or !static::isValidUrl($url)) {
            throw new \InvalidArgumentException('Invalid url');
        }

        static::$feeds = [];
        static::$strict = $strict;

        $client = static::getClient();
        $crawler = $client->request('GET', $url);
        $statusCode = (int)$client->getResponse()->getStatus();
        if ($statusCode !== 200) {
            throw new \RuntimeException("Request failed ({$url}), status: {$statusCode}");
        }

        $crawler->filter('link')->each(function (Crawler $node) use ($url) {
            $rel = strtolower(trim($node->attr('rel')));
            if ($rel === 'alternate' || $rel === 'self') {
                static::addFeed($node, $url);
            }
        });

        return static::$feeds;
    }

    /**
     * @param Crawler $node
     * @param $url
     * @return string
     */
    protected static function addFeed(Crawler $node, $url)
    {
        if (!$node->attr('href')) return;
        $uri = FeedLink::factory($node, $url)->getUri();
        if (!static::isValidUrl($uri)
            || in_array($uri, static::$feeds)
            || (static::$strict && !static::isValidFeed($uri))) {
            return;
        }
        static::$feeds[] = $uri;
    }

    /**
     * @return \Goutte\Client
     */
    public static function getClient()
    {
        if (!static::$client) {
            static::$client = new Client;
            static::$client->getClient()->setDefaultOption('config/curl/' . CURLOPT_TIMEOUT, 30);
        }
        return static::$client;
    }

    /**
     * @param \Goutte\Client $client
     */
    public static function setClient($client)
    {
        static::$client = $client;
    }

    /**
     * @param $url
     * @return bool
     */
    protected static function isValidUrl($url)
    {
        if (!$url) return false;
        return filter_var($url, FILTER_VALIDATE_URL);
    }

    /**
     * @param String $url
     * @return bool
     */
    protected static function isValidFeed($url)
    {
        $requestUrl = static::W3C_FEED_VALIDATOR_URL . '?' . http_build_query([
                'url'    => $url,
                'output' => 'soap12'
            ]);
        $crawler = static::getClient()->request('GET', $requestUrl);
        try {
            return "true" === strtolower(trim($crawler->filter('m|validity')->text()));
        } catch (\Exception $e) {
            return false;
        }
    }
}
