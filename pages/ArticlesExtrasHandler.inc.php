<?php

/**
 * @file ArticlesExtrasHandler.php
 *
 * Copyright (c) 2011 Richard González Alberto
 * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING.
 *
 * @ingroup plugins_generic_articlesExtras
 * @brief Articles Extras generic plugin Handler.
 *
 */
import('classes.handler.Handler');
import('file.ArticleFileManager');

//  import JSON class for use with all AJAX requests
//  import('lib.pkp.classes.core.JSON');
import('lib.pkp.classes.core.JSONManager');
import('lib.pkp.classes.core.JSONMessage');

class ArticlesExtrasHandler extends Handler {

    /**
     * Display a list of the current issues.
     */
    function listIssues($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = & PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $editType = array_shift($args);
            $journal = &Request::getJournal();
            $issueDao = &DAORegistry::getDAO('IssueDAO');
            $issues = &$issueDao->getIssues($journal->getJournalId(), Handler::getRangeInfo('issues'));

            $templateMgr = & TemplateManager::getManager();
            $templateMgr->assign('editType', $editType);
            $templateMgr->assign_by_ref('issues', $issues);
            $templateMgr->display($articlesExtrasPlugin->getTemplatePath() . 'issues.tpl');
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Display a list of articles from the selected issue.
     */
    function listArticles($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $issueId = array_shift($args);
            $editType = array_shift($args);
            $publishedArticleDao = &DAORegistry::getDAO('PublishedArticleDAO');
            $articles = &$publishedArticleDao->getPublishedArticles($issueId, null, false);

            $templateMgr = &TemplateManager::getManager();
            $templateMgr->assign_by_ref('articles', $articles);
            $templateMgr->assign('editType', $editType);
            $templateMgr->display($articlesExtrasPlugin->getTemplatePath() . 'articles.tpl');
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Show body submit form.
     */
    function submitBody($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasBodyForm');
            $form = & new ArticlesExtrasBodyForm($articlesExtrasPlugin, $journal->getJournalId());

            $form->initData($args);
            $form->display();
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Save submitted Body.
     */
    function saveBody($args = array()) {
        ArticlesExtrasHandler::validate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasBodyForm');
            $form = & new ArticlesExtrasBodyForm($articlesExtrasPlugin, $journal->getJournalId());

            // saving and staying on the form
            if (Request::getUserVar('articleBody')) {
                $form->readInputData();

                if ($form->validate()) {
                    // perform the save and reset the form
                    $form->save();
                    $form->initData(array($form->getData('current')));
                } else {
                    // add the tiny MCE script to the form 
                    $form->addTinyMCE();

                    $templateMgr->assign('currentBody', Request::getUserVar('articleBody'));
                }
                $form->display();
            } else {
                $form->initData($args);
                $form->display();
            }
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Show images submit form.
     */
    function submitImages($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            //catch delete
            foreach ($args as $arg) {
                if ($arg == "delete") {
                    $articleId = $args[0];
                    $fileId = $args[2];
                    ArticlesExtrasHandler::deleteImage($articleId, $fileId);
                }
            }

            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasImagesForm');
            $form = & new ArticlesExtrasImagesForm($articlesExtrasPlugin, $journal->getJournalId());

            $form->initData($args);
            $form->display();
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Delete an Image.
     * @param $articleId
     * @param $fileId
     */
    function deleteImage($articleId, $fileId) {
        ArticlesExtrasHandler::validate();

        //article
        $articleDao = &DAORegistry::getDAO('PublishedArticleDAO');
        $article = &$articleDao->getPublishedArticleByArticleId($articleId, null, false);

        //plugin
        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $plugin->import("classes.Image");

        //articlesExtras
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        //Get current
        $images = unserialize($articlesExtrasDao->getArticleImages($articleId));

        //Delete from array
        $newImages = array();
        foreach ($images as $image) {
            if ($image->getFileId() != $fileId)
                $newImages[] = $image;
        }

        //Delete file
        $articleFileManager = &new ArticleFileManager($articleId);
        $articleFileManager->deleteFile($fileId);

        // Make update
        $articlesExtrasDao->setArticleImages($article, serialize($newImages));

        //Refresh
        Request::redirect(null, 'ArticlesExtrasPlugin', 'submitImages', array($articleId));
    }

    /**
     * Save submitted Images.
     */
    function saveImages() {
        ArticlesExtrasHandler::validate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasImagesForm');
            $form = & new ArticlesExtrasImagesForm($articlesExtrasPlugin, $journal->getJournalId());

            // saving and staying on the form
            if (Request::getUserVar('name')) {
                $form->readInputData();

                if ($form->validate()) {
                    // perform the save and reset the form
                    $form->save();
                    $form->initData(array($form->getData('current')));
                }

                $form->display();
            } else {
                $form->initData($args);
                $form->display();
            }
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Show citations submit form.
     */
    function submitCitations($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();
        $current = array_shift($args);

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $locales = $journal->getSupportedLocaleNames();

            $articleDao = &DAORegistry::getDAO('PublishedArticleDAO');
            $article = &$articleDao->getPublishedArticleByArticleId($current, null, false);

            $templateMgr = &TemplateManager::getManager();
            $templateMgr->addStyleSheet(Request::getBaseUrl() . '/' . $articlesExtrasPlugin->getStyleSheet());
            $templateMgr->assign('current', $current);
            $templateMgr->assign('locales', $locales);
            $templateMgr->assign_by_ref('article', $article);
            $templateMgr->display($articlesExtrasPlugin->getTemplatePath() . 'citationsForm.tpl');
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Update the article citations field.
     */
    function updateOjsArticleCitations($args = array()) {
        $articleId = array_shift($args);
        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $articlesExtrasPlugin->import('classes.Citation');
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        // Get current citations
        $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));

        //$articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if (count($citations) > 1) {

            $citationsArray = array();
            foreach ($citations as $citation) {
                $templateMgr = &TemplateManager::getManager();
                $templateMgr->assign_by_ref('citation', $citation);
                $cite = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'citation.tpl');
                $cite = str_replace(array("\r\n", "\r"), "\n", $cite);
                $lines = explode("\n", $cite);
                $new_lines = array();
                foreach ($lines as $i => $line) {
                    if (!empty($line))
                        $new_lines[] = trim($line);
                }
                $cite = implode($new_lines);
                //$cite = str_replace(".", ". ", $cite);
                array_push($citationsArray, $cite);
            }

            $cite = "";
            $cont = 1;
            foreach ($citationsArray as $citation) {
                if ($cont == 1) {
                    $cite = $cont . ".  " . $citation;
                } else {
                    $cite = $cite . "\n" . $cont . ".  " . $citation;
                }
                $cont++;
            }

            $articlesExtrasDao->updateOjsArticleCitations($articleId, $cite);
        }
    }

    /**
     * Displays current citations
     */
    function getCitationsTable($args = array()) {
        $articleId = array_shift($args);
        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $articlesExtrasPlugin->import('classes.Citation');
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        // Get current citations
        $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));
        $citationsEncoded = array();
        foreach ($citations as $citation) {
            $citationsEncoded[] = $this->assemblyCitation($citation);
        }

        $templateMgr = &TemplateManager::getManager();
        $templateMgr->assign_by_ref('citations', $citationsEncoded);
        $templateMgr->assign('total', count($citationsEncoded));
        $html = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'citations.tpl');
        echo json_encode(array("content" => $html));
    }

