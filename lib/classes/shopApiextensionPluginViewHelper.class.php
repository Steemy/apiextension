<?php

/**
 * Plugin helper
 *
 * @author Steemy, created by 10.02.2025
 */

class shopApiextensionPluginViewHelper extends waPluginViewHelper
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
      $shopApiextensionPluginSeofilter = new shopApiextensionPluginSeofilter();
      return $shopApiextensionPluginSeofilter->getSeofilterForCategory($categoryId, $absolute);
    }

}