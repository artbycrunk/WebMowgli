/**
 *
 *Plan for General Manage Pages Jquery
 *
 **/

var wm_manage = {};

/**
* Removes the Row from the table ( front end only ),
* Has dual input param, input can be either the 'Row Object' OR 'row id'
*
* @param row	-- row Jquery object OR row id
**/
wm_manage.remove_row = function( row ){

	var rowObj;

	if( jQuery.type( row ) == 'object' ){

		// row is row Object
		rowObj = row;
	}
	else if ( jQuery.type( row ) == 'string' ){

		// row is 'row_id', get rowObj
		rowObj = wm_manage.get_row_obj_by_id( row );
	}
	else{
		alert('Error : unable to remove row, invalid data type provided');
	}

	rowObj.hide( 'slow', function(){
		rowObj.remove();
	} );
};


/**
* Get the Id of the current row being manipulated
* note: picks up value from <tr row_id="xx"> if element, attrib NOT specified.
*
* @param selector	-- selector of any item inside the row ( e.g. button, checkbox, etc )
* @param element	-- parent containing element that holds unique row id ( default = 'tr' )
* @param attrib		-- attribute of parent row element that contains id ( default = 'row_id' )
*
* @return id of current element
**/
wm_manage.get_row_id = function ( selector, element, attrib){

	var element = ( element == undefined ) ? "tr" : element;
	var attrib = ( attrib == undefined ) ? "row_id" : attrib;

	return $(selector).closest(element).attr(attrib);
};


/**
* Returns the Jquery object of the current row ( based on child selector provided )
*
* @param selector	-- selector of any item inside the row ( e.g. button, checkbox, etc )
* @param element	-- parent containing element that holds unique row id ( default = 'tr' )
*
* @return object	-- return Jquery object of row
**/
wm_manage.get_row_obj = function ( selector, element ){

	var element = ( element == undefined ) ? "tr" : element;
	var attrib = ( attrib == undefined ) ? "row_id" : attrib;

	return $(selector).closest(element);
};

wm_manage.get_row_obj_by_id = function( rowId ){

	return $( "tbody tr[row_id='" + rowId + "']" );
};

wm_manage.get_checked_row_ids = function (){

	var ids = [];

	$('tbody input:checked').each(function(){
		var rowId = wm_manage.get_row_id( this );
		ids.push( rowId );
	});

	return ids;
};






/********************************************************************
 ********************************************************************
 ********************** PLANNING ********************************
 ********************************************************************
 ********************************************************************/

/**
 *Load is responsible for reloading the main table, with data
 *It should also consider the current page number ( pagination ) and load the data for that current page only
 *	- if page number NOT specified, use current page from pagination, else load page provided
 *It should also take in account filters that may be set
 *	- filters could hold array of values in a 3 part format e.g. { key = 'date_modified', operator = '>', value = '2012-03-30' }
 *On completion AJAX should check status for error, success, info, warning and accordingly display at notification area.
 *custom handler functions should be callable on success, error
 *
 *Parameters:
 *
 *@param selector_display Provide Selector for Table container ( e.g. "#table_container" )
 *@param selector_pagination Provide selector container for pagination ( e.g. ".pagination_container" )
 *@param pageNo page to display in pagination, possible values should be  page_no, null, 'prev', 'next'
 *@param filters Array of filters to apply to table, each filter can be combination of key-operator-value arrays OR null for no filteration
 *@param controller uri of controller to send AJAX request, to obtain table to load
 *@param handler function to call after success OR error
 *
 **/
function load( selector_display, selector_pagination, pageNo, filters, controller, handler ){}


/*
 * PLANNING
 *
 * ----------------------------------------------------------------
 * Initializing params
 * ----------------------------------------------------------------
 *
 * Global Variables
 * ---------------------------------------------------
 * select_pagination -- the selector of the container holding the pagination ( mostly class )
 * table container id
 * row container class
 * row id identifier -- the property name ( in <tr > ) for each row that will hold the id of the element ( e.g. <tr post_id="13"> )
 *			note: this can be used with .closest() to find id of current element for various actions
 * current_page_no -- the current page number for pagination
 * pagination current page selector ( mostly class ) -- get the current page from this element
 *
 *
 * Creation
 *	selector -- trigger selector ids ( button id / anchor id to submit form to backend )
 *	post -- controller to submit to when form is filled up and submitted
 *	redirect -- ( optional ) in case add form is another page ( redirect to add page )
 *
 *	on_click  -- Overriding function to do when trigger is fired, instead of regular operation
 *	on_success -- Overriding function to do incase of success from backend
 *	on_success_after -- handler to call after default success operations are called
 *
 *	on_error -- Overriding function to do in case of failure from backend
 *	on_error_after -- handler to call after default success operations are called
 *
 * Updation
 *	selector -- trigger selector class ( button class, link class )
 *	post
 *	redirect
 *
 *	on_click
 *	on_success
 *	on_success_after
 *	on_error
 *	on_error_after
 *
 * Load / Refresh
 *	trigger selector ( class OR id ) --- any button, link that should reload the table
 *
 *	filters Array of filters to apply to table, each filter can be combination of key-operator-value arrays OR null for no filteration
 *	post -- controller to load only table, pagination
 *
 *	on_success
 *	on_success_complete
 *	on_error
 *	on_error_complete
 *
 * Delete
 *	selector -- selector class, individual buttons, links to delete single (current) item
 *	post -- controller to delete
 *
 *	on_success
 *	on_success_complete
 *	on_error
 *	on_error_complete
 *
 *
 * ----------------------------------------------------------------
 * Functions
 * ----------------------------------------------------------------
 *
 * add_row()
 * refresh() -- with pagination, filters
 * get_checked_items -- get all the currently checked items
 *
 *
 * ----------------------------------------------------------------
 * Notes:
 * ----------------------------------------------------------------
 * - to get the id of the element on button click ( for each row ) --> use .closest("tr.some_class").attr("post_id")
 * - use .live() OR .on() to bind events to table rows that may load inline due to pagination.
 *
 **/