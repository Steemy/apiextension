<?php

/**
 * Helper class shopApiextensionPluginCategory
 *
 * @author Steemy, created by 25.08.2021
 */

class shopApiextensionPluginCategory
{
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
        if(!$categoryId) return array();

        $collection = new shopProductsCollection('category/'.$categoryId);
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
     * @param $categoryId - идентификатор категории
     * @return array
     * @throws ReflectionException
     * @throws waDbException
     * @throws waException
     */
    public function filtersForCategory($categoryId)
    {
        if(!$categoryId) return array();

        $category_result = array();

        $category_model = new shopCategoryModel();
        $category = $category_model->getById($categoryId);

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
        $filter_data = waRequest::get();
        $filters = array();
        $feature_map = array();

        if ($category['filter'] || !empty($category['smartfilters'])) {
            // smartfilters
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

            $collection = new shopProductsCollection('category/' . $categoryId);
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

                    //set existing feature code with saved filter id
                    $feature_map[$features[$fid]['code']] = $fid;

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

        if ($category['type'] == shopCategoryModel::TYPE_DYNAMIC) {

            $conditions = shopProductsCollection::parseConditions($category['conditions']);

            foreach ($conditions as $field => $field_conditions) {
                switch ($field) {
                    case 'price':
                        foreach ($field_conditions as $condition) {
                            $type = reset($condition);
                            switch ($type) {
                                case '>=':
                                    $min = shop_currency(doubleval(end($condition)), null, null, false);

                                    if (empty($filter_data['price_min'])) {
                                        $filter_data['price_min'] = $min;
                                    } else {
                                        $filter_data['price_min'] = max($min, $filter_data['price_min']);
                                    }

                                    if (isset($filters['price']['min'])) {
                                        $filters['price']['min'] = max($filter_data['price_min'], $filters['price']['min']);
                                    }
                                    break;
                                case '<=':
                                    $max = shop_currency(doubleval(end($condition)), null, null, false);
                                    if (empty($filter_data['price_max'])) {
                                        $filter_data['price_max'] = $max;
                                    } else {
                                        $filter_data['price_max'] = min($max, $filter_data['price_max']);
                                    }
                                    if (isset($filters['price']['max'])) {
                                        $filters['price']['max'] = min($filter_data['price_max'], $filters['price']['max']);
                                    }
                                    break;

                            }
                        }

                        break;
                    case 'count':
                        /**
                         * count = {array} [2]
                         * 0 = ">="
                         * 1 = ""
                         */
                        break;
                    case 'rating':
                    case 'compare_price':
                    case 'tag':
                        break;
                    default:
                        if (preg_match('@(\w+)\.(value_id)$@', $field, $matches)) {
                            $feature_code = $matches[1];
                            $first_condition = reset($field_conditions);

                            //If first condition is array that is range. Not need this magic (May be) See below comment)
                            if (!is_array($first_condition)) {
                                $value_id = array_map('intval', preg_split('@[,\s]+@', end($field_conditions)));

                                $feature_id = ifset($feature_map, $feature_code, $feature_code);

                                if (empty($filter_data[$feature_code])) {
                                    $filter_data[$feature_code] = $value_id;
                                }

                                //If you understand what this block does write a comment please.
                                if (!empty($filters[$feature_id]['values'])) {
                                    foreach ($filters[$feature_id]['values'] as $_value_id => $_value) {
                                        if (!in_array($_value_id, $value_id)) {
                                            unset($filters[$feature_id]['values'][$_value_id]);
                                        }
                                    }
                                }
                            }
                        }
                        break;
                }
            }
        }

        // myLang
        // пока тестовом режиме, надо править код в приложении myLang
        // mylangShopFrontend_categoryHandler#filters - исправить модификатор private to public
        if (class_exists('mylangShopFrontend_categoryHandler')) {
            $myLang = new mylangShopFrontend_categoryHandler();
            $reflection = new ReflectionMethod($myLang, 'filters');
            if($reflection->isPublic()) {
                $myLang->filters($filters);
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