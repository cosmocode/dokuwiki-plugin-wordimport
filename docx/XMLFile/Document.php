<?php

namespace dokuwiki\plugin\wordimport\docx\XMLFile;

use dokuwiki\plugin\wordimport\docx\Paragraph\AbstractParagraph;

/**
 * The main document
 *
 * This class is responsible for parsing the main document.xml file of the Word document.
 *
 * It handles all the different paragraph types and creates the final text output
 */
class Document extends AbstractXMLFile
{
    /** @var string The final DokuWiki syntax for the document */
    protected $text = '';

    /** @inheritdoc */
    protected function parse()
    {
        $xml = $this->docx->loadXMLFile('/word/document.xml');
        $this->docx->registerNamespaces($xml);

        $last = null;
        foreach ($xml->xpath('//w:body')[0]->children('w', true) as $p) {
            $obj = $this->docx->createParagraph($p);
            if (!$obj instanceof AbstractParagraph) continue;
            $obj->parse();

            if (
                $obj->mergeToPrevious() &&
                $last &&
                get_class($obj) === get_class($last)
            ) {
                $this->text .= "\n";
            } elseif ($last) {
                $this->text .= "\n\n";
            }

            $this->text .= $obj; // toString
            $last = $obj;
        }

        $this->text .= "\n"; // add a final newline
    }


    /** @inheritdoc */
    public function __toString()
    {
        return $this->text;
    }
}
