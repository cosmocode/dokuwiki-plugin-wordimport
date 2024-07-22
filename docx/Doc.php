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

    public function parse()
    {
        $xml = simplexml_load_file($this->tmpdir . '/word/document.xml');

        $namespaces = $xml->getDocNamespaces();
        foreach ($namespaces as $prefix => $namespace) {
            $xml->registerXPathNamespace($prefix, $namespace);
        }

        foreach ($xml->xpath('//w:p') as $p) {
            $obj = $this->handleParagraph($p);
            if($obj) echo $obj . "\n\n";
        }
    }

    public function handleParagraph($p)
    {
        // headings
        if($p->xpath('w:pPr/w:pStyle[contains(@w:val, "Heading")]')) {
            return new Heading($p);
        }

        // text paragraphs
        if($p->xpath('w:r/w:t')) {
            return new Paragraph($p);
        }
        return null;
    }

}