    /**
     * deletes citations by id
     */
    function deleteCitation($args = array()) {
        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $plugin->import('classes.Citation');

        $articleId = array_shift($args);
        $id = array_shift($args); // citation id to delete		
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        $citationsNew = array();
        if ($articlesExtrasDao->settingExists($articleId, "citations")) {
            // Get current citations
            $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));
            foreach ($citations as $citationId => $citation) {
                if ($id == ($citationId + 1))
                    continue;
                $citationsNew[] = $citation;
            }
        }


        // Set citations
        $articlesExtrasDao->setArticleCitations($articleId, $citationsNew);
    }

    /**
     * moves citations by id
     */
    function moveCitations($args = array()) {
        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $articleId = array_shift($args);
        $order = array_shift($args);

        $citationsOrder = explode(",", $order);



        $plugin->import('classes.Citation');
        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        $citations = array();
        if ($articlesExtrasDao->settingExists($articleId, "citations")) {
            // Get current citations
            $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));
            $new = array();
            foreach ($citationsOrder as $cid) {
                if ($cid == "")
                    continue;

                $new[] = $citations[$cid - 1];
            }

            // Set citations
            $articlesExtrasDao->setArticleCitations($articleId, $new);
        }
    }

    /**
     * Edits an existing citation
     */
    function getCitation($args = array()) {
        $articleId = array_shift($args);
        $citationId = array_shift($args);

        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $plugin->import('classes.Citation');

        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        // Create new citation
        $citations = array();

        if ($articlesExtrasDao->settingExists($articleId, "citations")) {
            // Get current citations
            $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));
        }

        // Add new citation to array
        $citation = $citations[$citationId - 1];

        $data = $citation->getData();

        $form = "";
        $others = array('date' => false,
            'updDate' => false,
            'citDate' => false
        );

        foreach ($data as $id => $value) {
            if ($id == "year" || $id == "month" || $id == "day") {
                $value = $value == "" ? 0 : $value;
                $others['date'] = !($others['date']) ? $value : $others['date'] . "/" . $value;
                continue;
            } elseif ($id == "updYear" || $id == "updMonth" || $id == "updDay") {
                $value = $value == "" ? 0 : $value;
                $others['updDate'] = !($others['updDate']) ? $value : $others['updDate'] . "/" . $value;
                continue;
            } elseif ($id == "citYear" || $id == "citMonth" || $id == "citDay") {
                $value = $value == "" ? 0 : $value;
                $others['citDate'] = !($others['citDate']) ? $value : $others['citDate'] . "/" . $value;
                continue;
            } elseif ($id == "page_initial" || $id == "page_final") {
                $others['pages'] = (isset($others['pages'])) ? $others['pages'] . "-" . $value : $value;
            } elseif ($id == "bookChapAuthors" || $id == "bookChapTitle") {
                $others['bookChapter'][$id] = $value;
            } elseif ($id == "confTitle" || $id == "confYear" || $id == "confSponsor") {
                $others['conference'][$id] = $value;
            }

            $form .= $this->getCitationPart($id, $value);
        }

        // add others
        foreach ($others as $id => $value) {
            if ($value)
                $form .= $this->getCitationPart($id, $value);
        }

        // add id
        $form .= $this->getControl($citationId, "citationId", "hidden");

        echo json_encode(array("citationId" => $citationId, 'lang' => $citation->getLocale(), "citationForm" => $form));
    }

    /**
     * Inserts a new citation into database
     * @param $form array
     */
    function insertCitation($args = array()) {
        $articleId = array_shift($args);
        $form = $_POST;

        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $plugin->import('classes.Citation');

        $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');

        // Create new citation
        $newCitation = new Citation($form);
        $citations = array();

        if ($articlesExtrasDao->settingExists($articleId, "citations")) {
            // Get current citations
            $citations = unserialize($articlesExtrasDao->getCitationsByArticleId($articleId));
        }

        if (isset($form['citationId'])) {
            $citationId = $form['citationId'];
            $citations[$citationId - 1] = $newCitation;
        } else { // Add new citation to array
            $citations[] = $newCitation;
        }

        // Set citations
        $articlesExtrasDao->setArticleCitations($articleId, $citations);
    }

    /**
     * previews the current citation
     */
    function citationPreview($args = array()) {
        $form = $_POST;

        $plugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $plugin->import('classes.Citation');
        $citation = &new Citation($form);

        $output = $this->assemblyCitation($citation);
        echo urldecode($output);
    }

    /**
     * inserts a citation part on the new/edit citation form 
     */
    function insertCitationPart($args = array()) {
        $part = array_shift($args);
        $control = $this->getCitationPart($part);


        echo json_encode(array("content" => $control));
    }

    function applyCitationTemplate($args = array()) {
        $controls = NULL;
        $template = array_shift($args);

        switch ($template) {
            case "art-norm": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }

            case "org-as-author": {
                    $controls = $this->getCitationPart("author_organization");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "author-org-as-author": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("author_organization");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "no-author": {
                    $controls = $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "vol-suppl": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('suppl_volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "issue-suppl": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('suppl_issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "vol-part": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('part_volume');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "issue-part": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('part_issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "issue-no-vol": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "no-issue-no-vol": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "title-with-type": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "with-retraction": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    $controls .= $this->getCitationPart('retraction_object_of');
                    break;
                }
            case "with-parcial-retraction": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("date");
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('pages');
                    $controls .= $this->getCitationPart('retraction_contains');
                    break;
                }
            case "author-personal": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("edition");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "editor-compilator-as-author": {
                    $controls = $this->getCitationPart("editors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("edition");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "author-editor": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("editors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("edition");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "book-org-as-author": {
                    $controls = $this->getCitationPart("author_organization");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "book-chapter": {
                    $controls = $this->getCitationPart("bookChapter");
                    $controls .= $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    $controls .= $this->getCitationPart('pages');
                    break;
                }
            case "dissertation": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart("state");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "art-newspapper": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    $controls .= $this->getCitationPart('section');
                    $controls .= $this->getCitationPart('column');
                    break;
                }
            case "audiovisual": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart("state");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "cdrom": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    break;
                }
            case "art-internet": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("source");
                    $controls .= $this->getCitationPart("typeSource");
                    $controls .= $this->getCitationPart('date');
                    $controls .= $this->getCitationPart('citDate');
                    $controls .= $this->getCitationPart('volume');
                    $controls .= $this->getCitationPart('issue');
                    $controls .= $this->getCitationPart('sitePage');
                    $controls .= $this->getCitationPart('url');
                    break;
                }
            case "monog-internet": {
                    $controls = $this->getCitationPart("authors");
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('date');
                    $controls .= $this->getCitationPart('citDate');
                    $controls .= $this->getCitationPart('url');
                    break;
                }
            case "homepage": {
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('siteDate');
                    $controls .= $this->getCitationPart('updDate');
                    $controls .= $this->getCitationPart('citDate');
                    $controls .= $this->getCitationPart('url');
                    break;
                }
            case "homepage-part": {
                    $controls .= $this->getCitationPart("title");
                    $controls .= $this->getCitationPart("typeTitle");
                    $controls .= $this->getCitationPart("pubPlace");
                    $controls .= $this->getCitationPart('editorial');
                    $controls .= $this->getCitationPart('siteDate');
                    $controls .= $this->getCitationPart('updDate');
                    $controls .= $this->getCitationPart('citDate');
                    $controls .= $this->getCitationPart('pageTitleWeb');
                    $controls .= $this->getCitationPart('pageCount');
                    $controls .= $this->getCitationPart('url');
                    break;
                }
        }

        echo json_encode(array("content" => $controls));
    }

    function getCitationPart($part, $value = NULL) {
        $control = NULL;
        switch ($part) {
            case "authors": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.authors'), $part, "text", 25, $value);
                    break;
                }
            case "author_organization": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.orgAuthors'), $part, "text", 25, $value);
                    break;
                }
            case "editors": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.editors'), $part, "text", 25, $value);
                    break;
                }
            case "title": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.title'), $part, "text", 35, $value);
                    break;
                }
            case "typeTitle": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.typetitle'), $part, "text", 15, $value);
                    break;
                }
            case "typeSource": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.typesource'), $part, "text", 15, $value);
                    break;
                }
            case "edition": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.edition'), $part, "text", 15, $value);
                    break;
                }
            case "source": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.source'), $part, "text", 20, $value);
                    break;
                }
            case "pubPlace": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pubPlace'), $part, "text", 20, $value);
                    break;
                }
            case "state": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.state'), $part, "text", 20, $value);
                    break;
                }
            case "editorial": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.editorial'), $part, "text", 20, $value);
                    break;
                }
            case "date": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "", "date", 0, $value);
                    break;
                }
            case "volume": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.volume'), $part, "text", 5, $value);
                    break;
                }
            case "suppl_volume": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.supplvolume'), $part, "text", 5, $value);
                    break;
                }
            case "part_volume": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.partvolume'), $part, "text", 5, $value);
                    break;
                }
            case "issue": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.issue'), $part, "text", 5, $value);
                    break;
                }
            case "suppl_issue": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.supplissue'), $part, "text", 5, $value);
                    break;
                }
            case "part_issue": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.partissue'), $part, "text", 5, $value);
                    break;
                }
            case "retraction_object_of": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.retractionin'), NULL, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.authors'), "retAuthors", "text", 25, $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.source'), "retSource", "text", 15, $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "ret", "date", $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.volume'), "retVolume", "text", 5, $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.issue'), "retIssue", "text", 5, $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pages'), "ret_", "pages", $value);
                    $control .= $this->getControl("object_of", "retId", "hidden");
                    $control .= $this->addHr();
                    break;
                }
            case "retraction_contains": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.retractionof'), NULL, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.authors'), "retAuthors", "text", 25);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.source'), "retSource", "text", 15);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "ret", "date");
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.volume'), "retVolume", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.issue'), "retIssue", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pages'), "ret_", "pages");
                    $control .= $this->getControl("contains", "retId", "hidden");
                    $control .= $this->addHr();
                    break;
                }
            case "correction": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.correction'), NULL, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.source'), "corSource", "text", 15);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "cor", "date");
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.volume'), "corVolume", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.issue'), "corIssue", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pages'), "cor_", "pages");
                    $control .= $this->addHr();
                    break;
                }

            case "erratum": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.erratum'), NULL, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.source'), "errSource", "text", 15);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "err", "date");
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.volume'), "errVolume", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.issue'), "errIssue", "text", 5);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pages'), "err_", "pages");
                    $control .= $this->addHr();
                    break;
                }

            case "pages": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pages'), "", "pages", 0, $value);
                    break;
                }
            case "siteDate": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.dateweb'), "", "dateweb", $value);
                    break;
                }
            case "epub": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.epub'), NULL, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "epub", "date", $value);
                    $control .= $this->addHr();

                    break;
                }

            case "sitePage": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pagewebsite'), $part, "text", 10, $value);
                    break;
                }
            case "pageTitleWeb": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pageTitleWeb'), $part, "text", 35, $value);
                    break;
                }
            case "url": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.url'), $part, "text", "30", $value);
                    break;
                }
            case "citDate": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.citdate'), "cit", "date", 0, $value);
                    break;
                }
            case "updDate": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.upddate'), "upd", "date", 0, $value);
                    break;
                }
            case "bookChapter": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.bookchapter'), null, "subtitle", $value);
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.authors'), "bookChapAuthors", "text", 25, $value['bookChapAuthors']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.title'), "bookChapTitle", "text", 35, $value['bookChapTitle']);
                    $control .= $this->addHr();
                    break;
                }
            case "pageCount": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.pagecount'), $part, "text", "3", $value);
                    break;
                }
            case "section": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.section'), $part, "text", "3", $value);
                    break;
                }
            case "column": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.col'), $part, "text", "3", $value);
                    break;
                }
            case "forthcoming": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.forthcoming'), $part, "text", "6", $value);
                    break;
                }
            case "confArticle": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.confArticle'), null, "subtitle", $value);
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.authors'), "confArtAuthors", "text", 25, $value);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.title'), "confArtTitle", "text", 35, $value);
                    $control .= $this->addHr();
                    break;
                }
            case "conference": {
                    $control = $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.conference'), null, "subtitle");
                    $control .= $this->addHr();
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.confNumber'), "confNumber", "text", "5", $value['confNumber']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.sponsor'), "confSponsor", "text", "15", $value['confSponsor']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.title'), "confTitle", "text", "35", $value['confTitle']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.date'), "confDate", "date", null, $value['confDate']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.city'), "confCity", "text", "10", $value['confCity']);
                    $control .= $this->getControl(Locale::translate('plugins.generic.articlesExtras.citations.state_country'), "confState", "text", "10", $value['confState']);
                    $control .= $this->addHr();
                    break;
                }
        }

        return $control;
    }

    /**
     * Makes a citation
     */
    function assemblyCitation(&$citation) {
        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $templateMgr = &TemplateManager::getManager();
        $templateMgr->assign_by_ref('citation', $citation);

        // hack for citations form issue
        $url = $citation->getUrl();
        $warpedUrl = wordWrap($url, 5, "&#8203;", true);
        $templateMgr->assign_by_ref('warpedUrl', $warpedUrl);

        return $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'citationHighlights.tpl');
    }

    /* Return an HTML form input
     * @param $title string
     * @param $part string
     * @param $type string
     * @param @size int
     */

    function getControl($title, $part, $type, $size = 25, $value = NULL) {
        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);
        $templateMgr = &TemplateManager::getManager();
        
        $templateMgr->assign('title', $title);
        $templateMgr->assign('part', $part);
        $templateMgr->assign('size', $size);
        $templateMgr->assign('value', $value);
        
        $control = null;
        switch ($type) {
            case "text": {
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/text.tpl');
                    break;
                }
            case "subtitle": {
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/subtitle.tpl');
                    break;
                }
            case "date": {
                    $currYear = date("Y");
                    $years = $this->getYears();
                    $months = $this->getMonths();
                    $templateMgr->assign('currYear', $currYear);
                    $templateMgr->assign('years', $years);
                    $templateMgr->assign('months', $months);

                    $nameYear = $part == "" ? "year" : $part . "Year";
                    $nameMonth = $part == "" ? "month" : $part . "Month";
                    $nameDay = $part == "" ? "day" : $part . "Day";
                    $templateMgr->assign('nameYear', $nameYear);
                    $templateMgr->assign('nameMonth', $nameMonth);
                    $templateMgr->assign('nameDay', $nameDay);
                    

                    if ($value != NULL) {
                        list($valYear, $valMonth, $valDay) = explode("/", $value);
                    } else {
                        $valYear = "";
                        $valMonth = "";
                        $valDay = "";
                    }
                    $templateMgr->assign('valYear', $valYear);
                    $templateMgr->assign('valMonth', $valMonth);
                    $templateMgr->assign('valDay', $valDay);

                    for ($i = 1; $i <= 31; $i++)
                        $days[] = $i;
                    $templateMgr->assign('days', $days);

                    $divId = str_replace(" ", "_", $title);                    
                    $templateMgr->assign('divId', $divId);
                    
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/date.tpl');
                    break;
                }
            case "pages": {
                    if ($value != NULL) {
                        list($initial, $final) = explode("-", $value);
                    } else {
                        $initial = "";
                        $final = "";
                    }
                    $templateMgr->assign('initial', $initial);
                    $templateMgr->assign('final', $final);

                    $divId = str_replace(" ", "_", $title);
                    $templateMgr->assign('divId', $divId);
                    
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/pages.tpl');
                    break;
                }

            case "hidden": {
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/hidden.tpl');
                    break;
                }
            case "dateweb": {
                    $divId = str_replace(" ", "_", $title);
                    $templateMgr->assign('divId', $divId);
                    
                    $control = $templateMgr->fetch($articlesExtrasPlugin->getTemplatePath() . 'controls/dateweb.tpl');
                    break;
                }
        }

        return $control;
    }

    function addHr() {
        return "<hr />";
    }

    /**
     * Return an array of years from 1961-current
     */
    function getYears() {
        $year = date("Y");

        $years = array();
        for ($i = 1900; $i <= $year; $i++)
            $years[] = $i;

        return $years;
    }

    /**
     * Return an array of months
     */
    function getMonths() {
        $months = array(0 => "",
            1 => "Ene",
            2 => "Feb",
            3 => "Mar",
            4 => "Abr",
            5 => "May",
            6 => "Jun",
            7 => "Jul",
            8 => "Ago",
            9 => "Sep",
            10 => "Oct",
            11 => "Nov",
            12 => "Dic");

        return $months;
    }

    /**
     * Setup common template variables.
     * @param $subclass boolean set to true if caller is below this handler in the hierarchy
     */
    function setupTemplate($subclass = false) {
        parent::validate();

        $templateMgr = &TemplateManager::getManager();
        $templateMgr->append(
                'pageHierarchy', array(
            Request::url(null, 'ArticlesExtrasPlugin'),
            Locale::Translate('plugins.generic.articlesExtras.displayName'),
            true
                )
        );
    }

    /**
     * Validate that user is an editor/admin/manager/layout_editor in the selected journal.
     * Redirects to user index page if not properly authenticated.
     */
    function validate() {
        $journal = &Request::getJournal();
        if (!isset($journal) || (!Validation::isEditor($journal->getJournalId()) && !Validation::isSiteAdmin() && !Validation::isJournalManager($journal->getJournalId()) && !Validation::isLayoutEditor($journal->getJournalId()) )) {
            Validation::redirectLogin();
        }
    }

    /**
     * Show authorAditionalFields submit form.
     */
    function submitAuthorAdditionalFields($args = array()) {
        ArticlesExtrasHandler::validate();
        ArticlesExtrasHandler::setupTemplate();
        $journal = &Request::getJournal();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasAuthorAdditionalFieldsForm');
            $form = & new ArticlesExtrasAuthorAdditionalFieldsForm($articlesExtrasPlugin, $journal->getJournalId());

            $form->initData($args);
            $form->display();
        } else {
            Request::redirect(null, 'index');
        }
    }

    /**
     * Save submitted author aditional fields.
     */
    function saveAuthorAdditionalFields($args = array()) {
        ArticlesExtrasHandler::validate();
        $journal = &Request::getJournal();
        //var_dump($_POST);
        //die();

        if ($journal != null) {
            $journalId = $journal->getJournalId();
        } else {
            Request::redirect(null, 'index');
        }

        $articlesExtrasPlugin = &PluginRegistry::getPlugin('generic', ARTICLES_EXTRAS_PLUGIN_NAME);

        if ($articlesExtrasPlugin != null) {
            $articlesExtrasEnabled = $articlesExtrasPlugin->getEnabled();
        }

        if ($articlesExtrasEnabled) {
            $articlesExtrasPlugin->import('pages.forms.ArticlesExtrasAuthorAdditionalFieldsForm');
            $form = & new ArticlesExtrasAuthorAdditionalFieldsForm($articlesExtrasPlugin, $journal->getJournalId());

            // saving and staying on the form
            if (Request::getUserVar('authorsData')) {
                $form->readInputData();

                if ($form->validate()) {
                    // perform the save and reset the form
                    //var_dump($form);
                    //die();
                    $form->save();
                    $form->initData(array($form->getData('current')));
                } else {
                    // add the tiny MCE script to the form 
                    $form->addTinyMCE();

                    //$templateMgr->assign('currentBody', Request::getUserVar('articleBody'));
                    //$templateMgr->assign('articlesExtrasDao', &DAORegistry::getDAO('ArticlesExtrasDAO'));
                    //$articleDao = &DAORegistry::getDAO('ArticleDAO');            
                    //$article = &$articleDao->getArticle($current);
                    //authors
                    //$authors = $article->getAuthors();
                    //$templateMgr->assign('authors', $authors);
                }
                $form->display();
            } else {
                $form->initData($args);
                $form->display();
            }
        } else {
            Request::redirect(null, 'index');
        }
    }

}

?>
