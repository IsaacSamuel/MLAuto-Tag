<?php 

declare(strict_types=1);

namespace mlauto;

class Post_Info_Aggregator {


	function extract_post_features(bool $testing, array $taxonomies): array {
		
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

					if ($matching_terms) {
						$term_names = array_column($matching_terms, 'name');
					}
					else {
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

	function extract_targets(array $taxonomies): array {

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

	public function __construct() {

		$taxonomies = array("category", "post_tag");

		$targets = $this->extract_targets($taxonomies);

		$features = $this->extract_post_features(true, $taxonomies);

	}

}