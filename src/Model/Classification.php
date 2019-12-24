<?php

declare(strict_types=1);

namespace mlauto\Model;

	//Classification table
	//name of taxonomy
	//name of tag
	//location of classifier
	//date
	//version
	//specified features
	//runtime

class Classification {

	public static function intializeTable() {
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';

		$sql = "CREATE TABLE $table_name (
			  id mediumint(9) NOT NULL AUTO_INCREMENT,
			  created_at datetime NOT NULL,
			  custom_name tinytext NOT NULL,
			  taxonomy_name tinytext NOT NULL,
			  tag_name tinytext NOT NULL,
			  location_of_serialized_object tinytext NOT NULL,
			  accuracy float,
			  specified_features TEXT,
			  PRIMARY KEY  (public_key)
			) $charset_collate;";

		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	}

	public function saveClassification(array $args) {
		global $wpdb;

		//$tag_name = $args["tag_name"];
		//$taxonomy_name = $args["taxonomy_name"];
		//$specified_features = impolode($args["specified_features"], ", ");
		//accuracy = $args["accuracy"];

		//$custom_name = ($args["custom_name"] ? $args["custom_name"] : timestamp);

		//$location_of_serialized_object = custom_name / taxonomy_name / tag_name

		
		$table_name = $wpdb->prefix . 'MLAutoTag_Classifications';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'created_at' => current_time( 'mysql' ), 
				'custom_name' => $custom_name,
				'taxonomy_name' => $taxonomy_name,
				'accuracy' => $accuracy,
				'tag_name' => $tag_name,
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
				'id' => $id;
			) 
		);
	}





}