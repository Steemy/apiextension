<?php

/**
 * Plugin config
 *
 * @author Steemy, created by 21.07.2021
 */

return array(
    'name'            => 'Расширение функционала',
    'description'     => 'Дополнительный функционал магазина',
    'version'         => '1.1.0',
	'vendor'          => '989788',
    'img'             => 'img/icon.svg',
    'shop_settings'   => true,
    'frontend'        => true,
    'custom_settings' => true,
    'handlers'        => array(
        'products_reviews' => 'productsReviews',
        'frontend_review_add.before' => 'frontendReviewAddBefore',
        'frontend_review_add.after' => 'frontendReviewAddAfter',
        'order_action.complete' => 'orderActionСomplete',
        'order_action.refund' => 'orderActionRefund',
        'controller_after.shopMarketingPromoRuleEditorAction' => 'controllerAfterShopMarketingPromoRuleEditorAction',
        'controller_after.shopReviewsChangeStatusController' => 'controllerAfterShopReviewsChangeStatusController',
    ),
    'icons'           => array
        (
            16 => 'img/icon.svg',
        ),
) ;