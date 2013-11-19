wm.admin.tables = (function( window, undefined ){

	var obj = {};

	obj.init = function(){

		//Give all checkboxes a value.
		$(".checkbox").each(function(i, e) {
			$(e).attr("value", i)
		});

		//Init Tablesort on class=tablesorter.

		$(".tablesorter").tablesorter({
			headers: {
				0: { //Disable for checkbox column.
					sorter: false
				}
			}
		});
		$(".tablesorter").bind("sortStart",function() {
			//$("#overlay").show();
			}).bind("sortEnd",function() {
			$(".checkbox").each(function(i, e) {
				$(e).attr("value", i)
			});
		});

		//Show row buttons on hover.
		$(".row-small, .row-medium, .row-large").mouseover(function() {
			$(this).find(".row-buttons").toggle();
		}).mouseout(function(){
			$(this).find(".row-buttons").toggle();
		});

		// add multiple select / deselect functionality
		$("#selectall").click(function () {
			$('.checkbox').attr('checked', this.checked);
		});

		$(".checkbox").click(function(){

			if($(".checkbox").length == $(".checkbox:checked").length) {
				$("#selectall").attr("checked", "checked");
			} else {
				$("#selectall").removeAttr("checked");
			}

		});

		// add Shift select checkbox capabilities.

		var lastChecked = null;

		var chkboxes = $('.checkbox');
		chkboxes.click(function(event){

			if(!lastChecked) {
				lastChecked = this;
				return;
			}

			if(event.shiftKey) {
				var start = chkboxes.index(this);
				var end = chkboxes.index(lastChecked);

				chkboxes.slice(Math.min(start,end), Math.max(start,end)+ 1).attr('checked', lastChecked.checked);

			}

			lastChecked = this;

		});

	};

	return obj;

})( window );