<?php

/**
 * HELPER FOR REVIEWS
 *
 * @author Steemy, created by 17.08.2021
 */

class shopApiextensionPluginReviewsHelper
{
    private $apiextensionReviewsModel;

    public function __construct(){
        $this->apiextensionReviewsModel = new shopApiextensionPluginReviewsModel();
    }

    /**
     * Получить количество отзывов для товаров
     * @param $productIds - список ид товаров
     * @return array
     * @throws waDbException
     */
    public function reviewsCount($productIds)
    {
        if(!is_array($productIds)) {
            $productIds = explode(',', $productIds);
        }

        return $this->apiextensionReviewsModel->reviewsCount($productIds);
    }

    /**
     * Добавляем дополнительные поля для отзывов
     * @param $reviewsId
     * @throws waDbException
     * @throws waException
     */
    public function addAdditionalFieldsReview($reviewsId)
    {
        $experience  = htmlspecialchars(waRequest::post('apiextension_experience', null, waRequest::TYPE_STRING_TRIM));
        $dignity = htmlspecialchars(waRequest::post('apiextension_dignity', null, waRequest::TYPE_STRING_TRIM));
        $limitations  = htmlspecialchars(waRequest::post('apiextension_limitations', null, waRequest::TYPE_STRING_TRIM));
        $recommend  = htmlspecialchars(waRequest::post('apiextension_recommend', 0, waRequest::TYPE_INT));

        /*
         * Проверяем, что отзыв не является ответом
         */
        $shopProductReviewsModel = new shopProductReviewsModel();

        /*
         * - Добовляем в базу наши дополнительные поля
         */
        $data = array(
            'apiextension_experience' => $experience,
            'apiextension_dignity' => $dignity,
            'apiextension_limitations' => $limitations,
            'apiextension_recommend' => $recommend,
        );

        try {
            $shopProductReviewsModel->updateById($reviewsId, $data);
        } catch (Exception $e) {}
    }

    /**
     * Показываем дополнительные поля для отзывов в админке
     * @param $reviews
     */
    public function showAdditionalFieldsReviewBackend($reviews)
    {
        $additionalFields = array();
        foreach ($reviews as $r) {
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
                    .after('<p><span class=\"hint\">Рекомендуите ли вы этот товар</span>: ' + limitations + '</p>');
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
     * Получить голосвание клиента по отзывам
     * @param $reviewIds - список ид отзывов
     * @param $contactId - идентификатор пользователя
     * @return array
     * @throws waDbException
     * @throws waException
     */
    public function getReviewsVote($reviewIds, $contactId)
    {
        if(is_array($reviewIds)) {
            $reviewIds = implode(',', $reviewIds);
        }

        if(!$contactId) {
            // проверка что пользователь авторизован
            if(!wa()->getAuth()->isAuth()) {
                throw new waException('Not authorized', 403);
            }
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
