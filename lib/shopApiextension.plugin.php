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
     * @throws waDbException
     */
    static function affiliateBonus($contact_id)
    {
        $apiextensionCustomerHelper = new shopApiextensionPluginCustomerHelper();
        return $apiextensionCustomerHelper->affiliateBonus($contact_id);
    }

    /**
     * Получить количество отзывов для товаров
     * @param $product_ids - список ид товаров
     * @return array
     * @throws waDbException
     */
    static public function reviewsCount($product_ids)
    {
        $apiextensionReviewsHelper = new shopApiextensionPluginReviewsHelper();
        return $apiextensionReviewsHelper->reviewsCount($product_ids);
    }

    /**
     * Получить фото товаров
     * @param $product_ids - список ид товаров
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function productImages($product_ids)
    {
        $apiextensionProductHelper = new shopApiextensionPluginProductHelper();
        return $apiextensionProductHelper->productImages($product_ids);
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
        $apiextensionCategoryHelper = new shopApiextensionPluginCategoryHelper();
        return $apiextensionCategoryHelper->categoryProducts($category_id, $limit);
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
        $apiextensionCategoryHelper = new shopApiextensionPluginCategoryHelper();
        return $apiextensionCategoryHelper->filtersForCategory($category_id);
    }

    /**
     * HOOK frontend_review_add.after
     * @param $params
     * @throws waDbException
     * @throws waException
     */
    public function frontendReviewAddAfter($params)
    {
        /**
         * Добавляем поля только для отзыва и если разрешено в настройках плагина
         */
        if($this->getSettings('review_fields') && $params['data']['parent_id'] == 0) {
            $apiextensionReviewsHelper = new shopApiextensionPluginReviewsHelper();
            $apiextensionReviewsHelper->addAdditionalFieldsReview($params['id']);
        }
    }

    /**
     * HOOK products_reviews
     */
    public function productsReviews($params)
    {
        /**
         * Выводим в админке поля у отзывов
         */
        if ($this->getSettings('review_fields') && $params['reviews']) {
            $apiextensionReviewsHelper = new shopApiextensionPluginReviewsHelper();
            $apiextensionReviewsHelper->showAdditionalFieldsReviewBackend($params['reviews']);
        }
    }

    /**
     * HOOK controller_after.shopMarketingPromoRuleEditorAction
     */
    public function controllerAfterShopMarketingPromoRuleEditorAction(&$params)
    {
        /**
         * Выводим дополнительные поля в маркетинге промо у баннера
         */
        $ruleType = waRequest::post('rule_type', null, waRequest::TYPE_STRING_TRIM);
        if($ruleType == 'banner') {
            $apiextensionMarketingPromoRuleHelper = new shopApiextensionPluginMarketingPromoRuleHelper();
            $apiextensionMarketingPromoRuleHelper->showAdditionalFieldsPromoBannerBackend();
        }
    }
}