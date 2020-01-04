<?php 


declare(strict_types=1);

namespace mlauto\Wrapper;


class Term {
	public $name;
	public $taxonomy;

	private $path;
	private $accuracy;

	public function setPath(String $classifierPath) {
		$this->path = $classifierPath . "/" . $this->taxonomy . "/" . $this->name;
	}

	public function getPath() {
		if (isset($this->path)) {
			return $this->path;
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


	public function __construct($name, $taxonomyName) {
		$this->taxonomy = $taxonomyName;
		$this->name = $name;
	}
	
}