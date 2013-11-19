$(document).ready(function(){
	//Bind a click event handler to all the delete buttons
	$("table#list-all-pages tbody tr td a[type=delete_button]").bind('click', function() {// When a Delete action button is clicked...
		deletePages($(this));
		return false;//Do not continue with default browser action. That is goto #
	});
	//Bind a click event handler to all the publish buttons
	$("table#list-all-pages tbody tr td a[type=publish_toggle]").bind('click', function() {// When a publish action button is clicked...
		publishPages($(this));
		return false;//Do not continue with default browser action. That is goto #
	});

	//Bind a click event handler to the footer delete button
	$("table#list-all-pages tfoot tr td a[type=delete_all_button]").bind('click', function() {// When a Delete action button is clicked...
		deletePages(null);
		return false;//Do not continue with default browser action. That is goto #
	});
	//Bind a click event handler to the footer publish button
	$("table#list-all-pages tfoot tr td a[type=publish_all_button]").bind('click', function() {// When a publish action button is clicked...
		publishPages(null,"publish-all");
		return false;//Do not continue with default browser action. That is goto #
	});
	//Bind a click event handler to the footer un-publish button
	$("table#list-all-pages tfoot tr td a[type=unpublish_all_button]").bind('click', function() {// When a publish action button is clicked...
		publishPages(null,"unpublish-all");
		return false;//Do not continue with default browser action. That is goto #
	});
	
});


function convertToUrl(page_list){
	var pairs = [];
	for (var index=0;index<page_list.length;index++)
	pairs.push('page_list=' + encodeURIComponent(page_list[index]));
	return pairs.join('&');
}



/**
* This function handles delete operation. 
* It sends POST request requesting for .
*/
function deletePages(instance){
	var page_id;
	var post_url;
	var page_list=new Array();
	var page_name_list='';
	var confirmation=false;
	//Get the post url
	post_url=$('table[type=page_list] tbody tr:first-child td:last-child a[type=delete_button]').attr('href');		
	if(instance==null){
		//The footer action button was clicked. Will delete multiple pages 
	
			//// Get the count
			if($('table[type=page_list] tbody tr td:first-child input:checked').length==0)
			{alert('You havent selected any rows.. \n\n[Later buttons will be faded/inactive if no rows are selected]');return;}
	
			////Get all the page ids of the records that were checked 
			$('table[type=page_list] tbody tr td:first-child input:checked').each( 
				function() { 
				page_list.push($(this).parents('tr').attr('page_id'));
				page_name_list+='- '+$(this).parents('tr').children('td:nth-child(2)').html()+'\n';
				//Highlight the row
				$(this).parents('tr').addClass('row-delete');
	//////				console.debug($(this).attr('checked')+','+$(this).parents('tr').attr('page_id')+','+page_list.length)
				} 
			);		
			if(confirm('Are you sure you want to permanently delete the following page/s?\n'+page_name_list)){confirmation=true}
	} else {
		//Row button was clicked
			////Get only the current page id 
	//////	console.debug(instance.parents('tr').attr('page_id') )
			page_list.push(instance.parents('tr').attr('page_id'));
			page_name_list+='- '+instance.parents('tr').children('td:nth-child(2)').html()+'\n';
			//Highlight the row
			instance.parents('tr').addClass('row-delete');
	//////	console.debug($('td:nth-child(2)', instance.parents('tr')).html ())
			if(confirm('Are you sure you want to permanently delete this page?\n'+page_name_list)){confirmation=true}
	}

	if(confirmation){
	//alert($.param(page_list))	
	$.ajax({
	  url: post_url,
	  type: 'POST',
	  data:convertToUrl(page_list),
	  success: function(data, textStatus, XMLHttpRequest) {
			alert(data);
			var deleted_page_list;	
			deleted_page_list=data.page_list.join(',');	
			if(data.result=='success'){
				//all deleted successfully
				//Show notification
				alert('Successfully deleted '+data.count+' pages');
				$('table[type=page_list] tbody tr.row-delete').remove()			
				}	
	  },
	  error:  function(XMLHttpRequest, textStatus, errorThrown) {
		//Show error message above the area.
		alert('ERROR: A problem occured. Could not save data\n***This will be improved');
	  }
	});
	
	} else {//Confirm - else part
		//Un-Highlight the row
		$('table[type=page_list] tbody tr').removeClass('row-delete');
	}
}


