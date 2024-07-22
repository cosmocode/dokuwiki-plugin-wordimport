<?php

namespace dokuwiki\plugin\wordimport\docx;

class Paragraph
{
    protected $text = '';

    public function __construct(\SimpleXMLElement $p)
    {
        $trs = $p->xpath('w:r');
        foreach ($trs as $tr) {
            $textRun = new TextRun($tr);
            $this->text .= $textRun;
        }
    }

    public function __toString()
    {
        return $this->text;
    }
}
