<?php

namespace dokuwiki\plugin\wordimport\docx;

class Image
{

    protected $src = '';
    protected $alignment = '';
    protected $alt = '';

    public function __construct(\SimpleXMLElement $p)
    {
        $blip = $p->xpath('w:r/w:drawing/wp:inline//a:blip')[0];
        $this->src = $blip->attributes('r', true)->embed;

        $alignment = $p->xpath('w:pPr/w:jc');
        if ($alignment) {
            $this->alignment = (string)$alignment[0]->attributes('w', true)->val;
        }

        $alt = $p->xpath('w:r/w:drawing/wp:inline/wp:docPr')[0];
        $this->alt = $this->clean((string)$alt['descr']);
    }

    public function __toString()
    {
        $src = $this->src; // FIXME needs to resolve the ID

        switch ($this->alignment) {
            case 'left':
                $src = "$src ";
                break;
            case 'right':
                $src = " $src";
                break;
            case 'center':
                $src = " $src ";
                break;
        }

        return '{{' . $src . '|' . $this->alt . '}}';
    }

    protected function clean($string)
    {
        return str_replace(["\n", '[', ']', '|'], ' ', $string);
    }
}
