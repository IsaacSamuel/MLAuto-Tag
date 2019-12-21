<?php
/*
Plugin Name: MLAuto Tag
Plugin URI:  https://github.com/IsaacSamuel/MLAuto-Tag
Description: This plugin uses machine learning (php-ml) to suggest auto-tags and auto-categorizations for posts. 
Version:     0.1
Author:      Isaac Samuel
Author URI:  https://github.com/IsaacSamuel
*/
class MLAuto_Tag {

	static function init() {
		add_action('init', array(__CLASS__, 'main'));
	}

	function extract_post_features($testing, $taxonomies) {
		
		$features = array();

		$outputs = array();

		$args = array(
		  'numberposts' => -1
		);
 
		$all_posts = get_posts( $args );

		foreach ($all_posts as $post) {
			//push features to array in order of post
			array_push($features, array($post->post_title, $post->post_type, $post->post_date));

			if ($testing) {

				array_push($outputs, array());

				foreach ($taxonomies as $taxonomy) {
					$matching_terms = get_the_terms($post->ID, $taxonomy);

					$term_names = array_column($matching_terms, 'name');

					if (is_null($term_names)) {
						$term_names = "";
					}

					array_push($outputs[count($outputs) - 1], $term_names); 

				}
			}
		}

		echo "feature data: <br>";

		var_dump($features);

				echo "<br><br>";


		echo "output data: <br>";

		var_dump($outputs);

				echo "<br><br>";


		wp_die();

		return array($features, $outputs);

	}

	function extract_targets($taxonomies) {

		$targets = array();

		$args = array(
		    'hide_empty' => true,
		);

		foreach ($taxonomies as $taxonomy) {
			$args['taxonomy'] = $taxonomy;

			$terms = get_terms( $args );

			$term_names = array_column($terms, 'name');

			array_push($targets, $term_names);
		}

		echo "Targets: <br>";
		var_dump($targets);
		echo "<br><br>";

		return $targets;
	}

	function main($extract_post_features) {

		$taxonomies = array("category", "post_tag");

		$targets = self::extract_targets($taxonomies);

		$features = self::extract_post_features(true, $taxonomies);

	}

}

MLAuto_Tag::init();

?>