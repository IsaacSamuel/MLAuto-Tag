<?php 


declare(strict_types=1);

namespace mlauto\Wrapper;

use mlauto\Analysis\Classifier;


class Term {
	public $name;
	public $taxonomy;
	public $predicted_probability;

	private $path;
	private $accuracy;
	private $classifier;
	private $classification_id;


	public function setPath(String $classifierPath) {
		$this->path = $classifierPath . $this->taxonomy . "/" . $this->name;
	}

	public function getPath() {
		if (isset($this->path)) {
			return $this->path;
		}
		return false;
	}

	public function setClassifier(Classifier &$classifier, int $id) {
		$this->classifier = $classifier;
		$this->classification_id = $id;
	}

	public function getClassifier() {
		if (isset($this->classifier)) {
			return $this->classifier;
		}
		return false;
	}

	public function getClassificationId() {
		if (isset($this->classification_id)) {
			return $this->classification_id;
		}
		return false;
	}

	public function setAccuracy(float $accuracy) {
		$this->accuracy = $accuracy;
	}

	public function getAccuracy() {
		if (isset($this->accuracy)) {
			return $this->accuracy;
		}
		return $this->accuracy;
	}


	public function saveToFile(){
		if (isset($this->classifier)) {
			$this->classifier->saveToFile($this->path);
		}
	}

	public function loadClassifier() {
		//Restore the saved classifier from file
		$classifier = new Classifier();
		$classifier->restore($this->path);

		$this->classifier = $classifier;
	}

	public function predictProbability(&$vectorized_samples) {
		$this->predicted_probability = $this->classifier->predictProbability($vectorized_samples)[0];
	}


	public function __construct($name, $taxonomyName) {
		$this->taxonomy = $taxonomyName;
		$this->name = $name;
	}
	
}