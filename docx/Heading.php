<?php

namespace dokuwiki\plugin\wordimport\docx;

/**
 * A heading
 *
 * This is a paragraph with a specific style indicating a heading
 */
class Heading extends AbstractParagraph
{
    /** @var int header level 1 to 5 */
    protected $level = 1;
    /** @var string The text of the heading */
    protected $text = '';

    /**
     * Headers use a style ID that points to a style in style.xml. That style has a name like "heading 1"
     * We extract the number from that name to get the level of the heading.
     *
     * @inheritdoc
     */
    public function parse()
    {
        $this->text = (string) $this->p->xpath('w:r/w:t')[0];
        $style = $this->p->xpath('w:pPr/w:pStyle');
        $styleID = $style[0]->attributes('w', true)->val;
        $this->level =  substr($this->docx->getStyles()->getStyleName($styleID), -1); // translates to "heading X"
        if ($this->level < 1) $this->level = 1;
        if ($this->level > 5) $this->level = 5;
    }

    /** @inheritdoc  */
    public function __toString(): string
    {
        return str_pad('', 7 - $this->level, '=') . ' ' . $this->text . ' ' . str_pad('', 7 - $this->level, '=');
    }
}
