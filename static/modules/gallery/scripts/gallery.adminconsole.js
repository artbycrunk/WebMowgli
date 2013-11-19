//Paths: [For CSS selector optimization]
var gc__ff__tc = 'div#gallery_container div#floating-formbox div#topbar-content';
var gc__ff__tca = gc__ff__tc + ' div#topbar-content-area';
var gc__ff__caCP = gc__ff__tca + ' div#content-area__category_properties';
var gc__ff__fcp = gc__ff__tca + ' form#form__category_properties';
var gc__ff__cci = gc__ff__caCP + ' ul#category_cover_img';
//gallery_container__floating_formbox__category_cover_img



var isDragging = false;
var isPropertiesPanelOpen = false; //When setting cover img, the image gets dropped into the cover img slot and (hidden by prop panel) underneath Category.
var isImagePropertiesShowing=false;
var isCatPropertiesShowing=false;
var PropertiesPanel_activeform;
var tree={};
//@Note: Shifted to admin.. var depthBeforeDrag,depthAfterDrag;
var lastDroppedCategory,lastDroppedImage;
var lastSelectedCatId,lastSelectedImgId;

//Global to Gallery module
var default_cat_id;
var obj,objt,objg, preSelected=false;
var no_image_uri;//Set in init
//


// Only used for Backward compatibility, Delete Once modules are updated
var notification_timeout=5000;

// Only used for Backward compatibility, Delete Once modules are updated
function notificationUpdate(notification_id,notification_type,msg_type, msg, more_details, duration){
	wm.admin.utility.notifications.notify( msg_type, msg );
}

// Only used for Backward compatibility, Delete Once modules are updated
function notificationHandler(notification_type,msg_type, msg, more_details, duration){
	wm.admin.utility.notifications.notify( msg_type, msg );
}


/**
 * AJAX Request (Post) URIs
 * gallery_post_uri['image']['change_category']
 **/
var gallery_post_uri = {
	category: {
		init:'admin/gallery/get_json_array',
		new_categ: 'admin/gallery/create_category',
		get_images:'admin/gallery/get_category_data',
		get_all_cat_meta:'admin/gallery/get_categories',//only meta, no images
		change_cover:'admin/gallery/edit_category_cover',
		edit:'admin/gallery/edit_category_details'
	},
	image: 	{
		change_category: 'admin/gallery/change_category',
		edit: 'admin/gallery/edit_image_details',
		get_image_details: 'admin/gallery/get_images_specific'
	},
	category_image: {
		del: 'admin/gallery/delete_items',
		visibility:'admin/gallery/edit_visibility',
		sort_order:'admin/gallery/change_order'
	}
};

//DONE var default_cat_id=1;// should come within intijson>settings
//PENDING
function preLoadImages(){

}

$(document).ready(function(){
	//Init prettyLoader
	//preload images required for preloader at front-end.
	/*
	$.prettyLoader({
		animation_speed: 'fast',
		loader: globalResourcePath+'/scripts/prettyLoader/images/prettyLoader/ajax-loader.gif', \
		offset_top: 13,
		offset_left: 10
	});
	$.prettyLoader();
	 */
	no_image_uri=module_resource_uri+'/images/default_cover_img.png';

	bindEventHandlerToSidebarMenuButtons();
	bindEventHandlerToPropertyButtons();
	bindEventHandlerToImageCategories();//NOT WORKING!?

	//Keypress event handler
	/*	bindKeyPressEventHandlerToDocument();
	function bindKeyPressEventHandlerToDocument(){
		$(document).keyup(function(e) {
				console.log(e.which);
		});
	}*/

	//Get the default cat's id
	//Rest Cat's have images=null
	/*$.each(tree, function(key, img) {
			d(key+','+img.images);
		$.each(img, function(key2, img2) {
				d('>'+key2+','+img2);
			});
		});*/
	/*	$.each(tree, function(key, cat) {
			if(cat.meta.default=='1'){
			default_cat_id=key;
			return;
			}
	});
         */
	getInitialLoadJSONData();
	//Bind a mousedown event handler to image
	//$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").bind('mousedown', image_item__mousedownHandler);

	/****************************************************************************************/
	/////////////////selectable

	bindEventHandlerToSelectedImagesTopbarMenuButtons();

	//Bind a click event handler to all the Categories

	//PropertyPage
	$("div#gallery_container #floating-formbox .close_button").bind('click', function() {
		d('close_button clicked')
		isPropertiesPanelOpen = false;
		showCategoryListMask(false);
		closePropertyPage();
		return false;//Do not continue with default browser action. That is goto #
	});

	$("div#gallery_container #floating-formbox a#remove_cover_image").bind('click', function() {
		d('clear cover')
		$(this).hide();
		//remove inside image
		$(gc__ff__cci + ' li i').remove();
		$(gc__ff__cci + ' span#category_cover_img_dropbox_text').show();
		$(gc__ff__cci + ' li').removeClass('CatCoverImg-ui-state-active');

		$('#cat_cover_id').val('');
		$('#cat_uri').val('');
		$('#cat_uri_thumb').val('');


		return false;//Do not continue with default browser action. That is goto #
	});





	$('.more_info_container_toggle').bind('click',function(){
		$(this).siblings('.more_info_container_content').slideToggle('1000')
	});

//		if(confirm('You are about to move to another page. Do you want to continue?')){window.location=$(this).attr("href")}


});

//Global to Adminconsole
//Globally useful functions
var g;

function get_current_cat(){
	var obj={};
	obj.id=$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[current=true]').attr('cat_id');
	obj.name=tree[obj.id].meta.name;
	return obj;
}

//@Note: function wm.get_url(uri) moved to adminconsole.jquery.js - used by gallery n video


var g1,g2,g3;





function closePropertyPage(callback){
	//$('div#topbar-content').slideUp(250);
	if($('div#floating-formbox').attr('collapsed')=='false'){
		$('div#floating-formbox').animate({
			left:'-375px'
		}, function() {
			$('div#floating-formbox').attr('collapsed','true');
			$('div#floating-formbox div#topbar-content .topbar-content-containers').hide();
		});
	}
	if (typeof callback == 'function') { // make sure the callback is a function
		callback.call(this); // brings the scope to the callback
	}

	isImagePropertiesShowing=false;
	isCatPropertiesShowing=false;


//	$('div#floating-formbox').hide();
}

function bindEventHandlerToImageCategories(){
	$("div#gallery_container #panel-content #panel-content-container #content-listbar").bind('click', CategoryOrImageDeselector('categories'));
	$('div#gallery_container #panel-sidebar #sidebar-category_container').bind('click', CategoryOrImageDeselector('images'));
}

function CategoryOrImageDeselector(type){
	d('Deselect all')
	if(type=='categories'){
		path='div#gallery_container #panel-sidebar #sidebar-category_container ul li.category_item'
	}
	else{
		path='div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li'
	}
	$(path).each(function(){
		$(this).removeClass('ui-selected')
	});
}


/**
 * Request: Initial load of All categories and images of the default category
 */
function getInitialLoadJSONData(){

	wm.admin.utility.ajax.post({
		url: wm.get_url(gallery_post_uri['category']['init']),
		notification: false,
		on_success: function(response, textStatus, XMLHttpRequest) {

			if( response.status == wm.constants.status.success ){
				initializeGalleryModule(response.data)
			}
		}
	});

/*
	$.ajax({
		url: wm.get_url(gallery_post_uri['category']['init']),
		type: 'POST',
		dataType: 'json',
		data:'',
		success: function(response, textStatus, XMLHttpRequest) {
			//
			if(response.status=='success'){
				initializeGalleryModule(response.data)
			} else {
				var notification_description = 'Contact Admin for support.';
				var notification_id=notificationHandler('quick','error','Could not load images...',notification_description,notification_timeout);
			}
		//Show notification
		},
		error:  function(XMLHttpRequest, textStatus, errorThrown) {
			var notification_id=notificationHandler('quick','error','Could not load images.',wm.constants.msgs['ERROR']['INT_CNNT'],notification_timeout);
		}
	}); */
}

function initializeGalleryModule(json_data){
	tree = json_data.categories;//jQuery.parseJSON(cat_imgs);
	default_cat_id=json_data.settings.default_categ;
	lastSelectedCatId=default_cat_id;
	if(tree[default_cat_id].images!=null){
		renderImageList(tree[default_cat_id].images)
	} else {
		//		var notification_description = 'Click upload to start adding images.';
		//		var notification_id=notificationHandler('quick','warning','No images to display in your default gallery.',notification_description,notification_timeout);
		renderImageList(tree[default_cat_id].images)
	}

	renderCategoryList(tree);
	bindEventHandlerToImageItem();//makeImagesSelectable();
	bindEventHandlerToCategoryItem();//makeCategoriesSelectable();

	makeCatListSortable();
	makeImgListSortable();
	makeCatListDroppable();
	makeCatCoverImgDroppable();




}

var sort_start_img_Id,sort_start_cat_Id;

function makeCatListSortable(){

	$("ul#cat_list").sortable({
		revert: true,
		start: function(event, ui){
			d('sortable> start');
			//ev=ui;
			sort_start_cat_Id = $(ui.helper).attr('cat_id');
			depthBeforeDrag=getDepthDetails('category',sort_start_cat_Id);
			d('depthBeforeDrag.depth:'+depthBeforeDrag.depth+',cat_id:'+sort_start_cat_Id)

			//Make the z-index higher than properties page
			d('start moving cat_id'+sort_start_cat_Id);
			d('sortable> start [END]');
		},
		stop: function(event, ui) {
			d('sortable> stop[done sorting]');
			var new_id,change=true;
			var depthAfterDrag=getDepthDetails('category',sort_start_cat_Id);
			if(depthAfterDrag.found) {
				if( depthBeforeDrag.depth < depthAfterDrag.depth){
					//downward
					new_id=depthAfterDrag.prevId;
				} else if( depthBeforeDrag.depth > depthAfterDrag.depth){
					//upward
					new_id=depthAfterDrag.nextId;
				} else if(depthBeforeDrag.depth==depthAfterDrag.depth){
					change=false;
				}
				d('category depthBeforeDrag.depth:'+depthBeforeDrag.depth+',depthAfterDrag.depth:'+depthAfterDrag.depth+',sort_start_cat_Id:'+sort_start_cat_Id);
				d('category new_id:'+new_id+',old id:'+lastDroppedCategory+',change:'+change+',depthAfterDrag.found:'+depthAfterDrag.found);
				if(change){
					postSortOrder(lastDroppedCategory,new_id, wm.get_url(gallery_post_uri['category_image']['sort_order']));
				}
			}
		} ,
		change: function(event, ui) {
			d('sortable> change')
		},
		update:function(event, ui) {
			d('update sorting')
		}
	});
}

