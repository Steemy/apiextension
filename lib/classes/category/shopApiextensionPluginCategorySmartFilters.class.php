<?php

/**
 * Helper class shopApiextensionPluginCategorySmartFilters
 *
 * @author Steemy, created by 08.09.2025
 */

class shopApiextensionPluginCategorySmartFilters
{
  private $filters = array();
  private $productIds = array();
  private $featureModel;

  /**
   * @throws waException
   */
  public function getSmartFilters($categoryId, $filters, $requestData)
  {
    if (!$categoryId || !$filters) {
      return array();
    }

    $this->initialize($categoryId, $filters);
    if (empty($requestData) || empty($this->productIds)) {
      return array();
    }

    return $this->processFilters($requestData);
  }

//  public function getSmartFilters2($categoryId, $filters)
//  {
//    if (!$categoryId || !$filters) {
//      return array();
//    }
//
//    $this->initialize($categoryId, $filters);
//
//    $requestData = waRequest::get();
//
//    if (empty($requestData) || empty($this->productIds)) {
//      return array();
//    }
//
//    return $this->processFilters($requestData);
//  }

  /**
   * Initialize class properties
   *
   * @param int $categoryId
   * @param array $filters
   * @throws waException
   */
  private function initialize($categoryId, $filters)
  {
    $this->featureModel = new shopFeatureModel();
    $this->processFilterConfiguration($filters);
    $this->loadProductIdsForCategory($categoryId);
  }

  /**
   * Process filter configuration
   *
   * @param array $filters
   */
  private function processFilterConfiguration($filters)
  {
    foreach ($filters as $id => $filter) {
      if ($id === 'price') {
        continue;
      }
      if (isset($filter['code'])) {
        $this->filters[$filter['code']] = $filter;
      }
    }
  }

  /**
   * Load product IDs for the category
   *
   * @param int $categoryId
   * @throws waException
   */
  private function loadProductIdsForCategory($categoryId)
  {
    $categoryId = (int)$categoryId;
    $categoryModel = new shopCategoryModel();
    $category = $categoryModel->getById($categoryId);

    if (!$category) {
      $this->productIds = array();
      return;
    }

    // Динамическая категория — только через коллекцию (без лимита)
    if ((int)$category['type'] !== 0) {
      $collection = new shopProductsCollection('category/' . $categoryId);
      $count = $collection->count();
      $products = $count ? $collection->getProducts('id', 0, $count) : array();
      $this->productIds = array_keys($products);
      return;
    }

    // Статическая категория (+ подкатегории, если включено «показывать товары подкатегорий»)
    $categoryIds = array($categoryId);
    if (!empty($category['include_sub_categories'])) {
      $sub = $categoryModel->query(
    "SELECT id FROM shop_category WHERE left_key >= :l AND right_key <= :r AND type = 0",
        array('l' => $category['left_key'], 'r' => $category['right_key'])
      )->fetchAll(null, true);

      if ($sub) {
        $categoryIds = array_map('intval', $sub);
      }
    }

    $ids = $categoryModel->query(
  "SELECT DISTINCT cp.product_id FROM shop_category_products cp
      JOIN shop_product tsp ON tsp.id = cp.product_id
      WHERE cp.category_id IN (:ids) AND tsp.status = 1",
      array('ids' => $categoryIds)
    )->fetchAll(null, true);

    $this->productIds = array_map('intval', $ids);
  }

  /**
   * Process filters and determine disabled status
   *
   * @param array $requestData
   * @return array
   * @throws waException
   */
  private function processFilters($requestData)
  {
    $processedFilters = array();

    foreach ($this->filters as $code => $filter) {
      $disabledValues = $this->getDisabledFilterValues($code, $requestData);
      $filter['disabled'] = array_fill_keys(array_keys($filter['values']), false);

      if ($disabledValues) {
        foreach ($disabledValues as $valueId) {
          if (isset($filter['disabled'][$valueId])) {
            $filter['disabled'][$valueId] = true;
          }
        }
      }

      $processedFilters[$code] = $filter;
    }

    return $processedFilters;
  }

  /**
   * Get disabled filter values for a specific filter code
   *
   * @param string $code
   * @param array $data
   * @return array|bool
   * @throws waException
   */
  private function getDisabledFilterValues($code, $data)
  {
    // Remove system parameters
    $filteredData = $this->removeSystemParameters($data, $code);

    if (empty($filteredData)) {
      return array();
    }

    list($whereConditions, $joinConditions, $aliasCount) = $this->buildQueryConditions($filteredData);

    if ($aliasCount === 0) {
      return array();
    }

    $productIds = $this->executeProductQuery($whereConditions, $joinConditions);

    if (empty($productIds)) {
      return array_keys($this->filters[$code]['values']);
    }

    $usedValues = $this->getUsedFeatureValues($productIds, $code);

    return array_diff(array_keys($this->filters[$code]['values']), array_keys($usedValues));
  }

  /**
   * Remove system parameters from request data
   *
   * @param array $data
   * @param string $currentFilterCode
   * @return array
   */
  private function removeSystemParameters($data, $currentFilterCode)
  {
    $systemParams = ['page', 'sort', 'order', $currentFilterCode];

    foreach ($systemParams as $param) {
      unset($data[$param]);
    }

    return $data;
  }

