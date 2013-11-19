// scripts for admin/blog/post_edit
function blog_post_edit_init(){

	$("#edit-posts .post_categories").change(function(){

		// check if check action Or uncheck action
		if( $(this).is(":checked") ){

			// check if uncategorized
			// if YES --> reset all other categories ( and check uncategorized )
			if( $(this).val() == default_category ){

				resetCategories();
			}
			else{
				// other category checked, --> uncheck uncategorized
				manuallySetCategory( default_category, false );
			}
		}
		else{

		}

	});

}

function clear_form_elements(ele) {

	$(ele).find(':input').each(function() {
		switch(this.type) {
			case 'password':
			case 'select-multiple':
			case 'select-one':
			case 'text':
			case 'textarea':
				$(this).val('');
				break;
			case 'checkbox':
			case 'radio':
				this.checked = false;
		}
	});

}

// iterate through all categories,
// if uncategorized --> true
// Else --> false
function resetCategories(){

	$(".post_categories").each(function(){
		var categ = $(this).val();
		var isCheck = false;

		if ( categ != default_category ) {

			// uncheck all other
			isCheck = false;

		} else {
			// check 'uncategorized''
			isCheck = true;

		}
		$(this).attr('checked', isCheck);
	})
}

// sets the given category
function manuallySetCategory( categValue, checkState ){

	// iterate through each category, check/uncheck the mentioned categ, break
	$("#edit-posts .post_categories").each(function(){

		if( $(this).val() == categValue ){
			$(this).attr('checked', checkState );
			return;
		}
	})

}

var blog_manage = blog_manage || {};

blog_manage.posts = ( function( window ){

	var ob = {};

	ob.init = function(){


		wm.debug("Loaded Blog Posts");

		// handler for post delete button
		$("tbody a.[type='delete_button']").click(function(e){

			e.preventDefault();

			var buttonObj = $( this );

			confirm_action( {

				// title : '',
				message : "Permanently delete post ?",
				success : function(){

					// get current row dom
					// var curr_row = $(this).closest('tr');
					// var curr_row = wm_manage.get_row_obj(this);
					var rowId = wm_manage.get_row_id( buttonObj );
					var url = buttonObj.attr('href');

					wm.admin.utility.ajax.post({

						url : url,
						on_success_complete : function(){
							wm_manage.remove_row(rowId);
						}
					});

				}

			} );

			return false;
		});

		// handler for post visibility toggle
		$("tbody a.[type='status_toggle_button']").click(function(e){

			e.preventDefault();

			// get current row dom
			var rowId = wm_manage.get_row_id( this );
			var url = $(this).attr('href');

			wm.admin.utility.ajax.post({

				url : url,
				on_success_complete : function(){
					set_status(rowId);
				}
			});

			return false;
		});

		// batch action handler
		$('#batch_action_submit').click(function(){

			var batchObj = $('#batch_action');

			var actionType = batchObj.val();

			var rowIds = wm_manage.get_checked_row_ids();

			switch ( actionType )
			{

				case 'published' :

					wm.admin.utility.ajax.post({

						url : window.urls.published,
						data : {
							ids : rowIds,
							status : window.status.published
						},
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_status( rowId, window.status.published );
							});

						}
					});
					break;

				case 'draft' :

					wm.admin.utility.ajax.post({

						url : window.urls.draft,
						data : {
							ids : rowIds,
							status : window.status.draft
						},
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_status( rowId, window.status.draft );
							});

						}
					});
					break;

				case 'del' :

					confirm_action({
						message : "Permanently delete posts ?",
						success : function(){
							wm.admin.utility.ajax.post({

								url : window.urls.del,
								data : {
									ids : rowIds
								},
								on_success_complete : function(){

									// Delete post from front end
									$.each( rowIds, function(key, rowId){

										wm_manage.remove_row(rowId);
									});

								}
							});
						}
					});

					break;

				default :
					wm.debug('none selected');

			}
		});

	};

	/**
	 * Sets the status of a post ( row ) as provided
	 * if status NOT provided, by default toggles the current status of the post.
	 *
	 * @param rowId
	 * @param statusSetValue (optional) forces the status to change to provided status. toggles if NOT provided
	 **/
	function set_status( rowId, statusSetValue ){

		var statusSelect = '#status__' + rowId;
		var buttonSelectPublish = "#status_published__" + rowId;
		var buttonSelectUnpublish = "#status_unpublished__" + rowId;

		var statusObj = $(statusSelect);

		// check if status setValue
		if( statusSetValue != undefined ){

			// wm.debug('status of post : ' + status );
			if( statusSetValue == window.status.published ){

				//			wm.debug('converting to Published');

				$(buttonSelectPublish).hide();
				$(buttonSelectUnpublish).show();

				statusObj.html( window.status.published );
			}
			else if ( statusSetValue == window.status.draft  ){

				//			wm.debug('converting to Draft');

				$(buttonSelectUnpublish).hide();
				$(buttonSelectPublish).show();

				statusObj.html( window.status.draft );
			}
			else{
				alert('Error: invalid status provided');
			}

		}
		else{
			// toggle default

			var currStatus = $.trim( statusObj.html().toLowerCase() );
			$(buttonSelectUnpublish).toggle();
			$(buttonSelectPublish).toggle();

			var tempStatus = ( currStatus == window.status.published  ) ? window.status.draft : window.status.published;
			statusObj.html( tempStatus );
		}

	}

	return ob;
} )(window);

