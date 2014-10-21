<?php
namespace Feedseek;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;

class FeedLink extends Link
{
    /**
     * @var string feed type
     */
    protected $type;

    /**
     * @param Crawler $node
     * @param string $currentUri
     */
    public function __construct(Crawler $node, $currentUri){
        parent::__construct($node->getNode(0), $currentUri);
        $this->type = strtolower(trim($node->attr('type')));
    }

    /**
     * @param \DOMElement $node
     */
    protected function setNode(\DOMElement $node)
    {
        $this->node = $node;
    }

    /**
     * @return null|string
     */
    public function getUri()
    {
        switch ($this->type) {
            case 'application/rss+xml':
            case 'application/x.atom+xml':
            case 'application/atom+xml':
            case 'application/rdf+xml':
                return parent::getUri();
            default:
                break;
        }
        return null;
    }

    /**
     * @param Crawler $node
     * @param $currentUri
     * @return static
     */
    public static function factory(Crawler $node, $currentUri){
        return new static($node, $currentUri);
    }

}