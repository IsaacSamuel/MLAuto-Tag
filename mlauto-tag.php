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
		add_option('MLAuto_delete_old_classifiers', false);

	}

	private function getConfig() {
		return array (
			"taxonomies" => array("category"),
			"cost" => floatval(get_option('MLAuto_cost')),
			"gamma" => floatval(get_option('MLAuto_gamma')),
			"tolerance" => floatval(get_option('MLAuto_tolerance')),
			"cache_size" => intval(get_option('MLAuto_cache_size')),
			"delete_old_classifiers" => get_option('MLAuto_delete_old_classifiers', false),
			"specified_features" => get_option('MLAuto_features'),
			"label_minimum_count" => get_option('MLAuto_label_minimum_count')
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

				$args["taxonomy_name"] = $taxonomies[$i];
				$args["accuracy"] = Accuracy::score($test_labels, $predictedLabels, true);
				$args["tag_name"] = $target;
				$args["training_percentage"] = .75;

				//Classification::saveClassification($classifier, $args);

				//var_dump($predictedLabels);


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

		$this->runClassifier();

	}

}

//add_action('init', array(__CLASS__, 'main'));

new MLAuto_Tag();

/*
function wporg_settings_init()
{
    // register a new setting for "reading" page
    register_setting('reading', 'wporg_setting_name');
 
    // register a new section in the "reading" page
    add_settings_section(
        'wporg_settings_section',
        'WPOrg Settings Section',
        'wporg_settings_section_cb',
        'reading'
    );
 
    // register a new field in the "wporg_settings_section" section, inside the "reading" page
    add_settings_field(
        'wporg_settings_field',
        'WPOrg Setting',
        'wporg_settings_field_cb',
        'reading',
        'wporg_settings_section'
    );
}
 
/**
 * register wporg_settings_init to the admin_init action hook
 
add_action('admin_init', 'wporg_settings_init');


// section content cb
function wporg_settings_section_cb()
{
    echo '<p>WPOrg Section Introduction.</p>';
}
 
// field content cb
function wporg_settings_field_cb()
{
    // get the value of the setting we've registered with register_setting()
    $setting = get_option('wporg_setting_name');
    // output the field
    ?>
    <input type="text" name="wporg_setting_name" value="<?php echo isset( $setting ) ? esc_attr( $setting ) : ''; ?>">
    <?php
}
*/

?>