blog_manage.categs = ( function( window ){
	var ob = {};

	ob.init = function(){

		wm.debug("Loaded Categs Manage");

		// handler for post delete button
		$("tbody a.[type='delete_button']").click(function(e){

			e.preventDefault();

			var buttonObj = $( this );

			confirm_action({

				message : "Permanently delete category ?",
				success : function(){
					var rowId = wm_manage.get_row_id(buttonObj);
					var url = buttonObj.attr('href');

					wm.admin.utility.ajax.post({

						url : url,
						on_success_complete : function(){ wm_manage.remove_row(rowId); }
					});
				}
			});

			return false;
		});

		// handler for categ visibility toggle
		$("tbody a.[type='visibility_toggle_button']").click(function(e){

			e.preventDefault();

			// get current row dom
			var rowId = wm_manage.get_row_id(this);
			var url = $(this).attr('href');

			wm.debug( 'row id : ' + rowId );

			wm.admin.utility.ajax.post({

				url : url,
				on_success_complete : function(){ set_visibility(rowId); }
			});

			return false;
		});

		// handler for categ comments toggle
		$("tbody a.[type='comments_toggle_button']").click(function(e){

			e.preventDefault();

			// get current row dom
			var rowId = wm_manage.get_row_id(this);
			var url = $(this).attr('href');

			wm.debug( 'row id : ' + rowId );

			wm.admin.utility.ajax.post({

				url : url,
				on_success_complete : function(){ set_comments(rowId); }
			});

			return false;
		});

		// batch action handler
		$('#batch_action_submit').click(function(){

			var batchObj = $('#batch_action');

			var actionType = batchObj.val();

			var rowIds = wm_manage.get_checked_row_ids();

			switch ( actionType )
			{

				case 'visibility_show' :

					wm.admin.utility.ajax.post({

						url : window.urls.visibility_show,
						data : { ids : rowIds, is_visible : '1' },
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_visibility( rowId, window.visibility.visible );
							});
						}
					});
					break;

				case 'visibility_hide' :

					wm.admin.utility.ajax.post({

						url : window.urls.visibility_hide,
						data : { ids : rowIds, is_visible : '0' },
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_visibility( rowId, window.visibility.hidden );
							});

						}
					});
					break;

				case 'comments_enable' :

					wm.admin.utility.ajax.post({

						url : window.urls.comments_enable,
						data : { ids : rowIds, is_comments : '1' },
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_comments( rowId, window.comments.enabled );
							});

						}
					});
					break;

				case 'comments_disable' :

					wm.admin.utility.ajax.post({

						url : window.urls.comments_disable,
						data : { ids : rowIds, is_comments : '0' },
						on_success_complete : function(){

							// change status in front end
							$.each( rowIds, function(key, rowId){

								set_comments( rowId, window.comments.disabled );
							});

						}
					});
					break;

				case 'del' :

					confirm_action({
						message : 'Permanently delete categories ?',
						success : function(){
							wm.admin.utility.ajax.post({

								url : window.urls.del,
								data : { ids : rowIds },
								on_success_complete : function(){

									// Delete post from front end
									$.each( rowIds, function(key, rowId){

										wm_manage.remove_row(rowId);
									});

								}
							});
						}
					});

					break;

				default :
					wm.debug('none selected');

			}
		});

	}

	/**
	 * Sets the visibility of a post ( row ) as provided
	 * if visibility NOT provided, by default toggles the current visibility of the category.
	 *
	 * @param rowId
	 * @param visibleSetValue (optional) forces the status to change to provided visibility. toggles if NOT provided
	 **/
	function set_visibility( rowId, visibleSetValue ){

		var visibleSelect = '#visible__' + rowId;
		var buttonSelectVisible = "#visibility_visible__" + rowId;
		var buttonSelectHidden = "#visibility_hidden__" + rowId;

		var visibleObject = $(visibleSelect);

		// check if status setValue
		if( visibleSetValue != undefined ){

			// wm.debug('status of post : ' + status );
			if( visibleSetValue == window.visibility.visible ){

				//wm.debug('converting to Visible');

				$(buttonSelectVisible).hide();
				$(buttonSelectHidden).show();

				visibleObject.html( window.visibility.visible );
			}
			else if ( visibleSetValue == window.visibility.hidden  ){

				//wm.debug('converting to Hidden');

				$(buttonSelectHidden).hide();
				$(buttonSelectVisible).show();

				visibleObject.html( window.visibility.hidden );
			}
			else{
				alert('Error: invalid Visibility provided');
			}

		}
		else{
			// toggle default

			var currVisibility = $.trim( visibleObject.html().toLowerCase() );
			$(buttonSelectHidden).toggle();
			$(buttonSelectVisible).toggle();

			var tempVisibility = ( currVisibility == window.visibility.visible.toLowerCase()  ) ? window.visibility.hidden : window.visibility.visible;
			visibleObject.html( tempVisibility );
		}

	}

	/**
	 * Sets the comments of a categ ( row ) as provided
	 * if comments NOT provided, by default toggles the current comments of the category.
	 *
	 * @param rowId
	 * @param commentsSetValue (optional) forces the status to change to provided comments. toggles if NOT provided
	 **/
	function set_comments( rowId, commentsSetValue ){

		var commentsSelect = '#comments__' + rowId;
		var buttonSelectEnable = "#comments_enable__" + rowId;
		var buttonSelectDisable = "#comments_disable__" + rowId;

		var commentObject = $(commentsSelect);

		// check if status setValue
		if( commentsSetValue != undefined ){

			// wm.debug('status of categ : ' + status );
			if( commentsSetValue == window.comments.enabled ){

				//wm.debug('converting to Enabled');

				$(buttonSelectEnable).hide();
				$(buttonSelectDisable).show();

				commentObject.html( window.comments.enabled );
			}
			else if ( commentsSetValue == window.comments.disabled  ){

				//wm.debug('converting to Hidden');

				$(buttonSelectDisable).hide();
				$(buttonSelectEnable).show();

				commentObject.html( window.comments.disabled );
			}
			else{
				alert('Error: invalid comments status provided');
			}

		}
		else{
			// toggle default

			var currComments = $.trim( commentObject.html().toLowerCase() );
			$(buttonSelectDisable).toggle();
			$(buttonSelectEnable).toggle();

			var tempComments = ( currComments == window.comments.enabled.toLowerCase()  ) ? window.comments.disabled : window.comments.enabled;
			commentObject.html( tempComments );
		}

	}

	return ob;
} )(window);




