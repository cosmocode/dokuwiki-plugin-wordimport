<?php

namespace dokuwiki\plugin\wordimport\docx;

class ListItem extends Paragraph
{
    /** @var int the nesting level starting at 0 */
    protected $level = 0;
    protected $type = 'unordered';

    public function parse()
    {
        parent::parse($this->p);
        $this->level = (int)$this->p->xpath('w:pPr/w:numPr/w:ilvl')[0]->attributes('w', true)->val;
        $id = (int)$this->p->xpath('w:pPr/w:numPr/w:numId')[0]->attributes('w', true)->val;
        $this->type = $this->docx->getNumbering()->getType($id, $this->level);
    }


    public function __toString(): string
    {
        $text = parent::__toString();
        $bullet = $this->type === 'ordered' ? '-' : '*';
        return str_pad('', ($this->level + 1) * 2) . $bullet . ' ' . $text;
    }

    public function mergeToPrevious(): bool
    {
        return true;
    }
}