function makeImgListSortable(cat_id){

	var selector;
	if(typeof cat_id=='undefined'){
		selector=''
	}
	else{
		selector='[cat_id='+cat_id+']';
	}

	$('div#gallery_container #panel-content #panel-content-container #content-listbar ul'+selector).sortable({
		revert: true,
		start: function(event, ui){
			d('sortable> start');
			//ev=ui;
			sort_start_img_Id = $(ui.helper).attr('img_id');
			depthBeforeDrag=getDepthDetails('image',sort_start_img_Id);
			d('depthBeforeDrag.depth:'+depthBeforeDrag.depth+',img_id:'+sort_start_img_Id)

			//Make the z-index higher than properties page
			d('start moving img_id'+sort_start_img_Id)
			d('sortable> start [END]');

		},
		stop: function(event, ui) {
			d('sortable> stop[done sorting]');
			var new_id,change=true;
			var depthAfterDrag=getDepthDetails('image',sort_start_img_Id);
			if(depthAfterDrag.found) {
				if( depthBeforeDrag.depth < depthAfterDrag.depth){
					//downward
					new_id=depthAfterDrag.prevId;
				} else if( depthBeforeDrag.depth > depthAfterDrag.depth){
					//upward
					new_id=depthAfterDrag.nextId;
				} else if(depthBeforeDrag.depth==depthAfterDrag.depth){
					change=false;
				}
				d('img depthBeforeDrag.depth:'+depthBeforeDrag.depth+',depthAfterDrag.depth:'+depthAfterDrag.depth+',sort_start_img_Id:'+sort_start_img_Id);
				d('img new_id:'+new_id+',old id:'+lastDroppedImage+',change:'+change+',depthAfterDrag.found:'+depthAfterDrag.found);
				if(change){
					postSortOrder(lastDroppedImage,new_id,wm.get_url(gallery_post_uri['category_image']['sort_order']));
				}
			}
			d('sortable> stop[done sorting] [END]');
		} ,
		change: function(event, ui) {
			d('sortable> change')
		},
		update:function(event, ui) {
			d('update sorting')
		}
	});
}

function makeCatListDroppable(cat_id){

	$( "ul#cat_list li" ).droppable({
		accept: "div#gallery_container #panel-content #panel-content-container #content-listbar ul li",
		activeClass: "ui-state-hover",
		hoverClass: "ui-state-active",
		drop: function( event, ui ) {
			//$( this ).addClass( "ui-state-highlight" )
			//Animate the Category
			$(this).fadeOut(250).fadeIn(100).fadeOut(250).fadeIn(100);


		}
	});
}


function makeCatCoverImgDroppable(){
	$( "ul#category_cover_img li" ).droppable({
		accept: "div#gallery_container #panel-content #panel-content-container #content-listbar ul li",
		activeClass: "CatCoverImg-ui-state-hover",
		hoverClass: "CatCoverImg-ui-state-active",
		drop: function( event, ui ) {
			//$( this ).addClass( "ui-state-highlight" )
			//Animate the Category
			$(this).fadeIn(1000);


		}
	});
}

/**
 * Request:cat id, Response: All cat data in JSON
 */
//Rethink logic
var json_data_;
function getJsonData(){
	return json_data_;
}
function setJsonData(data){
	json_data_=data;
	alert(json_data_)
}

/**
 * Gets all Category data, including its images data
 */
function getCategoryJSONData(cat_id,type,hide_mask){
	d('getCategoryJSONData')
	wm.admin.utility.ajax.post({
		url: wm.get_url(gallery_post_uri['category']['get_images']),
		notification : false,
		data:'cat_id='+cat_id,
		on_success: function(response, textStatus, XMLHttpRequest) {
			if(type=='new_cat'){
				//add cat and images(null)
				addNewCat(response.data,cat_id,'new_categ');
			} else if(type=='get_image_list'){
				//Cat already exist. We are just refreshing the image list and rendering it.
				extractCategoryImageList(response.data,cat_id)
			} else if(type=='get_properties'){
				//Re-loading properties
				addNewCat(response.data,cat_id,'update');
			}
			if(hide_mask){
				showImageListMask(false);
			}
		}
	});
/*
	$.ajax({
		url: wm.get_url(gallery_post_uri['category']['get_images']),
		type: 'POST',
		dataType: 'json',
		data:'cat_id='+cat_id,
		success: function(response, textStatus, XMLHttpRequest) {
			if(type=='new_cat'){
				//add cat and images(null)
				addNewCat(response.data,cat_id,'new_categ');
			} else if(type=='get_image_list'){
				//Cat already exist. We are just refreshing the image list and rendering it.
				extractCategoryImageList(response.data,cat_id)
			} else if(type=='get_properties'){
				//Re-loading properties
				addNewCat(response.data,cat_id,'update');
			}
			if(hide_mask){
				showImageListMask(false);
			}
		},
		error:  function(XMLHttpRequest, textStatus, errorThrown) {
			//alert('ERROR: A problem occured. Could not load cat data');
			//Show notification
			var notification_description = 'Could not load the category and image(s) details of the <b>'+tree[cat_id].meta.name+'</b> category.<br />'+ wm.constants.msgs['ERROR']['INT_CNNT'];
			var notification_id=notificationHandler('quick','error','Could not load category...',notification_description,notification_timeout);
		}
	}); */

}


function addNewCat(data,cat_id,typeOfCat){
	//typeOfCat: new/update

	//add to tree
	if(typeof tree[cat_id]=='undefined'){
		tree[cat_id]={};

		tree[cat_id].meta=[];
		tree[cat_id].images=[];
	} else {
		d('addNewCat: Cat already exist!');
		//Overwrite
		tree[cat_id]={};

		tree[cat_id].meta=[];
		tree[cat_id].images=[];
	}
	//Update
	tree[cat_id].meta=data.meta;
	tree[cat_id].images=data.images;

	if(typeOfCat=='update') {
		//Get its position
		curr_position=getDepthDetails('category',cat_id);
		//Un-render it
		removeCategory(cat_id);
	} else {
		//curr_position
		curr_position=null;
	}

	//render
	renderCategory(cat_id,curr_position);

	//Bind events
	bindEventHandlerToCategoryItem(cat_id);

	//Bind droppable handler
	makeCatListDroppable();
}


function extractCategoryImageList(img_list,cat_id){

	d('extractCategoryImageList> img_list:'+img_list+','+'cat_id:'+cat_id)

	var flag_NOIMGS=false;
	if(img_list.images==null){
		flag_NOIMGS=true
	}
	if(!flag_NOIMGS){
		//add to tree
		tree[cat_id].images=img_list.images;
		//Update
		tree[cat_id].meta=img_list.meta;
		//renderImages
		renderImageList(img_list.images);
		bindEventHandlerToImageItem(cat_id);//makeImagesSelectable(cat_id);
		makeImgListSortable(cat_id);
	} else {
		renderImageList(img_list.images);
	}
}


/**
 * Gets all Img data, including its images data
 */

//PENDING
/*
function getImageJSONData(cat_id,img_id,type){
	$.ajax({
                url: wm.get_url(gallery_post_uri['image']['get_image_details']),
                type: 'POST',
                dataType: 'json',
                data:'img_id='+img_id,
                success: function(response, textStatus, XMLHttpRequest) {
						if(type=='get_properties'){
							//addNewCat(response.data,cat_id,'new_categ');
							refreshImgList(cat_id);
						}
                },
                error:  function(XMLHttpRequest, textStatus, errorThrown) {
                        //alert('ERROR: A problem occured. Could not load cat data');
                        //Show notification
                        var notification_description = 'Could not load the image details of the <b>'+tree[cat_id].images[img_id]+'</b> image.<br />'+ wm.constants.msgs['ERROR']['INT_CNNT'];
                        var notification_id=notificationHandler('quick','error','Could not load image details...',notification_description,notification_timeout);
                }
	});

}

 */


/*
function uploadifyTOrender(imageJSONInstance){
	var cat_id=imageJSONInstance.parent_id;
	d('uploadifyTOrender :')
	//add to tree
	tree[cat_id].images[imageJSONInstance.id]=imageJSONInstance;
	//renderImages
	$('#'+cat_id+'__img_list').append(renderImage(imageJSONInstance,cat_id));
	bindEventHandlerToImageItem(cat_id,imageJSONInstance.id);//makeImagesSelectable(cat_id);
	//makeImgListSortable(cat_id);
}
 */

function refreshImgList(cat_id){ //addUploadedImgsIntoList

	d('refreshImgList :'+cat_id)

	//mask the current image list
	showImageListMask(true);
	//Request for img list
	getCategoryJSONData(cat_id,'get_image_list',true);

	//alert(cat_id+','+parseInt(tree[cat_id].meta.count));
	//Update cat img count
	updateCatImgCount(cat_id,parseInt(tree[cat_id].meta.count));
}
function addNewImage(imageJSONInstance,cat_id,typeOfImg){//used when addin from uploadify into list or when adding edited img from BE into list
	//typeOfImg: new/update
	d('addNewImage :')
	//Update
	tree[cat_id].images[imageJSONInstance.id]=imageJSONInstance;
	if(typeOfImg=='update') {
		//Get its position
		curr_position=getDepthDetails('image',img_id);
		//Un-render it
		removeImage(img_id);

		//render
		//$('#'+cat_id+'__img_list').append(renderImage(imageJSONInstance,cat_id));
		renderImageList({
			0:imageJSONInstance
		},curr_position);
	} else {
		//render
		renderImageList({
			0:imageJSONInstance
		});
	}


	//Bind events
	bindEventHandlerToImageItem(cat_id,imageJSONInstance.id);//makeImagesSelectable(cat_id);
//makeImgListSortable(cat_id);
}

