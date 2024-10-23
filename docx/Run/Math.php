<?php

namespace dokuwiki\plugin\wordimport\docx\Run;

class Math extends AbstractRun
{

    protected $mathml = '';

    /** @inheritdoc */
    public function parse()
    {
        if (!class_exists('\\DOMDocument') || !class_exists('\\XSLTProcessor')) {
            return;
        }

        $xsl = new \DOMDocument();
        $xsl->load(__DIR__ . '/../../xslt/omml2mathml.xsl');

        $proc = new \XSLTProcessor();
        $proc->importStylesheet($xsl);

        $newdom = $proc->transformToXml($this->run);

        $dom = new \DOMDocument();
        $dom->loadXML($newdom);

        // Remove the existing namespaces and set a default namespace
        $root = $dom->documentElement;
        $root->removeAttributeNS('http://www.w3.org/1998/Math/MathML', 'mml');
        $root->removeAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/math', 'm');
        $root->setAttribute("xmlns", "http://www.w3.org/1998/Math/MathML");  // Set default namespace

        // Now, when saved, the default namespace will be applied
        $dom->preserveWhiteSpace = false;
        $dom->formatOutput = false;
        $this->mathml = $dom->saveXML($root);
    }

    /** @inheritdoc */
    public function __toString(): string
    {
        // when we support different plugins we might need to work on this:
        //$mathml = preg_replace(
        //'!<math xmlns="http://www.w3.org/1998/Math/MathML">!',
        // '<math [style=display:block]>',
        // $mathml);

        if(!$this->mathml) return ':!: formular missing, check requirements :!:';

        return $this->mathml;
    }
}
