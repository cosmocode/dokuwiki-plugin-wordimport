<?php

namespace dokuwiki\plugin\wordimport\docx;

class Paragraph extends AbstractParagraph
{
    /** @var TextRun[] */
    protected $texts = [];

    public function parse(\SimpleXMLElement $p)
    {
        $trs = $p->xpath('w:r');
        foreach ($trs as $tr) {
            $textRun = new TextRun($tr);
            $this->texts[] = $textRun;
        }
    }

    public function __toString(): string
    {
        $result = '';


        $currentText = '';
        $lastFormatting = [];

        foreach ($this->texts as $text) {
            // if the text is whitespace or has the same formatting as the last text append
            if($text->isWhiteSpace() || array_diff($text->getFormatting(), $lastFormatting) == []) {
                $currentText .= $text->__toString();
                continue;
            }
            // add the collected text
            if($currentText !== '') {
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

    public function wrapFormatting($text, $formatting)
    {
        if(ctype_space($text)) return $text; // no need to wrap whitespace

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
