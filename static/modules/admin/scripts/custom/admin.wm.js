//-----------------------------------------------------------------------//
//-------------------- Namespace : admin --------------------------------//
//-----------------------------------------------------------------------//

wm.admin = (function( window, undefined ){

	var obj = {};

	/**
	 * Initialize Admin Panel
	 **/
	obj.init = function(){

		// get environment from page variable, if not available, set as null
		// this is used to enable/disable debugging/logging ( note: no debugging in 'production' mode )
		wm.set_environment( window.wm_environment );

		// When a top menu item is clicked...
		$("#main-nav .nav-top-item").bind('click', function() {

			var navtopitem = $(this);
			if ( ! navtopitem.hasClass("nav-top-item-selected"))
			{
				$("#main-nav a").removeClass("nav-top-item-selected");
				$("#main-nav ul").slideUp("normal");
				navtopitem.addClass("nav-top-item-selected");
				navtopitem.next().slideToggle("normal");
			}
			return false;
		});

		// When Sub-menu item is clicked
		$("#main-nav .nav-sub-item").bind('click', function() {

			// get href
			var url = $(this).attr('href');

			// set hashbang
			wm.admin.utility.hashbang.setHashbang(url.substr(window.site_url.length));

			// load main content area ( ajax )
			obj.load_main_html(url);

			// scroll page to top
			wm.scrollPage(0);
			return false;
		});

		//////////////////////////////
		//FancyBOX initilize
		//////////////////////////////
		$('.img_link').fancybox({
			'titlePosition'  : 'over',
			'transitionIn'	: 'elastic',
			'transitionOut'	: 'elastic',
			'easingIn'      : 'easeOutBack',
			'easingOut'     : 'easeInBack'
		});

		// for a jquery alerts.. bug fix
		$( "#dialog:ui-dialog" ).dialog( "destroy" );

	};

	/**
	 * Initialize main content area
	 * note: to initialize scripts in main content area ( after ajax view )
	 * write all scripts in init=function(){ ... };
	 **/
	obj.init_main = function(){

		if( typeof(init) !== 'undefined' ){

			wm.debug('Initializing ajax main content');
			window.init();
		}

		// init handlers for anchor links in main content area ( anchors will open using ajax )
		default_anchor_click_handler();

	};

	/**
	 * Resets the init function in main content area
	 **/
	obj.reset_init_main = function(){

		// wm.debug('Resetting main content init');
		window.init = function(){};

	};

	/******************************
	* This function handles all sidebar menu click events.
	* It sends POST request requesting for HTML container of a certain page
	* and updates window.location for bookmarking support.
	*******************************/
	obj.load_main_html = function (url){

		wm.debug('Loading main content : url : ' + url);

		//over-write previous init()
		wm.admin.reset_init_main();

		// load main content area html
		wm.admin.utility.ajax.load({

			url : url,
			notification : false,
			on_success : function( output ){

				$('div#body-wrapper').html(output);

				// initialize main content area ( ajax )
				wm.admin.init_main();

			}
		});

	};

	/**
	 * Open appropriate admin menu item based on URL or hashbang provided
	 **/
	obj.setMenuSelected = function( window_hash ){

		if( typeof(window_hash) !== 'undefined' && window_hash != null )
		{
			//wm.debug('window_hash = ' + window_hash );
			var hash_split = window_hash.split("/");
			var hashclass = ('menu-'+hash_split[1]);
			$(("#main-nav ."+hashclass)).addClass("nav-top-item-selected").next().slideToggle("normal");
		}else{
			//wm.debug('window_hash = NULL' );
			var navselected = $("#main-nav").attr('class');
			$(("#main-nav ."+navselected)).addClass("nav-top-item-selected").next().slideToggle("normal");
		}
	};

	obj.closeLoginDialog = function(){
		$('#login-dailog-iframe').modal('hide');
		setTimeout("obj.removeLoginDialog()",6000);
	}

	obj.removeLoginDialog = function (){
		$('#login-dailog-iframe').remove();
	};


	/******************************
	* Initialize handler for all anchor tags
	*
	* Ajax Link Detection :
	* Bound Links will be loaded using AJAX load.
	* Expection : type="normal" and target not set.
	*******************************/
	function default_anchor_click_handler(){

		$('#body-wrapper a[href="#"]').bind({
			click:function(e){
				return false;
			}
		});

		$('#body-wrapper a').not('[href="#"]').not('[type="normal"]').not('[target*="_"]').bind({
			//$('#body-wrapper a').not('[href="#"]').not('[type]').not('[target*="_"]').bind({
			click:function(e){
				if (e.ctrlKey)
				{
					// open link normally ( refresh browser )
					return true;
				}else{
					// open link with AJAX
					wm.admin.utility.hashbang.setHashbang($(this).attr('href').substr(window.site_url.length));
					obj.load_main_html($(this).attr('href'));
					return false;
				}
			}
		});
	}


	/**
	* confirm dialogue box
	* Can ask user to confirm OR cancel operaiton
	* ( note: this can be used to make all confirm actions universal, incase other animations are required )
	*
	* @params List of params in below format
	* params = {
	*	dialogueSelector : Selector for dialogue box,
	*	msgSelector : Selector for message container,
	*	title : "title of dialog box",
	*	message : "Message to ask",
	*	success : callback for success event (deletion confirmed),
	*	cancel : callback for cancel event (deletion cancled )
	* }
	* @return bool
	**/
	obj.confirm_action = function( params ){

		// set defaults
		params.dialogueSelector = ( typeof( params.dialogueSelector ) === 'undefined' ) ? "#dialog-confirm" : params.dialogueSelector;
		params.msgSelector = ( typeof( params.msgSelector ) === 'undefined' ) ? "#dialogue-confirm-message" : params.msgSelector;
		params.title = ( typeof( params.title ) === 'undefined' ) ? 'Confirmation required' : params.title;
		params.message = ( typeof( params.message ) === 'undefined' ) ? 'Are you sure ?' : params.message;
		params.success = ( typeof( params.success ) === 'undefined' ) ? function(){
			alert('error : success function NOT defined');
		} : params.success;
		params.cancel = ( typeof( params.cancel ) === 'undefined' ) ? function(){} : params.cancel;

		var confirmDialogueObj = $( params.dialogueSelector );
		var confirmDialogueMsgObj = $( params.msgSelector );

		confirmDialogueObj.dialog({
			resizable: false,
			modal: true,
			title: params.title,
			open : function (){
				// set message of dialogue box
				confirmDialogueMsgObj.html(params.message);
			},
			buttons: {
				"Delete": function() {

					$( this ).dialog( "close" );
					params.success();
				},
				Cancel: function() {

					$( this ).dialog( "close" );
					params.cancel();
				}
			}

		});
	}

	return obj;

})( window );



