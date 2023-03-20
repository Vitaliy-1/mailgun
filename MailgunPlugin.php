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
use PKP\plugins\GenericPlugin;
use Illuminate\Support\Facades\Config as ContainerConfig;
use PKP\config\Config;

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
        $defaultMailer = Config::getVar('email', 'default');
        if ($defaultMailer !== 'mailgun') {
            return;
        }

        ContainerConfig::set('mail.mailers.mailgun', [
            'transport' => $defaultMailer,
            'domain' => Config::getVar('email', 'domain'),
            'secret' => Config::getVar('email', 'secret'),
            'endpoint' => Config::getVar('email', 'endpoint'),
        ]);

        ContainerConfig::set('mail.default', $defaultMailer);
    }

    public function isSitePlugin()
    {
        return true;
    }
}