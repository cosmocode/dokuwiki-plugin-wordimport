<?php

namespace dokuwiki\plugin\wordimport\docx;

use splitbrain\PHPArchive\Zip;

class DocX
{
    protected $tmpdir;
    protected $relationships;
    protected $numbering;
    protected $styles;
    protected $document;
    protected $pageId;
    protected $config;

    public function __construct($docx, $config)
    {
        $zip = new Zip();
        $zip->open($docx);

        $this->tmpdir = io_mktmpdir();
        $zip->extract($this->tmpdir);
        $zip->close();

        $this->config = $config;
    }

    public function import($pageid)
    {
        if (auth_quickaclcheck(getNS($pageid) . ':*') < AUTH_DELETE) {
            throw new \Exception('You do not have enough permission to import into this namespace');
        }

        $this->pageId = $pageid;
        if (checklock($pageid)) throw new \Exception('page is currently locked');
        lock($pageid);

        $doc = $this->getDocument();
        saveWikiText($pageid, (string)$doc, 'Imported from DOCX');

        unlock($pageid);
    }

    /**
     * Parse the document
     *
     * @return Document
     */
    public function getDocument(): Document
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
    public function getNumbering(): Numbering
    {
        if (!$this->numbering) $this->numbering = new Numbering($this);
        return $this->numbering;
    }

    /**
     * Parse the relationships
     *
     * @return Relationships
     * @internal
     */
    public function getRelationships(): Relationships
    {
        if (!$this->relationships) $this->relationships = new Relationships($this);
        return $this->relationships;
    }

    /**
     * Parse the style information
     *
     * @return Styles
     */
    public function getStyles(): Styles
    {
        if (!$this->styles) $this->styles = new Styles($this);
        return $this->styles;
    }

    /**
     * The page id to which this docx is imported. Used for media imports
     *
     * Important: this will return null if this is not called within a import process
     *
     * @return string|null
     */
    public function getPageId(): ?string
    {
        return $this->pageId;
    }

    /**
     * Load a file from the extracted docx
     *
     * @param string $file document relative path to the file to load
     * @return \SimpleXMLElement
     */
    public function loadXMLFile($file)
    {
        $file = $this->getFilePath($file);
        return simplexml_load_file($file);
    }

    /**
     * Get the full path to a file within the doc
     *
     * @param string $relative document relative path
     * @return string
     * @throws \Exception when the file does not exist
     */
    public function getFilePath($relative): string
    {
        $file = $this->tmpdir . '/' . $relative;

        if (!file_exists($file)) {
            throw new \Exception('File not found: ' . $file);
        }

        return $file;
    }

    /**
     * Get a configuration value
     *
     * @param string $key
     * @param mixed $default default value if the key is not set
     * @return mixed
     */
    public function getConf($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    public function __destruct()
    {
        io_rmdir($this->tmpdir, true);
        if ($this->pageId) unlock($this->pageId);
    }
}
