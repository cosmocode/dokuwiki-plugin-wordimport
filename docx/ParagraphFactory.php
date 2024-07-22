<?php

namespace dokuwiki\plugin\wordimport\docx;

class ParagraphFactory
{

    static function createParagraph(\SimpleXMLElement $p)
    {
        if($p->xpath('w:pPr/w:pStyle[contains(w:val, "Heading")]')) {
            return new Heading($p);
        }
    }

}
