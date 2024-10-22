<?php

use dokuwiki\Extension\CLIPlugin;
use dokuwiki\plugin\wordimport\docx\DocX;
use splitbrain\phpcli\Options;

/**
 * DokuWiki Plugin wordimport (CLI Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Andreas Gohr <dokuwiki@cosmocode.de>
 */
class cli_plugin_wordimport extends CLIPlugin
{
    /** @inheritDoc */
    protected function setup(Options $options)
    {
        $options->setHelp('Import Microsoft Word documents into DokuWiki');
        $options->registerOption('overwrite', 'Overwrite existing pages', 'o', false);

        $options->registerCommand('single', 'Import a single Word document');
        $options->registerArgument('docx', 'The .docx Word file', 'true', 'single');
        $options->registerArgument('page', 'The page ID to where the contents should be imported.', 'true', 'single');


        $options->registerCommand('bulk', 'Import multiple Word documents');
        $options->registerArgument('dir', 'The directory containing .docx files', 'true', 'bulk');
        $options->registerArgument('ns', 'The namespace to import the files into.', 'true', 'bulk' );
    }

    /** @inheritDoc */
    protected function main(Options $options)
    {
        $this->loadConfig();
        $args = $options->getArgs();

        switch ($options->getCmd()) {
            case 'single':
                return $this->import($args[0], $args[1], $options->getOpt('overwrite'));
            case 'bulk':
                return $this->bulk($args[0], $args[1], $options->getOpt('overwrite'));
            default:
                echo $options->help();
                return 1;
        }

    }

    protected function import($docx, $page, $overwrite = false)
    {
        $this->notice("Importing {docx}…", ['docx' => $docx]);

        if(page_exists($page) && !$overwrite) {
            $this->error('Skipping Import. Page exists: ' . $page);
            return 1;
        }

        $doc = new DocX($docx, $this->conf);
        $doc->import($page);
        $this->success('Imported ' . $doc->getPageId());
        return 0;
    }

    protected function bulk($dir, $ns, $overwrite)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );

        $ok = 0;

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'docx') {
                $docx = $file->getPathname();
                $page = pathinfo($file->getFilename(), PATHINFO_FILENAME);
                $path = substr($file->getPath(), strlen($dir));
                $pageid = cleanID($ns . ':' . $path . ':' . $page);

                try {
                    $this->import($docx, $pageid, $overwrite);
                } catch (\Exception $e) {
                    $this->error($e->getMessage());
                    $ok = 1;
                }
            }
        }

        return $ok;
    }
}
