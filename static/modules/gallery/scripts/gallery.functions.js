//var _gallery = _gallery || {};
wm.gallery = (function( window, undefined ){


	/**
	* @Continue
	* Reordering Images . .
	* Status :
	*	+ Core logic complete, Several issues in categs reordering
	*	+ Basic images reordering complete, But issues with default categ NOT selected, resetting images, etc
	*
	**/

	// holds this class object
	var obj = {};

	var constants = {
		states : {
			selected : {
				key : 'selected',
				cls : 'highlight-selected'
			},
			standby : {
				key : 'standby',
				cls : 'highlight-standby'
			},
			hidden : {
				key : 'hidden',
				cls : 'highlight-visible-0',
				value : 0
			},
			visible : {
				key : 'visible',
				cls : 'highlight-visible-1',
				value : 1
			}
		},
		highlightClasses : 'highlight-selected highlight-standby highlight-visible-0 highlight-visible-1',
		selectors : {
			categ : {
				ul : '#categ-list',
				li : 'li.categ-holders',
				placeholders : '.placeholder-categs',
				button_edit : '#button-categ-edit',
				button_visible : '#button-categ-visible',
				button_trash : '#button-categ-trash',
				button_new : '#button-categ-new'

			},
			image : {
				ul : '#image-list',
				li : 'li.image-holders',
				placeholders : '.placeholder-images',
				button_edit : '#button-image-edit',
				button_visible : '#button-image-visible',
				button_trash : '#button-image-trash',
				button_new : '#button-image-new'
			},
			general : {
				no_images : '#image-empty',
				button_move : '#button-image-move'
			}
		},
		type : {
			categ : 'category',
			image : 'image'
		}
	};


	/**
	 *Cache structure
	cache.categs = {
	      id1 : {
		      meta : {
				key1 : value1,
				key2 : value2,
				key3 : value3
			},
		      images : {
			      id1 : {
				      key : value,
				      key : value,
				      key : value
			      },
			      id2 : {
				      key : value,
				      key : value,
				      key : value
			      }
			}
	      },
	      id2 : {
		      key1 : value1,
		      key2 : value2,
		      key3 : value3,
		      images : { . . . . .}
	      }
	}

       */
	var cache = {
		categs : {}
	};

	// will hold HTML DOM objects
	var domObjs = {
		categ : {
			ul : $(constants.selectors.categ.ul),
			li : $(constants.selectors.categ.li)
		},
		image : {
			ul : $(constants.selectors.image.ul),
			li : $(constants.selectors.image.li)
		},
		general : {
			no_images : $(constants.selectors.general.no_images),
			button_move : $(constants.selectors.general.button_move)
		}
	};

	// will hold current Selected Category/Image id and object
	var current = {
		categ : {
			id : null,
			obj : null,
			selectId : null
		},
		image : {
			id : null,
			parentId : null,
			obj : null,
			selectId : null
		}
	};

	var urls = {
		categ : {
			get : wm.get_url('admin/gallery/get_category_data'),
			reorder : wm.get_url('admin/gallery/reorder_categs')
		},
		image : {
			get : wm.get_url('admin/gallery/get_image_data'),
			reorder : wm.get_url('admin/gallery/reorder_images')
		},
		common : {
			visibility : wm.get_url('admin/gallery/edit_visibility'),
			delete_items : wm.get_url('admin/gallery/delete_items')
		}
	};

	/**
	 * Holds the move mode state
	 **/
	var isMoveModeEnabled = false;

	obj.init = function(){

		// Initialize handlers for Gallery
		obj.handlers.init();

		wm.debug('Gallery Class initialized');
	};

	/**
	 * Get Category data [meta, images]
	 * If categ_id provided, it uses the provided categ_id
	 * Else, uses categ Id of current selected category
	 **/
	obj.categ_get_info = function(categ_id, callback){

		// if categ NOt provided, use current selected categ
		categ_id = (categ_id === undefined) ? current.categ.id : categ_id;

		wm.debug('Categ : Check if Cache Available');

		// check if categ available in cache
		var categCacheObj = obj.cache_get_categ( categ_id );

		if( typeof categCacheObj === 'undefined' ){

			wm.debug('Categ : Cache NOT found, Prepare AJAX request');

			// categ NOT found in cache
			// get info from db (AJAX)
			// save in cache
			// call callback
			wm.admin.utility.ajax.post({
				url : urls.categ.get,
				data : "cat_id=" + categ_id,
				on_success : function(response, textStatus, XMLHttpRequest){

					// set data in cache
					obj.cache_add_categ(categ_id, response.data);

					wm.debug('Categ : AJAX Success, Added categ to cache');

					var categInfo = obj.cache_get_categ(categ_id);

					// call callback function, if exists
					if( typeof callback !== 'undefined' ){

						// execute callback function
						callback(categInfo);
					}

					return;
				}
			});
		}
		else{
			wm.debug('Cache found : loading from Cache');
			// return cache data
			return obj.cache_get_categ(categ_id);
		}

	};

	/**
	 * Get meta data for individual image
	 **/
	obj.image_get_info = function(image_id){

		image_id = (image_id === undefined) ? current.image.id : image_id;

		var imageCacheObj = obj.cache_get_image( current.image.id );
		if( imageCacheObj === undefined ){

			// AJAX : get info from db
			// save in cache
			wm.admin.utility.ajax.post({
				url : urls.image.get,
				data : "img_id=" + image_id,
				on_success : function(response, textStatus, XMLHttpRequest){

					//					wm.debug(response);
					// set data in cache
					obj.cache_add_image(response.data.parent_id, response.data);
				}
			});

		}

		return obj.cache_get_image(image_id);

	};


	///////////////////////////////////
	/////////// CACHE functions ////////////////////////
	///////////////////////////////////

	obj.cache_clear = function(){
		cache = {
			categs : {}
		};
	};

	/**
	 * Add categ data to cache
	 **/
	obj.cache_add_categ = function(categ_id, data){

		cache.categs[categ_id] = {
			meta : data.meta,
			images : data.images
		};

	//		wm.debug(cache);
	};
	/**
	 * Add categ data to cache
	 **/
	obj.cache_add_image = function(parent_id, data){

	//		cache.categs[parent_id] = {
	//			meta : data.meta,
	//			images : data.images
	//		};

	//		wm.debug(cache);
	};

	/**
	 * Remove items from Cache
	 * if only categ_id provided, then delete full categ id
	 * if image_id also provided, delete particular image_id from given categ
	 **/
	obj.cache_remove = function(categ_id, image_id){

		if( image_id === undefined ){

			// delete entire category
			if( typeof cache.categs[categ_id] !== 'undefined' ){
				delete cache.categs[categ_id];
			}
		}
		else{
			// delete particular image_id from particular categ_id
			if( typeof cache.categs[categ_id]['images'][image_id] !== 'undefined' ){
				delete cache.categs[categ_id]['images'][image_id];
			}
		}

	};

	// get full cache object
	obj.cache_get = function(){
		return cache;
	};

	// get categ from cache
	// @return undefined if categ NOT available in cache
	obj.cache_get_categ = function( categ_id ){

		var returnVal = undefined;
		if( typeof categ_id !== 'undefined' ){

			if( typeof cache.categs[categ_id] !== 'undefined' ){

				// return cache value
				returnVal = cache.categs[categ_id];
			}
			else{
			//				wm.debug('Categ : Cache not found');
			}
		}
		else{
			wm.debug('Categ : categ_id NOT provided');
		}

		return returnVal;
	};

	// Get image data from cache
	// @return undefined if NOT found
	obj.cache_get_image = function( categ_id, image_id ){

		var returnVal = undefined;
		//		wm.debug(cache);
		if( categ_id !== undefined && image_id !== undefined ){

			if( typeof cache.categs[categ_id]['images'][image_id] !== 'undefined' ){
				returnVal = cache.categs[categ_id]['images'][image_id];
			}
		}

		return returnVal;
	};

	///////////////////////////////////
	/////////// Private functions ////////////////////////
	///////////////////////////////////

	// Sets Current categ object
	function set_categ(ob){
		current.categ.obj = ob;
		current.categ.id = ob.attr('data-id');
		current.categ.selectId = ob.attr('id');
	};

	// Sets Current Image object
	function set_image(ob){
		current.image.obj = ob;
		current.image.id = ob.attr('data-id');
		current.image.parentId = ob.attr('data-parentId');
		current.image.selectId = ob.attr('id');
	};

	/**
	 * Reload images view with images for provided category
	 * OR if Categ NOT provided, reload with current selected categ
	 **/
	function reload_images(categ_id){

		// get data for currently selected categ
		// check if available in cache, if NOT get from Ajax
		var cacheCategInfo = obj.categ_get_info(categ_id, _reload_images);

		// if data retreived from Cache and NOT ajax,
		// then call _reload_images manually
		if( cacheCategInfo !== undefined ){
			_reload_images(cacheCategInfo);
		}
	}

	/**
	 * Reload images to first available category
	 * note: this is mostly used after a category deletion
	 **/
	function reload_images_default(){

		// get id of first categ available in DOM
		var categ_id = $(constants.selectors.categ.li).first().attr('data-id');

		// reload gallery images with first categ
		reload_images(categ_id);
	}

	/**
	 * Actually load images html view
	 **/
	function _reload_images(categInfo){

		wm.debug('Reloading images . . .');

		// create list of images html for that data

		var imagesHtml = get_image_html(categInfo.images);

		if( imagesHtml !== null && imagesHtml !== '' ){

			// hide no images div
			(domObjs.general.no_images).hide();
			// load images in view
			(domObjs.image.ul).html(imagesHtml).show();
		}
		else{
			// hide image container
			(domObjs.image.ul).hide();

			// show #image-empty div
			(domObjs.general.no_images).show();
		}


	}

	/**
	 * Prepare html for dynamic images
	 *
	 * @param Object Object of Image data sent from Backend
	 * @return string Returns HTML of images OR '' if no images found
	 **/
	function get_image_html(images){

		var finalHtml = '';

		// check if images available to render
		if( typeof images !== 'undefined' && images !== null ){
			$.each( images, function(image_id, data){

				var imageHtml = "<li id='image__"+image_id+"' class='image-holders highlight-visible-"+data.visible+"'  data-id='" +image_id+ "'  data-order='" +data.order+ "' data-parentId='"+data.parent_id+"' data-visible='"+data.visible+"'> \n";
				imageHtml += "	<div class='thumbnail'> \n";
				//				imageHtml += "		<a type='normal' class='img_link' title='"+data.name+"' target='_blank' href='"+data.uri+"'> \n";
				imageHtml += "			<img data-src='"+data.uri+"' alt='"+data.name+"' src='"+data.uri_thumb+"'> \n";
				//				imageHtml += "		</a> \n";
				imageHtml += "	</div> \n";
				imageHtml += "</li> \n";

				finalHtml += imageHtml + "\n";
			});
		}

		return finalHtml;
	}

	/**
	 * Gets list of currently selected Categs in HTML
	 **/
	function get_selected_categs(){
		return $(constants.selectors.categ.ul +' .'+ constants.states.selected.cls);
	}

	/**
	 * Gets list of currently selected Images in HTML
	 **/
	function get_selected_images(){
		return $(constants.selectors.image.ul +' .'+ constants.states.selected.cls);
	}

	/**
	 * Remove Categs / Images provided
	 **/
	function remove_elements(ob){
		ob.remove();
	}

	function toggle_visibility_state(obj){

		// Toggle visibility
		if( get_visible_state(obj) === constants.states.visible.value ){
			//wm.debug('setting as hidden');
			set_state(obj, constants.states.hidden);
		}
		else{
			//wm.debug('setting as visible');
			set_state(obj, constants.states.visible);
		}
	}

	/**
	 * Get the visible state of the element, From DOM
	 **/
	function get_visible_state(obj){
		return parseInt(obj.attr('data-visible'));
	}

	/**
	 * Get Opposite value to visibility
	 * note: return number 0/1 NOT "hidden" OR "visible"
	 *
	 * @Param visibility value 0/1
	 * @return 0/1
	 **/
	function get_visibility_opposite_value(visible_value){

		var opp_visibility_value;
		if( visible_value === constants.states.hidden.value ){
			opp_visibility_value = constants.states.visible.value;
		}
		else if( visible_value === constants.states.visible.value ){
			opp_visibility_value = constants.states.hidden.value;
		}
		return opp_visibility_value;
	}

	/////////////////////////////////////////////////////////////////
	///////////////// MOVE : DRAGGING & Placement ////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////

	function move_mode_enable(){
		wm.debug("Move Mode activated");
		isMoveModeEnabled = true;

		// set categ states to standby
		$(constants.selectors.categ.li).each( function(index){

			set_state($(this), constants.states.standby);
		});

		// show Inbetween Place holders
		move_show_image_placeholders();

	}
	function move_mode_disable(){
		wm.debug("Move Mode DE-activated");
		isMoveModeEnabled = false;
//		$(constants.selectors.general.button_move).button('reset');
		(domObjs.general.button_move).button('reset');

		// set categ states to normal
		$(constants.selectors.categ.li).each( function(index){

			set_state($(this));
		});

		// Hide Inbetween Place holders
		move_hide_image_placeholders();
	}
	function move_mode_toggle(){
		wm.debug("Move Mode Toggled");
		if( isMoveModeEnabled === true ){
			move_mode_disable();
		}
		else{
			move_mode_enable();
		}
	}

	function move_show_categ_placeholders(){
		$(constants.selectors.categ.placeholders).show();
	}

	function move_hide_categ_placeholders(){
		$(constants.selectors.categ.placeholders).hide();
	}

	function move_show_image_placeholders(){
		var placeholderObjs = $(constants.selectors.image.placeholders);

		if( typeof placeholderObjs !== 'undefined' && placeholderObjs.length > 0 ){
		// placeholder objects already exist
		// Do Nothing
		}
		else{
			// create placeholder objects
			$(constants.selectors.image.li).each(function(index){

				//				var curr_image_id = $(this).attr('data-id');
				//				var next_image_id = $(this).next().attr('data-id');
				//
				//				// insert Placeholder after every Image
				//				$(this).before("<li class='placeholder-images' data-before_id='"+curr_image_id+"' data-after_id='"+next_image_id+"'></li>");
				var curr_image_pos = $(this).attr('data-order');

				// insert Placeholder after every Image
				$(this).before("<li class='placeholder-images' data-pos='"+curr_image_pos+"'></li>");
			});
			// show image placeholders
			placeholderObjs = $(constants.selectors.image.placeholders);
		}

		placeholderObjs.show();

	}

	function move_hide_image_placeholders(){
		$(constants.selectors.image.placeholders).hide();
	}



	/////////////////////////////////////////////////////////////////
	///////////////// Formating : CSS ////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////

	/**
	 * Sets/Changes state of Categ/Image as provided
	 *
	 * Highlight prvided dom element according to state provided
	 * states are available in constants.states
	 *
	 * @param obj jquery object
	 * @param state keywords ( selected, hidden/0, visible/1 ), note visibility works for both string OR value 0/1
	 **/
	function set_state(obj, state){

		switch(state){

			case constants.states.selected :
				obj.addClass(constants.states.selected.cls);
				break;
			case constants.states.standby :
				obj.removeClass(constants.states.selected.cls).addClass(constants.states.standby.cls);
				break;
			case constants.states.hidden.value :
			case constants.states.hidden :
				obj.removeClass(constants.states.visible.cls).addClass(constants.states.hidden.cls);
				obj.attr('data-visible', constants.states.hidden.value);
				break;
			case constants.states.visible.value :
			case constants.states.visible :
				obj.removeClass(constants.states.hidden.cls).addClass(constants.states.visible.cls);
				obj.attr('data-visible', constants.states.visible.value);
				break;
			default:
				// reset to default, if type invalid
				obj.removeClass(constants.highlightClasses);
		}
	}


	/////////////////////////////////////////////////////////////////
	///////////////// Handlers : Buttons ////////////////////////////////////////////////
	/////////////////////////////////////////////////////////////////

	obj.handlers = {
		init : function(){

			wm.debug('. . . initializing gallery handlers.');

			// Categ : CLICK
			$(document).on('click', constants.selectors.categ.li, function(e){

				// if CTRL key NOT pressed remove other selected items
				if( e.ctrlKey === false){

					// ONLY single Categ selected
					// set current categ
					set_categ($(this));

					// reset all categs as Unselected ()
					var selectedCategObjs = get_selected_categs();
					selectedCategObjs.removeClass(constants.states.selected.cls);

					// reload images in view
					reload_images(current.categ.id);
				}

				// hihglight current categ as selected
				set_state($(this), constants.states.selected);

			});

			// Image : CLICK
			$(document).on('click', constants.selectors.image.li, function(e){

				// if CTRL key NOT pressed remove other selected items
				if( e.ctrlKey === false){

					// ONLY single Image Selected
					// set current image
					set_image($(this));

					// unset selected classes, from all images
					var selectedImageObjs = get_selected_images();
					selectedImageObjs.removeClass(constants.states.selected.cls);
				}

				// set current image as selected
				set_state($(this), constants.states.selected);
			});

			// CATEG Buttons : EDIT
			$(constants.selectors.categ.button_edit).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));
			});
			// CATEG Buttons : VISIBLE
			$(constants.selectors.categ.button_visible).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));

				// get selected Categs, Toggle visibility
				var selectedCategObjs = get_selected_categs();

				var ids = [];
				var setToVisibilityState = null;
				$.each(selectedCategObjs, function(index){
					// save Opposite of first Elements visibility value
					if(index === 0) {
						setToVisibilityState = get_visibility_opposite_value(get_visible_state($(this)));
					}
					ids.push($(this).attr('data-id'));
				});

				// AJAX : visibility
				wm.admin.utility.ajax.post({
					url : urls.common.visibility,
					data : {
						'item_ids' : ids,
						'visible' : setToVisibilityState
					},
					on_success : function(response, textStatus, XMLHttpRequest){

						wm.debug(response);

						// run through each categ, set state in front as visible/hidden, for visual effect
						selectedCategObjs.each( function( index ){

							// in case of multiple elements, set state of all elements according to first element in list
							if( selectedCategObjs.length > 1 ){

								set_state($(this), setToVisibilityState);
							}
							else{
								toggle_visibility_state($(this));
							}

							// Delete Category from cache
							obj.cache_remove($(this).attr('data-id'));

						});


					}
				});

			});
			// CATEG Buttons : TRASH / DELETE
			$(constants.selectors.categ.button_trash).on('click', function(e){

				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));

				// Get confirmation from user before destructive action
				wm.admin.confirm_action( {

					// title : '',
					message : "This will permanently Delete this Category and All image inside it. Continue ?",
					success : function(){

						// get selected Categs, collect data for post
						var selectedCategObjs = get_selected_categs();
						var ids = [];
						$.each(selectedCategObjs, function(index){
							ids.push($(this).attr('data-id'));
						});

						// AJAX : Delete Categs
						wm.admin.utility.ajax.post({
							url : urls.common.delete_items,
							data : {
								'item_ids' : ids,
								'item_type' : constants.type.categ
							},
							on_success : function(response, textStatus, XMLHttpRequest){

								// run through each categ, delete in front from html
								selectedCategObjs.each( function( index ){

									var curr_cat_id = $(this).attr('data-id');

									// if current Categ being deleted is the Selected categ,
									// then remove images under this category from html,
									// delete categ and load the default category
									if( parseInt(curr_cat_id) === parseInt(current.categ.id) ){
										reload_images_default();
									}

									remove_elements($(this));
									// Delete Category from cache
									obj.cache_remove($(this).attr('data-id'));
								});
							}
						});
					}
				} );
			});


			// CATEG Buttons : NEW
			$(constants.selectors.categ.button_new).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));
			});

			///////////////// IMAGE Buttons //////////////////////

			// IMAGE Buttons : EDIT
			$(constants.selectors.image.button_edit).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));
			});
			// IMAGE Buttons : VISIBLE
			$(constants.selectors.image.button_visible).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));

				// get selected Images, Toggle visibility
				var selectedImageObjs = get_selected_images();

				var ids = [];
				var setToVisibilityState = null;
				var categ_id = null;
				$.each(selectedImageObjs, function(index){
					// save Opposite of first Elements visibility value
					if(index === 0) {
						setToVisibilityState = get_visibility_opposite_value(get_visible_state($(this)));
						categ_id = $(this).attr('data-parentId');
					}
					ids.push($(this).attr('data-id'));
				});

				// AJAX : visibility
				wm.admin.utility.ajax.post({
					url : urls.common.visibility,
					data : {
						'item_ids' : ids,
						'visible' : setToVisibilityState
					},
					on_success : function(response, textStatus, XMLHttpRequest){

						wm.debug(response);

						// run through each image, set state in front as visible/hidden, for visual effect
						selectedImageObjs.each( function( index ){

							// in case of multiple elements, set state of all elements according to first element in list
							if( selectedImageObjs.length > 1 ){

								set_state($(this), setToVisibilityState);
							}
							else{
								toggle_visibility_state($(this));
							}

						});

						// Delete Category from cache
						obj.cache_remove(categ_id);
					}
				});
			});
			// IMAGE Buttons : TRASH
			$(constants.selectors.image.button_trash).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));


				// Get confirmation from user before destructive action
				wm.admin.confirm_action( {

					// title : '',
					message : "The images will be permanently delete. Are you sure you want to continue ?",
					success : function(){

						// get selected Images, collect data for post
						var selectedImageObjs = get_selected_images();
						var ids = [];
						var categ_id = null;
						$.each(selectedImageObjs, function(index){
							categ_id = $(this).attr('data-parentId');
							ids.push($(this).attr('data-id'));
						});

						// AJAX : Delete Images
						wm.admin.utility.ajax.post({
							url : urls.common.delete_items,
							data : {
								'item_ids' : ids,
								'item_type' : constants.type.image
							},
							on_success : function(response, textStatus, XMLHttpRequest){

								// run through each image, delete in front from html
								selectedImageObjs.each( function( index ){

									remove_elements($(this));
								});

								// Delete Category from cache
								obj.cache_remove(categ_id);
							}
						});
					}
				} );

			});
			// IMAGE Buttons : NEW
			$(constants.selectors.image.button_new).on('click', function(e){
				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));
			});

			// IMAGE Buttons : MOVE
			(domObjs.general.button_move).on('click', function(e){

				e.preventDefault();
				wm.debug('Button Click : ' + $(this).attr('id'));

				// check if move mode is ON or OFF, and accordingly toggle
				if( $(this).hasClass('active') === true){
					move_mode_disable();
				}
				else{
					move_mode_enable();
				}
			});

			// IMAGE Placeholder : Click
			$(document).on('click', constants.selectors.image.placeholders, function(e){

				e.preventDefault();

				var categId = current.categ.id;
				var newPos = $(this).attr('data-pos');
				var oldPos = current.image.obj.attr('data-order');

				wm.debug("categ : "+categId+" || old Pos : " + oldPos + ' || new Pos : ' + newPos);

				// AJAX : Delete Images
				wm.admin.utility.ajax.post({
					url : urls.image.reorder,
					data : {
						'categ_id' : categId,
						'old_pos' : oldPos,
						'new_pos' : newPos
					},
					on_success : function(response, textStatus, XMLHttpRequest){

						move_mode_disable();

						// Delete Category from cache
						obj.cache_remove(categId);

						reload_images(categId);
					}
				});
			});
		}
	};

	return obj;

})( window );