function removeImage(img_id){
	$('li[img_id='+img_id+']').remove();
}
/**
 * Render single cat
 */
function renderCategory(cat_id,currPos){//currPos-->current_position_object
	var className, title_, cat_id, current_cat;
	var cat_HTML='';

	cat = tree[cat_id];
	if(cat.meta.visible!="1"){
		className='not_visible';
		title_='[Hidden] '+cat.meta.name;
	}
	else{
		className='';
		title_=cat.meta.name;
	}

	if(currPos==undefined || currPos==null ){
		//New cat
		current_cat='false';
	} else {
		//Currently edited/selected cat
		current_cat='true';
	}

	var image_word='pics';
	if(cat.meta.count==1){
		image_word='pic'
	}

	cat_HTML = '<li class="category_item '+className+'" cat_id="'+cat_id+'" cat_name="'+cat.meta.name+'" title="'+title_+'" cat_uri="'+cat.meta.uri+'" cat_order="'+cat.meta.order+'" current="'+current_cat+'"> <a href=\'#\'> <span> <i style="background:url(\''+cat.meta.uri_thumb+'\') no-repeat scroll 50% 50% #EEEEEE; background-size: cover;"></i> </span> </a>\
				<div class="category_item-details_container">\
				  <div class="details_cat_name">'+cat.meta.name+'</div>\
				  <div class="details_cat_imgcount">'+cat.meta.count+' '+image_word+'</div>\
				</div>\
			  </li>';

	//Re-rendering : Postion is important
	if(currPos==undefined){
		$('div#sidebar-category_container ul#cat_list').append(cat_HTML);
	} else {
		if(currPos.prevId==null){//1st
			$('div#sidebar-category_container ul#cat_list').prepend(cat_HTML);
		} else {//After a cat
			$('div#sidebar-category_container ul#cat_list li[cat_id='+currPos.prevId+']').after(cat_HTML);
		}
	}

}
/**
 * Render cat list
 */
var c;
function renderCategoryList(json__cat_list){
	var items = [];
	var className, title_, cat_id, current_cat;
	//Generate the HTML
	$.each(json__cat_list, function(key, cat) {

		if(cat.meta.visible!="1"){
			className='not_visible';
			title_='[Hidden] '+cat.meta.name + ': &#10;'+ cat.meta.description;
		}
		else{
			className='';
			title_=cat.meta.name + ': &#10;'+ cat.meta.description;
		}

		//REMOVE
		cat_id=key;

		//current_cat=yes if selected. Init its the default cat.
		var a_class='';
		if(default_cat_id==cat_id){
			current_cat='true';
			className+=' current_category ui-selected';
			a_class='class="current_category"';
		}
		else{
			current_cat='false'
		}

		//cat.meta.uri_thumb='sample_128_128.jpg';
		//cat.meta.description="desc";
		items.push('\
				  <li class="category_item '+className+'" cat_id="'+cat_id+'" cat_name="'+cat.meta.name+'" title="'+title_+'" cat_uri="'+cat.meta.uri+'" cat_order="'+cat.meta.order+'" current="'+current_cat+'"> <a '+a_class+'> <span> <i style="background:url(\''+cat.meta.uri_thumb+'\') no-repeat scroll 50% 50% #EEEEEE; background-size: cover;"></i> </span> </a>\
					<div class="category_item-details_container">\
					  <div class="details_cat_name">'+cat.meta.name+'</div>\
					  <div class="details_cat_imgcount">'+cat.meta.count+' pics</div>\
					</div>\
				  </li>');
	});
	//Render it
	$('<ul/>', {
		'id': 'cat_list',/*future use*/
		html: items.join('')
	}).appendTo('div#sidebar-category_container');
}



/**
 * Render img list
 */
function renderImageList(imgs_list,currPos){
	var flag_NOIMGS=false;
	if(imgs_list==null){
		flag_NOIMGS=true
	}

	if(!flag_NOIMGS){
		var items = [];
		var cat_id;
		d('imgs_list:'+imgs_list.length)
		$.each(imgs_list, function(key, img) {
			cat_id=img.parent_id;
			items.push(renderImage(img,cat_id));
		});

		//Improve
		//Hide content
		$('div#content-listbar').html('');
		//$('div#content-listbar ul').css("display","none").attr('current','no');

		//Re-rendering : Postion is important
		if(currPos==undefined){

			$('<ul/>', {
				'cat_id': cat_id,
				'current':'yes',
				'class' : 'unstyled',
				'id': cat_id+'__img_list',/*future use*/
				html: items.join('')
			}).appendTo('div#content-listbar');


		} else {
			if(currPos.prevId==null){//1st
				$('div#content-listbar ul#'+cat_id+'__img_list').prepend(items.join(''));
			} else {//After a img
				$('div#content-listbar  ul#'+cat_id+'__img_list li[img_id='+currPos.prevId+']').after(items.join(''));
			}
		}

	} else {
		//Show  a message
		$('div#content-listbar').html('');
		//$('div#content-listbar ul').css("display","none").attr('current','no');
		$('<ul/>', {
			'cat_id': cat_id,
			'current':'yes',
			'id': cat_id+'__img_list',/*future use*/
			html: wm.constants.msgs['HTML']['NO_IMGS_TO_DISPLAY']
		}).appendTo('div#content-listbar');

	}

}

/**
 * This function will generate single image item HTML only
 */
function renderImage(img,cat_id){
	var className, title_, cat_id;
	if(img.visible!="1"){
		className='not_visible';
		title_='[Hidden] '+img.name;
	}
	else{
		className='';
		title_=img.name;
	}

	//img.uri_thumb=img_path+'sample_128_128.jpg';
	//img.description="desc";

	return '<li class="image_item '+className+'" img_id="'+img.id+'" cat_id="'+img.parent_id+'" img_name="'+img.name+'" title="'+title_+'" img_uri="'+img.uri+'" img_order="'+img.order+'"> <a> <span> <i style="background:url(\''+img.uri_thumb+'\') no-repeat scroll 50% 50% #EEEEEE; background-size: cover;"></i> </span> </a>\
                <div class="image_item-details_container">			\
                  <div class="details_img_name">'+img.name+'</div>		\
                  <div class="details_img_imgcount">Drag icon</div>	\
                </div>												\
              </li>';
}


/**
 * This function will generate cat cover image item HTML only
 */
function generateCatCoverImageItemHTML(img){
	return '<i id="cover_img_container" title="'+img.name+'" style="background:url(\''+img.uri_thumb+'\') no-repeat scroll 50% 50% #EEEEEE;background-size:cover;display:block;height:96px;width:128px;"></i>';
}



/**
 * This function will handle selection ????
 */
function makeCategoriesSelectable(cat_id){
	var selector;
	if(typeof cat_id=='undefined'){
		selector='.category_item'
	}
	else{
		selector='[cat_id='+cat_id+']';
	}

	$('ul#cat_list li'+selector)
	.mousedown(function(e) {
		$(window).mousemove(function() {
			isDragging = true;
			d('makeCategoriesSelectable: mousemove');
			$(window).unbind("mousemove");
			cat_item__mousedown_mousemoveHandler(e);
		});
	})
	.mouseup(function(e) {
		var wasDragging = isDragging;
		isDragging = false;
		$(window).unbind("mousemove");
		if (!wasDragging) { //was clicking
			d('makeCategoriesSelectable: nodrag mouseup');
			cat_item__mousedown_mouseupHandler(e);
		} else { //was clicking
			d('makeCategoriesSelectable: drag mouseup');
			cat_item__mousedown_mousemove_mouseupHandler(e);
		}
	});
}

/**
 * If cat_id == 'undefined' then make current
 * else bind all images of a particular cat.
 */
function makeImagesSelectable(cat_id,img_id){

	var selector1,selector2;
	if(typeof cat_id=='undefined'){
		selector1='[current=yes]';
	}
	else{
		selector1='[cat_id='+cat_id+']';
	}
	if(typeof img_id=='undefined'){
		selector2='.image_item';
	}
	else{
		selector2='[img_id='+img_id+']';
	}

	$('ul'+selector1+' li'+selector2).unbind('mousedown').unbind('mouseup');

	$('ul'+selector1+' li'+selector2)
	.mousedown(function(e) {
		$(window).mousemove(function() {
			isDragging = true;
			d('mousemove');
			$(window).unbind("mousemove");
			d('image_item__mousedown_mousemoveHandler> [mousemove]');
			image_item__mousedown_mousemoveHandler(e);
		});
	})
	.mouseup(function(e) {
		var wasDragging = isDragging;
		isDragging = false;
		$(window).unbind("mousemove");
		if (!wasDragging) { //was clicking
			d('image_item__mousedown_mouseupHandler> [nodrag mouseup]');
			image_item__mousedown_mouseupHandler(e);
		} else { //was clicking
			d('drag mouseup');
			if(!isPropertiesPanelOpen){
				d('image_item__mousedown_mousemove_mouseupHandler> [!isPropertiesPanelOpen]');
				image_item__mousedown_mousemove_mouseupHandler(e);
			} else {
				d('PropertiesPanel___image_item__mousedown_mousemove_mouseupHandler> [isPropertiesPanelOpen]');
				PropertiesPanel___image_item__mousedown_mousemove_mouseupHandler(e);
			}
		}
	});
}

function getSelectedItems(type,asArray){ //no need to return as 'not an array'.
	var selectedItemsArray=[], path_, item_id;
	if(type=='image'){
		path_='div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li';
		type_id='img_id'
	}
	else{
		path_='div#gallery_container #panel-sidebar #sidebar-category_container ul li.category_item';
		type_id='cat_id'
	}
	var obj=$(path_);
	obj.each(function(){
		if($(this).hasClass('ui-selected') && !$(this).hasClass('ui-sortable-placeholder')){
			item_id = $(this).attr(type_id);
			selectedItemsArray.push(item_id);
		}
	});
	return selectedItemsArray;
}

//@Note: function getDepthDetails(type,id) moved to adminconsole.jquery.js - used by gallery n video

