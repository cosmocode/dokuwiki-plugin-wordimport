<?php

namespace dokuwiki\plugin\wordimport\docx;

use splitbrain\PHPArchive\Zip;

class Doc {

    protected $tmpdir = null;

    public function __construct($doc)
    {
        $zip = new Zip();
        $zip->open($doc);

        $this->tmpdir = io_mktmpdir();
        $zip->extract($this->tmpdir);
        $zip->close();
    }

    public function __destruct()
    {
        io_rmdir($this->tmpdir, true);
    }

    protected function registerNamespaces($xml)
    {
        $namespaces = $xml->getDocNamespaces(true);
        foreach ($namespaces as $prefix => $namespace) {
            $xml->registerXPathNamespace($prefix, $namespace);
        }
    }

    public function parse()
    {
        $xml = simplexml_load_file($this->tmpdir . '/word/document.xml');
        $this->registerNamespaces($xml);

        foreach ($xml->xpath('//w:p') as $p) {
            $obj = $this->handleParagraph($p);
            if($obj) echo $obj . "\n\n";
        }
    }

    public function handleParagraph($p)
    {
        $this->registerNamespaces($p); // it's odd why we need to reregister namespaces here, but it's necessary

        // code blocks
        if($match = $p->xpath('w:pPr/w:rPr/w:rFonts')) {
            if(in_array($match[0]->attributes('w', true)->ascii, ['Courier New', 'Consolas'])) { // fixme make configurable
                return new CodeBlock($p);
            }
        }

        // headings
        if($p->xpath('w:pPr/w:pStyle[contains(@w:val, "Heading")]')) {
            return new Heading($p);
        }

        // images
        if($p->xpath('w:r/w:drawing/wp:inline//a:blip')) {
            return new Image($p);
        }

        // text paragraphs
        if($p->xpath('w:r/w:t')) {
            return new Paragraph($p);
        }
        return null;
    }

}
