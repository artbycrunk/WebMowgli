wm.admin.forms = (function( window, undefined ){

	var obj = {};

	var selectors = {
		form		: 'form[form_type=wm_standard_form]',
		messagebox	: "#server_error_msgs",
		validationMsg	: '.form_inline_msgbox',
		inlineTip	: '.form_inline_tip',
		formWrapper	: '#form-wrapper',
		containerWrapper : '#container-wrapper'
	};

	var delayAnimate = 200;

	obj.init = function( formSelector ){

		wm.debug('Initializing forms');

		// if form selector NOT provided, use default standard form
		formSelector = typeof( formSelector ) == 'undefined' ? selectors.form : formSelector;

		if($(formSelector).length==1){

			$(selectors.messagebox).hide();
		}

		//input.form_text_fields
		$(formSelector+' div.controls').focusin(function() {
			$(this).children(selectors.inlineTip).slideDown(delayAnimate);
		})
		.focusout(function() {
			$(this).children(selectors.inlineTip).slideUp();
		});


		$(formSelector+' input[type=text]') //input.form_text_fields
		.focus(function() {
			//wm.debug('focus')
			$(this).parents('div.controls').removeClass('focus error_here').addClass('focus');
		})
		.blur(function() {
			//wm.debug('blur')
			$(this).parents('div.controls').removeClass('focus error_here');
			//validation
			validationField($(this));
		})
		.keyup(function(e) {
			//wm.debug('keyup')
			var keyCode = e.keyCode || e.which;

			if (keyCode == 9) {
				e.preventDefault();
				// call custom function here
				//wm.debug('tab')
				return false;
			}
			//validation
			validationField($(this));
		});

		$(formSelector).bind('submit',function() {

			$(selectors.containerWrapper).scrollTo( selectors.formWrapper, 800, {
				easing:'easeInQuad'
			} )

			if(validateAllRequiredFields()){

				//Clear validation error messages
				clear_validation_errors();

				//Ajax
				wm.admin.forms.post();
			}

			return false;//Dont submit the form, traditionally
		});

	};

	/**
	 * Main function for Posting a form using AJAX
	 * Also takes care of server side validation displays and notifications
	 * note: internally calls Main AJAX function ( wm.admin.utility.ajax.post ) for actual sending data
	 *
	 * @params formSelector	-- (optional) Selector of form element ( default : 'form' )
	 * @params params	-- (optional) array of values, given below
	 *
	 * params = {
	 *
	 *	url			-- url to post to
	 *	data			-- optional data to send
	 *	on_success		-- overriding handler to handle success ( will replace default success handler if provided )
	 *	on_success_complete	-- handler to execute after default success operations are complete
	 *	on_error		-- overriding handler to handle error ( will replace default error handler if provided )
	 *	on_error_complete	-- handler to execute after default error operations are complete
	 *	on_complete		-- handler to perform after operation is complete ( both status = success OR status == error )
	 * };
	 *
	 * @return bool
	 *
	 **/
	obj.post = function( formSelector, params ){

		var returnVar = false;

		formSelector = typeof( formSelector ) == 'undefined' ? selectors.form : formSelector;
		var formObj = $(formSelector);

		// set default param values
		params = ( typeof(params) === 'undefined' ) ? {} : params;
		//params.url = typeof( params.url ) == 'undefined' ? '' : params.url;
		//params.data = typeof( params.data ) == 'undefined' ? '' : params.data;

		params.on_complete		= typeof( params.on_complete ) == 'undefined' ? function(){} : params.on_complete;
		params.on_success_complete	= typeof( params.on_success_complete ) == 'undefined' ? function(){} : params.on_success_complete;
		params.on_error_complete	= typeof( params.on_error_complete ) == 'undefined' ? function(){} : params.on_error_complete;


		/**
		 * Call universal AJAX function. ( extending universal ajax function )
		 * Override the 'on_success' event handler
		 **/
		wm.admin.utility.ajax.post({

			url	: formObj.attr('action'),
			data	: formObj.serialize(),

			// handler for status='success' received from json
			on_success : function( response, textStatus, XMLHttpRequest ){

				//wm.debug( 'form response : success : ' + response.message );

				if( typeof(params.on_success) === 'undefined' ){

					// ----------------------------------- //
					// write general success functions here
					// ----------------------------------- //


					// if redirect command provided, redirect to provided url
					if( response.data.redirect != null && response.data.redirect.url != null ){

						if(response.data.redirect.delay.toString()!='0'){

							wm.redirect(response.data.redirect.url.toString());

						} else {
							setTimeout("wm.redirect(\'"+response.data.redirect.url+"\')",response.data.redirect.delay);
						}
					}


				}
				else{
					// overriding success functions
					params.on_success();
				}

				show_validation_errors(response.data.validations);
				params.on_success_complete();

			},

			// handler for status='error' received from json
			on_error : function( response, textStatus, XMLHttpRequest ){

				//wm.debug('form response : error : ' + response.message );

				// check if overriding on_error handler provided
				if( typeof(params.on_error) === 'undefined' ){


				// ----------------------------------- //
				// write general error functions here
				// ----------------------------------- //


				}
				else{
					// overriding error functions
					params.on_error();
				}

				params.on_error_complete();

				show_validation_errors(response.data.validations);
				returnVar = false;

			}

		});

		params.on_complete();

		return returnVar;
	};

	/**
	 * @private
	 **/
	function validateAllRequiredFields(){
		var FLAG_required_set=true;
		$('form input.form_text_fields').each(function(){
			validationField($(this));
			if($(this).attr('required')=='required'){
				if($.trim($(this).val())==''){
					FLAG_required_set=false;
				}
			}
		});
		return FLAG_required_set;
	}

	/**
	 * @private
	 **/
	function validationField(obj){
		var o=obj.siblings(selectors.validationMsg);
		if(obj.attr('required')!='required'){
			obj.parent().removeClass('focus error_here').addClass('focus');
			o.slideUp(delayAnimate).html('');
			return;
		}

		if(obj.val().length==0){
			var error_msg='Cannot be left blank.';
			obj.parent().removeClass('focus error_here').addClass('error_here');
			if(o.html()==error_msg){
				return
			}
			o.slideUp(0,function(){
				$(this).html(error_msg).slideDown(delayAnimate)
			});
		} else {
			obj.parent().removeClass('focus error_here').addClass('focus');
			o.slideUp(delayAnimate).html('');
		}
	}

	/**
	 * Displays error messages for respective field
	 * based on validations provided
	 * @private
	 **/
	function show_validation_errors(validations){

		//$(selectors.messagebox).hide();

		var className;
		if(validations !== undefined)
		{
			$('form div.controls').removeClass('focus error_here');
			$.each(validations, function(key, item) {

				var element_=$('#'+key);
				var parent_ = element_.parents('div.controls');
				className='';
				if(item.status=='error'){
					className='error_here';
				}
				parent_.removeClass('focus error_here').addClass(className);
				if(item.message!=''){
					parent_.children(selectors.validationMsg).slideUp().removeClass('information warning success error').addClass(item.status).html(item.message).slideDown(delayAnimate);
				}
			});
		}
	}

	function clear_validation_errors(){

		$(selectors.messagebox).hide();
		$(selectors.validationMsg).slideUp(delayAnimate, function(){
			$(this).html('');
		});

	}

	return obj;

})( window );