<?php

/**
 * Helper class shopApiextensionPluginCustomerHelper
 *
 * @author Steemy, created by 25.08.2021
 */

class shopApiextensionPluginCustomerHelper
{
    private $apiextensionCustomerModel;

    public function __construct(){
        $this->apiextensionCustomerModel = new shopApiextensionPluginCustomerModel();
    }

    /**
     * Получить количество бонусов авторизованного пользователя
     * @param $contact_id - идентификатор пользователя
     * @return bool|mixed
     * @throws waDbException
     */
    public function affiliateBonus($contact_id)
    {
        return $this->apiextensionCustomerModel->affiliateBonus($contact_id);
    }
}