function cat_item__mousedown_mousemoveHandler(e) {
	//Show recylebin
	//Drag
	////Sorting
	////Drop into bin
	d('cat moving')
	cat_id=e.currentTarget.getAttribute("cat_id");
	/*	depthBeforeDrag=getDepthDetails('category',cat_id);
	d('depthBeforeDrag.depth:'+depthBeforeDrag.depth+',cat_id:'+cat_id)*/
	clickedImg=$('ul#cat_list li[cat_id='+e.currentTarget.getAttribute("cat_id")+']');
	if(!clickedImg.hasClass('ui-selected')){
		clickedImg.addClass('ui-selected');
	}
}


function cat_item__mousedown_mousemove_mouseupHandler(e) {
	showSelectedImageOrCategoryControllerButtons('category');
	lastDroppedCategory=e.currentTarget.getAttribute("cat_id");

	//Make current clicked as current_category class
	changeCurrentCategory(lastDroppedCategory);
}

var clickedImg;
function cat_item__mousedown_mouseupHandler(e) {
	var cat_id=e.currentTarget.getAttribute("cat_id");
	clickedImg=$('ul#cat_list li[cat_id='+cat_id+']');
	if(e.ctrlKey){
		d('ctrlKey')
		//Set ui-selected class, do not alter state of other selected. images
		toggleSelectionStatus(clickedImg)
	//For ctrl click.. If A is selectd, I then ctrl click B. Then B imgs load. But then i ctrl clk B, only a is selected. But B is show as current gal...
	// So simply ..
	} else if(e.shiftKey){
		//
		d('shiftKey')

	} else {//Note: Init state: 1+ imgs selected
		d('cat_item__mousedown_mouseupHandler: jst a clk-cat')
		//De-select all other images
		$('ul#cat_list li.category_item').removeClass('ui-selected');

		//Select the clicked img
		clickedImg.addClass('ui-selected');

		//Clicked category id
		//Check if category details already exist
		//always exist
		if(typeof tree[cat_id]=='undefined'){
			d('error: bindEventHandlerToCategoryItem cat doesnt exist. Not insync');
			return null;
		}

		//mask the current image list
		showImageListMask(true);
		//Request for img list
		getCategoryJSONData(cat_id,'get_image_list',true);

		//Make current clicked as current_category class
		changeCurrentCategory(e.currentTarget.getAttribute("cat_id"));

	}


	showSelectedImageOrCategoryControllerButtons('category');//,e.currentTarget.getAttribute("cat_id")
}


/**
 *  Make current clicked as current_category class
 */
function changeCurrentCategory(clickedCatId){
	var current_cat_id=get_current_cat().id;
	//d('changeCurrentCategory>'+get_current_cat().id+', current_cat_id:'+current_cat_id)
	$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+current_cat_id+']').attr('current','false').removeClass('current_category');
	//This is required for 'Not Droppable' red effect class
	$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+current_cat_id+'] a').removeClass('current_category');

	$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+clickedCatId+']').attr('current','true').addClass('current_category');
	//This is required for 'Not Droppable' red effect class
	$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+clickedCatId+'] a').addClass('current_category');
	lastSelectedCatId=clickedCatId;
}

function image_item__mousedown_mousemove_mouseupHandler(e) {
	//Drop

	//Id of image being dragged
	var v_img_id=e.currentTarget.getAttribute("img_id");
	d('img_id:'+e.currentTarget.getAttribute("img_id")+', over cat_id'+$(this).attr('cat_id'));

	lastDroppedImage=e.currentTarget.getAttribute("img_id");

	//Check if over a cat
	$('ul#cat_list li.category_item').each(function(){
		if($(this).hasClass('ui-state-active')){ //check if img item being dragged over a cat


			//Dropping into a category
			//front end
			var new_cat_id=$(this).attr('cat_id');
			var old_cat_id=e.currentTarget.getAttribute("cat_id");
			//d2('new_cat_id-'+new_cat_id+', old'+old_cat_id)
			d('new_cat_id-'+new_cat_id+', old'+old_cat_id)
			if(old_cat_id==new_cat_id){
				//droppping into same cat!
				//will auto revert to original postn
				$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+v_img_id+']').removeClass('category_item_class');
				var notification_description = '';
				var notification_id=notificationHandler('quick','warning','Image is already part of this gallery.',notification_description,notification_timeout);
				return;
			} else {
				//Images dropped into a new category

				//Hide current dragged selected img. Also remove multiple dragged imgs (class: category_item_class) effect.
				//reverting done automatically
				$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+v_img_id+']').removeClass('category_item_class');

				//Hide/Lock(ENHANCEMENT: dnt hide.. set class. shldnt be affected by any user input.) selected images
				//Hide other selected images.
				var selectedImgArray=getSelectedItems('image');
				$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li').each(function(){
					//.ui-selected--dropped_into_cat--db_not_updated > Has been dropped but not confirmed by back-end
					if($(this).hasClass('ui-selected')){
						$(this).removeClass('ui-selected').addClass('ui-selected--dropped_into_cat--db_not_updated')
					}
				//For now: ui-selected--dropped_into_cat--db_not_updated > hidden
				});

				//Show notification
				var new_cat_name = $('ul#cat_list li[cat_id='+new_cat_id+']').attr('cat_name')
				var old_cat_name = $('ul#cat_list li[cat_id='+old_cat_id+']').attr('cat_name')
				var notification_description = 'Copying Images from <b>\''+old_cat_name+'\'</b> into <b>\''+new_cat_name+'\'</b>.';
				var notification_id=notificationHandler('quick','warning',wm.constants.msgs['BASIC']['SAVING'],notification_description);

				//Wait for Back-end to complete transfer
				//				d2('arr:'+selectedImgArray)
				d('arr:'+selectedImgArray)
				storeImagesIntoCategories(new_cat_id,old_cat_id,selectedImgArray,notification_id,new_cat_name,old_cat_name,selectedImgArray.length);
			//alert('ajax sent');
			}
		} else {
			//Not over a cat
			//So will revert to original position.
			//
			$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+v_img_id+']').removeClass('category_item_class');



		}
	});

	showSelectedImageOrCategoryControllerButtons('image');
}



function image_item__mousedown_mousemoveHandler(e) {
	//Drag
	if(isPropertiesPanelOpen){
		$('ul#category_cover_img li i#cover_img_container').hide();
		$('span#category_cover_img_dropbox_text_dropping').show();
	}


	////Sorting
	////Drop into Category
	img_id=e.currentTarget.getAttribute("img_id");
	clickedImg=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+e.currentTarget.getAttribute("img_id")+']');



	//Make the z-index higher than properties page
	d('moving img_id'+img_id)
	if(!clickedImg.hasClass('ui-selected')){
		clickedImg.addClass('ui-selected');
		lastSelectedImgId=img_id;
	}
	var imgsel = getSelectedItems('image').length;
	if(imgsel>1){
		d('multiple selected:'+imgsel)
		clickedImg.addClass('category_item_class');
	}

//image_item__mousedown_mousemove_mouseupHandler(e)
}




function image_item__mousedown_mouseupHandler(e) {

	img_id=e.currentTarget.getAttribute("img_id");
	clickedImg=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+img_id+']');
	if(isPropertiesPanelOpen){
		if(isImagePropertiesShowing){
			showImagePropertyPage(clickedImg);
		}
	}


	if(e.ctrlKey){
		d('ctrlKey')
		//Set ui-selected class, do not alter state of other selected. images
		toggleSelectionStatus(clickedImg)
	} else if(e.shiftKey){
		var startId,endId;
		d('shiftKey')
		d('>lastSelectedImgId: '+lastSelectedImgId+', currId:'+img_id)
		//Get direction
		//-Get depth
		if( getDepthDetails('image',lastSelectedImgId).depth > getDepthDetails('image',img_id).depth ){
			//Top to down selection. lastSelectedImgId at bottom.
			startId=img_id;
			endId=lastSelectedImgId;
		} else {
			d('heer: ')
			startId=lastSelectedImgId;
			endId=img_id;
		}
		d('>startId: '+startId+', endId:'+endId)

		//Remove all selected
		unselectAll('image');
		var FLAG_startSelecting=false;
		$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").each(function(){
			if(!FLAG_startSelecting){
				if( $(this).attr('img_id')== startId){
					FLAG_startSelecting=true;
					$(this).addClass('ui-selected');
				}
			} else {
				if( $(this).attr('img_id')== endId){
					$(this).addClass('ui-selected');
					return false;/*Jump out*/
				}
				$(this).addClass('ui-selected');
			}
		});
	} else {//Note: Init state: 1+ imgs selected
		d('jst a clk')
		//De-select all other images
		$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item').removeClass('ui-selected');

		//Select the clicked img
		clickedImg.addClass('ui-selected');
		lastSelectedImgId=img_id;

	}
	showSelectedImageOrCategoryControllerButtons('image');//,e.currentTarget.getAttribute("img_id")
}

function unselectAll(type){
	if(type=='category'){
		$('ul#cat_list li.category_item').removeClass('ui-selected');
	}
	else{
		$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item').removeClass('ui-selected');
	}
}


function PropertiesPanel___image_item__mousedown_mousemove_mouseupHandler(e) {
	//Drop
	//Id of image being dragged
	var v_img_id=e.currentTarget.getAttribute("img_id");
	d('img_id:'+e.currentTarget.getAttribute("img_id")+', as cover for:'+$(this).attr('cat_id'));

	lastDroppedImage=e.currentTarget.getAttribute("img_id");

	$('ul#category_cover_img li i#cover_img_container').show();


	//Check if over dropbox
	$('div#gallery_container #floating-formbox #topbar-content #topbar-content-area div#content-area__category_properties ul#category_cover_img li').each(function(){
		if($(this).hasClass('CatCoverImg-ui-state-active')){ //check if img item being dragged over cover img dropbox


			var prop_cat_id=$('form#form__category_properties #cat_id').val();
			var draggedImg_cat_id=e.currentTarget.getAttribute("cat_id");
			if(prop_cat_id!=draggedImg_cat_id){
				//Img dragged from another category
				alert('[ERROR:Pls contact ur admin]\nYou are dragging an image from another category. A category\'s cover image needs to be from the same category. Please open the category and then open the category\'s Properties panel.')
				return;
			}
			//Dropping into a cover img dropbox
			//front end
			manageDropBox(e.currentTarget.getAttribute("cat_id"),e.currentTarget.getAttribute("img_id"));

			$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+v_img_id+']').removeClass('category_item_class');
			$(gc__ff__cci + ' li').removeClass('CatCoverImg-ui-state-active');


		} else {
			//Not over a dropbox
			//So will revert to original position.
			//
			$('ul#category_cover_img li i#cover_img_container').show();
			$('span#category_cover_img_dropbox_text_dropping').hide();

			$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+v_img_id+']').removeClass('category_item_class');
		}
	});

	showSelectedImageOrCategoryControllerButtons('image');
}

