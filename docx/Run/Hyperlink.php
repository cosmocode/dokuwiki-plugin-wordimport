<?php

namespace dokuwiki\plugin\wordimport\docx\Run;

class Hyperlink extends AbstractRun
{
    protected $text = '';
    protected $url = '';

    /** @inheritdoc */
    public function parse()
    {
        // for now we only support simple text links, but there could also be an image here
        $this->text = join('', $this->run->xpath('w:r/w:t'));

        $rId = (string)$this->run->attributes('r', true)->{'id'};
        $this->url = $this->docx->getRelationships()->getTarget('hyperlink', $rId);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        if ($this->url !== $this->text) {
            return '[[' . $this->url . '|' . $this->text . ']]';
        } else {
            return '[[' . $this->text . ']]';
        }
    }
}
