<?php

class nqvHtmlParser {

    protected \DOMDocument $dom;
    protected \DOMElement $container;
    protected array $options;

    protected ?\DOMElement $currentH1Body = null;
    protected ?\DOMElement $currentH2Body = null;
    protected ?\DOMElement $currentNestedAccordion = null;

    protected \DOMElement $output;
    protected \DOMElement $rootAccordion;

    public static function parse(string $html, array $options = []): string {
        $parser = new self($html, $options);
        return $parser->run();
    }

    protected function __construct(string $html, array $options) {
        $this->options = $options;

        libxml_use_internal_errors(true);

        $this->dom = new \DOMDocument('1.0', 'UTF-8');

        $this->dom->loadHTML(
            '<?xml encoding="UTF-8">' .
            '<div id="nqv-html-parser">'.$html.'</div>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        $this->container = $this->dom->getElementById('nqv-html-parser');

        $this->output = $this->dom->createElement('div');
    }

    protected function run(): string {

        if (($this->options['format'] ?? null) === 'accordion') {
            $this->formatAccordion();
        } else {
            foreach (iterator_to_array($this->container->childNodes) as $node) {
                $this->output->appendChild($node->cloneNode(true));
            }
        }

        // ⚠️ DEVOLVER SOLO EL FRAGMENTO, NO EL DOCUMENTO
        return $this->dom->saveHTML($this->output);
    }


    /* ==========================================================
     * ACCORDION FORMAT
     * ========================================================== */

    protected function formatAccordion(): void {

        $this->rootAccordion = $this->dom->createElement('div');
        $this->rootAccordion->setAttribute('class', 'accordion');
        $this->rootAccordion->setAttribute('id', 'accordion-root');

        foreach (iterator_to_array($this->container->childNodes) as $node) {

            if ($node->nodeName === 'h1') {
                $this->openH1($node);
                continue;
            }

            if ($node->nodeName === 'h2') {
                $this->openH2($node);
                continue;
            }

            $this->appendContent($node);
        }

        $this->output->appendChild($this->rootAccordion);
    }

    protected function openH1(\DOMNode $node): void {

        $this->currentH1Body = null;
        $this->currentH2Body = null;
        $this->currentNestedAccordion = null;

        $accordionItem = $this->dom->createElement('div');
        $accordionItem->setAttribute('class', 'accordion-item');

        $header = $this->dom->createElement('h1');
        $header->setAttribute('class', 'accordion-header');

        $collapseId = 'collapse-' . uniqid();

        $button = $this->dom->createElement('button');
        $button->setAttribute('class', 'accordion-button collapsed');
        $button->setAttribute('type', 'button');
        $button->setAttribute('data-bs-toggle', 'collapse');
        $button->setAttribute('data-bs-target', '#'.$collapseId);

        foreach ($node->childNodes as $child) {
            $button->appendChild($child->cloneNode(true));
        }

        $header->appendChild($button);

        $collapse = $this->dom->createElement('div');
        $collapse->setAttribute('class', 'accordion-collapse collapse');
        $collapse->setAttribute('id', $collapseId);
        $collapse->setAttribute('data-bs-parent', '#accordion-root');

        $body = $this->dom->createElement('div');
        $body->setAttribute('class', 'accordion-body');

        $collapse->appendChild($body);
        $accordionItem->appendChild($header);
        $accordionItem->appendChild($collapse);

        $this->rootAccordion->appendChild($accordionItem);

        $this->currentH1Body = $body;
    }

    protected function openH2(\DOMNode $node): void {

        if (!$this->currentH1Body) {
            return;
        }

        if (!$this->currentNestedAccordion) {
            $this->currentNestedAccordion = $this->dom->createElement('div');
            $this->currentNestedAccordion->setAttribute('class', 'accordion mt-3');
            $this->currentNestedAccordion->setAttribute(
                'id',
                'accordion-nested-' . uniqid()
            );
            $this->currentH1Body->appendChild($this->currentNestedAccordion);
        }

        $this->currentH2Body = null;

        $accordionItem = $this->dom->createElement('div');
        $accordionItem->setAttribute('class', 'accordion-item');

        $header = $this->dom->createElement('h2');
        $header->setAttribute('class', 'accordion-header');

        $collapseId = 'collapse-' . uniqid();

        $button = $this->dom->createElement('button');
        $button->setAttribute('class', 'accordion-button collapsed');
        $button->setAttribute('type', 'button');
        $button->setAttribute('data-bs-toggle', 'collapse');
        $button->setAttribute('data-bs-target', '#'.$collapseId);

        foreach ($node->childNodes as $child) {
            $button->appendChild($child->cloneNode(true));
        }

        $header->appendChild($button);

        $collapse = $this->dom->createElement('div');
        $collapse->setAttribute('class', 'accordion-collapse collapse');
        $collapse->setAttribute('id', $collapseId);
        $collapse->setAttribute(
            'data-bs-parent',
            '#'.$this->currentNestedAccordion->getAttribute('id')
        );

        $body = $this->dom->createElement('div');
        $body->setAttribute('class', 'accordion-body');

        $collapse->appendChild($body);
        $accordionItem->appendChild($header);
        $accordionItem->appendChild($collapse);

        $this->currentNestedAccordion->appendChild($accordionItem);

        $this->currentH2Body = $body;
    }

    protected function appendContent(\DOMNode $node): void {

        $clone = $node->cloneNode(true);

        if ($this->currentH2Body) {
            $this->currentH2Body->appendChild($clone);
            return;
        }

        if ($this->currentH1Body) {
            $this->currentH1Body->appendChild($clone);
            return;
        }

        $this->output->appendChild($clone);
    }
}