/**
 * if cover img exist, will remove it. then gen the img HTML and render it. hides all spans within
 */
function manageDropBox(cat_id,img_id){
	var coverImg=undefined;
	//error handling
	if(cat_id!=undefined){
		if(tree[cat_id].images!=null){
			if(img_id!=undefined){
				coverImg=tree[cat_id].images[img_id];
			} else {
				coverImg=tree[cat_id].images[tree[cat_id].meta['cover_id']];
			}
		} else {
			d('manageDropBox 1> imgs null:'+tree[cat_id].images)
		}
	} else {
		d('manageDropBox 2> cat id:'+cat_id)
	}

	coverImgInstance = $('div#gallery_container #floating-formbox #topbar-content #topbar-content-area div#content-area__category_properties ul#category_cover_img li i#cover_img_container');
	coverImgContainer = $('div#gallery_container #floating-formbox #topbar-content #topbar-content-area div#content-area__category_properties ul#category_cover_img li');
	if(coverImgInstance.length==1){
		coverImgInstance.remove();
	}

	if(coverImg==undefined){
		return;
	} else {
		coverImgContainer.append(generateCatCoverImageItemHTML(coverImg));
		coverImgContainer.children('span').hide();
		$('a#remove_cover_image').show();
		$('#cat_cover_id').val(coverImg.id);
	}
}


function showSelectedImageOrCategoryControllerButtons(type,override){
	var selectedItems_list;
	var prefix,type_;

	if(type=='category'){
		type_='cat';
		//lastSelectedCatId=v_lastSelectedId;
		selectedItems_list=getSelectedItems('category');
		prefix='div#gallery_container div#panel-sidebar div#sidebar-menubar ';
	} else {
		type_='img';
		//lastSelectedImgId=v_lastSelectedId;
		selectedItems_list=getSelectedItems('image');
		prefix='div#gallery_container #panel-content #topbar-menu ';
	}

	d('showSelectedImageOrCategoryControllerButtons: selectedItems_list='+selectedItems_list)

	if(override){
		selectedItems_list='';
	}

	if(selectedItems_list.length==0){
		$(prefix+' div[button=btn_del_'+type_+']').animate({
			opacity:'0.3'
		});
		$(prefix+' div[button=btn_vis_'+type_+']').animate({
			opacity:'0.3'
		});
		$(prefix+' div[button=btn_edit_'+type_+']').animate({
			opacity:'0.3'
		});
	//		$(prefix+' div[button=btn_del_'+type_+']').hide("slide", { direction: "left" }, 1000);
	//		$(prefix+' div[button=btn_vis_'+type_+']').hide("slide", { direction: "left" }, 1000);
	//		$(prefix+' div[button=btn_edit_'+type_+']').hide("slide", { direction: "left" }, 1000);
	} else if(selectedItems_list.length==1){
		$(prefix+' div[button=btn_del_'+type_+']').animate({
			opacity:'1'
		});
		$(prefix+' div[button=btn_vis_'+type_+']').animate({
			opacity:'1'
		});
		$(prefix+' div[button=btn_edit_'+type_+']').animate({
			opacity:'1'
		});
	//		$(prefix+' div[button=btn_del_'+type_+']').show("slide", { direction: "right" }, 1000);
	//		$(prefix+' div[button=btn_vis_'+type_+']').show("slide", { direction: "right" }, 1000);
	//		$(prefix+' div[button=btn_edit_'+type_+']').show("slide", { direction: "right" }, 1000);
	} else if(selectedItems_list.length>1){
		$(prefix+' div[button=btn_del_'+type_+']').animate({
			opacity:'1'
		});
		$(prefix+' div[button=btn_vis_'+type_+']').animate({
			opacity:'1'
		});
		$(prefix+' div[button=btn_edit_'+type_+']').animate({
			opacity:'0.3'
		});
	//		$(prefix+' div[button=btn_del_'+type_+']').show("slide", { direction: "right" }, 1000);
	//		$(prefix+' div[button=btn_vis_'+type_+']').show("slide", { direction: "right" }, 1000);
	//		$(prefix+' div[button=btn_edit_'+type_+']').hide("slide", { direction: "left" }, 1000);
	}
}




var event_;
var img_id;


function toggleSelectionStatus(clickedImg){
	//Check if already selected
	if(clickedImg.hasClass('ui-selected')){
		//De-select it
		clickedImg.removeClass('ui-selected');
	} else {
		//Select it
		clickedImg.addClass('ui-selected');
		lastSelectedImgId=clickedImg.attr('img_id');
	}
}



function removePlaceHolder(o){
	var obj=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+o.attr('img_id')+'_clone]');
	if(obj.size()==1){
		obj.remove();
	}
}

function createPlaceHolder(o){
	d(o.attr('img_id')+'len>'+$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+o.attr('img_id')+']').size())
	if($('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+o.attr('img_id')+'_clone]').size()==0){
		newClassNameList = 'image_item';//o.attr('class').replace('ui-selected-clone','');
		o.after('<li img_id="'+o.attr('img_id')+'_clone" class="'+newClassNameList+'" style="visibility: hidden; height:'+o.height()+'px;"></li>')
	}
}













function bindEventHandlerToSelectedImagesTopbarMenuButtons(){
	//Bind a click event handler to Delete button
	$("div#gallery_container #panel-content #topbar-menu div[button=btn_del_img]").bind('click', function() {
		deleteImages();
		return false;
	});
	//Bind a click event handler to Edit button
	$("div#gallery_container #panel-content #topbar-menu div[button=btn_edit_img]").bind('click', function() {
		isImagePropertiesShowing=true;
		isCatPropertiesShowing=false;
		editImage(lastSelectedImgId);
		return false;
	});

	//VISIBILITY
	//Bind a click event handler to Visible button for images
	$("div#gallery_container #panel-content #topbar-menu div[button=btn_vis_img]").bind('click', function() {
		toggleVisiblity('image');
		return false;
	});

	//Bind a click event handler to Upload button
	$("div#gallery_container #panel-content #topbar-menu div[button=btn_upl_img]").bind('click', function() {
		uploadImages();
		return false;
	});
}

function editImage(selectedId){
	//get id from
	showImagePropertyPage($('div#gallery_container #panel-content #panel-content-container #content-listbar ul[current=yes] li[img_id='+selectedId+']'));
}

function updateJSONVisibility(type,visibility,obj){
	var current_cat_id=get_current_cat().id;
	var type_id;

	if(type=='category'){
		type_id='cat_id';
		$.each(obj, function(key, item) {
			tree[$(item).attr(type_id)].meta.visible=visibility;
		});
	} else {
		type_id='img_id';
		$.each(obj, function(key, item) {
			tree[current_cat_id].images[$(item).attr(type_id)].visible=visibility;
		});
	}


}

function toggleVisiblity(type){
	var obj;
	var visibility;
	if(type=='category'){
		obj=$('div#gallery_container div#panel-sidebar div#sidebar-category_container ul#cat_list li.ui-selected');
	} else {
		obj=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected');
	}
	if(obj.size()==0){
		return
	}
	else {

		var img_id;
		var count=1;

		//Render changes
		obj.each(function(){
			//read 1st visibility value.
			if(count==1){
				if($(this).hasClass('not_visible')){
					;
					visibility=1;
				}
				else{
					visibility=0;
				}
			}
			return;//break out. We only want the 1st value
		});

	}
	//Update backend
	postVisibility(type,visibility,(gallery_post_uri['category_image']['visibility']),'item_ids',obj);
}


//@Note: function postSortOrder(old_id,new_id,post_uri) moved to adminconsole.jquery.js - used by gallery n video


/**
 * This function handles visibility using AJAX
 */
function postVisibility(type,visibility,uri,post_uri_name,selObj){
	var selItemArray=[];
	var setVisible=visibility;
	if(type=='image'){
		selItemArray=getSelectedItems('image')
	}
	else{
		selItemArray=getSelectedItems('category')
	}
	d('selItemArray:'+selItemArray)
	wm.admin.utility.ajax.post({
		url: wm.get_url(uri),
		data:'visible='+visibility+'&'+convertArrayToUrl(post_uri_name,selItemArray),
		on_success: function(response, textStatus, XMLHttpRequest) {
			if(response.status== wm.constants.status.success ){

				//Render changes
				selObj.each(function(){
					//Set all to the visibility value
					$(this).removeClass('not_visible');
					if(!setVisible){
						$(this).addClass('not_visible');
					}
				});

				//Update JSON tree
				updateJSONVisibility(type,visibility,selObj);

			//				var notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);

			}
		}
	});
}

