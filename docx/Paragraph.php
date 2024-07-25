<?php

namespace dokuwiki\plugin\wordimport\docx;

class Paragraph extends AbstractParagraph
{
    /** @var TextRun[] */
    protected $texts = [];

    /** @var string */
    protected $alignment = '';

    /** @var array DokuWiki Syntax for the formatting */
    protected $fSyntax = [
        'bold' => ['**', '**'],
        'italic' => ['//', '//'],
        'underline' => ['__', '__'],
        'strike' => ['<del>', '</del>'],
        'mono' => ["''", "''"],
    ];

    /** @inheritdoc */
    public function parse()
    {
        $trs = $this->p->xpath('w:r');
        foreach ($trs as $tr) {
            $textRun = new TextRun($tr);
            $this->texts[] = $textRun;
        }
        $this->updateFormattingScores();

        $alignment = $this->p->xpath('w:pPr/w:jc');
        if ($alignment) {
            $this->alignment = (string)$alignment[0]->attributes('w', true)->val;
        }
    }

    /**
     * @inheritdoc
     * @fixme this is not optimizing formatting by longest chain
     */
    public function __toString(): string
    {
        $result = '';
        $fStack = [];

        foreach ($this->texts as $text) {
            // we don't want to wrap whitespace in formatting
            if ($text->isWhiteSpace()) {
                $result .= $text->__toString();
                continue;
            }

            $formatting = $text->getFormatting();
            $formatting = array_keys(array_filter($formatting));

            // close all formatting that is not in the current text
            $toclose = array_diff($fStack, $formatting);
            foreach ($toclose as $f) {
                // we need to make sure all formatting is closed, but we close by popping the
                // stack. This ensures we don't create invalid nesting
                while (in_array($f, $fStack)) {
                    $this->closeFormatting($result, array_pop($fStack));
                }
            }

            // open formatting that is in the current text
            $new = array_diff($formatting, $fStack);
            foreach ($new as $f) {
                $this->openFormatting($result, $f);
                $fStack[] = $f;
            }

            // add the text
            $result .= $text->__toString();
        }

        // close remaining formatting
        while ($fStack) {
            $this->closeFormatting($result, array_pop($fStack));
        }

        return $result;
    }

    /**
     * Update the formatting scores for all texts
     *
     * Walks through the texts in reverse order and updates the formatting scores
     */
    protected function updateFormattingScores()
    {
        $len = count($this->texts);
        if ($len < 2) return;
        for ($i = $len - 2; $i >= 0; $i--) {
            $this->texts[$i]->updateFormattingScores($this->texts[$i + 1]);
        }
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

    /**
     * Open a formatting
     *
     * @param string $text The text currently worked on
     * @param string $formatting Name of the formatting
     * @return void
     */
    public function openFormatting(&$text, $formatting)
    {
        if (!isset($this->fSyntax[$formatting])) {
            throw new \RuntimeException("Unknown formatting: $formatting");
        }

        $text .= $this->fSyntax[$formatting][0];
    }

    /**
     * Close a formatting
     *
     * Handles whitespace at the end of the text
     *
     * @param string $text The text currently worked on
     * @param string $formatting Name of the formatting
     * @return void
     */
    public function closeFormatting(&$text, $formatting)
    {
        preg_match('/^(.+?)(\s*)$/s', $text, $matches);
        $text = $matches[1];
        $suffix = $matches[2];

        if (!isset($this->fSyntax[$formatting])) {
            throw new \RuntimeException("Unknown formatting: $formatting");
        }

        $text .= $this->fSyntax[$formatting][1];
        $text .= $suffix;
    }
}
