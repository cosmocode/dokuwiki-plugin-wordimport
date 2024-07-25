<?php

namespace dokuwiki\plugin\wordimport\docx;

class Document extends AbstractXMLFile
{
    protected $text = '';

    protected function parse()
    {
        $xml = $this->docx->loadXMLFile('/word/document.xml');
        $this->registerNamespaces($xml);

        $last = null;
        foreach ($xml->xpath('//w:body')[0]->children('w', true) as $p) {
            $obj = $this->createParagraph($p);
            if (!$obj instanceof AbstractParagraph) continue;
            $obj->parse();

            if ($obj->mergeToPrevious() && get_class($obj) === ($last instanceof AbstractParagraph ? get_class($last) : self::class)) {
                $this->text .= "\n";
            } elseif ($last) {
                $this->text .= "\n\n";
            }

            $this->text .= $obj; // toString
            $last = $obj;
        }

        $this->text .= "\n"; // add a final newline
    }

    public function createParagraph(\SimpleXMLElement $p): ?AbstractParagraph
    {
        $this->registerNamespaces($p); // it's odd why we need to reregister namespaces here, but it's necessary

        // tables
        if ($p->getName() == 'tbl') {
            return new Table($this->docx, $p);
        }

        // code blocks
        if ($match = $p->xpath('w:pPr/w:rPr/w:rFonts')) {
            if (in_array($match[0]->attributes('w', true)->ascii, ['Courier New', 'Consolas'])) { // fixme make configurable
                return new CodeBlock($this->docx, $p);
            }
        }

        // headings
        if ($this->docx->getStyles()->hasStyle($p, ['heading 1', 'heading 2', 'heading 3', 'heading 4', 'heading 5'])) {
            return new Heading($this->docx, $p);
        }

        // lists
        if ($this->docx->getStyles()->hasStyle($p, ['list paragraph'])) {
            return new ListItem($this->docx, $p);
        }

        // images
        if ($p->xpath('w:r/w:drawing/wp:inline//a:blip')) {
            return new Image($this->docx, $p);
        }

        // text paragraphs
        if ($p->xpath('w:r/w:t')) {
            return new Paragraph($this->docx, $p);
        }
        return null;
    }

    public function __toString()
    {
        return $this->text;
    }
}
