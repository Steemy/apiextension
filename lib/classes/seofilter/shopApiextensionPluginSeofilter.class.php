<?php

/**
 * Helper class shopApiextensionPluginSeofilter
 *
 * @author Steemy, created by 10.05.2025
 */

class shopApiextensionPluginSeofilter
{

  /**
   * Получить seoFilters для категории
   * @param $categoryId - id категории
   * @param bool $absolute
   * @return array
   * @throws waException
   */
  public function getSeofilterForCategory($categoryId, $absolute = false)
  {
    if (!$categoryId) return array();
    if (!class_exists('shopSeofilterViewHelper')) return array();

    $routes = wa()->getRouting()->getByApp('shop');
    foreach ($routes as $routeDomain => $domainRoutes) {
      if (count($domainRoutes)) {
        $domain = $routeDomain;
        $route = reset($domainRoutes);
        break;
      }
    }

    $settings = shopSeofilterBasicSettingsModel::getSettings();
    $filterUrl = new shopSeofilterFilterUrl($settings->url_type, ifset($route['url_type'], 0));

    $categoryModel = new shopCategoryModel();
    $categories = $categoryModel->getAll('id');
    $storefront = $domain . '/' . $route['url'];


    $shopSeofilterFilter = new shopSeofilterFilter();
    $shopApiextensionPluginSeofilterModel = new shopApiextensionPluginSeofilterModel();
    $idsSeofilter = $shopApiextensionPluginSeofilterModel->getIdsSeofilter($categoryId, $storefront, ifset($route['drop_out_of_stock']) == 2);

    $filters = array();
    $categoryFilters = array();
    $category = ifset($categories[$categoryId]);

    if ($category) {
      foreach ($idsSeofilter as $id => $f) {
        $filter = ifset($filters[$id]);
        if ($filter === false) {
          continue;
        }

        if ($filter === null) {
          $filter = $shopSeofilterFilter->getById($id);
          if (!$filter) {
            $filters[$id] = false;
            continue;
          }

          $filters[$id] = $filter;
        }

        if ($f['feature_id']) {
          $categoryFilters[$f['feature_id']]['filter_id'] = $f['filter_id'];
          $categoryFilters[$f['feature_id']]['feature_id'] = $f['feature_id'];
          $categoryFilters[$f['feature_id']]['feature_name'] = $f['feature_name'];

          $categoryFilters[$f['feature_id']]['seo_filters'][] = [
            'seo_name' => $f['seo_name'],
            'full_url' => $filterUrl->getFrontendPageUrl($category, $filter, $absolute, $domain, $route['url']),
          ];
        } elseif ($f['feature_id_range']) {
          $categoryFilters[$f['feature_id_range']]['filter_id'] = $f['filter_id'];
          $categoryFilters[$f['feature_id_range']]['feature_id'] = $f['feature_id_range'];
          $categoryFilters[$f['feature_id_range']]['feature_name'] = $f['feature_name_range'];

          $categoryFilters[$f['feature_id_range']]['seo_filters'][] = [
            'seo_name' => $f['seo_name'],
            'full_url' => $filterUrl->getFrontendPageUrl($category, $filter, $absolute, $domain, $route['url']),
          ];
        }
      }
    }

    return $categoryFilters;
  }
}