<?php

/**
 * @file plugins/generic/mailgun/MailgunPluginSettingsForm.php
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * @package plugins.generic.mailgun
 * @class MailgunPluginSettingsForm
 *
 * @brief A form to manage Mailgun credentials
 */

namespace APP\plugins\generic\mailgun;

use APP\core\Application;
use APP\notification\Notification;
use APP\notification\NotificationManager;
use APP\template\TemplateManager;
use PKP\core\PKPApplication;
use PKP\form\Form;
use PKP\form\validation\FormValidatorCSRF;
use PKP\form\validation\FormValidatorPost;

class MailgunPluginSettingsForm extends Form
{
    public MailgunPlugin $plugin;

    /**
     * @copydoc Form::__construct()
     */
    public function __construct($plugin) {

        // Define the settings template and store a copy of the plugin object
        parent::__construct($plugin->getTemplateResource('settings.tpl'));
        $this->plugin = $plugin;

        // Always add POST and CSRF validation to secure your form.
        $this->addCheck(new FormValidatorPost($this));
        $this->addCheck(new FormValidatorCSRF($this));
    }

    /**
     * Load settings already saved in the database
     *
     * Settings are stored by context, so that each journal or press
     * can have different settings.
     */
    public function initData() {
        $context = Application::get()->getRequest()->getContext();
        $contextId = $context ? $context->getId() : PKPApplication::CONTEXT_SITE;
        $this->setData([
            'domain' => $this->plugin->getSetting($contextId, 'domain'),
            'secret' => $this->plugin->getSetting($contextId, 'secret'),
            'endpoint' => $this->plugin->getSetting($contextId, 'endpoint'),
        ]);
        parent::initData();
    }

    /**
     * Load data that was submitted with the form
     */
    public function readInputData() {
        $this->readUserVars([
            'domain',
            'secret',
            'endpoint',
        ]);
        parent::readInputData();
    }

    /**
     * Fetch any additional data needed for the form
     */
    public function fetch($request, $template = null, $display = false) {

        // Pass the plugin name to the template so that it can be
        // used in the URL that the form is submitted to
        $templateMgr = TemplateManager::getManager($request);
        $templateMgr->assign('pluginName', $this->plugin->getName());

        return parent::fetch($request, $template, $display);
    }

    /**
     * Save the settings
     *
     * @return null|mixed
     */
    public function execute(...$functionArgs) {
        $context = Application::get()->getRequest()->getContext();
        $contextId = $context ? $context->getId() : PKPApplication::CONTEXT_SITE;
        $this->plugin->updateSetting($contextId, 'domain', $this->getData('domain'));
        $this->plugin->updateSetting($contextId, 'secret', $this->getData('secret'));
        $this->plugin->updateSetting($contextId, 'endpoint', $this->getData('endpoint'));

        // Tell the user that the save was successful.
        $notificationMgr = new NotificationManager();
        $notificationMgr->createTrivialNotification(
            Application::get()->getRequest()->getUser()->getId(),
            Notification::NOTIFICATION_TYPE_SUCCESS,
            ['contents' => __('common.changesSaved')]
        );

        return parent::execute(...$functionArgs);
    }
}