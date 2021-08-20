<?php

/**
 * Model class
 *
 * @author Steemy, created by 21.07.2021
 */


class shopApiextensionPluginModel extends waModel
{
    const STATUS_PUBLISHED = 'approved';

    private $shop_product_reviews = 'shop_product_reviews';
    private $shop_customer = 'shop_customer';
    private $shop_product = 'shop_product';

    /**
     * Получить количество отзывов для товаров
     * @param $product_ids - список ид товаров
     * @return array
     * @throws waDbException
     */
    public function reviewsCount($product_ids)
    {
        $sqlCount = "SELECT product_id, COUNT(id) AS reviews_count FROM `{$this->shop_product_reviews}`
                WHERE review_id = 0 AND status = '".self::STATUS_PUBLISHED."' AND product_id IN(s:ids) GROUP BY product_id";

        $sqlImagesCount = "SELECT p.rating, r.product_id, SUM(images_count) as images_count FROM `{$this->shop_product_reviews}` as r
                LEFT JOIN `{$this->shop_product}` as p on p.id = r.product_id
                WHERE r.status = '".self::STATUS_PUBLISHED."' AND r.product_id IN(s:ids) GROUP BY r.product_id";

        $reviewsCount =
            $this->query($sqlCount, array('ids' => $product_ids))->fetchAll('product_id');
        $reviewsImagesCount =
            $this->query($sqlImagesCount, array('ids' => $product_ids))->fetchAll('product_id');

        foreach($reviewsCount as $id=>$r) {
            $reviewsCount[$id] = $r;
            if($reviewsImagesCount[$id]) {
                $reviewsCount[$id]['images_count'] = $reviewsImagesCount[$id]['images_count'];
                $reviewsCount[$id]['rating'] = $reviewsImagesCount[$id]['rating'];
            }
        }

        return $reviewsCount;
    }

    /**
     * Получить количество бонусов авторизованного пользователя
     * @param $contact_id - идентификатор пользователя
     * @return bool|mixed
     * @throws waDbException
     */
    public function affiliateBonus($contact_id)
    {
        $sql = "SELECT affiliate_bonus FROM `{$this->shop_customer}` WHERE contact_id=?";
        return $this->query($sql, (int)$contact_id)->fetchField();
    }
}
