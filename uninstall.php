<?php

$apartaments = get_posts(array('post_type' => 'apartament', 'numberposts' => -1));
foreach($apartaments as $apartament){
   wp_delete_post($apartament->ID, true);
}