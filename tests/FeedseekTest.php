<?php
namespace Feedseek\Tests;

use Feedseek\Feedseek;
use GuzzleHttp\Subscriber\Mock;
use GuzzleHttp\Message\Response;
use GuzzleHttp\Stream\Stream;


class FeedseekTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        Feedseek::setClient(null);
    }

    public function testFind()
    {
        $mock = new Mock([
            new Response(200, [], Stream::factory(file_get_contents(__DIR__ . '/test.html'))),
        ]);

        $client = Feedseek::getClient();
        $client->getClient()->getEmitter()->attach($mock);

        $url = 'http://www.example.com/';
        $actual = Feedseek::find($url, false);
        $expect = [
            'http://www.example.com/rss',
            'https://www.example.com/rdf',
            'http://www.example.com/atom1',
            'http://www.example.com/atom2',
        ];
        $this->assertEquals($expect, $actual);
    }

    public function testFindMultiple()
    {
        $mock = new Mock([
            new Response(200, [], Stream::factory(file_get_contents(__DIR__ . '/test.html'))),
            new Response(200, [], Stream::factory('<html><body><p>Hi</p></body></html>')),
        ]);
        $client = Feedseek::getClient();
        $client->getClient()->getEmitter()->attach($mock);

        $url = ['http://www.example.com/', 'http://www.example2.com/'];
        $actual = Feedseek::find($url, false);
        $this->assertCount(2, $actual);
        $this->assertArrayHasKey('http://www.example.com/', $actual);
        $this->assertEquals([
            'http://www.example.com/rss',
            'https://www.example.com/rdf',
            'http://www.example.com/atom1',
            'http://www.example.com/atom2',
        ], $actual['http://www.example.com/']);

        $this->assertArrayHasKey('http://www.example2.com/', $actual);
        $this->assertEquals([], $actual['http://www.example2.com/']);
    }

    public function testFindStrict()
    {
        $mock = new Mock([
            new Response(200, [], null),
        ]);
        $client = Feedseek::getClient();
        $client->getClient()->getEmitter()->attach($mock);

        $url = 'http://www.example.com/';
        $actual = Feedseek::find($url, true);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidUrl()
    {
        $url = ' invalid url ';
        Feedseek::find($url, true);
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testNotfound()
    {
        $mock = new Mock([
            new Response(404, [], null),
        ]);
        $client = Feedseek::getClient();
        $client->getClient()->getEmitter()->attach($mock);
        $url = 'http://www.example.com/';
        Feedseek::find($url, true);
    }


}