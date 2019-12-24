<?php

declare(strict_types=1);

namespace mlauto\Model;


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

	public function saveClassification(object &$classifier, array $args) {
		global $wpdb;

		$tag_name = $args["tag_name"];
		$taxonomy_name = $args["taxonomy_name"];
		$specified_features = maybe_serialize($args["specified_features"]);
		$accuracy = $args["accuracy"];
		$gamma = $args["gamma"];
		$tolerance = $args["tolerance"];
		$cost = $args["cost"];
		$training_percentage = $args["training_percentage"];

		$custom_name = ($args["custom_name"] ? $args["custom_name"] : current_time( 'timestamp' ));

		$dir_of_serialized_object = 'bin/' . $custom_name . '/'.  $taxonomy_name . '/';

		$create_dir = mkdir( MLAUTO_PLUGIN_URL . $dir_of_serialized_object, 0777, true);


		$location_of_serialized_object = $dir_of_serialized_object . trim($tag_name);


		$classifier->saveToFile(MLAUTO_PLUGIN_URL . $location_of_serialized_object);


		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'created_at' => current_time( 'timestamp' ), 
				'custom_name' => $custom_name,
				'taxonomy_name' => $taxonomy_name,
				'accuracy' => $accuracy,
				'gamma' => $gamma,
				'tolerance' => $tolerance,
				'tag_name' => $tag_name,
				'cost' => $cost,
				'training_percentage' => $training_percentage,
				'location_of_serialized_object' => $location_of_serialized_object,
				'specified_features' => $specified_features,
			) 
		);
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