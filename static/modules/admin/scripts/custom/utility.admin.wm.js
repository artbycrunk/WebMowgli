wm.admin.utility = (function( window, undefined ){

	var obj = {};

	return obj;

})( window );


/************************************************************
*************************************************************
***** Hashbang Namespace ( wm.admin.utility.hashbang )
*************************************************************
************************************************************/

wm.admin.utility.hashbang = (function( window, undefined ){

	var obj = {};

	var currentHashbang = '';

	obj.getHashbang = function()
	{
		return (window.location.hash.substr(1,1) == '!') ? (window.location.hash.substr(2)):null;
	};

	obj.setHashbang = function (url)
	{
		prevHashbang = currentHashbang;
		(window.location.hash) = '!'+ url;
		currentHashbang = url;
	};

	obj.getHashbangReload = function()
	{
		var window_hash = obj.getHashbang();

		if( window_hash != null ) //isValid Hashbang
		{
			obj.setHashbang(window_hash);
			wm.debug('[ready] Loading hash page >'+(currentHashbang));
			wm.admin.load_main_html( window.site_url + currentHashbang );

			hash_split = window_hash.split("/");
		}
	};


	return obj;

})( window );


/************************************************************
*************************************************************
***** AJAX Namespace ( wm.admin.utility.ajax )
*************************************************************
************************************************************/

wm.admin.utility.ajax = (function( window, undefined ){

	var obj = {};

	obj.defaults = {
		mainSelector : 'div#body-wrapper'
	};

	/**
	* General function to perform AJAX post,
	* Expects HTML as output.
	* Supports default and custom events that can be triggered on_success, on_success_complete.
	* On Error ( AJAX ) --> displays error message ( if notifications enabled )
	*
	* Following params can be passed in the form of an associative array {}
	*
	* @param params			-- Variables, Functions that can be passed ( given below ) <br/><br/>
	*
	* params = {					 <br/>
	*
	*	 url			-- url to post to <br/>
	*	 data			-- optional data to send <br/>
	*	 notification		-- (bool) to display notification message for current response or not <br/>
	*	 on_success		-- overriding handler to handle success ( will replace default success handler if provided )<br/>
	*	 on_complete		-- handler to perform after operation is complete ( both status = success OR status == error ) <br/>
	* }
	*
	* @return bool	-- returns true ( on ajax success ), return false ( on ajax failure )
	**/
	obj.load = function( params ){

		// set default param values
		params = ( typeof(params) === 'undefined' ) ? {} : params;
		params.url = typeof( params.url ) == 'undefined' ? '' : params.url;
		params.data = typeof( params.data ) == 'undefined' ? '' : params.data;
		params.notification = typeof( params.notification ) == 'undefined' ? true : params.notification;

		params.on_complete		= typeof( params.on_complete ) == 'undefined' ? function(){} : params.on_complete;
		//params.on_success_complete	= typeof( params.on_success_complete ) == 'undefined' ? function(){} : params.on_success_complete;
		//params.on_error_complete	= typeof( params.on_error_complete ) == 'undefined' ? function(){} : params.on_error_complete;

		// close previous notification ( if any )
		wm.admin.utility.notifications.close();

		$.ajax({
			url: params.url,
			type: 'POST',
			data: params.data,
			dataType: 'html',
			// jQuery handler for AJAX success ( note: this is different from the success/error status sent by json )
			success: function(response, textStatus, XMLHttpRequest) {

				if( typeof(params.on_success) === 'undefined' ){


				//------------------------------------------
				// write general success functions here
				//------------------------------------------



				}
				else{
					// overriding success functions
					params.on_success(response, textStatus, XMLHttpRequest);
				}

				//params.on_success_complete(response, textStatus, XMLHttpRequest);

				params.on_complete(response, textStatus, XMLHttpRequest);

				// send notification message in notification box
				//wm.admin.utility.notifications.notify( response.status, response.message );
				var respStatus = wm.constants.status.success;
				var respMessage = wm.constants.msgs['SUCCESS']['COMPLETE'];

				if( params.notification === true ){
					wm.admin.utility.notifications.notify( respStatus, respMessage );
				}

				return true;
			},

			// jQuery handler for AJAX error ( note: this is different from the error status sent by json )
			error:  function(XMLHttpRequest, textStatus, errorThrown) {

				//wm.admin.utility.notifications.notify( wm.constants.status.error, wm.constants.msgs['ERROR']['INT_CNNT'] );
				var respStatus = wm.constants.status.error;
				var respMessage = wm.constants.msgs['ERROR']['INT_CNNT'];

				if( params.notification === true ){
					wm.admin.utility.notifications.notify( respStatus, respMessage );
				}

				return false;
			}
		});

	};

	/**
	* General function to perform AJAX post,
	* Expects JSON as output ( in WM standard format ).
	* Supports default and custom events that can be triggered on success, error . . . etc
	*
	* Following params can be passed in the form of an associative array {}
	*
	* @param params			-- Variables, Functions that can be passed ( given below ) <br/><br/>
	*
	* params = {					 <br/>
	*
	*	 url			-- url to post to <br/>
	*	 data			-- optional data to send <br/>
	*	 notification		-- (bool) to display notification message for current response or not <br/>
	*	 on_success		-- overriding handler to handle success ( will replace default success handler if provided )<br/>
	*	 on_success_complete	-- handler to execute after default success operations are complete <br/>
	*	 on_error		-- overriding handler to handle error ( will replace default error handler if provided ) <br/>
	*	 on_error_complete	-- handler to execute after default error operations are complete <br/>
	*	 on_complete		-- handler to perform after operation is complete ( both status = success OR status == error ) <br/>
	* }
	*
	* @return bool	-- returns true ( on ajax success ), return false ( on ajax failure )
	**/
	obj.post = function( params ){

		// set default param values
		params = ( typeof(params) === 'undefined' ) ? {} : params;
		params.url = typeof( params.url ) == 'undefined' ? '' : params.url;
		params.data = typeof( params.data ) == 'undefined' ? '' : params.data;
		params.notification = typeof( params.notification ) == 'undefined' ? true : params.notification;
		params.async = typeof( params.async ) == 'undefined' ? true : params.async;

		params.on_complete		= typeof( params.on_complete ) == 'undefined' ? function(){} : params.on_complete;
		params.on_success_complete	= typeof( params.on_success_complete ) == 'undefined' ? function(){} : params.on_success_complete;
		params.on_error_complete	= typeof( params.on_error_complete ) == 'undefined' ? function(){} : params.on_error_complete;

		// close previous notification ( if any )
		wm.admin.utility.notifications.close();

		$.ajax({
			url: params.url,
			type: 'POST',
			data: params.data,
			dataType: 'json',
			async: params.async,
			// jQuery handler for AJAX success ( note: this is different from the success/error status sent by json )
			success: function(response, textStatus, XMLHttpRequest) {

				if( response.status == wm.constants.status.success ){

					wm.debug('post response : success : ' + response.message );

					if( typeof(params.on_success) === 'undefined' ){

					//---------------------------------------------
					// write general success functions here
					//---------------------------------------------

					}
					else{
						// overriding success functions
						params.on_success(response, textStatus, XMLHttpRequest);
					}

					params.on_success_complete(response, textStatus, XMLHttpRequest);
				}
				else if( response.status == wm.constants.status.error ) {

					wm.debug('post response : error : ' + response.message );

					// check if overriding on_error handler provided
					if( typeof(params.on_error) === 'undefined' ){


					//---------------------------------------------
					// write general error functions here
					//---------------------------------------------


					}
					else{
						// overriding error functions
						params.on_error(response, textStatus, XMLHttpRequest);
					}

					params.on_error_complete(response, textStatus, XMLHttpRequest);
				}

				params.on_complete(response, textStatus, XMLHttpRequest);

				// send notification message in notification box
				respStatus = response.status;
				respMessage = response.message;

				if( params.notification === true ){
					wm.admin.utility.notifications.notify( respStatus, respMessage );
				}

				return true;
			},

			// jQuery handler for AJAX error ( note: this is different from the error status sent by json )
			error:  function(XMLHttpRequest, textStatus, errorThrown) {

				wm.debug( wm.constants.msgs['ERROR']['INT_CNNT'] );

				respStatus = wm.constants.status.error;
				respMessage = wm.constants.msgs['ERROR']['INT_CNNT'];

				if( params.notification === true ){
					wm.admin.utility.notifications.notify( respStatus, respMessage );
				}

				return false;
			}
		});

	};

	return obj;

})( window );



