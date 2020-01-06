<?php

declare(strict_types=1);

namespace mlauto\Model;

use mlauto\Wrapper\Term;

global $wpdb;


class ClassificationModel {

	public static function intializeTable() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';


		$charset_collate = $wpdb->get_charset_collate();

		//If table does not exist
		if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {	

			$sql = "CREATE TABLE $table_name (
							  id mediumint(9) NOT NULL AUTO_INCREMENT,
							  created_at datetime NOT NULL,
							  custom_name tinytext NOT NULL,
							  classifier_directory tinytext NOT NULL,
							  active bool,
							  gamma float,
							  cost float,
							  tolerance float,
							  training_percentage float,
							  specified_features TEXT,
							  selected_taxonomies TEXT,
							  PRIMARY KEY  (id)
							) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}


	public static function saveClassificationModel(array $args) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';

		$specified_features = maybe_serialize($args["MLAuto_specified_features"]);
		$selected_taxonomies = maybe_serialize($args["MLAuto_taxonomies"]);
		$gamma = $args["MLAuto_gamma"];
		$tolerance = $args["MLAuto_tolerance"];
		$cost = $args["MLAuto_cost"];
		$training_percentage = $args["MLAuto_test_percentage"];
		$custom_name = $args["MLAuto_classifier_name"];

		//Create classifier directory and taxonomy directories
		$classifier_directory = 'bin/' . $custom_name . '/';
		foreach($args["MLAuto_taxonomies"] as $taxonomy) {
			mkdir( MLAUTO_PLUGIN_URL . $classifier_directory . $taxonomy, 0777, true);
		}


		//Save Classification to DB
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'created_at' => current_time( 'mysql' ), 
				'custom_name' => $custom_name,
				'gamma' => $gamma,
				'tolerance' => $tolerance,
				'cost' => $cost,
				'active' => true,
				'training_percentage' => $training_percentage,
				'classifier_directory' => $classifier_directory,
				'specified_features' => $specified_features,
				'selected_taxonomies' => $selected_taxonomies,
			) 
		);

		//return id of new classifier
		return $wpdb->get_row(
					"SELECT *
					FROM $table_name
					ORDER BY created_at DESC
					LIMIT 1", OBJECT
		);

	}


	public static function getClassificationModel(int $classifier_id) {
		global $wpdb;
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';


		if ($classifier_id !== 0) {
			$classifications = $wpdb->get_row( $wpdb->prepare(
					"SELECT * 
					FROM $table_name
					WHERE id = %d",
					$classifier_id),
					OBJECT);
		}
		else {
			//Barring a specified classifier, just use the most recent
			$classifications = $wpdb->get_row(
					"SELECT * 
					FROM $table_name
					ORDER BY created_at DESC
					LIMIT 1",
					OBJECT);
		}

		return $classifications;
	}

	public static function getClassificationModels() {
		global $wpdb;
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';


		$classifications = $wpdb->get_results(
					"SELECT * 
					FROM $table_name
					WHERE active = true
					ORDER BY created_at DESC",
					OBJECT);

		return $classifications;
	}

	public static function deleteDirectory(String $dir) {
	    if (!file_exists($dir)) {
	        return true;
	    }

	    if (!is_dir($dir)) {
	        return unlink($dir);
	    }

	    foreach (scandir($dir) as $item) {
	        if ($item == '.' || $item == '..') {
	            continue;
	        }

	        if (!ClassificationModel::deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
	            return false;
	        }

	    }

	    return rmdir($dir);
	}


	public static function deleteClassificationModel(int $classification_id) {
		global $wpdb;

		$classification = ClassificationModel::getClassificationModel($classification_id);

		ClassificationModel::deleteDirectory(MLAUTO_PLUGIN_URL . $classification->classifier_directory);


		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';
		
		$wpdb->update( 
			$table_name, 
			array( 
				'active' => false
			),
			array( 
				'id' => $classification->id
			)
		);
	}

}