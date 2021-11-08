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

        $productImages = array();
        $productImagesModel = new shopProductImagesModel();

        if(!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        $productImagesAll = $productImagesModel->getByField('product_id', $productIds, true);
        foreach($productImagesAll as $image) {
            $productImages[$image['product_id']][$image['id']] = $image;
        }

        return $productImages;
    }
}