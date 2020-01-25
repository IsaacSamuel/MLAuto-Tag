<?php

use mlauto\Model\ClassificationModel;

$classification_models = ClassificationModel::getClassificationModels();

$currentConfiguration = $this->getConfig();

foreach ($classification_models as $classification_model) { 
	if ($classification_model->id != $currentConfiguration["MLAuto_classifier_id"]) { ?>
		<div class="mlauto_past_classifier" id=<?php echo 'mlauto_classifier_model_' . $classification_model->id ?> >
			<h3><?php echo  $classification_model->custom_name ?></h3>
			<p><strong>Created at:</strong> <?php echo date("m/d/y g:i A", strtotime($classification_model->created_at)) ?></p>

			<div class="mlauto_classifier_info_list">
				<div class="mlauto_classifier_info_item">
					<p><strong>Selected Taxonomies:</strong></p>
					<?php 
					foreach(maybe_unserialize($classification_model->selected_taxonomies) as $taxonomy) {
						echo ("<span>" . $taxonomy . "</span>");
					} ?>
				</div>

				<div class="mlauto_classifier_info_item">
					<p><strong>Selected Features:</strong></p>
					<?php 
					foreach(maybe_unserialize($classification_model->specified_features) as $feature) {
						echo ("<span>" . $feature . "</span>");
					} ?>
				</div>
				<div class="mlauto_classifier_info_item">
					<p><strong>Advanced settings:</strong></p>
					<?php 
						echo "<p><strong>Gamma: </strong>" . $classification_model->gamma . "</p>";
						echo "<p><strong>Tolerance: </strong>" . $classification_model->tolerance. "</p>";
						echo "<p><strong>Training Percentage: </strong>" . $classification_model->training_percentage . "</p>";
						echo "<p><strong>Cost: </strong>" . $classification_model->cost . "</p>";
					?>
				</div>
			</div>
			<table class="data_display"><!-- The datatable will go here. !--></table>
			<?php

			echo "<a href='#' class='mlauto_button mlauto_get_term_data button button-primary' value='" . $classification_model->id . "'>Get Classifier Data</a>";


			echo "<a href='#' class='mlauto_button mlauto_select_classifer button button-primary' value='" . $classification_model->id . "'>Select Classifier</a>";

			echo "<a href='#' class='mlauto_button mlauto_delete_classifer button button-primary' value='" . $classification_model->id . "'>Delete Classifier</a>";

			?>
		</div>
	<?php
	}
}

?>