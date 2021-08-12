<?php

/**
 * Plugin
 *
 * @author Steemy, created by 21.07.2021
 */

class shopApiextensionPlugin extends shopPlugin
{
    /**
     * Получить количество бонусов авторизованного пользователя
     * @param $contact_id - идентификатор пользователя
     * @return bool|mixed
     */
    static function affiliateBonus($contact_id)
    {
        $apiextensionHelper = new shopApiextensionPluginHelper();
        return $apiextensionHelper->affiliateBonus($contact_id);
    }

    /**
     * Получить количество отзывов для товаров
     * @param $product_ids - список ид товаров
     * @return array
     */
    static public function reviewsCount($product_ids)
    {
        $apiextensionHelper = new shopApiextensionPluginHelper();
        return $apiextensionHelper->reviewsCount($product_ids);
    }

    /**
     * Получить товары категории
     * в фильтрации товаров участвуют все гет параметры фильтра и пагинации
     * @param $category_id - идентификатор категории
     * @param $limit - товаров на странице
     * @return array
     * @throws waException
     */
    static function categoryProducts($category_id, $limit=NULL)
    {
        $apiextensionHelper = new shopApiextensionPluginHelper();
        return $apiextensionHelper->categoryProducts($category_id, $limit);
    }

    /**
     * Получить фото товаров
     * @param $product_ids - список ид товаров
     * @return array
     */
    static function productImages($product_ids)
    {
        $apiextensionHelper = new shopApiextensionPluginHelper();
        return $apiextensionHelper->productImages($product_ids);
    }

    /**
     * Получить активный фильтр товаров для категории
     * @param $category_id - идентификатор категории
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function filtersForCategory($category_id)
    {
        $apiextensionHelper = new shopApiextensionPluginHelper();
        return $apiextensionHelper->filtersForCategory($category_id);
    }
}