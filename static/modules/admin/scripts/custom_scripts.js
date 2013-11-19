/*
 * Only for backward compatibility
 **/
function d(msg){
	wm.debug(msg);
}

/*
 * Can be used to convert a textfield data into its slug format
 * Converts the source text field into a slug and saves in the target text field
 **/
function slugify( source, target ){


	$(source).change(function(){
		var Text = $(this).val();
		$(target).val( convertToSlug( Text ) );
	});

}

// converts given string into its slug format
function convertToSlug( Text )
{
	return Text.toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-');
}


/************************************************************
*************************************************************
** GENERAL FUNCTIONS
*************************************************************
************************************************************/

//@Note: function getDepthDetails(type,id) moved to adminconsole.jquery.js - used by gallery n video
var depthBeforeDrag,depthAfterDrag;
function getDepthDetails(type,id){//always findNexts
	var clickedItem,prefix,prevId=null,nextId=null;
	var tempId;
	var findNext=true;

	if(type=='category'){
		prefix='cat';
		clickedItem=$('ul#cat_list li[cat_id='+id+']');
	} else if(type=='image'){
		prefix='img';
		clickedItem=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+id+']');
	} else if(type=='video'){
		prefix='vid';
		clickedItem=$('ul#video_playlist li[vid_id='+id+']');
	} else if(type=='album'){
		prefix='alb';
		clickedItem=$('ul#album_list li[alb_id='+id+']');
	} else if(type=='song'){
		prefix='sng';
		clickedItem=$('ul#song_list li[sng_id='+id+']');
	}
	var depth=0;
	var jumpOut=false;
	var FLAG_found=false;
	/*

	$.each($('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes]').children().not('.ui-sortable-helper'),function(){
wm.debug($(this).attr('img_id'))
});
         */
	//wm.debug('l>'+clickedItem.parents("ul").children().not('.ui-sortable-helper').length)
	$.each(clickedItem.parents("ul").children().not('.ui-sortable-helper'),function(){
		tempId=$(this).attr(prefix+'_id');
		if(jumpOut){
			nextId=tempId;
			return false;//We can stop the loop from within the callback function by returning false.
		}
		//wm.debug('cmpr:'+tempId+','+id+','+depth)
		//wm.debug('>'+$(this).attr('cat_id')+','+depth)
		depth=depth+1;
		if(tempId==id){
			FLAG_found=true;
			wm.debug('depth:'+depth);
			if(findNext){
				jumpOut=true
			}
			else{
				return false;//We can stop the loop from within the callback function by returning false
			}
		}
		if(!jumpOut){
			prevId=tempId;
		}
	});
	if(FLAG_found){
		return {
			'depth':depth,
			'prevId':prevId,
			'nextId':nextId,
			'found':true
		};

	}
	else{
		return {
			'depth':depth,
			'prevId':prevId,
			'nextId':nextId,
			'found':false
		};

	}

}

//@Note: function postSortOrder(old_id,new_id,post_uri) moved to adminconsole.jquery.js - used by gallery n video

/**
 * This function Sorts items provided
 */
function postSortOrder(old_id,new_id,uri){

	if( (old_id==undefined || old_id==null ) || (new_id==undefined || new_id==null) || (new_id==old_id) ){

		// do nothing
		wm.debug('Sorting : Ignored');
		return;
	}
	else{
		wm.debug('Sorting : Code Start ###');

		wm.admin.utility.ajax.post({

			url: uri,
			data: {
				old_id : old_id,
				new_id : new_id
			}
		});

	}


}


// for backward compatibility
// moved to wm.admin.confirm_action(params)
function confirm_action( params ){

	wm.admin.confirm_action(params);
}


/************************************************************
*************************************************************
** MYSTERY FUNCTIONS ( not sure of actual purpose of below functions )
*************************************************************
************************************************************/


//OuterHTML Plugin
jQuery.fn.outerHTML = function(s) {
	return (s) ? this.before(s).remove() : jQuery("<p>").append(this.eq(0).clone()).html();
}


// implement JSON.stringify serialization
JSON.stringify = JSON.stringify || function (obj) {
	var t = typeof (obj);
	if (t != "object" || obj === null) {
		// simple data type
		if (t == "string") obj = '"'+obj+'"';
		return String(obj);
	}
	else {
		// recurse array or object
		var n, v, json = [], arr = (obj && obj.constructor == Array);
		for (n in obj) {
			v = obj[n];
			t = typeof(v);
			if (t == "string") v = '"'+v+'"';
			else if (t == "object" && v !== null) v = JSON.stringify(v);
			json.push((arr ? "" : '"' + n + '":') + String(v));
		}
		return (arr ? "[" : "{") + String(json) + (arr ? "]" : "}");
	}
};