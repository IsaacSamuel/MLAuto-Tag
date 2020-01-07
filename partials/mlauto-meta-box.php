<?php 
$currentConfiguration = $this->getConfig();

$current_classifier_id = $currentConfiguration["MLAuto_classifier_id"];

$postID = get_the_ID();

if ($current_classifier_id !== null) { ?>
	<div id="mlauto-meta-box" rel=<?php echo '"' . $postID . '"'; ?>>
		<h3>MLAuto: Run Classifier</h3>
		<div id="mlauto-meta-box-content">
			<p>To get projected tags and classifications for this post, click the button below.</p>
		</div>				
		<p>
			<a href='#' id='classify_post' class='button button-primary'>Classify Post</a>
		</p>
	</div>

<?php
}
else {  ?>
	<div id="mlauto-meta-box" rel=<?php echo '"' . $postID . '"'; ?>>
		<h3>MLAuto: No Classifier Found</h3>
		<div id="mlauto-meta-box-content">
			<p>MLAuto classifier cannot find a classifier--has one been generated? Please click the button below to ensure a classifier has been built and selected.</p>
		</div>				
		<p>
			<a href=<?php echo admin_url( 'admin.php?page=mlauto-tag' ) ?> class='button button-primary'>Generate Classifier</a>
		</p>
	</div>
<?php
}