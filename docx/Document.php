<?php

namespace dokuwiki\plugin\wordimport\docx;


class Document extends AbstractXMLFile
{
    protected $text = '';

    public function parse()
    {
        $xml = $this->docx->loadFile('/word/document.xml');
        $this->registerNamespaces($xml);

        $last = null;
        foreach ($xml->xpath('//w:p') as $p) {
            $obj = $this->createParagraph($p);
            if (!$obj) continue;
            $obj->parse($p);

            if ($obj->mergeToPrevious() && get_class($obj) === get_class($last)) {
                $this->text .= "\n";
            } elseif ($last) {
                $this->text .= "\n\n";
            }

            $this->text .= $obj; // toString
            $last = $obj;
        }

        $this->text .= "\n"; // add a final newline
    }

    public function createParagraph($p): ?AbstractParagraph
    {
        $this->registerNamespaces($p); // it's odd why we need to reregister namespaces here, but it's necessary

        // code blocks
        if ($match = $p->xpath('w:pPr/w:rPr/w:rFonts')) {
            if (in_array($match[0]->attributes('w', true)->ascii, ['Courier New', 'Consolas'])) { // fixme make configurable
                return new CodeBlock($this->docx);
            }
        }

        // headings
        if ($p->xpath('w:pPr/w:pStyle[contains(@w:val, "Heading")]')) {
            return new Heading($this->docx);
        }

        // lists
        if ($p->xpath('w:pPr/w:pStyle[@w:val = "ListParagraph"]')) {
            return new ListItem($this->docx);
        }

        // images
        if ($p->xpath('w:r/w:drawing/wp:inline//a:blip')) {
            return new Image($this->docx);
        }

        // text paragraphs
        if ($p->xpath('w:r/w:t')) {
            return new Paragraph($this->docx);
        }
        return null;
    }

    public function __toString()
    {
        return $this->text;
    }
}