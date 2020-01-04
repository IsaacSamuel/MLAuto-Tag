<?php

use mlauto\Model\PostInfoAggregator;
use mlauto\Model\ClassificationModel;
use mlauto\Model\TermModel;


use mlauto\Analysis\Vectorizer;
use mlauto\Analysis\Classifier;

use mlauto\Wrapper\Term;


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
		    			if($setting["name"] == "MLAuto_classifier_name") {
		    				//delete any classification with this name

		    				//If the user didn't select a custom name, make it a timestamp
		    				if (!$setting["value"]) {
								$setting["value"] = current_time('timestamp');
		    				}

		    			}
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

		$post = get_post($post_id);

		$args = MLAuto_Tag::getConfig();

		//Get existing taxonomies for this post
		foreach($args["MLAuto_taxonomies"] as $taxonomy) {
			$terms = get_the_terms($post_id, $taxonomy);

			$term_names = array();
			if ($terms) {
				$term_names = array_map(array($this, 'getTermSlugs'), $terms);
			}
			$selected_terms[$taxonomy] = $term_names;
		}


		//Vectorize post
		$info = new PostInfoAggregator(array(), $args["MLAuto_specified_features"], $post_id);
		$vectorizer = new Vectorizer($info->features);


		//Identify classifier
		//TODO: Have a selected classifier
		//For now, we just use the most recent
		$classification = ClassificationModel::getClassifications(null);

		$termModels = TermModel::getTerms($classification->id);

		foreach($termModels as $termModel) {
			if (!isset($retval[$termModel->taxonomy_name])) {
				$retval[$termModel->taxonomy_name] = array();
			}

			$term = new Term($termModel->term_name, $termModel->taxonomy_name);
			$term->setPath(MLAUTO_PLUGIN_URL . $classification->classifier_directory);

			$term->loadClassifier();

			$term->predictProbability($vectorizer->vectorized_samples);

			array_push($retval[$termModel->taxonomy_name], array(
				"name" => $term->name,
				"probabilities" => $term->predicted_probability,
				"checked" => in_array($term->name, $selected_terms[$termModel->taxonomy_name])
			));
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

		$classificationModel = ClassificationModel::saveClassificationModel($args, $taxonomies);

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


				$term = new Term($target, $taxonomies[$i]);
				$term->setClassifier($classifier, $classificationModel->id);
				$term->setPath(MLAUTO_PLUGIN_URL . $classificationModel->classifier_directory);
				$term->setAccuracy(Accuracy::score($test_labels, $predictedLabels, true));

				TermModel::saveTermModel($term);
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
