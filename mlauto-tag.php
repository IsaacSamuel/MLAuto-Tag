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


use Phpml\Metric\Accuracy;
use mlauto\Post_Info_Aggregator;
use Phpml\Classification\NaiveBayes;
use Phpml\Classification\SVC;


use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\NGramTokenizer;
use Phpml\FeatureExtraction\StopWords\English;
use Phpml\Tokenization\WhitespaceTokenizer;

use Phpml\FeatureExtraction\TfIdfTransformer;
use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;

use Phpml\Metric\ClassificationReport;

use Phpml\SupportVectorMachine\Kernel;



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

	function extract_test_values($info) {

		return $targets = $info->targets[0];
	}

	function contains_term($term_list, $term) {
		$contains = 0;

		if (strpos($term_list, $term) !== false) {
			$contains = 1;
		}

		return $contains;
	}

	function vectorize_targets($info, $targets) {

		$vectorized_outputs = array();

		foreach ($targets as $target) {
			
			$outputs = array_column($info->outputs, 0);
			$target_outputs = array();
			
			foreach ($outputs as $key) {
				array_push($target_outputs, $this->contains_term($key, $target));
			}

			array_push($vectorized_outputs, $target_outputs);

		}

		return $vectorized_outputs;
	}

	public function vectorize($info) :void {
		$all_targets = $this->extract_test_values($info);

		$vectorized_outputs = $this->vectorize_targets($info, $all_targets);

		//var_dump(count($vectorized_outputs));

		for ($i=0; $i < count($vectorized_outputs); $i++) { 

			$this->dataset = new ArrayDataset($info->features, $vectorized_outputs[$i]);

		





			$samples = array_slice($this->dataset->getSamples(), 0, 60);
			$sample_labels = array_slice($this->dataset->getTargets(), 0, 60);


			$sample_vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
			$sample_vectorizer->fit($samples);
			$sample_vectorizer->transform($samples);



			$classifier = new SVC();//SVC(Kernel::RBF, 1.0, 3, null, 0.0, .001, 100, false, false);
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

			echo Accuracy::score($test_labels, $predictedLabels, true) . "<br>";


			//var_dump( $predicted);

			//exit;


		}

/*
		$this->dataset = new ArrayDataset($info->features, array_column($info->outputs, 0));


		$samples = array_slice($this->dataset->getSamples(), 0, 60);
		$sample_labels = array_slice($this->dataset->getTargets(), 0, 60);


		$sample_vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
		$sample_vectorizer->fit($samples);
		$sample_vectorizer->transform($samples);



		$classifier = new SVC(Kernel::RBF, 1.0, 3, null, 0.0, .001, 100, true, true);
		$classifier->train($samples, $sample_labels);


		$test_samples = array_slice ($this->dataset->getSamples(), 60);
		$test_labels = array_slice ($this->dataset->getTargets(), 60);

		$sample_vectorizer->transform($test_samples);
		

		$predicted = $classifier->predictProbability($test_samples);


		$highest_prediction = array();
		$i = 0;

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

		}


		var_dump( $predicted);

	*/	
		wp_die();

	}

	public function __construct() {
		$taxonomies = array("category", "post_tag");

		$info = new Post_Info_Aggregator($taxonomies);

		$this->vectorize($info);
	}

}

//add_action('init', array(__CLASS__, 'main'));

new MLAuto_Tag();

?>