blog_manage.tags = ( function( window ){

	var ob = {};

	ob.init = function(){

		wm.debug("Loaded Tags Manage");

		// handler for post delete button
		$("tbody a.[type='delete_button']").click(function(e){

			e.preventDefault();

			var buttonObj = $( this );

			confirm_action({

				message : "Permanently delete tag ?",
				success : function(){
					var rowId = wm_manage.get_row_id(buttonObj);
					var url = buttonObj.attr('href');

					wm.admin.utility.ajax.post({

						url : url,
						on_success_complete : function(){ wm_manage.remove_row(rowId); }
					});
				}
			});


			return false;
		});

		// batch action handler
		$('#batch_action_submit').click(function(){

			var batchObj = $('#batch_action');

			var actionType = batchObj.val();

			var rowIds = wm_manage.get_checked_row_ids();

			switch ( actionType )
			{

				case 'del' :

					confirm_action({
						message : 'Permanently delete tags ?',
						success : function(){
							wm.admin.utility.ajax.post({

								url : window.urls.del,
								data : { ids : rowIds },
								on_success_complete : function(){

									// Delete post from front end
									$.each( rowIds, function(key, rowId){

										wm_manage.remove_row(rowId);
									});

								}
							});
						}
					});

					break;

				default :
					wm.debug('none selected');

			}
		});

	};

	return ob;
} )(window);