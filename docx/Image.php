<?php

namespace dokuwiki\plugin\wordimport\docx;

class Image extends Paragraph
{

    protected $src = '';
    protected $alt = '';

    public function parse()
    {
        parent::parse();

        $blip = $this->p->xpath('w:r/w:drawing/wp:inline//a:blip')[0];
        $this->src = $blip->attributes('r', true)->embed;

        $alt = $this->p->xpath('w:r/w:drawing/wp:inline/wp:docPr')[0];
        $this->alt = $this->clean((string)$alt['descr']);
    }

    public function __toString(): string
    {
        $src = $this->src; // FIXME needs to resolve the ID
        $src = $this->alignmentPadding($src);

        return '{{' . $src . '|' . $this->alt . '}}';
    }

    protected function clean($string)
    {
        return str_replace(["\n", '[', ']', '|'], ' ', $string);
    }
}
