<?php

namespace dokuwiki\plugin\wordimport\docx;

use splitbrain\PHPArchive\Zip;

/**
 * The main DOCX object
 *
 * This class is the main entry point for importing a DOCX file into DokuWiki. It handles extracting the
 * document and offers access to its contents.
 */
class DocX
{
    /** @var string The temporary directory where the DOCX is extracted */
    protected $tmpdir;
    /** @var Relationships Relationship references*/
    protected $relationships;
    /** @var Numbering Numbering definitions for lists */
    protected $numbering;
    /** @var Styles Style definitions */
    protected $styles;
    /** @var Document The main document */
    protected $document;
    /** @var string|null The page id to which this docx is imported */
    protected $pageId;
    /** @var array The plugin configuration */
    protected $config;

    /**
     * Create a new DOCX object
     *
     * @param string $docx path to the DOCX file
     * @param array $config the plugin configuration
     */
    public function __construct(string $docx, array $config)
    {
        $zip = new Zip();
        $zip->open($docx);

        $this->tmpdir = io_mktmpdir();
        $zip->extract($this->tmpdir);
        $zip->close();

        $this->config = $config;
    }

    /**
     * Import the DOCX into DokuWiki
     *
     * @param string $pageid the page id to import the document into
     * @throws \Exception
     */
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
     * Parse and access the document
     *
     * @return Document
     */
    public function getDocument(): Document
    {
        if (!$this->document) $this->document = new Document($this);
        return $this->document;
    }

    /**
     * Parse and access the list number definitions
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
     * Parse and access the relationships
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
     * Parse and access the style information
     *
     * @return Styles
     * @internal
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
     * @internal
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
     * @throws \Exception when the file does not exist
     * @internal
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
     * @internal
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
     * @internal
     */
    public function getConf($key, $default = null)
    {
        return $this->config[$key] ?? $default;
    }

    /**
     * Cleanup the temporary directory
     */
    public function __destruct()
    {
        io_rmdir($this->tmpdir, true);
        if ($this->pageId) unlock($this->pageId);
    }
}
