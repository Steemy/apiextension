<?php

/**
 * Plugin helper
 *
 * @author Steemy, created by 30.11.2021
 */

class shopApiextensionPluginViewHelper extends waPluginViewHelper
{

    /**
     * Получить количество бонусов авторизованного пользователя
     * @param $contactId - идентификатор пользователя
     * @return bool|mixed
     * @throws waDbException
     * @throws waException
     */
    public function affiliateBonus($contactId=null)
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
    public function reviewsCount($productIds)
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
    public function productImages($productIds)
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
    public function categoryProducts($categoryId, $limit=NULL)
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
    public function filtersForCategory($categoryId)
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
    public function getReviewsVote($reviewIds, $contactId = null)
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
    public function getProductsForReviewBonus($contactId)
    {
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        return $apiextensionReviewsAffiliate->getProductsForReviewBonus($contactId);
    }

    /**
     * Получить количество отзывов для товаров
     * @param $product - информаци о товаре, массив
     */
    public function getBonusReviewForProduct($product=null)
    {
        $apiextensionReviewsAffiliate = new shopApiextensionPluginReviewsAffiliate();
        return $apiextensionReviewsAffiliate->getBonusReviewForProduct($product);
    }

    /**
     * Получить теги товаров текущей категории
     * @param $categoryId - идентификатор категории
     * @return array|mixed
     * @throws waDbException
     * @throws waException
     */
    public function getTagsByCategory($categoryId)
    {
        $apiextensionCategory = new shopApiextensionPluginCategory();
        return $apiextensionCategory->getTagsByCategory($categoryId);
    }

    /**
     * Пагинация без ссылок
     * @param $params (array ["total" => $pages_count, "attrs" =>["class" => "pagin"]])
     * @return string
     * @throws waException
     */
    public function pagination($params)
    {
        $apiextensionHelpersPagin = new shopApiextensionPluginHelpersPagin();
        return $apiextensionHelpersPagin->pagination($params);
    }

    /**
     * Настройки темы
     * @param $theme_id
     * @param string $app
     * @param false $values_only
     * @return array|mixed
     * @throws waException
     */
    public function getThemeSettings($theme_id, $app = "site", $values_only = false)
    {
        $theme = new waTheme($theme_id, $app);
        $settings = $theme->getSettings($values_only);
        if ($theme->parent_theme) {
            $parent_settings = $theme->parent_theme->getSettings($values_only);
            if ($parent_settings) {
                if (!$values_only) {
                    foreach ($parent_settings as $key => $v) {
                        $parent_settings[$key]['parent'] = 1;
                    }
                }
                $settings = $settings + $parent_settings;
            }
        }

        return $settings;
    }

    /**
     * Товары из промо маркетинга
     * @param $promo_id
     * @return array|mixed
     * @throws waException
     */
    public function getProductFromPromos($promo_id)
    {
        $apiextensionPromos = new shopApiextensionPluginPromos();
        return $apiextensionPromos->getProductFromPromos($promo_id);
    }

    /**
     * Фильтр для поиска
     * @param string $featuresIds
     * @return array|mixed
     * @throws waException
     */
    public function getSearchFilters($featuresIds)
    {
        $apiextensionSearch = new shopApiextensionPluginSearch();
        return $apiextensionSearch->getSearchFilters($featuresIds);
    }
}
