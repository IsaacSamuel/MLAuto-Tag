<?php

declare(strict_types=1);

namespace mlauto\Model;

use mlauto\Wrapper\Term;


class Classification {

	public static function intializeTable() {

		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';

		//If table does not exist
		if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {	

			$sql = "CREATE TABLE $table_name (
				  id mediumint(9) NOT NULL AUTO_INCREMENT,
				  created_at datetime NOT NULL,
				  custom_name tinytext NOT NULL,
				  taxonomy_name tinytext NOT NULL,
				  tag_name tinytext NOT NULL,
				  location_of_serialized_object tinytext NOT NULL,
				  accuracy float,
				  gamma float,
				  cost float,
				  tolerance float,
				  training_percentage float,
				  specified_features TEXT,
				  PRIMARY KEY  (id)
				) $charset_collate;";

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}

	}

	public static function saveClassification(Term &$term, array $args, $custom_name) {
		global $wpdb;

		$specified_features = maybe_serialize($args["MLAuto_specified_features"]);
		$gamma = $args["MLAuto_gamma"];
		$tolerance = $args["MLAuto_tolerance"];
		$cost = $args["MLAuto_cost"];
		$training_percentage = $args["MLAuto_test_percentage"];


		//Save Classification to file
		$dir_of_serialized_object = 'bin/' . $custom_name . '/'.  $term->taxonomy . '/';
		$create_dir = mkdir( MLAUTO_PLUGIN_URL . $dir_of_serialized_object, 0777, true);

		$term->setPath(MLAUTO_PLUGIN_URL . "bin/" . $custom_name);
		$term->saveToFile();


		//Save Classification to DB
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'created_at' => current_time( 'mysql' ), 
				'custom_name' => $custom_name,
				'taxonomy_name' => $term->taxonomy,
				'accuracy' => $term->getAccuracy(),
				'gamma' => $gamma,
				'tolerance' => $tolerance,
				'tag_name' => $term->name,
				'cost' => $cost,
				'training_percentage' => $training_percentage,
				'location_of_serialized_object' => $dir_of_serialized_object . $term->name,
				'specified_features' => $specified_features,
			) 
		);
	}


	//Instead, get location by DB custom_name
	public static function getClassifications($classifier_name) {
		/*Retval format 
			{
				"taxonomy1"
					{
						"term1" : filepath
						"term2" : filepath
						...
					}
				"taxonomy2"
					{...}
				...
			}
		*/

		global $wpdb;


		$classifier_terms = array();

		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';


		if (isset($classifier_name)) {
			$classifications = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					FROM $table_name
					WHERE custom_name = %s",
					$classifier_name),
					OBJECT);
		}
		else {
			//Barring a specified classifier, just use the most recent
			$most_recent_classifier = $wpdb->get_var(
					"SELECT custom_name
					FROM $table_name
					ORDER BY created_at DESC
					LIMIT 1"
				);

			$classifications = $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					FROM $table_name
					WHERE custom_name = %s",
					$most_recent_classifier),
					OBJECT);
		}

		if (count($classifications) == 0) {
			throw new Exception("Could not find classification in db: " . $classification);
		}

		return $classifications;
	}


	public function deleteClassification(int $id) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';

		//get custom_name of matching classification

		//recursively delete all in that folder

		//if success
		
		$wpdb->delete( 
			$table_name, 
			array( 
				'id' => $id
			) 
		);
	}





}