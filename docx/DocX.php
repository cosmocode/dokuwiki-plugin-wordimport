<?php

namespace dokuwiki\plugin\wordimport\docx;

use splitbrain\PHPArchive\Zip;

class DocX
{
    protected $tmpdir = null;
    protected $numbering = null;
    protected $document = null;

    public function __construct($docx)
    {
        $zip = new Zip();
        $zip->open($docx);

        $this->tmpdir = io_mktmpdir();
        $zip->extract($this->tmpdir);
        $zip->close();
    }

    /**
     * Parse the document
     *
     * @return Document
     */
    public function getDocument()
    {
        if (!$this->document) $this->document = new Document($this);
        return $this->document;
    }

    /**
     * Parse the list number definitions
     *
     * @return Numbering
     * @internal
     */
    public function getNumbering()
    {
        if (!$this->numbering) $this->numbering = new Numbering($this);
        return $this->numbering;
    }

    /**
     * Load a file from the extracted docx
     *
     * @param string $file
     * @return \SimpleXMLElement
     */
    public function loadFile($file)
    {
        return simplexml_load_file($this->tmpdir . '/' . $file);
    }


    public function __destruct()
    {
        io_rmdir($this->tmpdir, true);
    }
}
