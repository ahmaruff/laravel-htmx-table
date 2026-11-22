<?php

declare(strict_types=1);

namespace Ahmaruff\HtmxTable\Tests\Concerns;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMXPath;

trait InteractsWithTableHtml
{
    protected function makeXpath(string $html): DOMXPath
    {
        $doc = new DOMDocument;
        $previous = libxml_use_internal_errors(true);
        $doc->loadHTML('<?xml encoding="utf-8" ?>'.$html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        return new DOMXPath($doc);
    }

    /**
     * @return array<string, mixed>
     */
    protected function parseQuery(string $url): array
    {
        $queryString = parse_url($url, PHP_URL_QUERY);

        if (! is_string($queryString) || $queryString === '') {
            return [];
        }

        $params = [];
        parse_str($queryString, $params);

        return $params;
    }

    protected function getAttributeValue(?DOMNode $node, string $attribute): ?string
    {
        if (! $node instanceof DOMElement) {
            return null;
        }

        $value = $node->getAttribute($attribute);

        return $value !== '' ? $value : null;
    }
}
