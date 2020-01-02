function mlauto_createTermList(terms) {

	/*
		{
			[Term: [Prediction, Already checked]]
			"Advocacy" : [97%, true]
			"Legal" : [85%, false]
			...
		}
	*/

	//let term_list = document.createElement("div").addClass("mlauto_term_list");

	//terms.forEach(function(term){
		//let term_item = document.createElement("div").addClass("mlauto_term_item");

		//In each subdiv, display checkbox with value of term name and id of term name.
		//let checkbox = document.createElement("input");
		//checkbox.addAttribute("type", "checkbox");
		//checkbox.addID(term.key());
		//checkbox.addName(term.key());
		//checkbox.addValue(term.key());
		//If the term is already selected, display it as checked
		//checkbox.checked = term.value()[1];
		
		//let label = document.createElement("input");
		//label.addAttribute("for", term.key());
		//Display name of term and predicted probability of match
		//label.innerHTML = term.key() + ": " + term.value()[0] 


		//term_item.addChild(checkbox);
		//term_item.addChild(label);

		//term_list.addChild(term_item);
	//})

	//return term_list;
}


jQuery( "#classify_post" ).on("click", function( event ) {

	//Prevent jumping to the top
	event.preventDefault();

	//UI feedback that request is being processed
	var $button = jQuery( this );
	$button.width( $button.width() ).text('...');

	let data = {
			"action" : "classifyPost",
		}

	//Get post id
	data["post_id"] = document.getElementById("mlauto-meta-box").getAttribute("rel");

	console.log(data);

	//AJAX - send post id to classifyPost AJAX action
	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)

		.done( function(response ) {

		console.log(response)
		/*
		Structure of return data:
		{
			success : true / false
			data :
				{
					[Taxonomy Name : Predictions]
					"category" :
						{
							[Term: [Prediction, Already checked]]
							"Advocacy" : [97%, true]
							"Legal" : [85%, false]
							...
						}
					...
				}
		}
		*/

		//If success
		if (response.success) {
			//Find content div
			let container_div = jQuery("#mlauto-meta-box-content");
			//Erase contents
			container_div.innerHTML = "";

			//For each taxonomy
			response.data.forEach(function(taxonomy) {
				//Create div
				//let taxonomy_div = document.createElement("div").addClass("mlauto_taxonomy_div");

				//Inside div, create h3 with name of taxonomy, capitilize first letter
				//let taxonomy_name = document.createElement("h3");
				//let taxonomy_name.innerText = taxonomy.key().upperCaseFirstLetter();
				//taxonomy_div.addChild(taxonomy_name);

				//let term_list = mlauto_createTermList(taxonomy.values());
				//taxonomy_div.addChild(term_list);

				//container_div.addChild(taxonomy_div);
			});
		}
		//If error

			//Display error

		//Put button back to normal
		$button.width( $button.width() ).text('Classify Post');

	})
	.fail(function(xhr, status, error) {
		let container_div = document.getElementById("mlauto-meta-box-content");

		container_div.innerHTML = xhr.responseText;

		$button.width( $button.width() ).text('Classify Post');
    });
});

//Now pat yourself on the back, because beta is complete. :-)
