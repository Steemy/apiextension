<?php

/**
 * Model class shopApiextensionPluginReviewsModel
 *
 * @author Steemy, created by 25.08.2021
 */


class shopApiextensionPluginReviewsModel extends waModel
{
    const STATUS_PUBLISHED = 'approved';

    private $shop_product_reviews = 'shop_product_reviews';
    private $shop_product = 'shop_product';

    /**
     * Получить количество отзывов для товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     */
    public function reviewsCount($productIds)
    {
        $sqlCount = "SELECT product_id, COUNT(id) AS reviews_count FROM `{$this->shop_product_reviews}`
                WHERE review_id = 0 AND status = '".self::STATUS_PUBLISHED."' AND product_id IN(s:ids) GROUP BY product_id";

        $sqlImagesCount = "SELECT p.rating, r.product_id, SUM(images_count) as images_count FROM `{$this->shop_product_reviews}` as r
                LEFT JOIN `{$this->shop_product}` as p on p.id = r.product_id
                WHERE r.status = '".self::STATUS_PUBLISHED."' AND r.product_id IN(s:ids) GROUP BY r.product_id";

        $reviewsCount =
            $this->query($sqlCount, array('ids' => $productIds))->fetchAll('product_id');
        $reviewsImagesCount =
            $this->query($sqlImagesCount, array('ids' => $productIds))->fetchAll('product_id');

        foreach($reviewsCount as $id=>$r) {
            $reviewsCount[$id] = $r;
            if($reviewsImagesCount[$id]) {
                $reviewsCount[$id]['images_count'] = $reviewsImagesCount[$id]['images_count'];
                $reviewsCount[$id]['rating'] = $reviewsImagesCount[$id]['rating'];
            }
        }

        return $reviewsCount;
    }
}