<?php

namespace dokuwiki\plugin\wordimport\docx;

class Paragraph extends AbstractParagraph
{
    /** @var TextRun[] */
    protected $texts = [];

    /** @var string */
    protected $alignment = '';

    /** @inheritdoc */
    public function parse()
    {
        $trs = $this->p->xpath('w:r');
        foreach ($trs as $tr) {
            $textRun = new TextRun($tr);
            $this->texts[] = $textRun;
        }

        $alignment = $this->p->xpath('w:pPr/w:jc');
        if ($alignment) {
            $this->alignment = (string)$alignment[0]->attributes('w', true)->val;
        }
    }

    /**
     * @inheritdoc
     * @fixme this is not correctly joining text runs yet. We probably need a stack based approach to handle formatting changes
     */
    public function __toString(): string
    {
        $result = '';

        $currentText = '';
        $lastFormatting = [];

        foreach ($this->texts as $text) {
            // if the text is whitespace or has the same formatting as the last text append
            if ($text->isWhiteSpace() || array_diff($text->getFormatting(), $lastFormatting) == []) {
                $currentText .= $text->__toString();
                continue;
            }
            // add the collected text
            if ($currentText !== '') {
                $result .= $this->wrapFormatting($currentText, $lastFormatting);
                $currentText = '';
            }
            // start a new text
            $lastFormatting = $text->getFormatting();
            $currentText = $text->__toString();
        }
        // add the last text
        $result .= $this->wrapFormatting($currentText, $lastFormatting);

        return $result;
    }

    /**
     * Get the paragraph alignment
     *
     * This is only used in images and tables
     *
     * @return string
     */
    public function getAlignment()
    {
        return $this->alignment;
    }

    /**
     * Pad the given text according to the alignment
     *
     * @param string $text
     */
    public function alignmentPadding($text)
    {
        switch ($this->getAlignment()) {
            case 'left':
                $text = "$text ";
                break;
            case 'right':
                $text = " $text";
                break;
            case 'center':
                $text = " $text ";
                break;
        }
        return $text;
    }

    public function wrapFormatting($text, $formatting)
    {
        if (ctype_space($text)) return $text; // no need to wrap whitespace

        // only wrap the text, not the whitespace around it
        preg_match('/^(\s*)(.+?)(\s*)$/s', $text, $matches);

        $prefix = $matches[1];
        $text = $matches[2];
        $suffix = $matches[3];

        if ($formatting['mono'] ?? false) $text = "''" . $text . "''";
        if ($formatting['bold'] ?? false) $text = '**' . $text . '**';
        if ($formatting['italic'] ?? false) $text = '//' . $text . '//';
        if ($formatting['underline'] ?? false) $text = '__' . $text . '__';
        if ($formatting['strike'] ?? false) $text = '~~' . $text . '~~';

        return $prefix . $text . $suffix;
    }
}
