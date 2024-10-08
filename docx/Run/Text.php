<?php

namespace dokuwiki\plugin\wordimport\docx\Run;

/**
 * A text run
 *
 * This is a part of a paragraph with a specific formatting
 */
class Text extends AbstractRun
{

    protected $text = '';

    public function parse()
    {
        $this->parseFormatting($this->run);
        $this->text = (string)($this->run->xpath('w:t')[0] ?? '');
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->text;
    }

    /**
     * Check if this run is only whitespace
     *
     * @return bool
     */
    public function isWhiteSpace()
    {
        return ctype_space($this->text);
    }

    /**
     * Parse the formatting of this run
     *
     * @see http://www.datypic.com/sc/ooxml/e-w_rPr-4.html
     * @param \SimpleXMLElement $textRun
     */
    public function parseFormatting(\SimpleXMLElement $textRun)
    {
        $result = $textRun->xpath('w:rPr');
        if (empty($result)) return;

        foreach ($result[0]->children('w', true) as $child) {
            switch ($child->getName()) {
                case 'b':
                case 'bCs':
                    $this->formatting['bold'] = 1;
                    break;
                case 'i':
                case 'iCs':
                case 'em':
                    $this->formatting['italic'] = 1;
                    break;
                case 'u':
                    $this->formatting['underline'] = 1;
                    break;
                case 'strike':
                case 'dstrike':
                    $this->formatting['strike'] = 1;
                    break;
                case 'rFonts':
                    if (in_array($child->attributes('w', true)->ascii, $this->docx->getConf('codefonts'))) {
                        $this->formatting['mono'] = 1;
                    }
                    break;
            }
        }
    }
}
