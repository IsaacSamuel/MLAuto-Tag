<?php 

declare(strict_types=1);

namespace mlauto\Model;

class Post_Info_Aggregator {

	public $features;

	public $labels_collection;

	public $targets_collection;
	

	public function __toString() :string {
		//toString function--for quick & dirty debugging
	
		$o = "";

		$o .= "feature data: <br>";
		$o .= print_r($this->features, true);
		$o .= "<br><br>";

		$o .= "output data: <br>";
		$o .= print_r($this->labels_collection, true);
		$o .= "<br><br>";

		$o .= "targets_collection: <br>";
		$o .= print_r($this->targets_collection, true);
		$o .= "<br><br>";

		return $o;

	}


	private function lower_case_array(array &$array) : void {
		
		for ($i=0; $i < count($array); $i++) { 
			$array[$i] = strtolower($array[$i]);
		}

	}


	function extract_post_features(array $taxonomies): void {
		
		$this->features = array();

		$this->labels_collection = array();


		$args = array(
		  'numberposts' => -1
		);
 
		$all_posts = get_posts( $args );

		foreach ($all_posts as $post) {
			//push features to array in order of post
			array_push($this->features, strtolower($post->post_title));//, $post->post_type, $post->post_date));


			array_push($this->labels_collection, array());

			foreach ($taxonomies as $taxonomy) {
				$matching_terms = get_the_terms($post->ID, $taxonomy);

				if ($matching_terms) {
					$term_names = "";

					foreach ($matching_terms as $term) {
						$term_names .= ' ' . str_replace(" ", "_", strtolower($term->name));
					}

				}
				else {
					$term_names = "";
				}


				array_push($this->labels_collection[count($this->labels_collection) - 1], $term_names); 

			}
		}

	}

	function extract_targets_collection(array $taxonomies): void {

		$this->targets_collection = array();

		$args = array(
		    'hide_empty' => true,
		);



		foreach ($taxonomies as $taxonomy) {
			$taxonomy_terms = array();

			$args['taxonomy'] = $taxonomy;

			$terms = get_terms( $args );

			foreach ($terms as $term) {
				$term_names = ' ' . str_replace(" ", "_", strtolower($term->name));

				array_push($taxonomy_terms, $term_names);

			}

			array_push($this->targets_collection, $taxonomy_terms);


		}

	}

	public function __construct(array $taxonomies) {

		$this->extract_targets_collection($taxonomies);

		$this->extract_post_features($taxonomies);

	}

}