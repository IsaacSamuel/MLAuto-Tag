<?php

declare(strict_types=1);

namespace mlauto\Controller;

use Phpml\Classification\SVC;
use Phpml\SupportVectorMachine\Kernel;


class Classifier {

	private $trained_classifier;


	public function predict(array &$test_features) {


		$predictedLabels = $this->trained_classifier->predict($test_features);


		return $predictedLabels;
	}


	private function buildClassifier(array &$sample_features, array &$sample_labels) {

		//var_dump($sample_labels);

		$classifier = new SVC(Kernel::RBF, 1.0, 3, null, 0.0, .001, 100, false, true);
		$classifier->train($sample_features, $sample_labels);

		$this->trained_classifier = $classifier;

	}


	public function __construct(array &$sample_features, array &$sample_outputs) {

		$this->buildClassifier($sample_features, $sample_outputs);

	}
}