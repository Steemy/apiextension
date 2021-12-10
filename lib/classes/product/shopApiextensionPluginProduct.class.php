<?php

/**
 * Helper class shopApiextensionPluginProduct
 *
 * @author Steemy, created by 25.08.2021
 */

class shopApiextensionPluginProduct
{
    /**
     * Получить фото товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function productImages($productIds)
    {
        if(!$productIds) return array();

        if(is_array($productIds)) {
            $productIdsString = implode(',', $productIds);
        } else {
            $productIdsString = $productIds;
        }

        if ($cache = wa('shop')->getCache()) {
            $productImages = $cache->get('apiextension_product_images_' . $productIdsString);
            if ($productImages !== null) {
                return $productImages;
            }
        }

        $productImages = array();
        $productImagesModel = new shopProductImagesModel();

        if(!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        $productImagesAll = $productImagesModel->getByField('product_id', $productIds, true);
        foreach($productImagesAll as $image) {
            $productImages[$image['product_id']][$image['id']] = $image;
        }

        if (!empty($cache) && $productIdsString) {
            $cache->set('apiextension_product_images_' . $productIdsString, $productImages, 7200);
        }

        return $productImages;
    }
}