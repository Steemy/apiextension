<?php
/**
 * DB
 *
 * @author Steemy, created by 25.08.2021
 */

return array(
    'shop_apiextension_reviews_vote' => array(
        'review_id' => array('int', 11, 'null' => 0),
        'contact_id' => array('int', 11, 'null' => 0),
        'vote_like' => array('int', 1, 'null' => 0, 'default' => '0'),
        'vote_dislike' => array('int', 1, 'null' => 0, 'default' => '0'),
        ':keys' => array(
            'shop_apiextension_reviews_vote_reviews_id_contact_id' => array('review_id', 'contact_id', 'unique' => 1),
        ),
    ),
);