/************************************************************
*************************************************************
***** Notifications Namespace ( wm.admin.utility.notifications )
*************************************************************
************************************************************/

wm.admin.utility.notifications = (function( window, undefined ){

	var obj = {};

	// notifyObj will hold the jQuery objects for notification related fields
	var notifyObj = {
		container : $('#notifications'),
		status	: $('#notification_status'),
		message	: $('#notification_message')
	};

	var animateTime_down = 300;
	var animateTime_up = 100;


	/**
	 * Initialize Notifications
	 **/
	obj.init = function(){

		$('#notify_close_button').click(function(){
			wm.admin.utility.notifications.close();
		});

	// wm.debug('notifications initialized');

	};

	/**
	 * Notifies the user in notifications bar
	 * @params status	-- valid values ( success, error, info, warning )
	 * @params message	-- string message
	 * */
	obj.notify = function( status, message ){

		// set defaults
		status = ( typeof( status ) === 'undefined'  || status === '' ) ? '' : status;
		message = ( typeof(message) === 'undefined'  || message === '' ) ? '' : message;
		var className = 'alert-' + status.toLowerCase();

		obj.close();

		notifyObj.status.html( status );
		notifyObj.message.html( message );
		notifyObj.container.removeClass().addClass('alert').addClass(className).slideDown(animateTime_down);

	};

	/**
	 * Closes the notification bar
	 **/
	obj.close = function (){

		notifyObj.container.slideUp(animateTime_up);
	};

	return obj;

})( window );