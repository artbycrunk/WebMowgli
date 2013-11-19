$(document).ready(function(){
//	//Bind a click event handler to all the delete buttons
//	$("table[type=resource_list] tbody tr td a[type=delete_button]").bind('click', function() {// When a Delete action button is clicked...
//		deleteResources($(this));
//		return false;//Do not continue with default browser action. That is goto #
//	});
//
//	//Bind a click event handler to the footer delete button
//	$("table[type=resource_list] tfoot tr td a[type=delete_all_button]").bind('click', function() {// When a Delete action button is clicked...
//		deleteResources(null);
//		return false;//Do not continue with default browser action. That is goto #
//	});

});


function convertToUrl(resource_list){
	var pairs = [];
	for (var index=0;index<resource_list.length;index++)
	pairs.push('resource_list=' + encodeURIComponent(resource_list[index]));
	return pairs.join('&');
}

function bindEventHandlerToFormButtons_Edit(){
	//Bind a click event handler to update_resource_button button
	$("#update_resource_button").bind('click', function() {
		postFormData('edit-resource');
		return false;//Do not continue with default browser action. That is submit the form
	});
}

/**
* This function handles form submission using AJAX
*/
function postFormData(form_id){

	var confirmation=false;
	//Get the post url
	post_url=$('form#'+form_id).attr('action');
	////console.debug($('form#'+form_id).serialize())
	if(confirm('Continue to save your changes?')){confirmation=true}

	if(confirmation){
	$.ajax({
	  url: post_url,
	  type: 'POST',
	  data:$('form#'+form_id).serialize(),
	  success: function(data, textStatus, XMLHttpRequest) {
			alert(data);
			var deleted_page_list;
			deleted_page_list=data.page_list.join(',');
			if(data.result=='success'){
				//all deleted successfully
				//Show notification
				alert('Successfully deleted '+data.count+' pages');
				}
	  },
	  error:  function(XMLHttpRequest, textStatus, errorThrown) {
		//Show error message above the area.
		alert('ERROR: A problem occured. Could not save data\n***This will be improved');
	  }
	});

	} else {//Confirm - else part

	}
}

/**
* This function handles delete operation.
* It sends POST request requesting for .
*/
function deleteResources(instance){
	var resource_id;
	var post_url;
	var resource_list=new Array();
	var resource_name_list='';
	var confirmation=false;
	//Get the post url
	post_url=$('table[type=resource_list] tbody tr:first-child td:last-child a[type=delete_button]').attr('href');
	if(instance==null){
		//The footer action button was clicked. Will delete multiple resources
			//// Get the count
			if($('table[type=resource_list] tr td:first-child input:checked').length==0)
			{alert('You havent selected any rows.. \n\n[Later buttons will be faded/inactive if no rows are selected]');return;}

			////Get all the resource ids of the records that were checked
			$('table[type=resource_list] tr td:first-child input:checked').each(
				function() {
				resource_list.push($(this).parents('tr').attr('resource_id'));
				resource_name_list+='- '+$(this).parents('tr').children('td:nth-child(2)').html()+'\n';
				//Highlight the row
				$(this).parents('tr').addClass('row-delete');
	//////				console.debug($(this).attr('checked')+','+$(this).parents('tr').attr('resource_id')+','+resource_list.length)
				}
			);
			if(confirm('Are you sure you want to permanently delete the following resource/s?\n'+resource_name_list)){confirmation=true}
	} else {
		//Row button was clicked
			////Get only the current resource id
	//////	console.debug(instance.parents('tr').attr('resource_id') )
			resource_list.push(instance.parents('tr').attr('resource_id'));
			resource_name_list+='- '+instance.parents('tr').children('td:nth-child(2)').html()+'\n';
			//Highlight the row
			instance.parents('tr').addClass('row-delete');
	//////	console.debug($('td:nth-child(2)', instance.parents('tr')).html ())
			if(confirm('Are you sure you want to permanently delete this resource?\n'+resource_name_list)){confirmation=true}
	}

	if(confirmation){
	//alert($.param(resource_list))
	$.ajax({
	  url: post_url,
	  type: 'POST',
	  data:convertToUrl(resource_list)+'&resource_type='+$('table[type=resource_list]').attr('resource_type'),
	  success: function(data, textStatus, XMLHttpRequest) {
			alert(data);
			var deleted_resource_list;
			deleted_resource_list=data.resource_list.join(',');
			if(data.result=='success'){
				//all deleted successfully
				//Show notification
				alert('Successfully deleted '+data.count+' resources');
				$('table[type=resource_list] tr.row-delete').remove()
				}
	  },
	  error:  function(XMLHttpRequest, textStatus, errorThrown) {
		//Show error message above the area.
		alert('ERROR: A problem occured. Could not save data\n***This will be improved');
	  }
	});

	} else {//Confirm - else part
		//Un-Highlight the row
		$('table[type=resource_list] tr').removeClass('row-delete');
	}
}


