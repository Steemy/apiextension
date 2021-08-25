<?php

/**
 * Helper class shopApiextensionPluginProductHelper
 *
 * @author Steemy, created by 25.08.2021
 */

class shopApiextensionPluginProductHelper
{
    /**
     * Получить фото товаров
     * @param $product_ids - список ид товаров
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function productImages($product_ids)
    {
        $productImages = array();
        $productImagesModel = new shopProductImagesModel();

        if(is_array($product_ids)) {
            $product_ids = implode(',', $product_ids);
        }

        $productImagesAll =
            $productImagesModel
                ->select('*')
                ->where('product_id IN(' . $product_ids . ')')
                ->order('sort ASC')
                ->fetchAll();

        foreach($productImagesAll as $image) {
            $productImages[$image['product_id']][$image['id']] = $image;
        }

        return $productImages;
    }
}