  /**
   * Build SQL query conditions based on filter data
   *
   * @param array $data
   * @return array
   */
  private function buildQueryConditions($data)
  {
    $whereConditions = array();
    $joinConditions = array();
    $aliasCounter = 0;

    // Process price filters
    $whereConditions = array_merge($whereConditions, $this->processPriceFilters($data));

    // Process feature filters
    foreach ($data as $featureCode => $values) {
      if (!isset($this->filters[$featureCode])) {
        continue;
      }

      $processedValues = $this->processFeatureValues($featureCode, $values);

      if (!empty($processedValues)) {
        list($join, $where) = $this->buildFeatureCondition($featureCode, $processedValues, $aliasCounter++);
        $joinConditions[] = $join;
        $whereConditions[] = $where;
      }
    }

    return [$whereConditions, $joinConditions, $aliasCounter];
  }

  /**
   * Process price filters
   *
   * @param array $data
   * @return array
   */
  private function processPriceFilters(&$data)
  {
    $conditions = array();

    $priceMin = $data['price_min'] ?? null;
    $priceMax = $data['price_max'] ?? null;

    if ($priceMin !== null && $priceMin !== '') {
      $conditions[] = 'tsp.price >= ' . (int)$priceMin;
    }

    if ($priceMax !== null && $priceMax !== '') {
      $conditions[] = 'tsp.price <= ' . (int)$priceMax;
    }

    unset($data['price_min'], $data['price_max']);

    return $conditions;
  }

  /**
   * Process feature values
   *
   * @param string $featureCode
   * @param mixed $values
   * @return array
   */
  private function processFeatureValues($featureCode, $values)
  {
    $values = is_array($values) ? $values : array($values);
    $filterConfig = $this->filters[$featureCode];

    if ($this->isRangeFilter($values)) {
      return $this->processRangeFilter($values, $filterConfig);
    }

    return array_map('intval', $values);
  }

  /**
   * Check if values represent a range filter
   *
   * @param array $values
   * @return bool
   */
  private function isRangeFilter($values)
  {
    return isset($values['min']) || isset($values['max']) || isset($values['unit']);
  }

  /**
   * Process range filter values
   *
   * @param array $values
   * @param array $filterConfig
   * @return array
   */
  private function processRangeFilter($values, $filterConfig)
  {
    $min = $values['min'] ?? '';
    $max = $values['max'] ?? '';

    if ($min === '' && $max === '') {
      return array();
    }

    $unit = $values['unit'] ?? null;
    $processedMin = $this->processRangeValue($min, $filterConfig['type'], $unit);
    $processedMax = $this->processRangeValue($max, $filterConfig['type'], $unit);

    $valueModel = $this->featureModel->getValuesModel($filterConfig['type']);

    return $valueModel->getValueIdsByRange($filterConfig['id'], $processedMin, $processedMax);
  }

  /**
   * Process individual range value
   *
   * @param mixed $value
   * @param string $type
   * @param string|null $unit
   * @return mixed
   */
  private function processRangeValue($value, $type, $unit)
  {
    if ($value === null || $value === '') {
      return null;
    }

    $processedValue = str_replace(',', '.', $value);

    if ($unit) {
      $processedValue = shopDimension::getInstance()->convert($processedValue, $type, null, $unit);
    } elseif ($type === 'range.date') {
      $processedValue = shopDateValue::dateToTimestamp($processedValue);
    }

    return $processedValue;
  }

  /**
   * Build feature condition for SQL query
   *
   * @param string $featureCode
   * @param array $values
   * @param int $aliasIndex
   * @return array
   */
  private function buildFeatureCondition($featureCode, $values, $aliasIndex)
  {
    $alias = 'tspf' . $aliasIndex;
    $filterConfig = $this->filters[$featureCode];

    $join = sprintf(
      "JOIN shop_product_features %s ON tsp.id = %s.product_id AND %s.feature_id = %d",
      $alias,
      $alias,
      $alias,
      (int)$filterConfig['id']
    );

    $where = sprintf("%s.feature_value_id IN (%s)", $alias, implode(',', array_map('intval', $values)));

    return [$join, $where];
  }

  /**
   * Execute product query
   *
   * @param array $whereConditions
   * @param array $joinConditions
   * @return array
   */
  private function executeProductQuery($whereConditions, $joinConditions)
  {
    $whereConditions[] = 'tsp.id IN (:product_ids)';

    $sql = sprintf(
      "SELECT DISTINCT tsp.id FROM shop_product tsp %s WHERE %s",
      implode(' ', $joinConditions),
      implode(' AND ', $whereConditions)
    );

    $result = $this->featureModel->query($sql, ['product_ids' => $this->productIds]);

    return $result->fetchAll(null, true);
  }

  /**
   * Get used feature values for products
   *
   * @param array $productIds
   * @param string $featureCode
   * @return array
   */
  private function getUsedFeatureValues($productIds, $featureCode)
  {
    $sql = "SELECT DISTINCT product_id, feature_value_id FROM shop_product_features 
            WHERE product_id IN (:product_ids) AND feature_id = :feature_id";

    $result = $this->featureModel->query($sql, [
      'product_ids' => $productIds,
      'feature_id' => (int)$this->filters[$featureCode]['id']
    ]);

    return $result->fetchAll('feature_value_id', true);
  }
}