function REMOVEbindEventHandlerToSelectedImagesTopbarMenuButtons(){
	//Bind a click event handler to Move button
	$("div#gallery_container #panel-content #topbar-menu div[button=btn_move_cat]").bind('click', function() {

		var $arr=$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected"),count_=$arr.length;
		$arr.each(function(){
			$(this).removeClass("ui-selected").addClass("ui-selected-clone");
			$(this).children("div.image_item-details_container").children("div.details_img_imgcount").html(count_);
			$(this).children("div.image_item-details_container").show();
		});

		$( "#img_list" ).selectable( "destroy" );

		$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone").draggable({
			revert: false,
			//			containment: "#gallery_container" ,
			create: function(event, ui){
				d("create");
				o1=ui
				$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone').each(function(){
					$(this).css({
						'position':'absolute'
					})
					$(this).css({
						'position':'absolute'
					})
					var img_id=$(this).attr('img_id')
					createPlaceHolder($('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+img_id+']'));
				});

			},
			start: function(event, ui){
				d("start");
				o2=ui;
			/*var img_id=o2.helper[0].getAttribute('img_id');
				ooo=$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id='+img_id+']');
				//$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id=11]').offset()
				$('div#gallery_container #panel-content #panel-content-container #content-listbar ul li[img_id=11]').css({'position':'absolute'})
				var galleryOffset=$('#gallery_container').offset();
				//var position=ooo.offset(),l=position.left+galleryOffset.left,t=position.top+galleryOffset.top;
				var position=ooo.offset(),l=position.left+galleryOffset.left,t=position.top+galleryOffset.top;
				createPlaceHolder(ooo);
				ooo.css({'position':'absolute'});//,'top':t.toString(),'left':l.toString()
				ooo.offset({top:t,left:l});
				//ooo.offset({top:'0',left:'0'});
				$('#dummy').offset({top:t,left:l});
				d('l:'+l+' , t:'+t)*/
			////$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone")
			},//o2.helper[0].getAttribute('img_id')
			drag: function(event, ui){
				d("drag");
				o3=ui;
				d(o3.position.left+','+o3.position.right)
			},
			stop: function(event, ui){
				d("stop");
				o4=ui
			}
		});
		/*
                 */
		//	$( "div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone" ).draggable();
		$( "ul#cat_list li" ).droppable({
			accept: "div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone",
			activeClass: "ui-state-hover",
			hoverClass: "ui-state-active",
			drop: function( event, ui ) {
				$( this )
				.addClass( "ui-state-highlight" )
				.find( "p" )
				.html( "Dropped!" );
			}
		});
		/*		$("div#gallery_container #panel-content #panel-content-container #content-listbar ul").sortable({
			revert: true
		});
		$( "ul, li" ).disableSelection();
                 */
		return false;//Do not continue with default browser action. That is submit the form
	});
//End button=btn_move_cat

}

var o1,o2,o3,o4,ooo;
function bindEventHandlerToSidebarMenuButtons(){
	//Bind a click event handler to Visible button for categories
	$("div#gallery_container #panel-sidebar #sidebar-menubar div[button=btn_vis_cat]").bind('click', function() {
		toggleVisiblity('category');
		return false;
	});
	//Bind a click event handler to Visible button for categories
	$("div#gallery_container #panel-sidebar #sidebar-menubar div[button=btn_edit_cat]").bind('click', function() {
		isImagePropertiesShowing=false;
		isCatPropertiesShowing=true;
		editCategory(lastSelectedCatId);
		return false;
	});

	//Bind a click event handler to Save button
	$("div#gallery_container #panel-sidebar #sidebar-menubar div[button=btn_new_cat]").bind('click', function() {
		closePropertyPage(function(){
			var sectionName="newcategory_properties";
			//Show Properties page
			showPropertyPage('Categories','Create a new category',sectionName);
		});
		return false;//Do not continue with default browser action. That is submit the form
	});
	//Bind a click event handler to Delete button
	$("div#gallery_container #panel-sidebar #sidebar-menubar div[button=btn_del_cat]").bind('click', function() {
		deleteCategories();
		return false;//Do not continue with default browser action. That is submit the form
	});
}
function editCategory(selectedId){
	//get id from
	showCategoryPropertyPage($('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+selectedId+']'));
}

function showCategoryPropertyPage(obj){
	closePropertyPage(function(){
		var cat_id=obj.attr('cat_id'), sectionName="category_properties";
		populateCategoryPropertyPage(cat_id,sectionName);
		//Show Properties page
		showPropertyPage('Categories','Showing properties of <br /><b>'+obj.attr('cat_name')+ '</b> gallery',sectionName);
	});
}

function showImagePropertyPage(obj){
	//        closePropertyPage(function(){
	var img_id=obj.attr('img_id'),cat_id=obj.attr('cat_id'), sectionName="image_properties";
	populateImagePropertyPage(img_id,tree[cat_id].images[img_id],sectionName);
	//Show Properties page
	showPropertyPage('Images','Showing properties of <br /><b>'+obj.attr('img_name')+ '</b> gallery',sectionName);
//		});
}


/**
 * If cat_id == 'undefined' then event handlers applied to all images within current cat
 * ??
 */
function bindEventHandlerToImageItem(cat_id,img_id){

	var selector1,selector2;
	d('bindEventHandlerToImageItem: ')//(typeof cat_id=='undefined')

	if(typeof cat_id=='undefined'){
		selector1='[current=yes]';
		selector2='.image_item';
		makeImagesSelectable(cat_id);
	}
	else {
		selector1='[cat_id='+cat_id+']';
		selector2='.image_item';
		makeImagesSelectable(cat_id,img_id);
	}


	//Bind a click event handler to open image in fancybox
	$('div#gallery_container #panel-content #panel-content-container #content-listbar ul'+selector1+' li'+selector2).bind('dblclick', function() {
		//Open fancy box
		//PENDING
		//showImagePropertyPage($(this));
		return false;//Do not continue with default browser action. That is submit the form
	});

}

/**
 * Apply a mask over the current image list
 */
isImageListMaskActive=false;
function showImageListMask(applyMask){
	d('showImageListMask: maskactive='+ isImageListMaskActive+', applyMask='+applyMask)
	if($('div#gallery_container #panel-content #image-mask').length==0){
		$('div#gallery_container #panel-content').prepend('<div id="image-mask"></div>');
	}
	if(applyMask){
		if(isImageListMaskActive){
			return
		}
		isImageListMaskActive=true;
		//$.prettyLoader.show();
		//Limit the mask to the right side
		$('div#gallery_container #panel-content').css('position','relative');
		$('div#gallery_container #panel-content #image-mask').show(500)
	}else{
		//$.prettyLoader.hide();
		$('div#gallery_container #panel-content #image-mask').hide(300, function(){
			//Reset the CSS, else img wont drag beyond RHS
			$('div#gallery_container #panel-content').css('position','static');
			isImageListMaskActive=false;
		})
	}
}



/**
 * Apply a mask over the cat list
 */
function showCategoryListMask(applyMask){
	if($('div#gallery_container #panel-sidebar #category-mask').length==0){
		$('div#gallery_container #panel-sidebar').prepend('<div id="category-mask"></div>');
	}
	if(applyMask){
		//$.prettyLoader.show();
		//Limit the mask to the right side
		$('div#gallery_container #panel-sidebar').css('position','relative');
		$('div#gallery_container #panel-sidebar #category-mask').show(500)
	}else{
		//$.prettyLoader.hide();
		$('div#gallery_container #panel-sidebar #category-mask').hide(300,function(){
			//Reset the CSS, else img wont drag beyond RHS
			//$('div#gallery_container #panel-sidebar').css('position','static');
			})
	}
}

/**
 * If cat_id is 'undefined' then event handlers applied to all cats in list
 * On adding a new cat, we specify the cat_id and thus apply the events ONLY to this new cat
 */
function bindEventHandlerToCategoryItem(cat_id){
	/*
        var selector;
        if(typeof cat_id=='undefined'){selector='.category_item'}
        else{selector='[cat_id='+cat_id+']';}


	//Bind a click event handler to CatergoryItem
	$('div#gallery_container #panel-sidebar #sidebar-category_container ul li'+selector).bind('click', function(e) {

		//Clicked category id
		var cat_id=$(this).attr('cat_id');//e.currentTarget.parentNode.getAttribute("cat_id");
		var img_list;
		//Check if category details already exist
		//always exist
		if(typeof tree[cat_id]=='undefined'){
			d('error: bindEventHandlerToCategoryItem cat doesnt exist. Not insync');
			return null;
		}

		//mask the current image list
		showImageListMask(true);
		//Request for img list
		getCategoryJSONData(cat_id,'get_image_list',true);

		return false;//Do not continue with default browser action. That is submit the form
	});
	 */
	makeCategoriesSelectable(cat_id);
}



function bindEventHandlerToPropertyButtons(){
	$(path__content_area+" div[button=btn_del_img_details]").bind('click', function() {
		deleteImages($(this).attr('img_id'));
		return false;//Do not continue with default browser action. That is submit the form
	});
	$(path__content_area+" div[button=btn_set_cover_img]").bind('click', function() {
		setImgAsCoverImg($(this).attr('cat_id'),$(this).attr('img_id'));
		return false;//Do not continue with default browser action. That is submit the form
	});

	//Bind a click event handler to Save button
	$(gc__ff__tc+" input[button=btn_save_propertypanel_form_details]").bind('click', function() {
		postFormData(PropertiesPanel_activeform);
		return false;//Do not continue with default browser action. That is submit the form
	});
}



var path__content="div#gallery_container #floating-formbox #topbar-content";
var path__content_area=path__content+" #topbar-content-area";
//div#gallery_container #panel-sidebar #sidebar-category_container ul li.category_item
function showPropertyPage(PropertyPage_header,PropertyPage_title,sectionName){

	if(isPropertiesPanelOpen){}

	showCategoryListMask(true);
	//Set
	isPropertiesPanelOpen=true;
	PropertiesPanel_activeform='form__'+sectionName;//category_properties form__newcategory_properties   form__image_properties


	$(path__content+" #topbar-content-header").html(PropertyPage_header);
	$(path__content+" #topbar-content-title").html(PropertyPage_title);
	$(path__content_area+" #content-area__"+sectionName).show();
	$('div#floating-formbox').animate({
		left:'-5px'
	});
	$('div#floating-formbox').attr('collapsed','false');

}

function populateImagePropertyPage(img_id,image_instance,sectionName){

	if(image_instance==null){
		return null;/*ERROR*/
	}
	var path_=gc__ff__tca +" #content-area__"+sectionName+' form';

	var link_urls='<a target="blank_" href="'+image_instance.uri+'" title="Open in new window">[Original]</a>';
	link_urls+='&nbsp;&nbsp;<a target="blank_" href="'+image_instance.uri_thumb+'" title="Open in new window">[Thumbnail]</a>';
	$(path_+' span#img_links').html(link_urls);

	$(path_+' #img_id').val(image_instance.id);
	$(path_+' #img_name').val(image_instance.name);
	$(path_+' #img_description').val(image_instance.description);
	$(path_+' #img_alt').val(image_instance.alt);
	//$(path_+' #img_uri').val(image_instance.uri);
	//$(path_+' #img_uri_thumb').val(image_instance.uri_thumb);
	$(path_+' #img_name_url').val(image_instance.name_url);
	$(path_+' #img_created').html(image_instance.created);
	$(path_+' #img_modified').html(image_instance.modified);
	$(path_+' #img_visible').val(image_instance.visible);
}

function populateCategoryPropertyPage(cat_id,sectionName){

	var cat_instance=tree[cat_id].meta;

	if(cat_instance==null){
		return null;/*ERROR*/
	}
	var path_=gc__ff__tca +" #content-area__"+sectionName+' form';

	$(path_+' #cat_id').val(cat_instance.id);
	$(path_+' #cat_name').val(cat_instance.name);
	$(path_+' #cat_description').val(cat_instance.description);
	$(path_+' #cat_alt').val(cat_instance.alt);
	$(path_+' #cat_cover_id').val(cat_instance.cover_id);
	$(path_+' #cat_created').val(cat_instance.created);
	$(path_+' #cat_modified').val(cat_instance.modified);
	$(path_+' #cat_visible').val(cat_instance.visible);

	//Add the cover
	manageDropBox(cat_id);

}


/**
 * This function handles images into cats using AJAX
 *
 */
function storeImagesIntoCategories(new_cat_id,old_cat_id,selectedImgArray,notification_id,new_cat_name,old_cat_name,selectedCount){

	//Get the post url
	////d($('form#'+form_id).serialize())
	wm.admin.utility.ajax.post({
		url: wm.get_url(gallery_post_uri['image']['change_category']),
		data:'new_cat_id='+new_cat_id+'&old_cat_id='+old_cat_id+'&'+convertArrayToUrl('img_ids',selectedImgArray),
		notification : false,
		on_success: function(data, textStatus, XMLHttpRequest) {
			if(data.status == wm.constants.status.error){
				//Error occured
				//Rollback frontend.
				rollbackMovedImages(old_cat_id,new_cat_name,old_cat_name);
			//				var notification_description = 'Could not move Images from <b>\''+old_cat_name+'\'</b> into <b>\''+new_cat_name+'\'</b>.';
			//				notificationUpdate(notification_id,'quick',data.status,'Could not move images.',notification_description+'<br />'+wm.constants.msgs['ERROR']['CNT_ADM'],notification_timeout);
			} else {
				shiftImagesIntoNewCatAtFrontend(new_cat_id,old_cat_id,selectedImgArray,notification_id,new_cat_name,old_cat_name,selectedCount);
			}
		}
	});

}

/**
 * This function handles rollback effect when transfer of images from one cat to another fails at backend
 */
function rollbackMovedImages(old_cat_id,new_cat_name,old_cat_name){

	$('div#gallery_container #panel-content #panel-content-container #content-listbar ul[cat_id='+old_cat_id+'] li').each(function(){
		//.ui-selected--dropped_into_cat--db_not_updated > Has been dropped and NOT confirmed by back-end
		//For now: ui-selected--dropped_into_cat--db_not_updated > hidden
		if($(this).hasClass('ui-selected--dropped_into_cat--db_not_updated')){
			$(this).removeClass('ui-selected--dropped_into_cat--db_not_updated').addClass('ui-selected').fadeIn(1000)
		}
	});
}


/**
 * This function handles transfer of images from one cat to another at the frontend,
 * AFTER back end has successfully completed the transfer.
 */
function shiftImagesIntoNewCatAtFrontend(new_cat_id,old_cat_id,selectedImgArray,notification_id,new_cat_name,old_cat_name,selectedCount){
	//new_cat_id,old_cat_id,selectedImgArray

	refreshImgList(old_cat_id);

	//When imgs moved from one cat into another the follwg attributes of img change: img_order, cat_id
	//NOTE: We are removing the moved imgs from old cat
	var moved_images_collector = '';


	//BUG: $(this).outerHTML();$(this).remove(); cause placeholder error for multiple images selected..
	/*        $('div#gallery_container #panel-content #panel-content-container #content-listbar ul[cat_id='+old_cat_id+'] li').each(function(){

                //if($(this).hasClass('ui-selected--dropped_into_cat--db_not_updated')){$(this).remove();}
				//d($(this).attr('img_id') + ','+$(this).hasClass('ui-selected--dropped_into_cat--db_not_updated'))
				//$(this).remove();
        });
	 */
	//Notification
	//notificationDestroy(notification_id);
	var img_txt='image';
	if(selectedCount>1){
		img_txt='images'
	}
	var notification_description = 'Moved '+selectedCount+' '+img_txt+' from <b>\''+old_cat_name+'\'</b> into <b>\''+new_cat_name+'\'</b>.';
	notificationUpdate(notification_id,'quick','success','Moved '+selectedCount+' '+img_txt+'...',notification_description,notification_timeout);

	//Update cat img count
	updateCatImgCount(new_cat_id,parseInt(tree[new_cat_id].meta.count)+selectedCount);
	updateCatImgCount(old_cat_id,parseInt(tree[old_cat_id].meta.count)-selectedCount);

//-Re-rendering the entire target cat..

//-Fetch the JSON for the updated cat
//-render
}


function updateCatImgCount(cat_id,newCount){
	//Update tree
	newCount=parseInt(newCount);
	tree[cat_id].meta.count=newCount;

	var image_word='pics';
	if(newCount==1){
		image_word='pic'
	}

	//render it
	$('div#sidebar-category_container ul#cat_list li[cat_id='+cat_id+'] .details_cat_imgcount').html(newCount+' pics');
}



/**
 * This function render Upload form
 */
function uploadImages(){

	//show cat mask
	showCategoryListMask(true);

	//Hide the upload btn
	$('div#gallery_container #panel-content #topbar-menu div[button=btn_upl_img]').hide(500);

	//Passed as args to the
	arg_upload_target_name=get_current_cat().name;
	arg_upload_target_id=get_current_cat().id;
	$('#upload_box_status').html('Uploading to '+get_current_cat().name);

	$('#container-wrapper').scrollTo( '#upload_box', 800, {
		easing:'easeInQuad'
	} )

	$('#upload_box').width($('#gallery_container').width());
	$('#upload_box').slideDown('1000').fadeIn('500');

	//Check if not loaded
	if(loading_uploadify_using_iframes){
		//---IFRAME---
		if($('iframe_upload_module')){
			//Show loader
			showThrobber($('#upload_box'));

			//Write to DOM
			$('div#gallery_container div#upload_box').append('<iframe id="iframe_upload_module" name="iframe_upload_module" style=" width:100%;display:none;" src="http://lloyd-pc/ENCUBE_ADMIN/admin/testmodule/upload/abc/"></iframe>');

		//Now we wait, for the iframe to load. Once loaded a function will run and pass the reference of the namespace
		//in the view page to the parent. This tells the parent that it is ready and parent can now use this reference to communicate with it.
		} else {
		//Already loaded. Was hidden now re-show
		//PENDING
		}
	} else {
	//---IN LINE---
	//Do nothing
	//Already initialized.
	//Already rendered.
	}



}




function setImgAsCoverImg(cat_id,img_id){
	wm.admin.utility.ajax.post({
		url: wm.get_url(gallery_post_uri['category']['change_cover']),
		data:'cat_id='+cat_id+'&img_id='+img_id,
		on_success: function(response, textStatus, XMLHttpRequest) {
			//Success!
			if(response.status=='success'){
				//				var notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);
				//Update $tree
				//				d('here'+img_id)
				tree[cat_id].meta.cover_id=img_id;
				//Update HTML
				$.each(tree[cat_id].images, function(key, img) {
					if(img.id==img_id){
						//Set the new Cover img in HTML
						$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+cat_id+'] a span i').css('background-image','url("'+img.uri+'")')
						tree[cat_id].meta.uri=img.uri;
						tree[cat_id].meta.uri_thumb=img.uri_thumb;
						return false;
					}

				});

			} else {
			//				var notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);
			}
		}
	});

}



