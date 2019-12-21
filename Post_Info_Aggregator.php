<?php 

declare(strict_types=1);

namespace mlauto;

class Post_Info_Aggregator {

	public $features;

	public $outputs;

	public $targets;


	function extract_post_features(bool $testing, array $taxonomies): void {
		
		$this->features = array();

		$this->outputs = array();

		$args = array(
		  'numberposts' => -1
		);
 
		$all_posts = get_posts( $args );

		foreach ($all_posts as $post) {
			//push features to array in order of post
			array_push($this->features, array($post->post_title, $post->post_type, $post->post_date));

			if ($testing) {

				array_push($this->outputs, array());

				foreach ($taxonomies as $taxonomy) {
					$matching_terms = get_the_terms($post->ID, $taxonomy);

					if ($matching_terms) {
						$term_names = array_column($matching_terms, 'name');
					}
					else {
						$term_names = "";
					}


					array_push($this->outputs[count($this->outputs) - 1], $term_names); 

				}
			}
		}

		echo "feature data: <br>";

		var_dump($this->features);

		echo "<br><br>";


		echo "output data: <br>";

		var_dump($this->outputs);

		echo "<br><br>";


		wp_die();

	}

	function extract_targets(array $taxonomies): void {

		$this->targets = array();

		$args = array(
		    'hide_empty' => true,
		);

		foreach ($taxonomies as $taxonomy) {
			$args['taxonomy'] = $taxonomy;

			$terms = get_terms( $args );

			$term_names = array_column($terms, 'name');

			array_push($this->targets, $term_names);
		}

		echo "Targets: <br>";
		var_dump($this->targets);
		echo "<br><br>";

	}

	public function __construct($taxonomies) {

		$this->extract_targets($taxonomies);

		$this->extract_post_features(true, $taxonomies);

	}

}