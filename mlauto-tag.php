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


use mlauto\Post_Info_Aggregator;

use Phpml\FeatureExtraction\TokenCountVectorizer;
use Phpml\Tokenization\WhitespaceTokenizer;

use Phpml\Dataset\ArrayDataset;
use Phpml\CrossValidation\StratifiedRandomSplit;


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



	public function vectorize($info) :void {

		//for each in outputs
		$this->dataset = new ArrayDataset($info->features, array_column($info->outputs, 0));

		echo $this->__toString();

		
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