/**
 * This function handles deletion of Categories
 */
var t;
function deleteCategories(){
	var cat_id=get_current_cat().id;
	var delTheseCats_list=getSelectedItems('category');
	var confirmation=false;
	var defaultSelected=false;
	$.each(delTheseCats_list,function(index,id){
		if(default_cat_id==id){
			alert('You cannot delete the default category: \''+tree[default_cat_id].meta.name+'\'.');
			defaultSelected=true;
			return false;
		}
	});
	if(defaultSelected){
		return
	}

	var category_word='category';
	if(delTheseCats_list.length>1){
		category_word='categories';
	}
	//d('cat_names_list:'+cat_names_list)
	var cnt=delTheseCats_list.length,s_1;
	if(cnt>1){
		s_1=''
	}
	else{
		s_1=cnt
	}

	if(confirm('You are about to delete the '+s_1+' selected '+category_word+'.\n\nThis will permanently delete all the images within it. \nContinue deletion?')){
		confirmation=true
	}

	if(confirmation){

		//Make the deletion request
		wm.admin.utility.ajax.post({
			url: wm.get_url(gallery_post_uri['category_image']['del']),
			data:'item_type=category&'+convertArrayToUrl('item_ids',delTheseCats_list),
			//data:convertArrayToUrl('item_ids[]',delTheseCats_list),
			on_success: function(response, textStatus, XMLHttpRequest) {
				//Success!
				if(response.status=='success'){
//					var notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);

					//Permanently del the images from the cat.
					//-remove from $tree
					//-remove from HTML
					$.each(delTheseCats_list, function(key, cat_id) {
						d(key+','+cat_id+','+tree[cat_id]);
						delete tree[cat_id];
						removeCategory(cat_id);
					});
					//Select the 1st existing category
					var first_cat_instance=$("div#gallery_container #panel-sidebar #sidebar-category_container ul li:first-child");
					var first_cat_instance_id=first_cat_instance.attr('cat_id');

					//selected
					first_cat_instance.addClass('ui-selected');
					first_cat_instance.attr('current','true').addClass('current_category');
					//This is required for 'Not Droppable' red effect class
					first_cat_instance.children('a').addClass('current_category');

					//mask the current image list
					showImageListMask(true);
					//Request for img list
					getCategoryJSONData(first_cat_instance_id,'get_image_list',true);

					//Update the interface
					showSelectedImageOrCategoryControllerButtons('category',true); //PENDING

				} else {
//					var notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);
				}
			}
		});
	}
}

