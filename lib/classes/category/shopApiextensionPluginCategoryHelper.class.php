<?php

/**
 * Helper class shopApiextensionPluginCategoryHelper
 *
 * @author Steemy, created by 25.08.2021
 */

class shopApiextensionPluginCategoryHelper
{
    /**
     * Получить товары категории
     * в фильтрации товаров участвуют все гет параметры фильтра и пагинации
     * @param $category_id - идентификатор категории
     * @param $limit - товаров на странице
     * @return array
     * @throws waException
     */
    public function categoryProducts($category_id, $limit=NULL)
    {
        $collection = new shopProductsCollection('category/'.$category_id);
        $collection->filters(waRequest::get());

        $limit = (int)waRequest::cookie('products_page_count', $limit, waRequest::TYPE_INT);
        if (!$limit || $limit < 0 || $limit > 500) {
            $limit = $this->getConfig()->getOption('products_per_page');
        }

        $page = waRequest::get('page', 1, waRequest::TYPE_INT);

        if ($page < 1) {
            $page = 1;
        }
        $offset = ($page - 1) * $limit;

        $collection->setOptions(array(
            'overwrite_product_prices' => true,
        ));

        $products = $collection->getProducts('*,skus_filtered,skus_image', $offset, $limit);

        $count = $collection->count();
        $pages_count = ceil((float)$count / $limit);

        return array(
            'products'  => $products,
            'products_count'  => $count,
            'pages_count'  => $pages_count,
        );
    }

    /**
     * Получить активный фильтр товаров для категории
     * @param $category_id - идентификатор категории
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function filtersForCategory($category_id)
    {
        $category_result = array();

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($category_id);

        $category['frontend_url'] = wa()->getRouteUrl('shop/frontend/category', [
            'category_url' => $category['full_url'],
        ], false);

        // params for category and subcategories
        $category['params'] = array();
        $category_params_model = new shopCategoryParamsModel();
        $rows = $category_params_model->getByField('category_id', array_keys(array($category['id'] => 1)), true);
        foreach ($rows as $row) {
            if ($row['category_id'] == $category['id']) {
                $category['params'][$row['name']] = $row['value'];
            }
        }

        $category_result['category'] = $category;
        $filters = array();

        if ($category['filter'] || !empty($category['smartfilters'])) {
            if(!empty($category['smartfilters'])) {
                $filter_ids = explode(',', $category['smartfilters']);
                $filter_names = explode(',', $category['smartfilters_name']);
            } else {
                $filter_ids = explode(',', $category['filter']);
            }

            $feature_model = new shopFeatureModel();
            $features = $feature_model->getById(array_filter($filter_ids, 'is_numeric'));
            if ($features) {
                $features = $feature_model->getValues($features);
            }

            $collection = new shopProductsCollection('category/' . $category_id);
            $category_value_ids = $collection->getFeatureValueIds(false);

            foreach ($filter_ids as $k => $fid) {
                if ($fid == 'price') {
                    $range = $collection->getPriceRange();
                    if ($range['min'] != $range['max']) {
                        $filters['price'] = array(
                            'min' => shop_currency($range['min'], null, null, false),
                            'max' => shop_currency($range['max'], null, null, false),
                        );
                    }
                } elseif (isset($features[$fid]) && isset($category_value_ids[$fid])) {
                    if(!empty($filter_names[$k])) {
                        $features[$fid]['name'] = $filter_names[$k];
                    }
                    //set feature data
                    $filters[$fid] = $features[$fid];

                    $min = $max = $unit = null;

                    foreach ($filters[$fid]['values'] as $v_id => $v) {

                        //remove unused
                        if (!in_array($v_id, $category_value_ids[$fid])) {
                            unset($filters[$fid]['values'][$v_id]);
                        } else {
                            if ($v instanceof shopRangeValue) {
                                $begin = $this->getFeatureValue($v->begin);
                                if (is_numeric($begin) && ($min === null || (float)$begin < (float)$min)) {
                                    $min = $begin;
                                }
                                $end = $this->getFeatureValue($v->end);
                                if (is_numeric($end) && ($max === null || (float)$end > (float)$max)) {
                                    $max = $end;
                                    if ($v->end instanceof shopDimensionValue) {
                                        $unit = $v->end->unit;
                                    }
                                }
                            } else {
                                $tmp_v = $this->getFeatureValue($v);
                                if ($min === null || $tmp_v < $min) {
                                    $min = $tmp_v;
                                }
                                if ($max === null || $tmp_v > $max) {
                                    $max = $tmp_v;
                                    if ($v instanceof shopDimensionValue) {
                                        $unit = $v->unit;
                                    }
                                }
                            }
                        }
                    }
                    if (!$filters[$fid]['selectable'] && ($filters[$fid]['type'] == 'double' ||
                            substr($filters[$fid]['type'], 0, 6) == 'range.' ||
                            substr($filters[$fid]['type'], 0, 10) == 'dimension.')
                    ) {
                        if ($min == $max) {
                            unset($filters[$fid]);
                        } else {
                            $type = preg_replace('/^[^\.]*\./', '', $filters[$fid]['type']);
                            if ($type == 'date') {
                                $min = shopDateValue::timestampToDate($min);
                                $max = shopDateValue::timestampToDate($max);
                            } elseif ($type != 'double') {
                                $filters[$fid]['base_unit'] = shopDimension::getBaseUnit($type);
                                $filters[$fid]['unit'] = shopDimension::getUnit($type, $unit);
                                if ($filters[$fid]['base_unit']['value'] != $filters[$fid]['unit']['value']) {
                                    $dimension = shopDimension::getInstance();
                                    $min = $dimension->convert($min, $type, $filters[$fid]['unit']['value']);
                                    $max = $dimension->convert($max, $type, $filters[$fid]['unit']['value']);
                                }
                            }
                            $filters[$fid]['min'] = $min;
                            $filters[$fid]['max'] = $max;
                        }
                    }
                }
            }
        }

        $category_result['filters'] = $filters;

        return $category_result;
    }

    /**
     * @param shopDimensionValue|double $v
     * @return double
     */
    private function getFeatureValue($v)
    {
        if ($v instanceof shopDimensionValue) {
            return $v->value_base_unit;
        } elseif ($v instanceof shopDateValue) {
            return $v->timestamp;
        }
        if (is_object($v)) {
            return $v->value;
        }
        return $v;
    }

    /**
     * @return SystemConfig|waAppConfig
     * @throws waException
     */
    private function getConfig()
    {
        return waSystem::getInstance()->getConfig();
    }
}