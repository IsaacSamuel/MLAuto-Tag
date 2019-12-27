<?php
/*
Plugin Name: MLAuto Tag
Plugin URI:  https://github.com/IsaacSamuel/MLAuto-Tag
Description: This plugin uses machine learning (php-ml) to automatically categorize posts by taxonomy. Can be used out of the box, or optimized with advanced options.
Version:     0.1
Author:      Isaac Samuel
Author URI:  https://github.com/IsaacSamuel
*/

declare(strict_types=1);

require 'class_loader.php';

use mlauto\Model\PostInfoAggregator;
use mlauto\Model\Classification;


use mlauto\Analysis\Vectorizer;
use mlauto\Analysis\Classifier;



use Phpml\Metric\Accuracy;
use Phpml\Classification\NaiveBayes;

use Phpml\CrossValidation\StratifiedRandomSplit;

use Phpml\Metric\ClassificationReport;



define( 'MLAUTO_PLUGIN_URL', plugin_dir_path( __FILE__ ) );

class MLAuto_Tag {

	private function buildConfig() {

		add_option( "MLAuto_version", "0.1" );

		add_option('MLAuto_taxonomies', array("category"));
		add_option('MLAuto_features', array("post_title"));
		add_option('MLAuto_cost', 1.0);
		add_option('MLAuto_gamma', null);
		add_option('MLAuto_tolerance', .001);
		add_option('MLAuto_cache_size', 100);
		add_option('MLAuto_label_minimum_count', 1);
		add_option('MLAuto_save_old_classifiers', true);

	}

	public function MLAutoTagSettingsMessages($error_message){
         if ($error_message == 1) {
                 $message = 'There was an error adding this setting. Please try again.  If this persists, shoot us an email.';
                 $err_code = esc_attr( 'mlauto_tag_example_setting' );  

                 $setting_field = 'mlauto_tag_example_setting';

         }

         $type = 'error';
         add_settings_error(
                $setting_field,
                $err_code,
                $message,
                $type
            );
     }

	public function displayPluginAdminSettings() {
         require_once 'partials/mlauto-tag-admin-settings-display.php';
    }

	public function addPluginAdminMenu() {
	add_menu_page(  $this->plugin_name, 'MLAuto Tag', 'administrator', $this->plugin_name, array( $this, 'displayPluginAdminSettings' ) );
	}



	private function getConfig() {
		return array (
			"MLAuto_taxonomies" => array("category"),
			"MLAuto_cost" => floatval(get_option('MLAuto_cost')),
			"MLAuto_gamma" => floatval(get_option('MLAuto_gamma')),
			"MLAuto_tolerance" => floatval(get_option('MLAuto_tolerance')),
			"MLAuto_cache_size" => intval(get_option('MLAuto_cache_size')),
			"MLAuto_save_old_classifiers" => get_option('MLAuto_save_old_classifiers', false),
			"MLAuto_specified_features" => get_option('MLAuto_features'),
			"MLAuto_label_minimum_count" => get_option('MLAuto_label_minimum_count')
		);
	}

	function runClassifier() {

		$args = $this->getConfig();

		$taxonomies = $args["taxonomies"];//, "post_tag");

		$info = new PostInfoAggregator($taxonomies);

		$vectorizer = new Vectorizer($info->features);


		for ($i=0; $i < count($taxonomies); $i++) { 

			foreach($info->targets_collection[$i] as $target) {

				$labels = array_column($info->labels_collection, $i);

				$vectorized_labels = $vectorizer->vectorize_labels($labels, $target);


				$train_samples = array_slice($vectorizer->vectorized_samples, 0, 60);
				$train_labels = array_slice($vectorized_labels, 0, 60);

				$test_samples = array_slice($vectorizer->vectorized_samples, 60);
				$test_labels = array_slice($vectorized_labels, 60);

				$classifier = new Classifier($train_samples, $train_labels, $args);
				$classifier->trainClassifier($train_samples, $train_labels, $args);

				$predictedLabels = $classifier->predict($test_samples, $test_labels);

				echo 'Target: ' . $target . " " . Accuracy::score($test_labels, $predictedLabels, true) . "<br>";

				$args["MLAuto_taxonomy_name"] = $taxonomies[$i];
				$args["MLAuto_accuracy"] = Accuracy::score($test_labels, $predictedLabels, true);
				$args["MLAuto_tag_name"] = $target;
				$args["MLAuto_training_percentage"] = .75;

				//Classification::saveClassification($classifier, $args);



			}
			wp_die();

		}
	}

	function init() {
		$this->buildConfig();
		Classification::intializeTable();
	}

	public function __construct() {
		$this->init();


		//Add actions and hooks
		add_action('admin_menu', array( $this, 'addPluginAdminMenu' ), 9);    

	}

}

//add_action('init', array(__CLASS__, 'main'));

new MLAuto_Tag();

?>