function removeCategory(cat_id){
	$("div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id="+cat_id+"]").fadeOut(250,function(){
		$(this).remove();
	});
}


/**
 * This function handles deletion of images
 */
function deleteImages(del_img_id){
	var image_word='image';
	var confirmation=false;
	var delTheseImgs_list;
	var cat_id=get_current_cat().id;
	var notification_id;

	if(typeof del_img_id!='undefined'){
		delTheseImgs_list=del_img_id;
	} else {
		delTheseImgs_list=getSelectedItems('image');
		if(delTheseImgs_list.length>1){
			image_word='images';
		}
	}

	if(confirm('You are about to delete '+delTheseImgs_list.length+' selected '+image_word+'\n\nContinue to delete the selected '+image_word+'?')){
		confirmation=true
	}

	if(confirmation){

		//notification_id=notificationHandler('quick','warning','Deleting '+image_word,'');

		//Make the deletion request
		wm.admin.utility.ajax.post({
			url: wm.get_url(gallery_post_uri['category_image']['del']),
			data:'item_type=image&'+convertArrayToUrl('item_ids',delTheseImgs_list),
			on_success: function(response, textStatus, XMLHttpRequest) {
				//alert(data);
				if(response.status=='success'){
					//notificationUpdate(notification_id,'quick',response.status,response.message,'',notification_timeout);
//					notification_id=notificationHandler('quick',response.status,response.message,'',notification_timeout);

					//Success!
					$.each(delTheseImgs_list, function(key, delImgId) {
						//Permanently del the images from the cat.
						//-remove from $tree
						delete tree[cat_id].images[delImgId];

						//-remove from HTML
						//Add a Wrapper
						var i=$("div#gallery_container #panel-content #panel-content-container #content-listbar ul[cat_id="+cat_id+"] li[img_id="+delImgId+"]");
						i.css({
							bottom: 'auto',
							right: 'auto',
							top: '0',
							position:'relative',
							margin:'22px 0 0'
						});
						i.wrap('<div class="slide-wrapper" style="font-size: 100%; background: none repeat scroll 0% 0% transparent; border: medium none; margin: 0 0 0 24px; padding: 0px; width: 142px; height: auto; float: left; position: relative; z-index: auto; top: 0px; left: 0px; bottom: 0px; right: 0px; overflow: hidden;" />');
						i.animate({
							left: '-=143',
							opacity:0,
						}, 1000, function() {
							// Animation complete.
							});
						i.parent('div.slide-wrapper').animate({
							width: '-=143',
							marginLeft:'-=24'
						}, 1000, function() {
							// Animation complete.
							i.parent('div.slide-wrapper').remove();
						});

						//remove from cover in $tree
						if(tree[cat_id].meta.cover_id==delImgId){
							tree[cat_id].meta.cover_id='';
							tree[cat_id].meta.uri='';
							tree[cat_id].meta.uri_thumb='';
							//remove as cover
							$('div#gallery_container #panel-sidebar #sidebar-category_container ul li[cat_id='+cat_id+'] a span i').css({
								'background-image':'url(\''+no_image_uri+'\')'
							});


						}

						//Update cat img count
						var newCount=parseInt(tree[cat_id].meta.count)-parseInt(delTheseImgs_list.length);
						updateCatImgCount(cat_id,newCount);

					});
					//Update the interface
					showSelectedImageOrCategoryControllerButtons('image',true); //PENDING
				} else {
//					notificationUpdate(notification_id,'quick',response.status,response.message,'',notification_timeout);
				}
			}
		});
	}
}





/**
 * This function handles form submission using AJAX
 */
function postFormData(form_id){

	var type,action;
	var edited_cat,edited_img;
	var formInstance = $('form#'+form_id);

	//var notification_id=notificationHandler('quick','warning',wm.constants.msgs['BASIC']['SAVING'],'');

	if(form_id=='form__category_properties'){
		if($.trim($('form#'+form_id + ' .field_container table tbody tr td input#cat_name').val()).length==0){
			alert('Category name cannot be left blank');
			return;
		}
		edited_cat=$('form#form__category_properties input#cat_id').val();
		type='category';
		action='edit';
	} else if(form_id=='form__newcategory_properties') {
		//form validation
		if($.trim($('form#'+form_id + ' .field_container table tbody tr td input#cat_name').val()).length==0){
			alert('Category name cannot be left blank');
			return;
		}
		type='category';
		action='new_categ';
	} else if(form_id=='form__image_properties') {
		edited_img=$('form#form__image_properties input#img_id').val();
		if($.trim($('form#'+form_id + ' .field_container table tbody tr td input#img_name').val()).length==0){
			alert('Image name cannot be left blank');
			return;
		}
		type='image';
		action='edit';
	}

	wm.admin.utility.ajax.post({
		url: wm.get_url(gallery_post_uri[type][action]),
		data:formInstance.serialize(),
		on_success: function(response, textStatus, XMLHttpRequest) {
			if(response.status== wm.constants.status.success ){
				//d(response.status+','+response.message)
				//notificationUpdate(notification_id,'quick',response.status,response.message,'',notification_timeout);
				if(type=='category' && action=='new_categ'){
					//Get new category info
					getCategoryJSONData(response.data.cat_id,'new_cat');
				} else if(type=='category' && action=='edit') {
					//X Update the tree,//Update front end-
					//Get the updated cat info
					getCategoryJSONData(edited_cat,'get_properties');
				} else if(type=='image' && action=='edit') {

					getImageJSONData(get_current_cat().id,edited_img,'get_properties');

				}
			}
		}
	});

/*
	$.ajax({
		url: wm.get_url(gallery_post_uri[type][action]),
		type: 'POST',
		data:formInstance.serialize(),
		success: function(response, textStatus, XMLHttpRequest) {
			if(response.status=='success'){
				//d(response.status+','+response.message)
				//notificationUpdate(notification_id,'quick',response.status,response.message,'',notification_timeout);
				if(type=='category' && action=='new_categ'){
					//Get new category info
					getCategoryJSONData(response.data.cat_id,'new_cat');
				} else if(type=='category' && action=='edit') {
					//X Update the tree,//Update front end-
					//Get the updated cat info
					getCategoryJSONData(edited_cat,'get_properties');
				} else if(type=='image' && action=='edit') {
					//
					getImageJSONData(get_current_cat().id,edited_img,'get_properties');

				}
			} else {
				//notificationUpdate(notification_id,'quick',response.status,response.message,'',notification_timeout);
			}
		//postFormData('category','new'
		},
		error:  function(XMLHttpRequest, textStatus, errorThrown) {
			//Show error message above the area.
			//alert('ERROR: A problem occured. Could not post data.');
			//notificationUpdate(notification_id,'quick','error',wm.constants.msgs['ERROR']['CNG_NS'],wm.constants.msgs['ERROR']['INT_CNNT'],notification_timeout);
		}
	});
	*/
}




function convertArrayToUrl(varName,arr_list){
	var pairs = [];
	for (var index=0;index<arr_list.length;index++)
		pairs.push(varName+'[]=' + encodeURIComponent(arr_list[index])); //Needs to be an array.
	return pairs.join('&');
}


function convertToUrl(template_list){
	var pairs = [];
	for (var index=0;index<template_list.length;index++)
		pairs.push('template_list=' + encodeURIComponent(template_list[index]));
	return pairs.join('&');
}






function reEnableSelectable(e){
	d('reEnableSelectable: selectable-enabled');
	if(!dragHandleClicked){
		$( "#img_list" ).selectable( "enable" );
	}
	dragHandleClicked=false;
	e.stopImmediatePropagation();
}

function test1(){ //after selection complete
	d('test1: selectable-disabled');
	$( "#img_list" ).selectable( "disable" );

	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").not('.ui-selected').unbind('click', reEnableSelectable);

	//unbind prev binds

	//$("li.img_item").not('.ui-selected').unbind('mouseover', abc);//bind
	//$("li.ui-selected").bind('mouseover', abc);//bind
	/**/
	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").not('.ui-selected').unbind('mouseenter', showDrag);
	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").not('.ui-selected').unbind('mouseleave', hideDrag);



	var img_id=$(this).attr('img_id')
	//d('mouseover ui-selectd');

	var $arr=$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected"),count_=$arr.length;
	$arr.each(function(){
		//$(this).removeClass("ui-selected").addClass("ui-selected-clone");
		$(this).children("div.image_item-details_container").children("div.details_img_imgcount").html(count_);
	//$(this).children("div.image_item-details_container").show();
	});

	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected").bind('mouseenter', showDrag);
	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected").bind('mouseleave', hideDrag);
	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected div.image_item-details_container").bind("mousedown",dragdrop);
	$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.image_item").bind('mouseup',reEnableSelectable);

}
function showDrag() {
	//d('mouseenter ui-selectd');
	$(this).children("div.image_item-details_container").fadeIn(250);
}
function hideDrag() {
	//d('mouseleave ui-selectd');
	$(this).children("div.image_item-details_container").fadeOut(250);
}
var e_obj;
var dragHandleClicked;
function dragdrop(e){
	e_obj=e;
	dragHandleClicked=true;
	//e_obj.currentTarget.parentNode.getAttribute("img_id")
	d('clkd dragdrop'+e);
	e.stopImmediatePropagation();
}

/*


		$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone").bind('mouseenter', function() {//delay(1000).
		//Ref: http://www.bennadel.com/blog/1864-Experimenting-With-jQuery-s-Queue-And-Dequeue-Methods.htm
			var dragHandle = $("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone div.image_item-details_container");
			var img_id=$(this).attr('img_id')

			d('enter: '+dragHandle.queue("showDragHandlerQueue_"+img_id).length);
			dragHandle.queue("showDragHandlerQueue_"+img_id, function(next){
				dragHandle.fadeIn(250);
				next();
			});
		});
		$("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone").bind('leave', function() {
			var dragHandle = $("div#gallery_container #panel-content #panel-content-container #content-listbar ul li.ui-selected-clone div.image_item-details_container");
			var img_id=$(this).attr('img_id')
			d('leave: '+dragHandle.queue("showDragHandlerQueue_"+img_id).length);
			dragHandle.queue("showDragHandlerQueue_"+img_id, function(next){
				dragHandle.fadeOut(250);
				next();
			});

		});
 */





















