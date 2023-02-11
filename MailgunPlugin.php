<?php

/**
 * @file plugins/generic/mailgun/MailgunPlugin.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.mailgun
 * @class MailgunPlugin
 *
 * @brief An example plugin to send emails via Mailgun API
 */

namespace APP\plugins\generic\mailgun;

use APP\core\Application;
use APP\core\Request;
use PKP\core\JSONMessage;
use PKP\linkAction\LinkAction;
use PKP\linkAction\request\AjaxModal;
use PKP\plugins\GenericPlugin;
use Illuminate\Support\Facades\Config;
use PKP\core\PKPApplication;

require_once(dirname(__FILE__) . '/vendor/autoload.php');

class MailgunPlugin extends GenericPlugin
{

    public function register($category, $path, $mainContextId = null)
    {
        $isRegistered = parent::register($category, $path, $mainContextId);
        if (Application::isUnderMaintenance()) {
            return $isRegistered;
        }

        if ($isRegistered && $this->getEnabled($mainContextId)) {
            $this->setConfig();
        }
        return $isRegistered;
    }

    public function getDisplayName()
    {
        return __('plugins.generic.mailgun.displayName');
    }

    public function getDescription()
    {
        return __('plugins.generic.mailgun.description');
    }

    protected function setConfig()
    {
        $context = Application::get()->getRequest()->getContext();
        $contextId = $context ? $context->getId() : PKPApplication::CONTEXT_SITE;

        Config::set('mail.mailers.mailgun', [
            'transport' => 'mailgun',
            'domain' => $this->fallbackToSiteData('domain', $contextId),
            'secret' => $this->fallbackToSiteData('secret', $contextId),
            'endpoint' => $this->fallbackToSiteData('endpoint', $contextId),
        ]);

        Config::set('mail.default', 'mailgun');
    }

    /**
     * If context-specific data isn't set, get site-wide plugin settings
     */
    protected function fallbackToSiteData(string $dataSetting, int $contextId)
    {
        $dataValue = $this->getSetting($contextId, $dataSetting);
        if ($contextId === PKPApplication::CONTEXT_SITE) {
            return $dataValue;
        }

        if (empty($dataValue)) {
            return $this->getSetting(PKPApplication::CONTEXT_SITE, $dataSetting);
        }

        return $dataValue;
    }

    /**
     * Enable the settings form in the site-wide plugins list
     *
     * @return boolean
     */
    public function isSitePlugin(): bool
    {
        return true;
    }

    /**
     * Add a settings action to the plugin's entry in the
     * plugins list.
     *
     * @param Request $request
     * @param array $actionArgs
     * @return array
     */
    public function getActions($request, $actionArgs)
    {
        // Get the existing actions
        $actions = parent::getActions($request, $actionArgs);

        // Only add the settings action when the plugin is enabled
        if (!$this->getEnabled()) {
            return $actions;
        }

        // Create a LinkAction that will make a request to the
        // plugin's `manage` method with the `settings` verb.
        $router = $request->getRouter();
        import('lib.pkp.classes.linkAction.request.AjaxModal');
        $linkAction = new LinkAction(
            'settings',
            new AjaxModal(
                $router->url(
                    $request,
                    null,
                    null,
                    'manage',
                    null,
                    [
                        'verb' => 'settings',
                        'plugin' => $this->getName(),
                        'category' => 'generic'
                    ]
                ),
                $this->getDisplayName()
            ),
            __('manager.plugins.settings'),
            null
        );

        // Add the LinkAction to the existing actions.
        // Make it the first action to be consistent with
        // other plugins.
        array_unshift($actions, $linkAction);

        return $actions;
    }

    /**
     * Show and save the settings form when the settings action
     * is clicked.
     *
     * @param array $args
     * @param Request $request
     * @return JSONMessage
     */
    public function manage($args, $request) {
        switch ($request->getUserVar('verb')) {
            case 'settings':

                // Load the custom form
                $form = new MailgunPluginSettingsForm($this);

                // Fetch the form the first time it loads, before
                // the user has tried to save it
                if (!$request->getUserVar('save')) {
                    $form->initData();
                    return new JSONMessage(true, $form->fetch($request));
                }

                // Validate and save the form data
                $form->readInputData();
                if ($form->validate()) {
                    $form->execute();
                    return new JSONMessage(true);
                }
        }
        return parent::manage($args, $request);
    }
}