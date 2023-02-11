{**
 * templates/settings.tpl
 *
 * Copyright (c) 2014-2023 Simon Fraser University
 * Copyright (c) 2003-2023 John Willinsky
 * Distributed under the GNU GPL v3. For full terms see the file docs/COPYING.
 *
 * Settings form for the mailgun plugin.
 *}
<script>
    $(function() {ldelim}
        $('#pluginTemplateSettings').pkpHandler('$.pkp.controllers.form.AjaxFormHandler');
        {rdelim});
</script>

<form
        class="pkp_form"
        id="pluginTemplateSettings"
        method="POST"
        action="{url router=$smarty.const.ROUTE_COMPONENT op="manage" category="generic" plugin=$pluginName verb="settings" save=true}"
>
    {csrf}

    {fbvFormArea id="mailgunInfo"}
        {fbvFormSection for="domain"}
            {fbvElement type="text" id="domain" value=$domain label="plugins.generic.mailgun.domain.description"}
        {/fbvFormSection}
        {fbvFormSection for="secret"}
            {fbvElement type="text" id="secret" value=$secret label="plugins.generic.mailgun.secret.description"}
        {/fbvFormSection}
        {fbvFormSection for="endpoint"}
            {fbvElement type="text" id="endpoint" value=$endpoint label="plugins.generic.mailgun.endpoint.description"}
        {/fbvFormSection}
    {/fbvFormArea}
    {fbvFormButtons submitText="common.save"}
</form>