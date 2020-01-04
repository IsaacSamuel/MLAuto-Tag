
jQuery( "#save_settings" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	var $button = jQuery( this );

	//Click feedback
    $button.width( $button.width() ).text('...');

    let serialized_array = jQuery("#save_settings_form").serializeArray();

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

	//json_form = JSON.stringify(serialized_form);

	
	//console.log(json_form);

	console.log(serialized_form)

	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, serialized_form, function( response ) {

        console.log(response);

       	$button.width( $button.width() ).text('Save Settings');
    } );
	
})

jQuery( "#generate_classifier" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	var $button = jQuery( this );

	//Click feedback
    $button.width( $button.width() ).text('...');


    var data = {"action" : "generateClassifier"};


	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)
		.done( function(response ) {

	        console.log(response);

	        $button.width( $button.width() ).text('Generate Classifier');
	    })

		.fail(function(xhr, status, error) {
			console.log(xhr.responseText);

			$button.width( $button.width() ).text('Classify Post');
	    });
});

