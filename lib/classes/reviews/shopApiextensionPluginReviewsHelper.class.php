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
     * @param $product_ids - список ид товаров
     * @return array
     * @throws waDbException
     */
    public function reviewsCount($product_ids)
    {
        if(!is_array($product_ids)) {
            $product_ids = explode(',', $product_ids);
        }

        return $this->apiextensionReviewsModel->reviewsCount($product_ids);
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


    public function showAdditionalFieldsReviewBackend($reviews)
    {
        $additionalFields = array();
        foreach ($reviews as $r) {
            $additionalFields[$r['id']]['apiextension_experience'] = $r['apiextension_experience'];
            $additionalFields[$r['id']]['apiextension_dignity'] = $r['apiextension_dignity'];
            $additionalFields[$r['id']]['apiextension_limitations'] = $r['apiextension_limitations'];
            $additionalFields[$r['id']]['apiextension_recommend'] = $r['apiextension_recommend'];
        }

        if ($additionalFields) {
            $additionalFieldsJson = json_encode($additionalFields);
            $script = "
<script>
    $(function() {
        const additionalFields = " . $additionalFieldsJson . ";
        for (let id in additionalFields) {
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
}
