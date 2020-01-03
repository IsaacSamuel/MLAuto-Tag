function mlauto_createTermList(terms, taxonomy_name) {

	/*
		[
			{name: termName, probabilities: Object(0:false probability, 1:true probability), checked:  is term checked? }
			{name: "Advocacy", probabilities: {0: .03, 1: 97%}, checked: true}
			{name: "Legal", ...}
			...
		]
	*/

	let term_list = document.createElement("div")
	term_list.classList.add("mlauto_term_list");

	terms.forEach(function(term){
		console.log(term)
		let term_item = document.createElement("div")
		term_item.classList.add("mlauto_term_item");

		//In each subdiv, display checkbox with value of term name and id of term name.
		let checkbox = document.createElement("input");
		let term_name = term["name"]
		checkbox.setAttribute("type", "checkbox");
		checkbox.setAttribute("id", term_name);
		checkbox.setAttribute("name", taxonomy_name + "||" + term_name);
		checkbox.setAttribute("value", term_name);
		//If the term is already selected, display it as checked
		checkbox.checked = term["checked"];
		
		let label = document.createElement("label");
		label.setAttribute("for", term_name);
		//Display name of term and predicted probability of match
		label.innerHTML = term_name + ": <strong>" + ((1 - term["probabilities"]["0"]) * 100).toFixed(2) + "%</strong>";

		term_item.appendChild(checkbox);
		term_item.appendChild(label);

		if(term["probabilities"]["0"] > .97) {
			term_item.style.display = "none";
		}

		term_list.appendChild(term_item);
	})

	return term_list;
}

function mlauto_sortByProbability(term1, term2) {
	if (term1["probabilities"]["0"] > term2["probabilities"]["0"]) { return 1}
	if (term1["probabilities"]["0"] < term2["probabilities"]["0"]) { return -1}
	return 0
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


	//AJAX - send post id to classifyPost AJAX action
	jQuery.post( MLAuto_Ajax_Settings.ajaxurl, data)

		.done( function(response ) {

			console.log(response)
			/*
			Structure of return data:
			[
				success : true / false
				data :
					{
						[Taxonomy Name : Term Data]
						"category" :
							[
								[Term Data]
							]
						...
					}
			]
			*/

			//If success
			if (response.success) {
				//Find content div
				let container_div = document.getElementById("mlauto-meta-box-content");
				//Erase contents
				container_div.innerHTML = "";

				//For each taxonomy
				Object.entries(response.data).forEach(function(taxonomy) {

					taxonomy[1].sort(mlauto_sortByProbability);

					//Create div
					let taxonomy_div = document.createElement("div");
					taxonomy_div.classList.add("mlauto_taxonomy_div");
					taxonomy_div.setAttribute("id", taxonomy[0]);

					//Inside div, create h3 with name of taxonomy
					let taxonomy_name = document.createElement("h3");
					//Capitilize taxonomy name
					taxonomy_name.innerText = taxonomy[0].charAt(0).toUpperCase() + taxonomy[0].slice(1);
					taxonomy_div.appendChild(taxonomy_name);

					//Create form with taxonomy name


					let term_list = mlauto_createTermList(taxonomy[1], taxonomy[0]);
					taxonomy_div.appendChild(term_list);

					container_div.appendChild(taxonomy_div);
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
