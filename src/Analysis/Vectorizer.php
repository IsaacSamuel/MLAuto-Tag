<?php 

declare(strict_types=1);

namespace mlauto\Analysis;

use Phpml\Dataset\ArrayDataset;
use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;



class Vectorizer {

	public $vectorized_samples;


	private function contains_term($term_list, $term) : int {

		$contains = 0;

		if (strpos($term_list, $term) !== false) {
			$contains = 1;
		}

		return $contains;
	}

	public function vectorize_labels(array &$labels, String $target) : array {

		$vectorized_labels = array();

		foreach ($labels as $key) {
			array_push($vectorized_labels, $this->contains_term($key, $target));
		}

		return $vectorized_labels;

	}


	private function vectorize_samples($sample_features) : array {

		$sample_vectorizer = new TokenCountVectorizer(new WhitespaceTokenizer());
		$sample_vectorizer->fit($sample_features);
		$sample_vectorizer->transform($sample_features);

		return $sample_features;
	}

	public function __construct(array $sample_features) {

		$this->vectorized_samples = $this->vectorize_samples($sample_features);

	}


}