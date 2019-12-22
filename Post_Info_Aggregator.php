<?php 

declare(strict_types=1);

namespace mlauto;

class Post_Info_Aggregator {

	public $features;

	public $outputs;

	public $targets;

	public function __toString() :string {
		//toString function--for quick & dirty debugging
	
		$o = "";

		$o .= "feature data: <br>";
		$o .= print_r($this->features, true);
		$o .= "<br><br>";

		$o .= "output data: <br>";
		$o .= print_r($this->outputs, true);
		$o .= "<br><br>";

		$o .= "Targets: <br>";
		$o .= print_r($this->targets, true);
		$o .= "<br><br>";

		return $o;

	}


	private function lower_case_array(&$array) : void {
		
		for ($i=0; $i < count($array); $i++) { 
			$array[$i] = strtolower($array[$i]);
		}

	}


	function extract_post_features(bool $testing, array $taxonomies): void {
		
		$this->features = array();

		$this->outputs = array();


		$args = array(
		  'numberposts' => -1
		);
 
		$all_posts = get_posts( $args );

		foreach ($all_posts as $post) {
			//push features to array in order of post
			array_push($this->features, array(strtolower($post->post_title)));//, $post->post_type, $post->post_date));

			if ($testing) {

				array_push($this->outputs, array());

				foreach ($taxonomies as $taxonomy) {
					$matching_terms = get_the_terms($post->ID, $taxonomy);

					if ($matching_terms) {
						$term_names = array_column($matching_terms, 'name');

						$this->lower_case_array($term_names);
					}
					else {
						$term_names = "";
					}


					array_push($this->outputs[count($this->outputs) - 1], $term_names); 

				}
			}
		}

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

			$this->lower_case_array($term_names);

			array_push($this->targets, $term_names);
		}

	}

	public function __construct($taxonomies) {

		$this->extract_targets($taxonomies);

		$this->extract_post_features(true, $taxonomies);

	}

}