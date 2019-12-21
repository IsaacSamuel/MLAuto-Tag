<?php
/*
Plugin Name: MLAuto Tag
Plugin URI:  https://github.com/IsaacSamuel/MLAuto-Tag
Description: This plugin uses machine learning (php-ml) to suggest auto-tags and auto-categorizations for posts. 
Version:     0.1
Author:      Isaac Samuel
Author URI:  https://github.com/IsaacSamuel
*/

declare(strict_types=1);


require 'class_loader.php';

use mlauto\Post_Info_Aggregator;



class MLAuto_Tag {

	public function __construct() {
		add_action('init', array(__CLASS__, 'main'));
	}

	function main() : void {

		$taxonomies = array("category", "post_tag");

		$info = new Post_Info_Aggregator($taxonomies);
	}

}

new MLAuto_Tag();

?>