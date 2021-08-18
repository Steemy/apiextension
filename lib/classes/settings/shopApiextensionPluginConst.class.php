<?php

/**
 * CONSTANS
 *
 * @author Steemy, created by 21.07.2021
 */

class shopApiextensionPluginConst
{
    /**
     * Название плагина
     * @return string
     */
    public function getNamePlugin()
    {
        return 'apiextension';
    }

    /**
     * Возвращает массив настроек по умолчанию
     * @return array
     * @throws waException
     */

    public function getSettingsDefault()
    {
        return array(
            'review_fields' => 0,
            'plugin_info'   => wa()->getConfig()->getAppConfig('shop')->getPluginInfo($this->getNamePlugin()),
        );
    }
}