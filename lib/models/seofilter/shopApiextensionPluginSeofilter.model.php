<?php

/**
 * Model class shopApiextensionPluginSeofilterModel
 *
 * @author Steemy, created by 10.05.2025
 */

class shopApiextensionPluginSeofilterModel extends waModel {

    /**
     * Получить ids seoFilters для категории
     * @param $categoryId - id категории
     * @param $storefront - storefront
     * @param $dropOutOfStock - Hide out-of-stock products
     * @return array
     * @throws waDbException
     */
    public function getIdsSeofilter($categoryId, $storefront, $dropOutOfStock)
    {
      $USE_MODE_ALL = shopSeofilterFilter::USE_MODE_ALL;
      $USE_MODE_LISTED = shopSeofilterFilter::USE_MODE_LISTED;
      $USE_MODE_EXCEPT = shopSeofilterFilter::USE_MODE_EXCEPT;

      $queryParams = [
        'category_id' => $categoryId,
        'storefront' => $storefront,
      ];

      $stockCondition = $dropOutOfStock
        ? 'cc.have_in_stock_products = 1'
        : 'cc.have_any_products = 1';

      // ? improve
      $sql = "
SELECT cc.filter_id, f.seo_name, ffv.feature_id, ffvr.feature_id AS feature_id_range, ff_v.name AS feature_name, ff_v_r.name AS feature_name_range
FROM shop_seofilter_catalog_cache AS cc
	JOIN shop_seofilter_filter AS f
		ON f.id = cc.filter_id
  LEFT JOIN shop_seofilter_filter_feature_value AS ffv
		ON ffv.filter_id = cc.filter_id
  LEFT JOIN shop_seofilter_filter_feature_value_range AS ffvr
		ON ffvr.filter_id = cc.filter_id
  LEFT JOIN shop_feature ff_v
    ON ff_v.id = ffv.feature_id
  LEFT JOIN shop_feature ff_v_r
    ON ff_v_r.id = ffvr.feature_id
	LEFT JOIN shop_seofilter_filter_category AS fc
		ON fc.filter_id = f.id AND fc.category_id = :category_id
	LEFT JOIN shop_seofilter_filter_storefront AS fs
		ON fs.filter_id = f.id AND fs.storefront = :storefront
WHERE cc.category_id = :category_id
	AND {$stockCondition}
	AND f.is_enabled = 1
	AND (
		f.categories_use_mode = '{$USE_MODE_ALL}'
		OR (f.categories_use_mode = '{$USE_MODE_LISTED}' AND fc.category_id = :category_id)
		OR (f.categories_use_mode = '{$USE_MODE_EXCEPT}' AND fc.id IS NULL)
	)
	AND (
		f.storefronts_use_mode = '{$USE_MODE_ALL}'
		OR (f.storefronts_use_mode = '{$USE_MODE_LISTED}' AND fs.storefront = :storefront)
		OR (f.storefronts_use_mode = '{$USE_MODE_EXCEPT}' AND fs.id IS NULL)
	)
";

      return $this->query($sql, $queryParams)->fetchAll('filter_id');
    }
}
