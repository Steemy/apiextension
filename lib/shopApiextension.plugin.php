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
     * @param $contactId - идентификатор пользователя
     * @return bool|mixed
     * @throws waDbException
     */
    static function affiliateBonus($contactId)
    {
        $apiextensionCustomerHelper = new shopApiextensionPluginCustomerHelper();
        return $apiextensionCustomerHelper->affiliateBonus($contactId);
    }

    /**
     * Получить количество отзывов для товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     */
    static public function reviewsCount($productIds)
    {
        $apiextensionReviewsHelper = new shopApiextensionPluginReviewsHelper();
        return $apiextensionReviewsHelper->reviewsCount($productIds);
    }

    /**
     * Получить фото товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function productImages($productIds)
    {
        $apiextensionProductHelper = new shopApiextensionPluginProductHelper();
        return $apiextensionProductHelper->productImages($productIds);
    }

    /**
     * Получить товары категории
     * в фильтрации товаров участвуют все гет параметры фильтра и пагинации
     * @param $categoryId - идентификатор категории
     * @param $limit - товаров на странице
     * @return array
     * @throws waException
     */
    static function categoryProducts($categoryId, $limit=NULL)
    {
        $apiextensionCategoryHelper = new shopApiextensionPluginCategoryHelper();
        return $apiextensionCategoryHelper->categoryProducts($categoryId, $limit);
    }

    /**
     * Получить активный фильтр товаров для категории
     * @param $categoryId - идентификатор категории
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function filtersForCategory($categoryId)
    {
        $apiextensionCategoryHelper = new shopApiextensionPluginCategoryHelper();
        return $apiextensionCategoryHelper->filtersForCategory($categoryId);
    }

    /**
     * Получить голосвание клиента по отзывам
     * @param $reviewIds - список ид отзывов
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function getReviewsVote($reviewIds, $contactId = null)
    {
        $apiextensionReviewsHelper = new shopApiextensionPluginReviewsHelper();
        return $apiextensionReviewsHelper->getReviewsVote($reviewIds, $contactId);
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