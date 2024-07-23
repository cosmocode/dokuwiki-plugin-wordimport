<?php

namespace dokuwiki\plugin\wordimport\docx;

class Heading extends AbstractParagraph
{

    protected $level = 1;
    protected $text = '';


    public function parse(\SimpleXMLElement $p)
    {
        $this->text = $p->xpath('w:r/w:t')[0];
        $this->level =  substr($p->xpath('w:pPr/w:pStyle')[0]->attributes('w', true)->val, -1);
        if($this->level < 1) $this->level = 1;
        if($this->level > 5) $this->level = 5;
    }

    public function __toString(): string
    {
        return str_pad('', 7 - $this->level, '=') . ' ' . $this->text . ' ' . str_pad('', 7 - $this->level, '=');
    }

}
