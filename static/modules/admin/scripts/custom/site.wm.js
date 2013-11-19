//-----------------------------------------------------------------------//
//-------------------- Namespace : wm.site --------------------------------//
//-----------------------------------------------------------------------//

wm.site = (function( window, undefined ){

	var obj = {};

	/**
	 * Initialize Admin Panel ( Page front )
	 **/
	obj.init = function(){

		//Init the top-bar

		$("a#clpse-arrow")
		.click(function(event) {
			event.stopPropagation();

			if($(this).attr('position')=='open'){
				$('#clpse-arrow span.up_arrow').hide();
				$('#clpse-arrow span.down_arrow').show();
				$('.navbar').animate({
					top: -20
				}, {
					duration: 200,
					complete: function(){}
				});
				$(this).attr('position','closed');
			} else {
				$('#clpse-arrow span.down_arrow').hide();
				$('#clpse-arrow span.up_arrow').show();
				$('.navbar').animate({
					top: 0
				}, {
					duration: 200,
					complete: function(){}
				});
				$(this).attr('position','open');
			}
			return false;//Do not continue with default browser action. That is goto #
		});

		//Top sliding panel
		$("#toppanel-container ul#button-list li a").click(function(){
			if($(this).attr('panel_status')=='closed') {
				$("div#toppanel div#panel").slideDown("slow");
				$(this).removeClass('open');
				$(this).addClass('close');
				$(this).attr('panel_status','opened');
			} // Expand Panel
			else {
				$("div#toppanel div#panel").slideUp("slow");
				$(this).removeClass('close');
				$(this).addClass('open');
				$(this).attr('panel_status','closed');
			} // Collapse Panel
		});

	};

	return obj;

})( window );