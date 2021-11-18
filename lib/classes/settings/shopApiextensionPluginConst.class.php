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
            'review_fields'              => 0,
            'bonus_for_review_status'    => 0,
            'bonus_for_review_all'       => 0,
            'bonus_for_review_all_photo' => 0,
            'bonus_for_review_all_type'  => 'number',
            'bonus_for_review_all_round' => 'percent',
            'bonus_for_review_days'      => 30,
            'bonus_text'                 => 'Бонусы за отзыв о товаре - %s',
            'bonus_text_cancel'          => 'Отмена бонусов за отзыв о товаре - %s',
            'bonus_by_category'          => array(),
            'bonus_max'                  => 1000,
            'bonus_max_photo'             => 1500,
            'plugin_info'                => wa()->getConfig()->getAppConfig('shop')->getPluginInfo($this->getNamePlugin()),
        );
    }
}