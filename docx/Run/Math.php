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

        // convert the current run (the omath block) into it's own DOMDocument
        $oMathSxe = dom_import_simplexml($this->run);
        $oMathDom = new \DOMDocument();
        $oMathSxe = $oMathDom->importNode($oMathSxe, true);
        $oMathSxe = $oMathDom->appendChild($oMathSxe);

        // load the XSLT stylesheet
        $xsltDom = new \DOMDocument();
        $xsltDom->load(__DIR__ . '/../../xslt/omml2mathml.xsl');

        // transform the omath block into MathML
        $processor = new \XSLTProcessor();
        $processor->importStylesheet($xsltDom);
        $mmlXml = $processor->transformToXml($oMathDom);

        // create a new DOMDocument from the xml to manipulate the namespaces
        $mmlDom = new \DOMDocument();
        $mmlDom->loadXML($mmlXml);
        $root = $mmlDom->documentElement;
        $root->removeAttributeNS('http://www.w3.org/1998/Math/MathML', 'mml');
        $root->removeAttributeNS('http://schemas.openxmlformats.org/officeDocument/2006/math', 'm');
        $root->setAttribute("xmlns", "http://www.w3.org/1998/Math/MathML");  // Set default namespace

        // Now, when saved, the default namespace will be applied
        $mmlDom->preserveWhiteSpace = false;
        $mmlDom->formatOutput = false;
        $this->mathml = $mmlDom->saveXML($root);
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
