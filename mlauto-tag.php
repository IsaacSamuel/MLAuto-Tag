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

use mlauto\Model\Post_Info_Aggregator;

use mlauto\Controller\Vectorizer;
use mlauto\Controller\Classifier;



use Phpml\Metric\Accuracy;
use Phpml\Classification\NaiveBayes;

use Phpml\CrossValidation\StratifiedRandomSplit;

use Phpml\Metric\ClassificationReport;




class MLAuto_Tag {

	private $dataset;

	public function __toString() : string {
		//toString function--for quick & dirty debugging
		$o = "";

		$o .= "Samples: <br>";
		$o .= print_r($this->dataset->getSamples(), true);
		$o .= "<br><br>";

		$o .= "Targets: <br>";
		$o .= print_r($this->dataset->getTargets(), true);
		$o .= "<br><br>";

		return $o;
	}

	//name of taxonomy
	//name of tag
	//location of classifier
	//date
	//version
	//specified features
	//runtime

	public function vectorize_samples($info) :void {
/*		$all_targets = $this->extract_test_values($info);

		$vectorized_outputs = $this->vectorize_targets($info, $all_targets);

		//var_dump(count($vectorized_outputs));

		for ($i=0; $i < count($vectorized_outputs); $i++) { 

			$this->dataset = new ArrayDataset($info->features, $vectorized_outputs[$i]);



			$samples = array_slice($this->dataset->getSamples(), 0, 60);
			$sample_labels = array_slice($this->dataset->getTargets(), 0, 60);


			$sample_vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
			$sample_vectorizer->fit($samples);
			$sample_vectorizer->transform($samples);



			$classifier = new SVC(Kernel::RBF, 1.0, 3, null, 0.0, .001, 100, false, true);
			$classifier->train($samples, $sample_labels);


			$test_samples = array_slice ($this->dataset->getSamples(), 60);
			$test_labels = array_slice ($this->dataset->getTargets(), 60);

			$sample_vectorizer->transform($test_samples);
			

			//$predicted = $classifier->predictProbability($test_samples);


			$predictedLabels = $classifier->predict($test_samples);


			$highest_prediction = array();
			//$i = 0;
/*
			foreach ($predicted as $prediction) {

				$highest_prediction = array();
				$max = 0;

				foreach ($prediction as $key => $value) {
					if ($value > $max) {
						$max = $value;
						$highest_prediction[$i] = array($key, $value);
					}
				}
				//var_dump($highest_prediction);

				$i++;

			}*/

			//var_dump($predictedLabels);

			//echo  Accuracy::score($test_labels, $predictedLabels, true) . "<br>";




			//var_dump( $predicted);

			//exit;


	}

	public function __construct() {
		$taxonomies = array("category");//, "post_tag");

		$info = new Post_Info_Aggregator($taxonomies);

		$vectorizer = new Vectorizer($info->features);


		for ($i=0; $i < count($taxonomies); $i++) { 

			foreach($info->targets_collection[$i] as $target) {

				$labels = array_column($info->labels_collection, $i);

				$vectorized_labels = $vectorizer->vectorize_labels($labels, $target);


				$train_samples = array_slice($vectorizer->vectorized_samples, 0, 60);
				$train_labels = array_slice($vectorized_labels, 0, 60);

				$test_samples = array_slice($vectorizer->vectorized_samples, 60);
				$test_labels = array_slice($vectorized_labels, 60);

				$classifier = new Classifier($train_samples, $train_labels);


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