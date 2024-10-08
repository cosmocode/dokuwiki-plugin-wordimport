<?php

namespace dokuwiki\plugin\wordimport\docx\Run;

use dokuwiki\plugin\wordimport\docx\DocX;
use dokuwiki\plugin\wordimport\docx\Paragraph\AbstractParagraph;

abstract class AbstractRun
{
    protected DocX $docx;
    protected \SimpleXMLElement $run;
    protected AbstractParagraph $parent;

    /**
     * @var int[] The formatting of this run and the length of the formatting chain. A value of 0 means
     *             the formatting is not present, a value of 1 means it is present only in this run. A value
     *             of 2 or higher means it is present in this run and the n-1 following runs.
     */
    protected $formatting = [
        'bold' => 0,
        'italic' => 0,
        'underline' => 0,
        'strike' => 0,
        'mono' => 0,
    ];


    public function __construct(DocX $docx, \SimpleXMLElement $run, AbstractParagraph $parent)
    {
        $this->docx = $docx;
        $this->run = $run;
        $this->parent = $parent;
        $this->docx->registerNamespaces($run);
    }

    /**
     * A list of set formattings on this run
     *
     * @return int[]
     */
    public function getFormatting()
    {
        return $this->formatting;
    }

    /**
     * Parse the run XML element
     */
    abstract public function parse();

    /**
     * @return string The DokuWiki syntax representation of the paragraph
     */
    abstract public function __toString(): string;

    /**
     * Check if this run is only whitespace
     *
     * @return bool
     */
    public function isWhiteSpace()
    {
        return false;
    }

    /**
     * Use the formatting of the following run to update the scores of this one
     *
     * This is used to find the longest chains of formatting
     *
     * @param AbstractRun $nextRun
     * @return void
     */
    public function updateFormattingScores(AbstractRun $nextRun)
    {
        $next = $nextRun->getFormatting();
        foreach ($next as $key => $value) {
            if ($this->formatting[$key] === 0) continue;
            $this->formatting[$key] += $value;
        }

        // sort by value, longest chains first
        arsort($this->formatting);
    }

}