/******************************
 * OnReady Functions
 *******************************/

$(document).ready(function(){

	wm.admin.utility.hashbang.getHashbangReload();
	// Open current module based on URL OR HashBang
	wm.admin.setMenuSelected( wm.admin.utility.hashbang.getHashbang() );

	// Initialize notifications
	wm.admin.utility.notifications.init();


	/******************************
	 * Switch - inAdminPanel defined in admin views/front/head.php,
	 * which is only visible on the rendered pages.
	 ******************************/
	if ( typeof(window.inAdminPanel) == 'undefined' )
	{
		// in admin panel
		wm.admin.init();
		wm.admin.init_main();
		wm.debug('Admin Panel Loaded');
	}
	else{
		// front pages initialize
		wm.site.init();
		wm.debug('Front Admin initialized');
	}

	$("body").ajaxComplete(function(event, request, settings){

		if( wm.isLogged ){

			if(request.status == 401){
				if($('#login-dailog-iframe').length == 0){
					$('body').prepend('<iframe id="login-dailog-iframe" src="'+window.site_url+'user/login/modal'+'"></iframe>');
				}
				$('#login-dailog-iframe').modal({
					show : true
				});
			}
		}
	});

});

/*
 SAMPLE = (function( window, undefined ){

	var obj = {};

	return obj;

})( window );
*/