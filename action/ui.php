<?php

use dokuwiki\Extension\ActionPlugin;
use dokuwiki\Extension\Event;
use dokuwiki\Extension\EventHandler;
use dokuwiki\Form\Form;
use dokuwiki\Form\InputElement;
use dokuwiki\plugin\wordimport\docx\DocX;

/**
 * DokuWiki Plugin wordimport (Action Component)
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author Andreas Gohr <dokuwiki@cosmocode.de>
 */
class action_plugin_wordimport_ui extends ActionPlugin
{
    /** @inheritDoc */
    public function register(EventHandler $controller)
    {
        $controller->register_hook('ACTION_ACT_PREPROCESS', 'BEFORE', $this, 'handleActionActPreprocess');
        $controller->register_hook('TPL_ACT_UNKNOWN', 'BEFORE', $this, 'handleTplActUnknown');
    }

    /**
     * Event handler for ACTION_ACT_PREPROCESS
     *
     * @see https://www.dokuwiki.org/devel:events:ACTION_ACT_PREPROCESS
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleActionActPreprocess(Event $event, $param)
    {
        $act = act_clean($event->data);
        if ($act !== 'wordimport') return;


        global $ID;

        if (
            isset($_FILES['file']['tmp_name']) &&
            is_uploaded_file($_FILES['file']['tmp_name']) &&
            checkSecurityToken()
        ) {
            try {
                $this->import($_FILES['file']['tmp_name'], $ID);
                $event->data = 'show'; // change back to normal display
                msg($this->getLang('imported'), 1);
                return;
            } catch (Exception $e) {
                msg(hsc($e->getMessage()), -1);
            }
        }

        $event->preventDefault();
    }

    /**
     * Event handler for TPL_ACT_UNKNOWN
     *
     * @see https://www.dokuwiki.org/devel:events:tpl_act_unknown
     * @param Event $event Event object
     * @param mixed $param optional parameter passed when event was registered
     * @return void
     */
    public function handleTplActUnknown(Event $event, $param)
    {
        if ($event->data !== 'wordimport') return;
        $event->preventDefault();
        $this->html();
    }

    public function import($file, $page)
    {
        $docx = new DocX($file);
        $docx->import($page);
    }

    public function html()
    {
        global $ID;
        $form = new Form(['class' => 'plugin_wordimport', 'enctype' => 'multipart/form-data', 'method' => 'post']);
        $form->addHTML($this->locale_xhtml('intro'));

        $form->addFieldsetOpen();

        $upload = new InputElement('file', 'file', $this->getLang('uploadfield'));
        $upload->attr('accept', '.docx');

        $form->addElement($upload);

        $form->addTagOpen('p')->addClass('buttons');
        $form->addButton('do[wordimport]', sprintf($this->getLang('btn_import'), $ID));
        $form->addButton('do[show]', $this->getLang('btn_cancel'));
        $form->addTagClose('p');
        $form->addFieldsetClose();
        echo $form->toHTML();
    }
}
