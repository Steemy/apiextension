<?php

/**
 * Plugin config
 *
 * @author Steemy, created by 21.07.2021
 */

return array(
    'name'            => 'Расширение функционала',
    'description'     => 'Дополнительный функционал магазина',
    'version'         => '1.0.1',
	'vendor'          => '989788',
    'img'             => 'img/icon.svg',
    'shop_settings'   => true,
    'custom_settings' => true,
    'handlers'        => array(
        'frontend_review_add.after' => 'frontendReviewAddAfter',
    ),
    'icons'           => array
        (
            16 => 'img/icon.svg',
        ),
) ;