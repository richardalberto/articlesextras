<?php/** * @file ArticlesExtrasPlugin.php * * Copyright (c) 2009-2011 Richard González Alberto * Distributed under the GNU GPL v2. For full terms see the file docs/COPYING. * * @ingroup plugins_generic_articlesExtras * @brief Articles Extras generic plugin. * */import('lib.pkp.classes.plugins.GenericPlugin');class ArticlesExtrasPlugin extends GenericPlugin {    /** 	      * Register the plugin, attaching to hooks as necessary.	      * @param $category string	      * @param $path string	      * @return boolean	      */    function register($category, $path) {        $success = parent::register($category, $path);        $this->addLocaleData();        if ($success && $this->getEnabled()) {            $this->import('classes.ArticlesExtrasDAO');            // PHP4 Requires explicit instantiation-by-reference            if (checkPhpVersion('5.0.0')) {                $articlesExtrasDao = new ArticlesExtrasDAO();            } else {                $articlesExtrasDao = & new ArticlesExtrasDAO();            }            DAORegistry::registerDAO('ArticlesExtrasDAO', $articlesExtrasDao);            // Handler for public pages            HookRegistry::register('LoadHandler', array($this, 'setupPublicHandler'));            // Enable TinyMCE for body text area            HookRegistry::register('TinyMCEPlugin::getEnableFields', array($this, 'enableTinyMCE'));            // Editor page for editor access            HookRegistry::register('Templates::Editor::Index::AdditionalItems', array($this, 'displayEditorHomeLink'));        }        return $success;    }    function getDisplayName() {        return __('plugins.generic.articlesExtras.displayName');    }    function getDescription() {        return __('plugins.generic.articlesExtras.description');    }    /**     * Get the template path for this plugin.     */    function getTemplatePath() {        return parent::getTemplatePath() . 'templates/';    }    /**     * Get the handler path for this plugin.     */    function getHandlerPath() {        return $this->getPluginPath() . '/pages/';    }    /**     * Get the stylesheet for this plugin.     */    function getStyleSheet() {        return $this->getPluginPath() . '/styles/styles.css';    }    /**     * Displays a link to the plugin on the editor's page     */    function displayEditorHomeLink($hookName, $params) {        if ($this->getEnabled()) {            $smarty = & $params[1];            $output = & $params[2];            $templateMgr = TemplateManager::getManager();            $output .= '<h3>' . $templateMgr->smartyTranslate(array('key' => 'plugins.generic.articlesExtras.displayName'), $smarty) . '</h3><ul class="plain">   <li>&#187; <a href="' . $templateMgr->smartyUrl(array('page' => 'ArticlesExtrasPlugin', 'op' => 'listIssues', 'path' => 'author'), $smarty) . '">' . $templateMgr->smartyTranslate(array('key' => 'plugins.generic.articlesExtras.displayName'), $smarty) . '</a></li></ul>';            //echo $output;        }        return false;    }    /**     * Displays additional fields on author metadata edit.     */    function displayAuthorAdditionalFields($hookName, $params) {        if ($this->getEnabled()) {            $smarty = & $params[1];            $output = & $params[2];            $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');            $articleId = $smarty->get_template_vars('articleId');            $authorIndex = $smarty->get_template_vars('authorIndex');            $authors = $smarty->get_template_vars('authors');            $author = $authors[$authorIndex];            $extraFields = array("aff_orgname", "aff_orgdiv1", "aff_orgdiv2", "aff_orgdiv3", "aff_city", "aff_state", "aff_country", "aff_zipcode", "aff_email");            foreach ($extraFields as $extra) {                $value = $articlesExtrasDao->getAuthorMetadataByAuthorId($author['authorId'], $extra);                $smarty->assign($extra, $value);            }            $output .= $smarty->fetch($this->getTemplatePath() . 'authorAdditionalFields.tpl');        }        return false;    }    /**     * Save extra fields on the metadata page.     */    function saveMetadata($hookName, $params) {        if ($this->getEnabled()) {            $article = & $params[0];            $authors = $_POST['authors'];            $articlesExtrasDao = &DAORegistry::getDAO('ArticlesExtrasDAO');            // TODO: make multi-lang            $extraFields = array("aff_orgname", "aff_orgdiv1", "aff_orgdiv2", "aff_orgdiv3", "aff_city", "aff_state", "aff_country", "aff_zipcode", "aff_email");            foreach ($authors as $author) {                $authorId = $author["authorId"];                foreach ($extraFields as $extraField) {                    $value = $author[$extraField];                    $articlesExtrasDao->setAuthorMetadata($authorId, $extraField, $value);                    //echo "{$authorId} ->>>> {$extraField} = {$value} <br />";                }            }        }        return false;    }    /**     * Setup plublic handler     */    function setupPublicHandler($hookName, $params) {        $page = &$params[0];        if ($page == 'ArticlesExtrasPlugin') {            define('ARTICLES_EXTRAS_PLUGIN_NAME', $this->getName());            define('HANDLER_CLASS', 'ArticlesExtrasHandler');            Locale::requireComponents(array(LOCALE_COMPONENT_APPLICATION_COMMON, LOCALE_COMPONENT_PKP_USER, LOCALE_COMPONENT_OJS_EDITOR));            $handlerFile = &$params[2];            $handlerFile = $this->getHandlerPath() . '/' . 'ArticlesExtrasHandler.inc.php';        }    }    /**     * Enable TinyMCE support for body text area.     */    function enableTinyMCE($hookName, $params) {        $fields = & $params[1];        $page = Request::getRequestedPage();        $op = Request::getRequestedOp();        $pageName = defined("ARTICLES_EXTRAS_PLUGIN_NAME") ? ARTICLES_EXTRAS_PLUGIN_NAME : null;        if (strtolower($page) == $pageName && ($op == 'submitBody' || $op == 'saveBody')) {            $fields[] = 'articleBody';        }        return false;    }    /**     * Get the name of the settings file to be installed site-wide when     * OJS is installed.     * @return string     */    function getInstallSitePluginSettingsFile() {        return $this->getPluginPath() . '/settings.xml';    }}?>