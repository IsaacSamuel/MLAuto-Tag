<?php

use mlauto\Model\PostInfoAggregator;
use mlauto\Model\Classification;

use mlauto\Analysis\Vectorizer;
use mlauto\Analysis\Classifier;



use Phpml\Metric\Accuracy;
use Phpml\Metric\ClassificationReport;

use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\RandomSplit;


function MLAuto_saveSettings() {
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


function MLAuto_classifyPost() {
	$data = $_POST;

	//$post = get_post($data->postID);

	//$args = MLAuto_Tag::getConfig();

	//Get matching terms for post
	//selected_terms = array()
	//foreach($args["MLAuto_taxonomies"] as $taxonomy) {
		//$terms = get_the_terms($data->postID, $taxonomy);
		//term_names = array();
		//if ($terms) {
			//$term_names = $terms.reduce(PostInfoAggregator::cleanTerms($terms->name));
		//}
		//selected_terms->taxononmy = $term_names;
	//}

	//Vectorize post
	//$info = new PostInfoAggregator(array(), $args["MLAuto_specified_features"]);
	//$vectorizer = new Vectorizer($info->features);


	try {
		//Identify classifier
		//TODO: Have a selected classifier
		//For now, we just use the most recent

		//Find bin directory
		//For each file in bin directory
			//get most recently created classifiers

		//For each taxonomy in classifiers
			//$reval["taxonomy"] = array();
			//For each file in category

				//Load Classifier
				//$retval->$filename = array();

				//First element of the array is the prediction
				//$predicted_probability = classifier->predictProbability(vectorizer->features);
				//$probabilty_feature_matches_post = $predicted_probability[0]
				//array_push($retval->$filename, $probabilty_feature_matches_post);

				//Second argument in the array is a bool indicating if the term has already been selected for the post by the user
				//if (in_array($filename, $selected_terms->taxonomy) {
					//array_push($retval->$filename, true);
				//}
				//else {
					//array_push($retval->$filename, false);
				//}


	    wp_send_json_success($retval);
	}
	catch (Exception $e) {
		wp_send_json_error('Caught exception: '. $e->getMessage() . "\n");
	}

	wp_die();
}


function MLAuto_generateClassifier() {

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

	wp_die();
}


add_action( 'wp_ajax_saveSettings', 'MLAuto_saveSettings' );  
add_action( 'wp_ajax_generateClassifier', 'MLAuto_generateClassifier' ); 
add_action( 'wp_ajax_classifyPost', 'MLAuto_classifyPost' ); 

