<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * A list item
 *
 * This is a paragraph that is part of a list
 */
class ListItem extends Paragraph
{
    /** @var int the nesting level starting at 0 */
    protected $level = 0;
    /** @var string the type of list, ordered or unordered */
    protected $type = 'unordered';

    /**
     * The type of list is determined looking at the numbering.xml data
     * @inheritdoc
     */
    public function parse()
    {
        parent::parse();
        $this->level = (int)$this->p->xpath('w:pPr/w:numPr/w:ilvl')[0]->attributes('w', true)->val;
        $id = (int)$this->p->xpath('w:pPr/w:numPr/w:numId')[0]->attributes('w', true)->val;
        $this->type = $this->docx->getNumbering()->getType($id, $this->level);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        $text = parent::__toString();
        $bullet = $this->type === 'ordered' ? '-' : '*';
        return str_pad('', ($this->level + 1) * 2) . $bullet . ' ' . $text;
    }

    /** @inheritdoc */
    public function mergeToPrevious(): bool
    {
        return true;
    }
}
