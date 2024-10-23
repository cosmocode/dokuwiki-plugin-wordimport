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

            // new line before the paragraph
            $this->text .= "\n";

            // should this merge with previous?
            if (
                $obj->mergeToPrevious() &&
                $last &&
                get_class($obj) === get_class($last)
            ) {
                [$rmPrev, $rmNow] = $obj->mergeToPrevious();
                $this->text = substr($this->text, 0, -$rmPrev);
            } else {
                $rmNow = 0;
            }

            $this->text .= substr($obj, $rmNow); // toString() is called here
            $last = $obj; // remember for next iteration

            // new line after the paragraph
            $this->text .= "\n";
        }
    }


    /** @inheritdoc */
    public function __toString()
    {
        return $this->text;
    }
}
