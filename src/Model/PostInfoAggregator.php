<?php 

declare(strict_types=1);

namespace mlauto\Model;

class PostInfoAggregator {

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


	//Take wordpress terms, convert them to lowercase, and connect multi-word terms with an underscore
	private function cleanTerms($terms) {
		if ($terms) {
			$term_names = "";

			foreach ($terms as $term) {
				$term_names .= ' ' . $term->slug;
			}
		}
		else {
			$term_names = "";
		}

		return $term_names;
	}

	private function clean_frequency_feature(String $feature) {
		//E.g. "<p>You're tearing me <i>apart</i>, Lisa!</p>" -->
		// "youre tearing me apart lisa"

		//Convert to lowercase
		$cleaned_feature = strtolower($feature);
		//Strip HTML tags
		$cleaned_feature = strip_tags($cleaned_feature);
		//Strip punctuation and numbers
		$cleaned_feature = preg_replace("/[^a-z]+/i", " ", $cleaned_feature);

		return $cleaned_feature;
	}

	private function clean_categorical_feature(String $feature) {
		//strip all punctuation and whitespace, replace with underscores
		//E.g. "Walt Whitman" => "Walt_Whitman"
		//E.g. "My Brother's Posts" => "My_Brother_s_Posts"

		$cleaned_feature = preg_replace('/[^A-z0-9]+/', '_', $feature); 

		return $cleaned_feature;
	}



	private function extract_post_features(Object &$post, array $feature_names) {
		$post_features = array();

		/*
		Different features must be cleaned differently, because their value in categorizing is different conceptually.

		For the features post_content, post_excerpt, and post_title, the we want to find important keywords and their frequency.*/

		$frequncy_features = array("post_content", "post_title", "post_excerpt");
		
		//For the features post_author and post_type, we wouldn't expect word frequency to matter. "Walt Disney" and "Walt Whitman" aren't producing related works on the basis of their first names. We essentially want to hash the names. Similarly a post type named "Authors" and "Types of Authors" shouldn't have similar tags on the basis of having the word "Authors" in their names.

		$categorical_features = array("post_author", "post_type");
		
		foreach($feature_names as $feature_type) {
			//Clean frequency features
			if (in_array($feature_type, $frequncy_features)) {
				$cleaned_feature = $this->clean_frequency_feature($post->$feature_type);
			}

			//Clean categorical features
			if (in_array($feature_type, $categorical_features)) {

				//post_author is actually a numerical string representing the author's id. That's absolutely fine for statistical purposes, but we'll get the author's display name to make it easier for humans to reason about while doing diagnostics.
				if($feature_type == "post_author") {
					$feature = get_author_name($post->$feature_type);
				}
				else {
					$feature = $post->$feature_type;
				}

				$cleaned_feature = $this->clean_categorical_feature($feature);
			}

			array_push($post_features, $cleaned_feature);
		}

		array_push($this->features, $post_features);
	}


	private function extract_post_attributes(array $taxonomies, array $feature_names, int $post_id): void {
		
		$this->features = array();

		$this->labels_collection = array();


		$args = array(
		  'numberposts' => -1
		);

		//If we're only retrieving one post
		if ($post_id) {
			$args["include"] = array($post_id);
			$args["numberposts"] = 1;
		}
 
		$all_posts = get_posts( $args );

		foreach ($all_posts as $post) {

			//push features to array in order of post
			$this->extract_post_features($post, $feature_names);


			if (count($taxonomies) != 0) {

				array_push($this->labels_collection, array());

				foreach ($taxonomies as $taxonomy) {
					$matching_terms = get_the_terms($post->ID, $taxonomy);

					$term_names = $this->cleanTerms($matching_terms);


					array_push($this->labels_collection[count($this->labels_collection) - 1], $term_names); 

				}
			}
		}
	}

	private function extract_targets_collection(array $taxonomies): void {

		$this->targets_collection = array();

		$args = array(
		    'hide_empty' => true,
		);


		foreach ($taxonomies as $taxonomy) {
			$taxonomy_terms = array();

			$args['taxonomy'] = $taxonomy;

			$terms = get_terms( $args );



			foreach ($terms as $term) {
				$term_names = $term->slug;

				array_push($taxonomy_terms, $term_names);

			}

			array_push($this->targets_collection, $taxonomy_terms);

		}

	}

	public function __construct(array $taxonomies, array $specified_features, int $post_id) {

		//If we're only classifying an individual post, we don't need to extract any targets; the classifier already exists and has been trained for the targets.
		if (count($taxonomies) != 0) {
			$this->extract_targets_collection($taxonomies);
		}

		$this->extract_post_attributes($taxonomies, $specified_features, $post_id);
	}

}