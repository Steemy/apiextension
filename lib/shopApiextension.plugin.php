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
     * @throws waException
     */
    static function affiliateBonus($contactId=null)
    {
        $apiextensionCustomer = new shopApiextensionPluginCustomer();
        return $apiextensionCustomer->affiliateBonus($contactId);
    }

    /**
     * Получить количество отзывов для товаров
     * @param $productIds - список ид товаров
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    static public function reviewsCount($productIds)
    {
        $apiextensionReviews = new shopApiextensionPluginReviews();
        return $apiextensionReviews->reviewsCount($productIds);
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
        $apiextensionProduct = new shopApiextensionPluginProduct();
        return $apiextensionProduct->productImages($productIds);
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
        $apiextensionCategory = new shopApiextensionPluginCategory();
        return $apiextensionCategory->categoryProducts($categoryId, $limit);
    }

    /**
     * Получить активный фильтр товаров для категории
     * @param $categoryId - идентификатор категории
     * @return array
     * @throws ReflectionException
     * @throws waDbException
     * @throws waException
     */
    static function filtersForCategory($categoryId)
    {
        $apiextensionCategory = new shopApiextensionPluginCategory();
        return $apiextensionCategory->filtersForCategory($categoryId);
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
        $apiextensionReviews = new shopApiextensionPluginReviews();
        return $apiextensionReviews->getReviewsVote($reviewIds, $contactId);
    }

    /**
     * Получить товары за которые можно получить бонус за отзыв
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    static function getProductsForReviewBonus($contactId)
    {
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        return $apiextensionReviewsAffiliate->getProductsForReviewBonus($contactId);
    }

    /**
     * Получить теги товаров текущей категории
     * @param $categoryId - идентификатор категории
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    static function getTagsByCategory($categoryId)
    {
        $apiextensionCategory = new shopApiextensionPluginCategory();
        return $apiextensionCategory->getTagsByCategory($categoryId);
    }

    /**
     * HOOK frontend_review_add.before
     * @param $params
     */
    public function frontendReviewAddBefore($params)
    {
        // Добавляем поля только для отзыва и если разрешено в настройках плагина
        $apiextensionReviews = new shopApiextensionPluginReviews();
        $apiextensionReviews->addAdditionalFields($params);
    }

    /**
     * HOOK frontend_review_add.after
     */
    public function frontendReviewAddAfter($params)
    {
        // Бонус за отзыв
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        $apiextensionReviewsAffiliate->addBonusesByWritingReview($params);
    }

    /**
     * HOOK products_reviews backend
     */
    public function productsReviews($params)
    {
        // Выводим в админке поля у отзывов
        $apiextensionReviews = new shopApiextensionPluginReviews();
        return $apiextensionReviews->showAdditionalFieldsReviewBackend($params);
    }

    /**
     * HOOK order_action.complete
     */
    public function orderActionСomplete($params)
    {
        // При переводе заказа в статус выполнено, делаем запись о возможности получить бонусы за отзыв
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        $apiextensionReviewsAffiliate->addAffiliateWhenOrderComplete($params);
    }

    /**
     * HOOK order_action.refund
     */
    public function orderActionRefund($params)
    {
        // При возврате заказа, списываем бонусы у клиента
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        $apiextensionReviewsAffiliate->cancelAffiliateWhenOrderRefund($params);
    }

    /**
     * HOOK controller_after.shopMarketingPromoRuleEditorAction
     */
    public function controllerAfterShopMarketingPromoRuleEditorAction(&$params)
    {
        // Выводим дополнительные поля в маркетинге промо у баннера
        $apiextensionMarketingPromoRule = new shopApiextensionPluginMarketingPromoRule();
        $apiextensionMarketingPromoRule->showAdditionalFieldsPromoBannerBackend();
    }

    /**
     * HOOK controller_after.shopReviewsChangeStatusController
     */
    public function controllerAfterShopReviewsChangeStatusController(&$params)
    {
        // Изменение бонусов за отзывы при модерации в бекенде
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        $apiextensionReviewsAffiliate->addAffiliateWhenModerationBackend();

        // Удаление отзыва
        $apiextensionReviews = new shopApiextensionPluginReviews();
        $apiextensionReviews->removeReview();
    }
}