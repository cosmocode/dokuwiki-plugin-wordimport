<?php

namespace dokuwiki\plugin\wordimport\docx;

class CodeBlock
{
    protected $text = '';

    public function __construct(\SimpleXMLElement $p)
    {
        $runs = $p->xpath('w:r');
        foreach ($runs as $run) {
            $tr = new TextRun($run);
            $this->text .= $tr->__toString();
        }
    }

    public function __toString()
    {
        return '<code>' . "\n" . $this->text  ."\n" . '</code>';
    }
}