/**
* This function handles publish operation. 
* It sends POST request requesting for .
*/
function publishPages(instance,mode){
	//For single page action button: mode=toggle
	//For multiple page footer action button: mode=publish-all/unpublish-all
	var page_id;
	var post_url;
	var page_list=new Array();
	var page_name_list='';
	var confirmation=false;
	mode=(mode==null)?"toggle":mode;

        //Get the post url
        post_url=$('table[type=page_list] tbody tr:first-child td:last-child a[type=publish_toggle]').attr('href');
	if(instance==null){
		//The footer action button was clicked. Will publish multiple pages 

//// Get the count
			if($('table[type=page_list] tbody tr td:first-child input:checked').length==0)
			{alert('You havent selected any rows.. \n\n[Later buttons will be faded/inactive if no rows are selected]');return;}
	
			////Get all the page ids of the records that were checked 
			$('table[type=page_list] tbody tr td:first-child input:checked').each( 
				function() { 
				page_list.push($(this).parents('tr').attr('page_id'));
				page_name_list+='- '+$(this).parents('tr').children('td:nth-child(2)').html()+'\n';
				//Highlight the row
				if(mode=='publish-all'){$(this).parents('tr').addClass('row-publish');}
				else{$(this).parents('tr').addClass('row-unpublish');}
				
	//////				console.debug($(this).attr('checked')+','+$(this).parents('tr').attr('page_id')+','+page_list.length)
				} 
			);		
			if(mode=='publish-all'){if(confirm('Are you sure you want to publish all these pages?\n'+page_name_list)){confirmation=true}}
			else{if(confirm('Are you sure you want to unpublish all these pages?\n'+page_name_list)){confirmation=true}}

	} else {
		//Row button was clicked
			////Get only the current page id 
	//////	console.debug(instance.parents('tr').attr('page_id') )
			page_list.push(instance.parents('tr').attr('page_id'));
			page_name_list+='- '+instance.parents('tr').children('td:nth-child(2)').html()+'\n';
			//Highlight the row
			
	//////	console.debug($('td:nth-child(2)', instance.parents('tr')).html ())
			if(instance.attr('class')=='published'){
				if(instance.parents('tr').attr('class')=='published'){instance.parents('tr').attr('published','yes');instance.parents('tr').removeClass('published');}								
				instance.parents('tr').addClass('row-unpublish');
				if(confirm('Are you sure you want to publish this page?\n'+page_name_list)){confirmation=true}
				mode="unpublish";
			} else { 
				instance.parents('tr').addClass('row-publish');
				if(confirm('Are you sure you want to unpublish this page?\n'+page_name_list)){confirmation=true}
				mode="publish";				
			}
	}

	if(confirmation){
	/*
	mode=publish-all/unpublish-all/publish/unpublish
	
	*/
	$.ajax({
	  url: post_url,
	  type: 'POST',
  	  data:convertToUrl(page_list)+'&command='+mode,
  	  success: function(data, textStatus, XMLHttpRequest) {
			alert(data);
			var deleted_page_list;	
			deleted_page_list=data.page_list.join(',');	
			if(data.result=='success'){
				//all deleted successfully
				//Show notification
				//@TO DO: change using JS or refresh??
				//
				}	
	  },
	  error:  function(XMLHttpRequest, textStatus, errorThrown) {
		//Show error message above the area.
		alert('ERROR: A problem occured. Could not save data\n***This will be improved');
	  }
	});
	
	} else {//Confirm - else part
		//Un-Highlight the row
		$('table[type=page_list] tbody tr').removeClass('row-delete');
		$('table[type=page_list] tbody tr').removeClass('row-unpublish');
		$('table[type=page_list] tbody tr').removeClass('row-publish');
		$('table[type=page_list] tbody tr').each(function() {
			if($(this).attr('published')=='yes'){$(this).attr('class','published');}	
		});							
	}
}
	








/*				//remove rows
			$('table[type=page_list] tbody tr').each( 
				function() { 
					//if($(this).attr('page_id')+',');deleted_page_list
				page_name_list+='- '+$(this).parents('tr').children('td:nth-child(2)').html()+'\n';
//				console.debug($(this).attr('checked')+','+$(this).parents('tr').attr('page_id')+','+page_list.length)
				} 
			);
*/	