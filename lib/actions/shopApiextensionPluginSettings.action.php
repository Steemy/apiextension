<?php

/**
 * Settings for plugin backend
 *
 * @author Steemy, created by 21.07.2021
 */

class shopApiextensionPluginSettingsAction extends waViewAction
{
    public function execute()
    {
        $pluginSetting = shopApiextensionPluginSettings::getInstance();

        $settings = $pluginSetting->getSettings();
        $pluginSetting->getSettingsCheck($settings);

        $this->view->assign("settings", $settings);
    }
}