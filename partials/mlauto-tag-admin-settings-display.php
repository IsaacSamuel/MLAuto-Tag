<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       plugin_name.com/team
 * @since      1.0.0
 *
 * @package    PluginName
 * @subpackage PluginName/admin/partials
 */

use mlauto\Model\ClassificationModel;



$currentConfiguration = $this->getConfig();


//Get taxonomy terms
$taxonomies = get_taxonomies(array("_built_in" => false), "names");
$taxonomy_names = array_keys($taxonomies);
array_unshift($taxonomy_names, "category");
array_unshift($taxonomy_names, "post_tag");

//For now, we're hardcoding the potential features. Not many are supported.
$features = array("post_title", "post_content");

$gamma = ($currentConfiguration["MLAuto_gamma"] == null ? 0 : $currentConfiguration["MLAuto_gamma"] );
$cost = $currentConfiguration["MLAuto_cost"];
$tolerance = $currentConfiguration["MLAuto_tolerance"];
$test_percentage = $currentConfiguration["MLAuto_test_percentage"];


$current_classification = ClassificationModel::getClassificationModel($currentConfiguration["MLAuto_classifier_id"]);

?>


<div class="wrap">
    <div id="icon-themes" class="icon32"></div>  
    <h1>MLAuto Tag: Generate Classifiers</h1>  
	
	<div id="mlauto_error"></div>

	<div class="mlauto_classifier_container">
		<h2>Generate a new classifier</h2>
		<br>

		<form id="mlauto_save_settings_form">
			<div class="mlauto_settings_form_item">
	        	<h3>Classifier Name</h3>
	        	<p>Choose a name for your classifier. <strong>Reusing classifier names deletes classifiers with the same name.</strong> If you leave the field blank, a unique timestamp will be used instead.</p>
	        	<label for="MLAuto_classifier_name"><p><strong>Classifier Name:</strong></p></label>
				<input type="text" name="MLAuto_classifier_name" id="MLAuto_classifier_name"> 
	    	</div>

	    	<div class="mlauto_settings_form_item">
	        	<h3>Taxonomies</h3>
	        	<p>Select the taxonomies you'd like to run the classifier upon. <strong>Note:</strong> The classifier runs a lot better on taxonomies with a lot of examples. Taxonomies with very few matching posts will not be predicted well.</p>
	        	<div class="mlauto_checkbox">
	        		<?php foreach ($taxonomy_names as $name) { ?>
	        			<input type=checkbox name="taxonomies" value=<?php echo '"' . $name . '" ' . (in_array($name, $currentConfiguration["MLAuto_taxonomies"]) ? "checked" : "" ) ?> id = <?php echo $name ?> > 
	        			<label for=<?php echo '"' . $name . '"' ?>><?php echo $name ?> </label>
	        		<?php } ?>
	    		</div>
	    	</div>

	    	<div class="mlauto_settings_form_item">
	    		<h3>Features</h3>
	        	<p>Features are the data that the classifier will use to predict which classification(s) your post belongs in. The more numberous and less random the features, the more accurate the classifier will get in general, but the longer it will take to run.</p>
	    		
	    		<div class="mlauto_checkbox">
	        		<?php foreach ($features as $feature) { ?>
	        			<input type=checkbox name="features" value=<?php echo '"' . $feature . '" ' . (in_array($feature, $currentConfiguration["MLAuto_specified_features"]) ? "checked" : "" ) ?> id = <?php echo $feature ?> > 
	        			<label for=<?php echo '"' . $feature . '"' ?>><?php echo $feature ?> </label>
	        		<?php } ?>
	    		</div>
	    	</div>


	    	<div class="mlauto_settings_form_item">
	    		<h3>Save old classifiers?</h3>
	    		<p>Once a classifier is generated, it is saved to file. Depending on various factors, such as the number of features being used to predict classifications, and the number of classifications, these files can get large and/or numerous. If space is a concern, you shouldn't save old classifiers; you should only keep the most recent one. However, if you testing and fiddling with settings to find an optimal mix of settings, there may be value in keeping old classifiers.</p>
	    		<!--Add current space being taken up-->
	    		  <input type="radio" id="save_old_classifiers" name="MLAuto_save_old_classifiers" value="true"
				         <?php echo (currentConfiguration["MLAuto_save_old_classifiers"] == true ? "checked" : "")?>>
				  <label for="save_old_classifiers">Save old classifiers</label>
				  <br>
				  <input type="radio" id="delete_old_classifiers" name="MLAuto_save_old_classifiers" value="false"
				         <?php echo (currentConfiguration["MLAuto_save_old_classifiers"] == true ? "" : "checked")?> >
				  <label for="delete_old_classifiers">Keep only the most recent classifier</label>
			</div>

			<div class="mlauto_settings_form_item">
	        	<h3>Advanced Options</h3>
	        	<p>These options are for users more acquainted with machine learning and statistical methods. MLAuto Tag uses a Support Vector Machine (SVM) with an RBF kernal. You can adjust the parameters of cost, gamma, and tolerance to tweak the algorithm and optimize it to greatness.</p>
	        	<p>Additionally, you can adjust the size of the test set, which is used to determine how accurate the classifier is. The smaller the test set, the more accurate the classifier is, but the less certain you can be of the feedback.</p>

	        	<input type="number" id="MLAuto_gamma" name="MLAuto_gamma" value=<?php echo $gamma ?>>
	        	<label for="MLAuto_gamma"><strong>Gamma</strong> (Default: 0, meaning (1/features)</label>
	        	<br>

	        	<input type="number" step=".25" id="MLAuto_cost" name="MLAuto_cost" value=<?php echo $cost ?>>
	        	<label for="MLAuto_cost"><strong>Cost</strong> (Default: 1.0)</label>
	        	<br>

	        	<input type="number" id="MLAuto_tolerance" step=".0005" name="MLAuto_tolerance" value=<?php echo $tolerance ?>>
	        	<label for="MLAuto_tolerance"><strong>Tolerance</strong> (Default: .001)</label>

		        	<br>
		        	<input type="number" step=".025" id="MLAuto_test_percentage" name="MLAuto_test_percentage" value=<?php echo $test_percentage ?>>
		        	<label for="MLAuto_test_percentage"><strong>Test Percentage Size</strong> (Default: .2 (20%))</label>
		        </div>
		</form>

        <?php             
			echo "<p><a href='#' id='generate_classifier' class='mlauto_button button button-primary'>Generate Classifier</a></p>";
        ?>  
	</div>

    <div id="current_classifier" class="mlauto_classifier_container">
    	<h2>Current Classifier</h2>
    	<p><strong>Name:</strong> <?php echo  $current_classification->custom_name ?></p>
    	<p><strong>Created at:</strong> <?php echo date("m/d/y g:i A", $classification_model->created_at) ?></p>

    	<div class="mlauto_classifier_info_list">
    		<div class="mlauto_classifier_info_item">
    			<p><strong>Selected Taxonomies:</strong></p>
    			<?php 
    			foreach(maybe_unserialize($current_classification->selected_taxonomies) as $taxonomy) {
    				echo ("<span>" . $taxonomy . "</span>");
    			} ?>
    		</div>

    		<div class="mlauto_classifier_info_item">
    			<p><strong>Selected Features:</strong></p>
    			<?php 
    			foreach(maybe_unserialize($current_classification->specified_features) as $feature) {
    				echo ("<span>" . $feature . "</span>");
    			} ?>
    		</div>

    		<div class="mlauto_classifier_info_item">
    			<p><strong>Advanced settings:</strong></p>
    			<?php 
    				echo "<span><strong>Gamma: </strong>" . $current_classification->gamma . "</span>";
					echo "<span><strong>Tolerance: </strong>" . $current_classification->tolerance . "</span>";
					echo "<span><strong>Training Percentage: </strong>" . $current_classification->training_percentage . "</span>";
					echo "<span><strong>Cost: </strong>" . $current_classification->cost . "</span>";
    			?>
    		</div>
    	</div>
    </div>


    <h2>Past Classifiers</h2>
    <div id="mlauto_past_classifiers">
    	<?php include("mlauto-classifier-brief.php"); ?>
    </div>
</div>