<?php

use mlauto\Model\PostInfoAggregator;
use mlauto\Model\Classification;

use mlauto\Analysis\Vectorizer;
use mlauto\Analysis\Classifier;


use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;

use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\RandomSplit;


class MLAuto_Tag_Ajax_Hooks {
	function saveSettings() {
		$data = $_POST;

		try {
			if (isset($data["settings"])) {
		    	foreach ($data["settings"] as $setting) {
		    		if (isset($setting["name"])) {
		    			update_option($setting["name"], $setting["value"]);
		    		}
		    	}

		    	$message = MLAuto_Tag::getConfig();
		    }
		    else {
		    	wp_send_json_error('Could not detect a settings option.');
		    }

		    wp_send_json_success($message);
		}
		catch (Exception $e) {
			wp_send_json_error('Caught exception: '. $e->getMessage() . "\n");
		}

		wp_die();
	}


	function getTermSlugs($term) {
		return $term->slug;
	}


	function classifyPost() {
		$data = $_POST;

		$post_id = intval($data["post_id"]);

		$retval = array();


		if (is_null($post_id)) {
			wp_send_json_error("Did not recieve a valid input for parameter 'post_id. Recieved: " . $post_id);
			wp_die();
		}
		else {
			$post_id = intval($post_id);
		}

		$post = get_post($post_id);

		if (is_null($post)) {
			wp_send_json_error("Could not find post using post id: " . $post_id);
			wp_die();
		}

		$args = MLAuto_Tag::getConfig();

		//Get matching terms for post
		$selected_terms = array();
		foreach($args["MLAuto_taxonomies"] as $taxonomy) {
			$terms = get_the_terms($post_id, $taxonomy);

			$term_names = array();
			if ($terms) {
				$term_names = array_map(array($this, 'getTermSlugs'), $terms);
			}
			$selected_terms[$taxonomy] = $term_names;
		}

		if ($post_id) {
			$args["include"] = array($post_id);
			$args["numberposts"] = 1;
		}

		$all_posts = get_posts( $args );

		//Vectorize post
		$info = new PostInfoAggregator(array(), $args["MLAuto_specified_features"], $post_id);
		$vectorizer = new Vectorizer($info->features);


		//Identify classifier
		//TODO: Have a selected classifier
		//For now, we just use the most recent
		$classifier_folder_name = end(scandir(MLAUTO_PLUGIN_URL . "bin"));

		$taxonomy_directory_names = scandir(MLAUTO_PLUGIN_URL . "bin/" . $classifier_folder_name);

		$taxonomy_directory_names = array_diff($taxonomy_directory_names, [".", ".."]);

		foreach($taxonomy_directory_names as $taxonomy_directory_name) {
			if ($taxonomy_directory_name !== "." && "taxonomy_directory_name" !== ".." ) {
				$retval[$taxonomy_directory_name] = array();

				//Get direcotroy object
				$taxonomy_filenames = scandir(MLAUTO_PLUGIN_URL . "bin/" . $classifier_folder_name . "/" . $taxonomy_directory_name );

				$taxonomy_filenames = array_diff($taxonomy_filenames, [".", ".."]);

				foreach ($taxonomy_filenames as $filename) {
					$index = array_push($retval[$taxonomy_directory_name], array("name" => $filename)) -1;
					
					//Restore the saved classifier from file
					$classifier = new Classifier();

					$file_path = MLAUTO_PLUGIN_URL . "bin/" . $classifier_folder_name . "/" . $taxonomy_directory_name . "/" . $filename;


					//Finally, predict the probability
					$classifier->restore($file_path);
					$predicted_probability = $classifier->predictProbability($vectorizer->vectorized_samples);


					//First arg in array is the predicted probability
					$retval[$taxonomy_directory_name][$index]["probabilities"] = $predicted_probability[0];

					//Second argument in the array is a bool indicating if the term has already been selected for the post by the user
					if (in_array($filename, $selected_terms[$taxonomy_directory_name])) {
						$retval[$taxonomy_directory_name][$index]["checked"] = true;
					}
					else {
						$retval[$taxonomy_directory_name][$index]["checked"] = false;
					}

				}
			}
		}

		wp_send_json_success($retval);

		wp_die();
	}


	function generateClassifier() {

		$retval = array();

		$args = MLAuto_Tag::getConfig();

		$taxonomies = $args["MLAuto_taxonomies"];

		$info = new PostInfoAggregator($taxonomies, $args["MLAuto_specified_features"], 0);

		$vectorizer = new Vectorizer($info->features);

		$args["custom_name"] = current_time( 'timestamp' );

				
		for ($i=0; $i < count($taxonomies); $i++) { 

			$retval[$taxonomies[$i]] = array();

			foreach($info->targets_collection[$i] as $target) {

				$labels = array_column($info->labels_collection, $i);

				$vectorized_labels = $vectorizer->vectorize_labels($labels, $target);

				$dataset = new ArrayDataset($vectorizer->vectorized_samples, $vectorized_labels);

				$randomizedDataset = new RandomSplit($dataset, $args['MLAuto_test_percentage']);

				//train group
				$train_samples = $randomizedDataset->getTrainSamples();
				$train_labels = $randomizedDataset->getTrainLabels();

				//test group
				$test_samples = $randomizedDataset->getTestSamples();
				$test_labels = $randomizedDataset->getTestLabels();


				$classifier = new Classifier($train_samples, $train_labels, $args);
				$classifier->trainClassifier($train_samples, $train_labels, $args);

				$predictedLabels = $classifier->predict($test_samples, $test_labels);

				$retval[$taxonomies[$i]][$target] = Accuracy::score($test_labels, $predictedLabels, true);

				$args["taxonomy_name"] = $taxonomies[$i];
				$args["accuracy"] = Accuracy::score($test_labels, $predictedLabels, true);
				$args["tag_name"] = $target;

				Classification::saveClassification($classifier, $args);

			}

		}

		wp_send_json_success($retval);
	}

	function __construct() {
		add_action( 'wp_ajax_saveSettings', array($this, 'saveSettings'));  
		add_action( 'wp_ajax_generateClassifier', array($this, 'generateClassifier')); 
		add_action( 'wp_ajax_classifyPost', array($this, 'classifyPost')); 
	}

}
