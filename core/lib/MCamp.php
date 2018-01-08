<?php

class MCamp 
{
    private $route;
    
    public function __construct(\Core\Model\Router $router) 
    {
        $this->route = $router;
    }

    
    public function render()
    {
        echo '<!doctype html>';
        echo '<html amp lang=\"', $this->route->siteLanguage, '"><head>';
        echo '<meta charset="utf-8"><script async src="https://cdn.ampproject.org/v0.js"></script>';
        echo '<title>', '', '</title>';
        echo '<link rel="canonical" href="', 'http://example.ampproject.org/article-metadata.html', '">';
        echo '<meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">';
        echo '<script type="application/ld+json">', PHP_EOL,
            '{', PHP_EOL,
            '"@context": "http://schema.org",', PHP_EOL,
            '"@type": "NewsArticle",', PHP_EOL,
            '"headline": "Open-source framework for publishing content",', PHP_EOL,
            '"datePublished": "2015-10-07T12:02:41Z",', PHP_EOL,
            '"image": [', PHP_EOL,
              '"logo.jpg"', PHP_EOL,
            ']', PHP_EOL,
            '}', PHP_EOL,
        '</script>', PHP_EOL;
        echo '<style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>';
        echo '</head><body>';
        echo '</body></html>';
    }
}
