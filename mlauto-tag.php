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



use mlauto\Model\ClassificationModel;
use mlauto\Model\TermModel;



class MLAuto_Tag {

	private function buildConfig() {

		add_option( "MLAuto_version", "0.1" );

		add_option('MLAuto_taxonomies', array("category"));
		add_option('MLAuto_specified_features', array("post_title"));
		add_option('MLAuto_test_percentage', .3);
		add_option('MLAuto_cost', 1.0);
		add_option('MLAuto_gamma', null);
		add_option('MLAuto_tolerance', .001);
		add_option('MLAuto_cache_size', 100);
		add_option('MLAuto_label_minimum_count', 1);
		add_option('MLAuto_save_old_classifiers', true);
		add_option('MLAuto_classifier_id', null);

	}


    public function enqueueAdminScripts() {
		wp_enqueue_style( 'mlauto-admin-style', plugins_url('static/css/style.css', __FILE__));
		wp_enqueue_style( 'jquery.dataTables-style', plugins_url('static/css/jquery.dataTables.min.css', __FILE__));


   		wp_enqueue_script( 'jquery');
   		wp_enqueue_script( 'jquery.dataTables', plugins_url('static/js/jquery.dataTables.min.js', __FILE__), array ( 'jquery' ), 1.1, true);

    	wp_enqueue_script( 'mlauto-settings', plugins_url('static/js/settings.js', __FILE__), array ( 'jquery', 'jquery.dataTables' ), 1.1, true);
    	wp_enqueue_script( 'mlauto-classify-post', plugins_url('static/js/classify_post.js', __FILE__), array ( 'jquery' ), 1.1, true);


    	wp_localize_script( 'mlauto-settings', 'MLAuto_Ajax_Settings', array(
		    'ajaxurl'    => admin_url( 'admin-ajax.php' ),
		) );

		wp_localize_script( 'mlauto-classify-post', 'MLAuto_Ajax_Settings', array(
		    'ajaxurl'    => admin_url( 'admin-ajax.php' ),
		) );
    }

    public function metaboxSaveMeta( $post_id ) {

    	$data = $_POST;

    	$post = get_post($post_id);

    	$args = $this->getConfig();

    	$taxonomies = $args["MLAuto_taxonomies"];

    	foreach($taxonomies as $taxonomy) {
    		//get all terms
    		$all_terms = get_terms(array('taxonomy' => $taxonomy));

    		foreach($all_terms as $term) {
    			//This is the id we used on the form
    			$id_name = $taxonomy . "||" . $term->slug;

    			//If it is checked on the form, associate the term with the post
    			if (isset($data[$id_name])){
    				wp_set_post_terms($post_id, $term->term_id, $taxonomy, true );
    			}
    			//If it's not checked on the form, unassociate the term with the post
    			else {
    				wp_remove_object_terms($post_id, $term->term_id, $taxonomy);
    			}
    		}
    	}
    }


    public function displayPluginMetaBox() {
    	require_once 'partials/mlauto-meta-box.php';
    }

    public function addPluginMetaBox() {
		add_meta_box( 'mlauto-classify-post', // ID attribute of metabox
                  'MLAuto Classify Post',       // Title of metabox visible to user
                  array($this, 'displayPluginMetaBox'), // Function that prints box in wp-admin
                  'post',              // Show box for posts, pages, custom, etc.
                  'normal',            // Where on the page to show the box
                  'low' );            // Priority of box in display order
    }

	public function displayPluginAdminSettings() {
         require_once 'partials/mlauto-tag-admin-settings-display.php';
    }

	public function addPluginAdminMenu() {
	add_menu_page(  $this->plugin_name, 'MLAuto Tag', 'administrator', "mlauto-tag", array( $this, 'displayPluginAdminSettings' ) );
	}


	public static function getConfig() {
		return array (
			"MLAuto_taxonomies" => get_option('MLAuto_taxonomies'),
			"MLAuto_cost" => floatval(get_option('MLAuto_cost')),
			"MLAuto_gamma" => floatval(get_option('MLAuto_gamma')),
			"MLAuto_tolerance" => floatval(get_option('MLAuto_tolerance')),
			"MLAuto_cache_size" => intval(get_option('MLAuto_cache_size')),
			"MLAuto_save_old_classifiers" => (get_option('MLAuto_save_old_classifiers') == "false" ? false : true) ,
			"MLAuto_specified_features" => get_option('MLAuto_specified_features'),
			"MLAuto_label_minimum_count" => get_option('MLAuto_label_minimum_count'),
			"MLAuto_test_percentage" => floatval(get_option('MLAuto_test_percentage')),
			"MLAuto_classifier_name" => get_option("MLAuto_classifier_name"),
			"MLAuto_classifier_id" => intval(get_option("MLAuto_classifier_id"))
		);
	}


	private function init() {
		//If we haven't set the default classification configuration, configure it
		$GLOBALS["mlauto_db_version"] = 1.0;

		//If SQL Table isn't initiated, initiate it
		ClassificationModel::intializeTable();
		TermModel::intializeTable();

		$this->buildConfig();

		update_option("MLAuto_version", $GLOBALS["mlauto_db_version"]);
	}

	public function __construct() {

		$this->init();

		new MLAuto_Tag_Ajax_Hooks();

		//Add actions and hooks
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueueAdminScripts'));

		add_action('admin_menu', array( $this, 'addPluginAdminMenu' )); 
		add_action('add_meta_boxes', array($this, 'addPluginMetaBox') );

		add_action('save_post', array($this, 'metaboxSaveMeta'));
	}

}

new MLAuto_Tag();

?>