<?php

declare(strict_types=1);

namespace mlauto\Analysis;

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;

use Phpml\ModelManager;

class Classifier {

	private $trained_classifier;

	public function saveToFile($filePath) {

		$modelManager = new ModelManager();
		$modelManager->saveToFile($this->trained_classifier, $filePath);
	}

	public function restore(String $filepath) {
		$modelManager = new ModelManager();
		$restoredClassifier = $modelManager->restoreFromFile($filepath);

		$this->trained_classifier =  $restoredClassifier;
	}

	public function predictProbability(array &$sample) {
		$probability = $this->trained_classifier->predictProbability($sample);

		return $probability;
	}

	public function predict(array &$test_features) {

		$predictedLabels = $this->trained_classifier->predict($test_features);

		return $predictedLabels;
	}


	public function trainClassifier(array &$sample_features, array &$sample_labels, array $args) {

		//var_dump($sample_labels);

		$classifier = new SVC(Kernel::POLYNOMIAL, $args["MLAuto_cost"], 3, $args["MLAuto_gamma"], 0.0, $args["MLAuto_tolerance"], 10, true, true);
		$classifier->train($sample_features, $sample_labels);

		$this->trained_classifier = $classifier;

	}


	public function __construct() {

	}
}