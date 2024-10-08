<?php

namespace dokuwiki\plugin\wordimport\docx;

use dokuwiki\plugin\wordimport\docx\Paragraph\AbstractParagraph;
use dokuwiki\plugin\wordimport\docx\Paragraph\CodeBlock;
use dokuwiki\plugin\wordimport\docx\Paragraph\Heading;
use dokuwiki\plugin\wordimport\docx\Paragraph\ListItem;
use dokuwiki\plugin\wordimport\docx\Paragraph\Paragraph;
use dokuwiki\plugin\wordimport\docx\Paragraph\Table;
use dokuwiki\plugin\wordimport\docx\Run\AbstractRun;
use dokuwiki\plugin\wordimport\docx\Run\Drawing;
use dokuwiki\plugin\wordimport\docx\Run\Newline;
use dokuwiki\plugin\wordimport\docx\Run\Text;
use dokuwiki\plugin\wordimport\docx\XMLFile\Document;
use dokuwiki\plugin\wordimport\docx\XMLFile\Numbering;
use dokuwiki\plugin\wordimport\docx\XMLFile\Relationships;
use dokuwiki\plugin\wordimport\docx\XMLFile\Styles;
use splitbrain\PHPArchive\Zip;

/**
 * The main DOCX object
 *
 * This class is the main entry point for importing a DOCX file into DokuWiki. It handles extracting the
 * document, offers access to its contents and provides factory methods
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

    /**
     * Register all namespaces that we access in XPath queries
     *
     * This needs to be extended when new namespaces are used
     *
     * @param \SimpleXMLElement $xml
     */
    public function registerNamespaces($xml)
    {
        $namespaces = [
            'rs' => 'http://schemas.openxmlformats.org/package/2006/relationships',
            'w' => 'http://schemas.openxmlformats.org/wordprocessingml/2006/main',
            'wp' => 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing',
            'a' => 'http://schemas.openxmlformats.org/drawingml/2006/main',
        ];

        foreach ($namespaces as $prefix => $namespace) {
            $xml->registerXPathNamespace($prefix, $namespace);
        }
    }

    /**
     * This factory method creates the correct paragraph object for the given XML element
     *
     * @param \SimpleXMLElement $p
     * @return AbstractParagraph|null
     */
    public function createParagraph(\SimpleXMLElement $p): ?AbstractParagraph
    {
        $this->registerNamespaces($p);

        // tables
        if ($p->getName() == 'tbl') {
            return new Table($this, $p);
        }

        // code blocks
        if ($match = $p->xpath('w:pPr/w:rPr/w:rFonts')) {
            if (in_array($match[0]->attributes('w', true)->ascii, $this->getConf('codefonts'))) {
                return new CodeBlock($this, $p);
            }
        }

        // headings
        if (
            $this->getStyles()->hasStyle($p, ['heading 1', 'heading 2', 'heading 3', 'heading 4', 'heading 5']) &&
            $p->xpath('w:r/w:t') # headings must have text
        ) {
            return new Heading($this, $p);
        }

        // lists
        if ($this->getStyles()->hasStyle($p, ['list paragraph'])) {
            return new ListItem($this, $p);
        }

        // images
        #if ($p->xpath('w:r/w:drawing/wp:inline//a:blip')) {
        #    return new Image($this->docx, $p);
        #}

        // "normal" paragraphs
        //if ($p->xpath('w:r')) {
        return new Paragraph($this, $p);
        //}
        //return null;
    }

    /**
     * This factory method creates the correct run object for the given XML element
     */
    public function createRun(\SimpleXMLElement $run, Paragraph $parent): ?AbstractRun
    {
        if (!empty($run->xpath('w:drawing'))) {
            return new Drawing($this, $run, $parent);
        }

        if (!empty($run->xpath('*[self::w:t or self::w:br]'))) {
            return new Text($this, $run, $parent);
        }

        return null;
    }

}
