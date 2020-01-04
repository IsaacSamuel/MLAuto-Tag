<?php 


declare(strict_types=1);

namespace mlauto\Wrapper;


class Term {
	public $name;
	public $slug;
	public $taxonomy;

	private $path;

	public function getPath() {
		if (isset($this->path)) {
			return $this->path;
		}
		return false;
	}

	public function setPath(String $classifierPath) {
		$this->path = $classifierPath . "/" . $this->taxonomy . "/" . $this->slug;
	}

	public function __construct($slug, $taxonomyName) {
		$this->taxonomy = $taxonomyName;
		$this->slug = $slug;
	}
	
}