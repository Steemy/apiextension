<?php

/**
 * Model class shopApiextensionPluginReviewsModel
 *
 * @author Steemy, created by 25.08.2021
 */


class shopApiextensionPluginReviewsAffiliateModel extends waModel
{
    const STATE_AFFILIATE_ACTIVE = 'active';
    const STATE_AFFILIATE_COMPLETED = 'completed';
    const STATE_AFFILIATE_DELETE = 'delete';
    const STATE_AFFILIATE_EXPIRED = 'expired';

    private $settings;
    private $shopOrderItems = 'shop_order_items';

    protected $table = 'shop_apiextension_reviews_affiliate';

    public function __construct(){
        parent::__construct();
        $pluginSetting = shopApiextensionPluginSettings::getInstance();
        $this->settings = $pluginSetting->getSettings();
    }

    /**
     * Получить товары за которые можно начислить бонус за отзыв
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getProductsForReviewBonus($contactId)
    {
        // update old
        $this->updateOld();

        $sql = "SELECT i.product_id, a.affiliate, a.create_datetime FROM `{$this->shopOrderItems}` as i
                LEFT JOIN `{$this->table}` AS a ON i.order_id = a.order_id and i.product_id = a.product_id
                WHERE a.state = '".self::STATE_AFFILIATE_ACTIVE."' AND a.create_datetime >= '{$this->getDateSort()}' AND a.contact_id = ?
                ORDER BY a.create_datetime ASC";

        $products = array();
        $productsAffiliate = $this->query($sql, (int)$contactId)->fetchAll('product_id');

        // sortings
        if($productsAffiliate) {
            $collection = new shopProductsCollection(array_keys($productsAffiliate));
            $productsCollection = $collection->getProducts();
            if($productsCollection) {
                foreach($productsAffiliate as $id => $p) {
                    if(!empty($productsCollection[$id])) {
                        $productsCollection[$id]['apiextension_affiliate'] = $p['affiliate'];
                        $productsCollection[$id]['apiextension_create_datetime'] = $p['create_datetime'];
                        $productsCollection[$id]['apiextension_expires_datetime'] = strtotime("+" .$this->settings['bonus_for_review_days']. " day", strtotime($p['create_datetime']));
                        $productsCollection[$id]['apiextension_bonus_days'] = $this->settings['bonus_for_review_days'];
                        $products[] = $productsCollection[$id];
                    }
                }
            }
        }

        return $products;
    }

    /**
     * Обновить старые записи бонусов проставив статус expired
     * @throws waException
     */
    public function updateOld()
    {
        $this->query("UPDATE `$this->table` SET `state` = '".self::STATE_AFFILIATE_EXPIRED."' 
                        WHERE create_datetime < '{$this->getDateSort()}' AND state = '".self::STATE_AFFILIATE_ACTIVE."'");
    }

    /**
     * Получить записи начисления баллов за отзывы
     * @throws waException
     */
    public function getReviewsAffiliate($contactId, $productId)
    {
        return
            $this
                ->query("SELECT * FROM `$this->table`
                        WHERE contact_id = '{$contactId}' AND product_id = '{$productId}' 
                        AND (state = '".self::STATE_AFFILIATE_COMPLETED."' OR state = '".self::STATE_AFFILIATE_ACTIVE."')")
                ->fetchAll();
    }

    private function getDateSort() {
        return date('Y-m-d H:i:s', strtotime('-'.$this->settings['bonus_for_review_days'].' day'));
    }
}