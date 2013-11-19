$(document).ready(function(){
	//Bind a click event handler to all the delete buttons
	$("table#list-all-templates tbody tr td a[type=delete_button]").bind('click', function() {// When a Delete action button is clicked...
		deleteTemplates($(this));
		return false;//Do not continue with default browser action. That is goto #
	});

	//Bind a click event handler to the footer delete button
	$("table#list-all-templates tfoot tr td a[type=delete_all_button]").bind('click', function() {// When a Delete action button is clicked...
		deleteTemplates(null);
		return false;//Do not continue with default browser action. That is goto #
	});

	//Bind a click event handler to the footer add button
	$("table#list-all-templates tfoot tr td input[name=add]").bind('click', function() {// When a Delete action button is clicked...
		if(confirm('You are about to move to another page. Do you want to continue?')){window.location=$(this).attr("href")}
		return false;//Do not continue with default browser action. That is goto #
	});	

	//Bind a click event handler to the footer import button
	$("table#list-all-templates tfoot tr td input[name=import]").bind('click', function() {// When a Delete action button is clicked...
		if(confirm('You are about to move to another page. Do you want to continue?')){window.location=$(this).attr("href")}
		return false;//Do not continue with default browser action. That is goto #
	});	


});


/**
* This function handles chkbox click event
*/

function bindEventHandlerToAlternateCheckbox(){
	//Init
	if($("form table[type=template_list] tbody tr input#alternate_template_chkbox").is(':checked')) {
		$("form table[type=template_list] tbody tr.alternate_template_hide").each(function(){
			$(this).removeClass('alternate_template_hide')
			$(this).addClass('alternate_template_show')				
		});
	}
	//Bind a click event handler to the footer import button
	$("form table[type=template_list] tbody tr td span.jqTransformCheckboxWrapper a.jqTransformCheckbox").bind('click', function() {
		if($(this).hasClass('jqTransformChecked')){
			$("form table[type=template_list] tbody tr.alternate_template_hide").each(function(){
					$(this).removeClass('alternate_template_hide')
					$(this).addClass('alternate_template_show')				
				});
		} else{
			$("form table[type=template_list] tbody tr.alternate_template_show").each(function(){
					$(this).removeClass('alternate_template_show')
					$(this).addClass('alternate_template_hide')				
				});
		}
	});	
}

/**
* This function handles filter seletor 
* It sends POST request requesting for tab content as per specified filter.
*/

function bindEventHandlerToSelector(){
	//Action handler
	$("form#manage-template-1 div.jqTransformSelectWrapper ul li a").bind('click',function(){
	var index_,filter;
	var index_=$("form#manage-template-1 div.jqTransformSelectWrapper ul li a[class=selected]").attr('index');
	//Get the selected filter value
	var filter=$("form#manage-template-1 select#template-type-selector option:nth-child("+(parseInt(index_)+1)+")").attr('value');
	
	$.ajax({
		url: $("select#template-type-selector").attr('action')+'/'+filter,
		type: 'POST',
		data: 'filter='+filter,
		success: function(data, textStatus, XMLHttpRequest){
		//alert(data);
                $('div#body-wrapper').html(data)
	},
	complete: function(XMLHttpRequest, textStatus){
	}
	});
	
	return false; //prevent default browser action
	});

}



function convertToUrl(template_list){
	var pairs = [];
	for (var index=0;index<template_list.length;index++)
	pairs.push('template_list=' + encodeURIComponent(template_list[index]));
	return pairs.join('&');
}



/**
* This function handles delete operation. 
* It sends POST request requesting for .
*/
function deleteTemplates(instance){
	var template_id;
	var post_url;
	var template_list=new Array();
	var template_name_list='';
	var confirmation=false;
	//Get the post url
	post_url=$('table[type=template_list] tbody tr:first-child td:last-child a[type=delete_button]').attr('href');		
	if(instance==null){
		//The footer action button was clicked. Will delete multiple templates 
	
			//// Get the count
			if($('table[type=template_list] tbody tr td:first-child input:checked').length==0)
			{alert('You havent selected any rows.. \n\n[Later buttons will be faded/inactive if no rows are selected]');return;}
	
			////Get all the template ids of the records that were checked 
			$('table tbody tr td:first-child input:checked').each( 
				function() { 
				template_list.push($(this).parents('tr').attr('template_id'));
				template_name_list+='- '+$(this).parents('tr').children('td:nth-child(2)').html()+'\n';
				//Highlight the row
				$(this).parents('tr').addClass('row-delete');
	//////				console.debug($(this).attr('checked')+','+$(this).parents('tr').attr('template_id')+','+template_list.length)
				} 
			);		
			if(confirm('Are you sure you want to permanently delete the following template/s?\n'+template_name_list)){confirmation=true}
	} else {
		//Row button was clicked
			////Get only the current template id 
	//////	console.debug(instance.parents('tr').attr('template_id') )
			template_list.push(instance.parents('tr').attr('template_id'));
			template_name_list+='- '+instance.parents('tr').children('td:nth-child(2)').html()+'\n';
			//Highlight the row
			instance.parents('tr').addClass('row-delete');
	//////	console.debug($('td:nth-child(2)', instance.parents('tr')).html ())
			if(confirm('Are you sure you want to permanently delete this template?\n'+template_name_list)){confirmation=true}
	}

	if(confirmation){
	//alert($.param(template_list))	
	$.ajax({
	  url: post_url,
	  type: 'POST',
	  data:convertToUrl(template_list),
	  success: function(data, textStatus, XMLHttpRequest) {
			if(data.result=='success'){
				//all deleted successfully
				//Show notification
				alert('Successfully deleted '+data.count+' templates');
				$('table[type=template_list] tbody tr.row-delete').remove()			
				}	
	  },
	  error:  function(XMLHttpRequest, textStatus, errorThrown) {
		//Show error message above the area.
		alert('ERROR: A problem occured. Could not save data\n***This will be improved');
	  }
	});
	
	} else {//Confirm - else part
		//Un-Highlight the row
		$('table[type=template_list] tr').removeClass('row-delete');
	}
}

