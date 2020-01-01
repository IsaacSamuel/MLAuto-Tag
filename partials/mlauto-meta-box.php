<?php 
$postID = get_the_ID();

?>

<div id="mlauto-meta-box" rel=<?php echo '"' . $postID . '"'; ?>>
	<h3>MLAuto: Run Classifier</h3>
	<div id="mlauto-meta-box-content">
		<p>To get projected tags and classifications for this post, click the button below.</p>
	</div>				
	<p>
		<a href='#' id='classify_post' class='button button-primary'>Classify Post</a>
	</p>
</div>