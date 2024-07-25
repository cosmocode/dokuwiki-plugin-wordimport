<?php

namespace dokuwiki\plugin\wordimport\docx;

class CodeBlock extends AbstractParagraph
{
    protected $text = '';

    public function parse()
    {
        $runs = $this->p->xpath('w:r');
        foreach ($runs as $run) {
            $tr = new TextRun($this->docx, $run, "\n");
            $this->text .= $tr->__toString();
        }
    }

    public function __toString(): string
    {
        return '<code>' . "\n" . $this->text  . "\n" . '</code>';
    }
}
