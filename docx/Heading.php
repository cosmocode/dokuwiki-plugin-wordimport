<?php

namespace dokuwiki\plugin\wordimport\docx;

class Heading extends AbstractParagraph
{
    protected $level = 1;
    protected $text = '';


    public function parse()
    {
        $this->text = (string) $this->p->xpath('w:r/w:t')[0];
        $style = $this->p->xpath('w:pPr/w:pStyle');
        $styleID = $style[0]->attributes('w', true)->val;
        $this->level =  substr($this->docx->getStyles()->getStyleName($styleID), -1); // translates to "heading X"
        if ($this->level < 1) $this->level = 1;
        if ($this->level > 5) $this->level = 5;
    }

    public function __toString(): string
    {
        return str_pad('', 7 - $this->level, '=') . ' ' . $this->text . ' ' . str_pad('', 7 - $this->level, '=');
    }
}
