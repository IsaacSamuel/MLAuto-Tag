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

	public static function saveClassification(object &$classifier, Term $term, array $args, $custom_name) {
		global $wpdb;

		$specified_features = maybe_serialize($args["MLAuto_specified_features"]);
		$gamma = $args["MLAuto_gamma"];
		$tolerance = $args["MLAuto_tolerance"];
		$cost = $args["MLAuto_cost"];
		$training_percentage = $args["MLAuto_test_percentage"];


		$dir_of_serialized_object = 'bin/' . $custom_name . '/'.  $term->taxonomy . '/';

		$create_dir = mkdir( MLAUTO_PLUGIN_URL . $dir_of_serialized_object, 0777, true);


		$term->setPath(MLAUTO_PLUGIN_URL . "bin/" . $custom_name);
		$classifier->saveToFile($term->getPath());


		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'created_at' => current_time( 'timestamp' ), 
				'custom_name' => $custom_name,
				'taxonomy_name' => $term->taxonomy,
				'accuracy' => $term->getAccuracy(),
				'gamma' => $gamma,
				'tolerance' => $tolerance,
				'tag_name' => $term->name,
				'cost' => $cost,
				'training_percentage' => $training_percentage,
				'location_of_serialized_object' => $term->getPath(),
				'specified_features' => $specified_features,
			) 
		);
	}

	public static function getClassifierTerms($classifier_name) {
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

		$classifier_terms = array();

		//By default, we just select the most recent classifier
		if (!isset($classifier_name)) {
			$classifier_name = end(scandir(MLAUTO_PLUGIN_URL . "bin"));
		}

		//Get the names of the taxonomy directories
		$taxonomy_directory_names = scandir(MLAUTO_PLUGIN_URL . "bin/" . $classifier_name);

		//remove the filenames that represtent current and parent directory
		$taxonomy_directory_names = array_diff($taxonomy_directory_names, [".", ".."]);


		foreach($taxonomy_directory_names as $taxonomy_directory_name) {

			$taxonomy_filenames = scandir(MLAUTO_PLUGIN_URL . "bin/" . $classifier_name . "/" . $taxonomy_directory_name );

			$taxonomy_filenames = array_diff($taxonomy_filenames, [".", ".."]);

			foreach ($taxonomy_filenames as $filename) {

				$term = new Term($filename, $taxonomy_directory_name);

				$term->setPath(MLAUTO_PLUGIN_URL . "bin/" . $classifier_name);

				array_push($classifier_terms, $term);

			}
	
		}

		return $classifier_terms;
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