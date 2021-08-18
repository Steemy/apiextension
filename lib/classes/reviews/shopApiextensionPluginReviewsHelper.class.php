<?php

/**
 * ADD FIELDS REVIEWS
 *
 * @author Steemy, created by 17.08.2021
 */

class shopApiextensionPluginReviewsHelper
{
    /**
     * Добавляем дополнительные поля для отзывов
     * @param $reviewsId
     * @throws waDbException
     * @throws waException
     */
    public function reviewAddFields($reviewsId)
    {
        $experience  = htmlspecialchars(trim(waRequest::post('apiextension_experience')));
        $dignity = htmlspecialchars(trim(waRequest::post('apiextension_dignity')));
        $limitations  = htmlspecialchars(trim(waRequest::post('apiextension_limitations')));

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
        );



        try {
            $shopProductReviewsModel->updateById($reviewsId, $data);
        } catch (Exception $e) {}
    }
}
