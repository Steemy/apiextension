<?php

/**
 * UPDATE 1.0.2
 *
 * @author Steemy, created by 25.08.2021
 */

$model = new waModel();
try {
    $sql = <<<SQL
CREATE TABLE IF NOT EXISTS `shop_apiextension_reviews_vote` (
  `review_id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `vote_like` int(1) NOT NULL DEFAULT 0,
  `vote_dislike` int(1) NOT NULL DEFAULT 0,
  UNIQUE KEY `shop_apiextension_reviews_vote_reviews_id_contact_id` (`review_id`,`contact_id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1
SQL;
    $model->exec($sql);

} catch (waDbException $e) {

}