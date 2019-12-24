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

use mlauto\Model\PostInfoAggregator;

use mlauto\Controller\VectorizerController;
use mlauto\Controller\ClassifierController;



use Phpml\Metric\Accuracy;
use Phpml\Classification\NaiveBayes;

use Phpml\CrossValidation\StratifiedRandomSplit;

use Phpml\Metric\ClassificationReport;




class MLAuto_Tag {

	public function __construct() {
		$taxonomies = array("category");//, "post_tag");

		$info = new PostInfoAggregator($taxonomies);

		$vectorizer = new VectorizerController($info->features);


		for ($i=0; $i < count($taxonomies); $i++) { 

			foreach($info->targets_collection[$i] as $target) {

				$labels = array_column($info->labels_collection, $i);

				$vectorized_labels = $vectorizer->vectorize_labels($labels, $target);


				$train_samples = array_slice($vectorizer->vectorized_samples, 0, 60);
				$train_labels = array_slice($vectorized_labels, 0, 60);

				$test_samples = array_slice($vectorizer->vectorized_samples, 60);
				$test_labels = array_slice($vectorized_labels, 60);

				$classifier = new ClassifierController($train_samples, $train_labels);


				$predictedLabels = $classifier->predict($test_samples, $test_labels);

				echo 'Target: ' . $target . " " . Accuracy::score($test_labels, $predictedLabels, true) . "<br>";


			}

			wp_die();

		}


	}

}

//add_action('init', array(__CLASS__, 'main'));

new MLAuto_Tag();

?>