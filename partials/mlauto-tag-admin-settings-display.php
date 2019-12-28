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


$currentConfiguration = $this->getConfig();


//Get taxonomy terms
$taxonomies = get_taxonomies(array("_built_in" => false), "names");
$taxonomy_names = array_keys($taxonomies);
array_unshift($taxonomy_names, "category");
array_unshift($taxonomy_names, "post_tags");

//For now, we're hardcoding the potential features. Not many are supported.
$features = array("post_title", "post_content");


$gamma = ($currentConfiguration["MLAuto_gamma"] == null ? 0 : $currentConfiguration["MLAuto_gamma"] );
$cost = $currentConfiguration["MLAuto_cost"];
$tolerance = $currentConfiguration["MLAuto_tolerance"];

?>

<div class="wrap">
		        <div id="icon-themes" class="icon32"></div>  
		        <h2>MLAuto Tag Settings</h2>  
				<?php settings_errors(); ?>  
		        <form id="save_settings_form">  

		        	<h3>Taxonomies</h3>
		        	<p>Select the taxonomies you'd like to run the classifier upon. <strong>Note:</strong> The classifier runs a lot better on taxonomies with a lot of examples.</p>
	        		<?php foreach ($taxonomy_names as $name) { ?>
	        			<input type=checkbox name="taxonomies" value=<?php echo '"' . $name . '" ' . (in_array($name, $currentConfiguration["MLAuto_taxonomies"]) ? "checked" : "" ) ?> id = <?php echo $name ?> > 
	        			<label for=<?php echo '"' . $name . '"' ?>><?php echo $name ?> </label>
	        			</br>
	        		<?php } ?>
	        		<br>

	        		<h3>Features</h3>
		        	<p>Features are the data that the classifier will use to predict which classification(s) your post belongs in. The more features, the more accurate the classifier will get in general, but the longer it will take to run.</p>
	        		
	        		<?php foreach ($features as $feature) { ?>
	        			<input type=checkbox name="features" value=<?php echo '"' . $feature . '" ' . (in_array($feature, $currentConfiguration["MLAuto_specified_features"]) ? "checked" : "" ) ?> id = <?php echo $feature ?> > 
	        			<label for=<?php echo '"' . $feature . '"' ?>><?php echo $feature ?> </label>
	        			</br>
	        		<?php } ?>
	        		<br>

	        		<h3>Save old classifiers?</h3>
	        		<p>Once a classifier is generated, it is saved to file Depending on various factors, such as the number of features being used to predict classifications, and the number of classifications, these files can get large and/or numerous. If space is a concern, you shouldn't save old classifiers; you should only keep the most recent one. However, if you testing and fiddling with settings to find an optimal mix of settings, there may be value in keeping old classifiers.</p>
	        		<!--Add current space being taken up-->
	        		  <input type="radio" id="save_old_classifiers" name="MLAuto_save_old_classifiers" value="true"
					         <?php echo (currentConfiguration["MLAuto_save_old_classifiers"] == true ? "checked" : "")?>>
					  <label for="save_old_classifiers">Save old classifiers</label>
					  <br>
					  <input type="radio" id="delete_old_classifiers" name="MLAuto_save_old_classifiers" value="false"
					         <?php echo (currentConfiguration["MLAuto_save_old_classifiers"] == true ? "" : "checked")?> >
					  <label for="delete_old_classifiers">Keep only the most recent classifier</label>


		        	<h3>Advanced Options</h3>
		        	<p>These options are for users more acquainted with machine learning and statistical methods. MLAuto Tag uses a Support Vector Machine (SVM) with an RBF kernal. You can adjust the parameters of cost, gamma, and tolerance to tweak the algorithm and optimize it to greatness.</p>

		        	<input type="number" id="MLAuto_gamma" name="MLAuto_gamma" value=<?php echo $gamma ?>>
		        	<label for="MLAuto_gamma">Gamma (Default: 0, meaning (1/features)</label>

		        	<input type="number" id="MLAuto_cost" name="MLAuto_cost" value=<?php echo $cost ?>>
		        	<label for="MLAuto_cost">Cost (Default: 1.0)</label>

		        	<input type="number" id="MLAuto_tolerance" name="MLAuto_tolerance" value=<?php echo $tolerance ?>>
		        	<label for="MLAuto_tolerance">Tolerance (Default: .001)</label>


		            <?php 
			            echo "<p><a href='#' id='save_settings' class='button button-primary'>Save Settings</a></p>";

		            ?>  
		        </form> 
</div>