function mainEventInit( module_root ){

	wm.debug('init Events form')
	wm.admin.forms.init();

	var dates = $( ".date_instance" ).datepicker({
		dateFormat: 'yy-mm-dd',
		numberOfMonths: 3,
		showOn: "button",
		buttonImage: module_root + "/images/calendar.png",
		buttonImageOnly: true,
		showAnim:'drop',
		onSelect: function( selectedDate ) {
			var option = this.id == "start_date" ? "minDate" : "maxDate",
			instance = $( this ).data( "datepicker" ),
			date = $.datepicker.parseDate(
				instance.settings.dateFormat ||
				$.datepicker._defaults.dateFormat,
				selectedDate, instance.settings );
			dates.not( this ).datepicker( "option", option, date );
		}
	});

	$(".time_instance").timePicker({
		show24Hours: true,
		separator: ':',
		step: 15
	});

}

function submitHandler(){
	$('#start').val($('#start_date').val()+' '+$('#start_time').val())
	$('#end').val($('#end_date').val()+' '+$('#end_time').val())

	wm.debug("in submitHandler @ events.functions.jquery.js");
}
