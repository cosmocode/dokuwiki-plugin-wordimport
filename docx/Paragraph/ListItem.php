<?php

namespace dokuwiki\plugin\wordimport\docx\Paragraph;

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
    protected $type = 'ordered'; // ordered seems to be the default in word

    /**
     * The type of list is determined looking at the numbering.xml data
     * @inheritdoc
     */
    public function parse()
    {
        parent::parse();

        $level = $this->p->xpath('w:pPr/w:numPr/w:ilvl');
        $this->level = $level ? (int)$level[0]->attributes('w', true)->val : 0;

        $idref = $this->p->xpath('w:pPr/w:numPr/w:numId');
        if ($idref) {
            $id = (int)$idref[0]->attributes('w', true)->val;
            $this->type = $this->docx->getNumbering()->getType($id, $this->level);
        }
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        $text = parent::__toString();
        $bullet = $this->type === 'ordered' ? '-' : '*';
        return str_pad('', ($this->level + 1) * 2) . $bullet . ' ' . $text;
    }

    /** @inheritdoc */
    public function mergeToPrevious()
    {
        // '\n, ''
        return [1, 0];
    }
}
