//-----------------------------------------------------------------------//
//-------------------- Namespace : wm -----------------------------------//
//-----------------------------------------------------------------------//
var wm = wm || {};
wm = (function( window, undefined ){

	var obj = {};

	var site_url = null;

	var debugging = true;
	var environment = null;	// ( possible values : 'development', 'testing', 'production'  )

	obj.isLogged = true;

	obj.constants = {
		status : {
			success	: 'success',
			error	: 'error',
			info	: 'info',
			warning	: 'warning'
		},

		env : {
			dev	: 'development',
			test	: 'testing',
			prod	: 'production'
		},

		msgs : {
			SUCCESS: {
				COMPLETE : 'Operation completed successfully.'
			},
			ERROR:{
				INT_CNNT:'Unable to reach the server. Please check your internet connection or company\'s network settings.',
				CNT_ADM:'Kindly contact the Admin.',
				CNG_NS:'Changes could not be saved.'
			},
			HTML:{
				NO_IMGS_TO_DISPLAY:'<div class="GMSGS__NO_IMGS_TO_DISPLAY">No Images added yet. Click upload or add images from another category.</div>'
			},
			BASIC:{
				SAVING:'Saving changes...'
			}
		}
	};

	obj.set_environment = function( env ){

		if( typeof( env ) !== 'undefined' && env !== null ){
			environment = env;
		}
	};

	/******************************
	 * DEBUG Functions.
	 ******************************/
	obj.debug = function (msg){

		if( debugging === true ){

			if(typeof(console) === "undefined") {
				console = {
					log: function(){}
				};
				return;
			}

			// DO NOT log debug messages in 'production' mode
			if( environment !== obj.constants.env.prod ){
				console.log(msg);
			}

		}
	};

	/**
	 * Scrolls page to given offset
	 **/
	obj.scrollPage = function (targetOffset) {
		$('html,body').animate({
			scrollTop: targetOffset
		}, 500);
		return false;
	};

	/**
	 * Redirects the browser to the url provided
	 **/
	obj.redirect = function(url){
		window.location = url;
	};

	/**
	 * Sets Full URL of site ( e.g. example.com )
	 *
	 * @param url	-- (optional) url to set as
	 **/
	obj.set_url = function(url){

		obj.site_url = url;
	};
	/**
	 * Gets Full URL of site ( e.g. example.com )
	 * if uri provided, then gets full url, along with concatinated uri
	 *
	 * @param uri	-- (optional) Uri to add to root url
	 *
	 * @return string -- Returns full url
	 **/
	obj.get_url = function(uri){

		// if site_url NOT defined, set from window.site_url
		if( typeof( obj.site_url ) === 'undefined' ){

			obj.set_url(window.site_url);
		}

		// if uri NOT provided, just return site_url, else concatinate with uri
		return ( typeof( uri ) === 'undefined' ) ? obj.site_url : obj.site_url + uri;
	};

	return obj;

})( window );