<?php

/**
 * UNINSTALL
 *
 * @author Steemy, created by 17.08.2021
 */

$model = new waModel();

try {
    $model->query('ALTER TABLE `shop_product_reviews` DROP apiextension_experience');
} catch(waDbException $e) {
    waLog::log('Unable to remove "apiextension_experience" column.');
}

try {
    $model->query('ALTER TABLE `shop_product_reviews` DROP apiextension_dignity');
} catch(waDbException $e) {
    waLog::log('Unable to remove "apiextension_dignity" column.');
}

try {
    $model->query('ALTER TABLE `shop_product_reviews` DROP apiextension_limitations');
} catch(waDbException $e) {
    waLog::log('Unable to remove "apiextension_limitations" column.');
}

try {
    $model->query('ALTER TABLE `shop_product_reviews` ADD `apiextension_recommend` INT(1) NULL AFTER `text`');
} catch(waDbException $e) {
    waLog::log('Unable to remove "apiextension_recommend" column.');
}