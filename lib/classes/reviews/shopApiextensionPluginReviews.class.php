<?php

/**
 * HELPER FOR REVIEWS
 *
 * @author Steemy, created by 17.08.2021
 */

class shopApiextensionPluginReviews
{
    private $apiextensionReviewsModel;
    private $settings;

    public function __construct(){
        $this->apiextensionReviewsModel = new shopApiextensionPluginReviewsModel();
        $pluginSetting = shopApiextensionPluginSettings::getInstance();
        $this->settings = $pluginSetting->getSettings();
    }

    /**
     * Получить количество отзывов для товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     */
    public function reviewsCount($productIds)
    {
        if(!$productIds) return array();

        if(!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        return $this->apiextensionReviewsModel->reviewsCount($productIds);
    }

    /**
     * Добавляем поля только для отзыва и если разрешено в настройках плагина
     * @param $params
     */
    public function addAdditionalFields($params)
    {
        if ($this->settings['review_fields'] && $params['data']['parent_id'] == 0) {
            $params['data']['apiextension_experience'] =
                htmlspecialchars(waRequest::post('apiextension_experience', null, waRequest::TYPE_STRING_TRIM));

            $params['data']['apiextension_dignity'] =
                htmlspecialchars(waRequest::post('apiextension_dignity', null, waRequest::TYPE_STRING_TRIM));

            $params['data']['apiextension_limitations'] =
                htmlspecialchars(waRequest::post('apiextension_limitations', null, waRequest::TYPE_STRING_TRIM));

            $params['data']['apiextension_recommend'] =
                htmlspecialchars(waRequest::post('apiextension_recommend', 0, waRequest::TYPE_INT));
        }
    }

    /**
     * Показываем дополнительные поля для отзывов в админке
     * @param $params
     */
    public function showAdditionalFieldsReviewBackend($params)
    {
        if(!$this->settings['review_fields'] || !$params['reviews']) {
            return;
        }

        $additionalFields = array();
        foreach ($params['reviews'] as $r) {
            $additionalFields[$r['id']]['apiextension_recommend'] = $r['apiextension_recommend'];
            $additionalFields[$r['id']]['apiextension_experience'] = $r['apiextension_experience'];
            $additionalFields[$r['id']]['apiextension_dignity'] = $r['apiextension_dignity'];
            $additionalFields[$r['id']]['apiextension_limitations'] = $r['apiextension_limitations'];
            $additionalFields[$r['id']]['apiextension_votes'] = json_decode($r['apiextension_votes'], true);
        }

        if ($additionalFields) {
            $additionalFieldsJson = json_encode($additionalFields);
            $script = "
<script>
    $(function() {
        const additionalFields = " . $additionalFieldsJson . ";
        for (let id in additionalFields) {
            if(additionalFields[id]['apiextension_votes']) {
                $('.s-review[data-id=' + id + ']')
                    .find('.s-review-text')
                    .after('<p><span class=\"hint\">Голсование</span>: за - ' + additionalFields[id]['apiextension_votes']['vote_like'] + ', против - ' + additionalFields[id]['apiextension_votes']['vote_dislike'] + '</p>');
            }
            
            if(additionalFields[id]['apiextension_recommend'] && +additionalFields[id]['apiextension_recommend'] > 0) {
                const limitations = additionalFields[id]['apiextension_recommend'] == 1 ? '<span style=\"color:red\">Не рекомендую</span>' : '<span style=\"color:green\">Рекомендую</span>' ;
                $('.s-review[data-id=' + id + ']')
                    .find('.s-review-text')
                    .after('<p><span class=\"hint\">Рекомендуете ли вы этот товар</span>: ' + limitations + '</p>');
            }
            
            if(additionalFields[id]['apiextension_limitations']) {
                $('.s-review[data-id=' + id + ']')
                    .find('.s-review-text')
                    .after('<p><span class=\"hint\">Недостатки</span>: ' + additionalFields[id]['apiextension_limitations'] + '</p>');
            }
            
            if(additionalFields[id]['apiextension_dignity']) {
                $('.s-review[data-id=' + id + ']')
                    .find('.s-review-text')
                    .after('<p><span class=\"hint\">Достоинства</span>: ' + additionalFields[id]['apiextension_dignity'] + '</p>');
            }
            
            if(additionalFields[id]['apiextension_experience']) {
                $('.s-review[data-id=' + id + ']')
                    .find('.s-review-text')
                    .after('<p><span class=\"hint\">Опыт использования</span>: ' + additionalFields[id]['apiextension_experience'] + '</p>');
            }
        }
    });
</script>";
            echo $script;
        }
    }

    /**
     * Получить голосование клиента по отзывам
     * @param $reviewIds - список ид отзывов
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getReviewsVote($reviewIds, $contactId)
    {
        if(!$reviewIds || !wa()->getAuth()->isAuth()) return array();

        if(is_array($reviewIds)) {
            $reviewIds = implode(',', $reviewIds);
        }

        if(!$contactId) {
            $contactId = wa()->getUser()->getId();
        }

        $apiextensionReviewsVoteModel = new shopApiextensionPluginReviewsVoteModel();

        return
            $apiextensionReviewsVoteModel
                ->select('*')
                ->where("contact_id={$contactId} and review_id IN({$reviewIds})")
                ->fetchAll('review_id');
    }
}
