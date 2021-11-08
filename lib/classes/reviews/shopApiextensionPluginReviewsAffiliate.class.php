<?php

/**
 * Affiliate for review
 *
 * @author Steemy, created by 01.11.2021
 */

class shopApiextensionPluginReviewsAffiliate
{
    private $reviewsAffiliateModel;
    private $settings;

    public function __construct(){
        $this->reviewsAffiliateModel = new shopApiextensionPluginReviewsAffiliateModel();
        $pluginSetting = shopApiextensionPluginSettings::getInstance();
        $this->settings = $pluginSetting->getSettings();
    }

    /**
     * Получить товары за которые можно получить бонус за отзыв
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getProductsForReviewBonus($contactId)
    {
        if(!wa()->getAuth()->isAuth() || !$this->settings['bonus_for_review_status']) return array();

        if(!$contactId) {
            $contactId = wa()->getUser()->getId();
        }

        return $this->reviewsAffiliateModel->getProductsForReviewBonus($contactId);
    }

    /**
     * Начислить бонусы в момент написания отзыв
     * @param $params
     * @throws waDbException
     * @throws waException
     */
    public function addBonusesByWritingReview($params)
    {
        if($this->settings['bonus_for_review_status'] && $params['data']['parent_id'] == 0) {
            // update old
            $this->reviewsAffiliateModel->updateOld();

            // update bonuses
            // проверяем есть ли записи в таблице активные для данного товара на получение бонуса за отзыв
            $revAffiliate =
                $this->reviewsAffiliateModel->getByField([
                    'contact_id' => $params['data']['contact_id'],
                    'product_id' => $params['product']['id'],
                    'state' => shopApiextensionPluginReviewsAffiliateModel::STATE_AFFILIATE_ACTIVE
                ]);

            if(!empty($revAffiliate)) {
                $shopAffTrans = new shopAffiliateTransactionModel();
                $shopAffTrans->applyBonus(
                    $revAffiliate['contact_id'],
                    $revAffiliate['affiliate'],
                    $revAffiliate['order_id'],
                    sprintf($this->settings['bonus_for_review_text'], $params['product']['name']),
                    shopAffiliateTransactionModel::TYPE_ORDER_BONUS);

                // после начисления бонусов обновляем статус у записи на - completed
                $this->reviewsAffiliateModel->updateById(
                    $revAffiliate['id'],
                    ['state' => shopApiextensionPluginReviewsAffiliateModel::STATE_AFFILIATE_COMPLETED]
                );
            }
        }
    }

    /**
     * При возврате заказа, списываем бонусы у клиента
     * @throws waException
     */
    public function cancelAffiliateWhenOrderRefund($params)
    {
        if($this->settings['bonus_for_review_status']) {
            // update old
            $this->reviewsAffiliateModel->updateOld();

            $shopOrderModel = new shopOrderModel();
            $order = $shopOrderModel->getById($params['order_id']);

            $shopOrderItemsModel = new shopOrderItemsModel();
            $orderItems = $shopOrderItemsModel->getItems($order['id']);

            // проверяем были ли начислены баллы и делаем отмену баллов
            if(!empty($orderItems)) {
                foreach ($orderItems as $item) {
                    $revAffiliate =
                        $this->reviewsAffiliateModel->getByField([
                            'contact_id' => $order['contact_id'],
                            'product_id' => $item['product_id'],
                            'state' => shopApiextensionPluginReviewsAffiliateModel::STATE_AFFILIATE_COMPLETED,
                        ]);

                    // меняем статус на delete и делаем отмену баллов
                    if(!empty($revAffiliate)) {
                        $shopAffTrans = new shopAffiliateTransactionModel();
                        $shopAffTrans->applyBonus(
                            $revAffiliate['contact_id'],
                            -$revAffiliate['affiliate'],
                            $revAffiliate['order_id'],
                            sprintf($this->settings['bonus_text_cancel'], $item['name']),
                            shopAffiliateTransactionModel::TYPE_ORDER_CANCEL);

                        // после отмены бонусов обновляем статус у записи на - delete
                        $this->reviewsAffiliateModel->updateById(
                            $revAffiliate['id'],
                            ['state' => shopApiextensionPluginReviewsAffiliateModel::STATE_AFFILIATE_DELETE]
                        );
                    }
                }
            }

        }
    }

    /**
     * При переводе заказа в статус выполенено, делаем запись о возможности поулчить бонусы за отзыв
     * @throws waException
     */
    public function addAffiliateWhenOrderComplete($params)
    {
        if($this->settings['bonus_for_review_status']) {
            // update old
            $this->reviewsAffiliateModel->updateOld();

            $shopOrderModel = new shopOrderModel();
            $order = $shopOrderModel->getById($params['order_id']);

            $shopOrderItemsModel = new shopOrderItemsModel();
            $orderItems = $shopOrderItemsModel->getItems($order['id']);

            // получаем товары заказа и делаем записи в таблицу для начиселния бонусов, если еще не заносилась
            // если для товара создавалась запись и она в статусе активна или выполенена, то новой записи создано не будет
            if(!empty($orderItems)) {
                foreach($orderItems as $item) {
                    $revAffiliate =
                        $this->reviewsAffiliateModel->getReviewsAffiliate($order['contact_id'],  $item['product_id']);

                    // добавляем запись для начисления бонусов за отзыв, тут же рассчитывает бонус за товар
                    if(empty($revAffiliate)) {
                        $this->reviewsAffiliateModel->insert(array(
                            'contact_id' => $order['contact_id'],
                            'order_id'   => $order['id'],
                            'product_id' => $item['product_id'],
                            'affiliate'  => $this->getAffiliate($item),
                            'state'      => shopApiextensionPluginReviewsAffiliateModel::STATE_AFFILIATE_ACTIVE,
                        ));
                    }
                }
            }
        }
    }

    private function getAffiliate($item)
    {
        $bonus = $this->settings['bonus_for_review_all'];
        $type = $this->settings['bonus_for_review_all_type'];
        $round = $this->settings['bonus_for_review_all_round'];

        $shopProductModel = new shopProductModel();
        $product = $shopProductModel->getById($item['product_id']);

        if(!empty($this->settings['bonus_by_category'][$product['category_id']]['bonus'])) {
            $bonusByCategory = $this->settings['bonus_by_category'][$product['category_id']];
            $bonus = $bonusByCategory['bonus'];
            $type  = $bonusByCategory['type'];
            $round = $bonusByCategory['round'];
        }

        if($type == 'percent') {
            $bonus = $item['price'] * $bonus / 100;
        }

        if($round == 'round_up') {
            $bonus = round($bonus, 0, PHP_ROUND_HALF_UP);
        } elseif ($round == 'round_down') {
            $bonus = round($bonus, 0, PHP_ROUND_HALF_DOWN);
        }

        return $bonus;
    }
}
