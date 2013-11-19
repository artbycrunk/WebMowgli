/*
Notes: All forms with form_type="wm_user_forms"> Use the below functions and
have the server_message variable set by the server. Otherwise its NULL.
 */

var DURATION_form_inline_msgbox=200;
var formSelector = 'form[form_type=wm_user_forms]';
var formObj = null;
var msgObj = null;
var debugging = true;

function user_debug(msg){

	if( debugging === true ){

		if(typeof(console) === "undefined") {
			console = {
				log: function(){}
			};
			return;
		}

		console.log(msg);
	}
};

$(document).ready(function(){

	formObj = $(formSelector);
	msgObj = $('#server_error_msgs');

	if(formObj.length==1){
		if(server_message!=''){
			msgObj.slideUp().addClass('information').html(server_message).slideDown(250);
		}
		else{
			msgObj.slideUp();
		}
	}

	$(formSelector + ' input.form_text_fields')
	.focus(function() {
		//wm.debug('focus')
		$(this).parents('div.form_field_container').removeClass('focus error_here').addClass('focus');
	})
	.blur(function() {
		//wm.debug('blur')
		$(this).parents('div.form_field_container').removeClass('focus error_here').removeClass('focus');
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

	formObj.bind('submit',function() {

		user_debug('submit button clicked');

		//Create mask
		if($('div#form_mask').length==0){
			$(this).parent('div').prepend('<div id="form_mask"></div>');
		}
		$('div#form_mask').css({
			opacity: 0,
			display:'block'
		});
		$('div#form_mask').animate({
			opacity: 0.7
		}, {
			duration: 0,
			complete: function(){}
		});

		if(validateAllRequiredFields()){

			user_debug('submit button : validation Success');
			//Clear everthing
			/* msgObj.html();*/
			// msgObj.css("display","none").html('');
			msgObj.slideUp();
			$('.form_inline_msgbox').slideUp(DURATION_form_inline_msgbox, function(){
				$(this).html('')
			});

			//Ajax spinner

			//msgObj.html('<div class="spinner" style="margin:0 auto;"></div>').fadeIn(200);
			$('.spinner').fadeIn('slow', function() {});


			//Ajax
			postformdata();
		}
		else{
			user_debug('submit button : validation Fail');
		}

		return false;//Dont submit the form
	});

});//Ready END

function validateAllRequiredFields(){
	var FLAG_required_set=true;
	$('form input.form_text_fields').each(function(){
		validationField($(this));
		if($(this).attr('required')=='true'){
			if($.trim($(this).val())==''){
				FLAG_required_set=false;
			}
		}
	});
	return FLAG_required_set;
}

function validationField(obj){
	var o=obj.parents('div.form_field_container').children('.form_inline_msgbox');
	if(obj.attr('required')!='required'){
		obj.parent().removeClass('focus error_here').addClass('focus');
		o.slideUp(DURATION_form_inline_msgbox).html('');
		return;
	}

	if(obj.val().length==0){
		var error_msg='Cannot be left blank.';
		obj.parents('div.form_field_container').removeClass('focus error_here').addClass('error_here');
		if(o.html()==error_msg){
			return
		}
		o.slideUp(0,function(){
			$(this).html(error_msg).slideDown(DURATION_form_inline_msgbox)
		});
	} else {

		obj.parents('div.form_field_container').removeClass('focus error_here').addClass('focus');
		o.slideUp(DURATION_form_inline_msgbox).html('');
	}
}


function goToURL(URL){
	window.parent.location = URL;
}

var temp_response;
function postformdata(){
	$.ajax({
		url:  formObj.attr('action'),//form_type > type of user module form
		type: 'POST',
		data: formObj.serialize(),
		success: function(response, textStatus, XMLHttpRequest) {
			temp_response = response;
			$('.spinner').fadeOut('slow', function() {});

			user_debug( 'BEFORE : status = ' + response.status + ' : message = ' + response.message);

			if(response.status == 'success'){

				user_debug( 'SUCCESS : status = ' + response.status + ' : message = ' + response.message);

				msgObj.removeClass().addClass(("form_inline_msgbox alert alert-"+response.status)).html(response.message).slideDown();
				$('div.form_field_container').removeClass('focus error_here').addClass('focus');

				if($('#login-mode').val() == 'modal'){
					parent.wm.admin.closeLoginDialog();
					parent.wm.admin.load_main_html(response.data.redirect.url);
				}else{
					//redirecting
					if(response.data.redirect != undefined){

						user_debug( 'REDIRECT : status = ' + response.status + ' : message = ' + response.message);
						if(response.data.redirect.url != null){

							var redirectUrl = response.data.redirect.url.toString();
							var redirectDelay = response.data.redirect.delay;

							//alert( 'redirect = ' + redirectUrl + '\n' + 'delay = ' + redirectDelay );

							if( redirectDelay != undefined && redirectDelay > 0 ){
								hideFormContainer();
								setTimeout("goToURL(\'"+redirectUrl+"\')",redirectDelay);
							} else {
								goToURL(redirectUrl);
							}
						}
					}
				}
			} else {

				user_debug( 'ERROR : status = ' + response.status + ' : message = ' + response.message);
				msgObj.slideUp(200).html(response.message).removeClass().addClass("form_inline_msgbox alert alert-"+response.status).slideDown(200);

				//				msgObj.slideUp(200);
				//				msgObj.hide();
				//				msgObj.removeClass();
				//				msgObj.addClass("form_inline_msgbox").addClass("alert").addClass("alert-error");
				//				msgObj.html(response.message);
				//				msgObj.show();
				//				msgObj.slideDown(200);
			}
			//removeFormMask();
			manageServersideValidation(response.data.validations);
		},
		error:  function(XMLHttpRequest, textStatus, errorThrown) {
			$('.spinner').fadeOut('slow', function() {});
			removeFormMask();
			$('.form_field_container').removeClass('focus error_here').addClass('error_here');
			msgObj.removeClass().addClass("form_inline_msgbox alert alert-error").html('Could not connect to server.').slideDown(200);
		}
	});
}

/**
 *Can be used to hide form after success message with a redirect statement is given
 **/
function hideFormContainer(){

	$('#form').hide();

}
function removeFormMask() {
	$('div#form_mask').animate({
		opacity: 0
	}, {
		duration: 250,
		complete: function(){
			$('div#form_mask').css({
				opacity: 0,
				display:'none'
			});
		}
	});
}
function manageServersideValidation(validations){
	var className;
	if(validations==undefined){
		return
	}
	$('form div.form_field_container').removeClass('focus error_here');
	$.each(validations, function(key, item) {
		var element_=$('form #'+key);
		//if(element_.length!=1){alert('ERROR: Invalid form elemetn name')}
		var parent_ = element_.parents('div.form_field_container');
		className='';
		if(item.status=='error'){
			className='error_here'
		}
		parent_.removeClass('focus error_here').addClass(className);
		if(item.message!=''){
			parent_.children('.form_inline_msgbox').slideUp().removeClass('information warning success error').addClass(item.status).html(item.message).slideDown(DURATION_form_inline_msgbox)
		}
	});
}



