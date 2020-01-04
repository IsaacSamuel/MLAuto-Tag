<?php

declare(strict_types=1);

namespace mlauto\Model;

use mlauto\Wrapper\Term;



class TermModel {

	public static function intializeTable(){
		global $wpdb;
		$charset_collate = $wpdb->get_charset_collate();
		$table_name = $wpdb->prefix . 'MLAutoTag_Terms';
		$classification_table_name = $wpdb->prefix . 'MLAutoTag_Classifications';

		//If table does not exist
		if(!$wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name) {

			$sql = "CREATE TABLE $table_name (
							  id mediumint(9) NOT NULL AUTO_INCREMENT,
							  classification_id mediumint(9),
					  		  taxonomy_name tinytext NOT NULL,
					          tag_name tinytext NOT NULL,
					          accuracy float,

					          FOREIGN KEY (classification_id) REFERENCES $classification_table_name(id),
							  PRIMARY KEY  (id)
							) $charset_collate;";	

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	public static function saveTerms(Object $term){
		global $wpdb;

		//Save Classification to file
		/*$dir_of_serialized_object = 'bin/' . $custom_name . '/'.  $term->taxonomy . '/';
		$create_dir = mkdir( MLAUTO_PLUGIN_URL . $dir_of_serialized_object, 0777, true);

		$term->setPath(MLAUTO_PLUGIN_URL . "bin/" . $custom_name);
		$term->saveToFile();*/


		//Save Classification to DB
		$table_name = $wpdb->prefix . 'MLAutoTag_Terms';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'classification_id' => current_time( 'mysql' ), 
				'taxonomy_name' => $term->taxonomy,
				'accuracy' => $term->getAccuracy(),
				'term_name' => $term->name,
			) 
		);
	}


}

?>