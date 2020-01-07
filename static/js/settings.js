function mlauto_generateClassifier() {

	button = jQuery( "#generate_classifier" );

    var data = {"action" : "generateClassifier"};


	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)
		.done( function(response ) {

			location.reload(); 

	    })

		.fail(function(xhr, status, error) {
			jQuery( "#mlauto_error" ).html(xhr.responseText);

			button.width( button.width() ).text('Generate Classifier');
	    });

}


jQuery(document).ready(function() {
    jQuery('#current_classifier_term_data').DataTable();
} );


function mlauto_saveSettings(event) {

    let serialized_array = jQuery("#mlauto_save_settings_form").serializeArray();

	var MLAuto_taxonomies = [];
	var MLAuto_specified_features = [];

	//Reduce form values down into one value
	serialized_form = serialized_array.filter(
		function(object){
			if (object.name === "taxonomies") {
				MLAuto_taxonomies.push(object.value);

				return false;
			}
			if (object.name === "features") {
				MLAuto_specified_features.push(object.value)

				return false;
			}

			return true;

	});

	serialized_form.push({name : "MLAuto_taxonomies", value : MLAuto_taxonomies});
	serialized_form.push({name : "MLAuto_specified_features", value : MLAuto_specified_features});


	serialized_form = {settings : serialized_form};

	serialized_form["action"] = 'saveSettings';

	console.log(serialized_form)

	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, serialized_form)
		.done( function(response ) {

			mlauto_generateClassifier();

    	})
    	.fail(function(xhr, status, error) {

			jQuery("#mlauto_error").html(xhr.responseText);
    		
			button.width( button.width() ).text('Save Settings');
	    });
}


jQuery( "#generate_classifier" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	var button = jQuery( this );

	//Click feedback
    button.width( button.width() ).text('...');


    mlauto_saveSettings(event);

});



jQuery( ".mlauto_select_classifer" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	button = jQuery( this );

	//Click feedback
    button.width( button.width() ).text('...');


    let classifier_id = button.attr("value");

    var data = {
    	"action" : "selectClassifier",
    	"classifier_id" : classifier_id
	};


	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)
		.done( function(response ) {

			location.reload(); 

	    })

		.fail(function(xhr, status, error) {
			jQuery( "#mlauto_error" ).html(xhr.responseText);

			button.width( button.width() ).text('Select Classifier');
	    });
});


jQuery( ".mlauto_delete_classifer" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	button = jQuery( this );

	//Click feedback
    button.width( button.width() ).text('...');

    let classifier_id = button.attr("value");

    var data = {
    	"action" : "deleteClassifier",
    	"classifier_id" : classifier_id
	};


	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)
		.done( function(response ) {

			jQuery("#mlauto_classifier_model_" + classifier_id ).fadeOut()

	    })

		.fail(function(xhr, status, error) {
			jQuery( "#mlauto_error" ).html(xhr.responseText);

			button.width( button.width() ).text('Delete Classifier');
	    });
});