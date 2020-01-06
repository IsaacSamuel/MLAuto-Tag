<?php

declare(strict_types=1);

namespace mlauto\Model;

use mlauto\Wrapper\Term;

use mlauto\Model\ClassificationModel;



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
					          term_name tinytext NOT NULL,
					          accuracy float,

							  PRIMARY KEY  (id)
							) $charset_collate;";	

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	public static function saveTermModel(Object $term){
		global $wpdb;

		$term->saveToFile();

		//Save Classification to DB
		$table_name = $wpdb->prefix . 'MLAutoTag_Terms';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'classification_id' => $term->getClassificationId(), 
				'taxonomy_name' => $term->taxonomy,
				'accuracy' => $term->getAccuracy(),
				'term_name' => $term->name,
			) 
		);
	}


	public static function getTerms(int $classification_id) {
		global $wpdb;

		$table_name = $wpdb->prefix . 'MLAutoTag_Terms';

		return $wpdb->get_results( $wpdb->prepare(
					"SELECT * 
					FROM $table_name
					WHERE classification_id = %d",
					$classification_id));
	}
}

?>