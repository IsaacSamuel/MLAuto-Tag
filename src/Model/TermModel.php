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
		if(!is_null(get_option("MLAuto_version")) || $GLOBALS["mlauto_db_version"] > get_option("MLAuto_version")) {

			$sql = "CREATE TABLE $table_name (
							  id mediumint(9) NOT NULL AUTO_INCREMENT,
							  classification_id mediumint(9),
					  		  taxonomy_name tinytext NOT NULL,
					          term_name tinytext NOT NULL,
					          accuracy float,
					          total mediumint(9),
					          positives mediumint(9),
					          negatives mediumint(9),
					          true_positives mediumint(9),
					          true_negatives mediumint(9),
					          false_positives mediumint(9),
					          false_negatives mediumint(9),
							  PRIMARY KEY  (id)
							) $charset_collate;";	

			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( $sql );
		}
	}

	public static function saveTermModel(Object $term) {
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
				'total' => $term->total_samples,
				'positives' => $term->positives,
				'negatives' => $term->negatives,
				'true_positives' => $term->true_positives,
				'true_negatives' =>  $term->true_negatives,
				'false_positives' =>  $term->false_positives,
				'false_negatives' =>  $term->false_negatives,
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