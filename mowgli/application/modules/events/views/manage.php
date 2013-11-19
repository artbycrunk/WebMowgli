<style>

	.event_item {
		background: none repeat scroll 0 0 #FFFFFF;
		border: 1px solid #D6D6D6;
		border-radius: 13px 13px 13px 13px;
		margin: 0 0 15px;
		padding: 8px;
	}

	.menu-button-item-blue{
		border-radius: 14px 14px 14px 14px;
		min-height: 13px;
		padding: 6px 6px 4px;
		width: 43px;
		float: right;
		margin:0px 5px 0 0;
	}


	.draghandle {
		background: -moz-linear-gradient(center top , #FFFFFF 0%, #F2F2E8 100%) repeat scroll 0 0 transparent;
		border: 1px solid #DFDFDF;
		border-radius: 3px 3px 3px 3px;
		box-shadow: 0 2px 2px #D6D6D6;
		display: block;
		font-size: 16px;
		height: auto;
		margin: 0 0 10px;
		padding: 12px 0 0 10px;
		position: relative;
	}

	.bottom_col{
		display:none;
		float: left;
		width: 98%;
	}

	.ent_title{
		color: #434343;
		display: block;
		font: 18px Helvetica;
		margin: 0 0 10px;
	}

	.ent_desc {
		border: 1px solid #D5D5D5;
		color: #6D6D6D;
		font-size: 13px;
		height: auto;
		line-height: 18px;
		margin: 5px 0 0;
		overflow: auto;
		padding: 5px;
		width: 98%;
	}

	.ent_label{
		color: #0A0909;
		font-size:12px;
	}

	.ent_text{
		color:#63666D;
		font-size:14px;
	}
	/*
	.ent_month{
	    color:#3E3E3E;
	    display: block;
	    font-size: 20px;
	    text-align: center;
	}

	.ent_day {
	    color: white;
	    display: block;
	    font-size: 23px;
	    margin: 5px 0 0;
	    text-align: center;
	}
	*/
	.date {
		background-color: #016DA5;
		border-radius: 5px 5px 5px 5px;
		box-shadow: 0 3px 3px #6A7478;
		color: #F2F2F2;
		display: inline-block;
		float: left;
		margin-top: -25px;
		text-shadow: 0 -1px 1px rgba(0, 0, 0, 0.48);
		width: 60px;
	}


	.date span.month {
		background-color: #444444;
		border-radius: 4px 4px 0 0;
		border-top: 1px solid #869DA6;
		color: #EDEDED;
		font-size: 1rem;
		line-height: 0.9em;
		padding: 3px 0;
		text-shadow: none;
		text-transform: uppercase;
	}

	.date span {
		display: block;
		font-weight: 700;
		text-align: center;
	}

	.date span.day {
		background-color: #EDEDED;
		border-radius: 0 0 4px 4px;
		border-top: 1px solid #869DA6;
		color: #444444;
		font-size: 2.6rem;
		line-height: 0.9em;
		text-shadow: 0 0 1px #777777;
	}

	.date span {
		display: block;
		font-weight: 700;
		text-align: center;
		text-transform: uppercase;
	}

	.expand_button {
		float: right;
		font-size: 25px;
		margin: -2px 10px 0 15px;
	}

	.expand_button a {
		background-color: #4D90FE;
		background-image: -moz-linear-gradient(center top , #81B1FC, #4288F8);
		border: 1px solid #AAAAAA;
		border-radius: 5px 5px 5px 5px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
		color: #FAFAFA;
		cursor: pointer;
		display: inline-block;
		font-family: "Helvetica Neue","Helvetica",Arial,Verdana,sans-serif;
		font-size: 18px;
		font-weight: bold;
		line-height: 1;
		padding: 2px 6px;
		position: relative;
		text-align: center;
		text-decoration: none;
		text-shadow: 0 -1px 1px rgba(0, 0, 0, 0.28);
		width: 10px;
	}

	.expand_button a:hover {
		background-color: #375AE8;
		background-image: -moz-linear-gradient(center top , #4D90FE, #357AE8);
		border: 1px solid #3079ED;
		color:#FFF;
	}

	.draghandle_left{
		display: block;
		float: left;
		padding: 0 0 8px 14px;
		width: 70%;
	}
	.draghandle_left span{ margin:0 0 5px 0;}


</style>

<script>
	
	init=function(){
		wm.debug('evnts init')

		// @Lloyd : handle Delete button click
		$('#events_list a.event-delete-button').click(function(e){

			e.preventDefault();

			var songObj = $(this);
			var itemId = songObj.attr('callback_id');

			//wm.debug('Delete button clicked : id = ' + itemId);

			confirm_action({

				message : "Permanently delete event ?",
				success : function(){

					wm.admin.utility.ajax.post({

						url : songObj.attr('href'),
						on_success : function(){

							$('li[ent_id='+itemId+']').hide(1500).remove();

						}

					});
				}
			});
		});

		$('div.expand_button a[type=js_container_toggler]').bind('click',function(){
			if($(this).html()=='+'){$(this).html('-').attr('title','Hide description');$(this).parents('.event_item').children('.bottom_col').slideDown(1500);}
			else{$(this).html('+').attr('title','Show description');$(this).parents('.event_item').children('.bottom_col').slideUp(1500);}
			return false;
		})

	}
</script>

<ul id="events_list">
	{events}
	<li class="event_item" ent_id="{event:id}">
		<div class="draghandle">
			<div class="date">
				<span class="month">{event:month}</span>
				<span class="day">{event:day}</span>
			</div>
			<div class="draghandle_left">
				<span class="ent_title">{event:name}</span>
				<span class="ent_label">Venue</span>&nbsp;&nbsp;<span class="ent_text">{event:venue}</span> - <span class="ent_label">Start date</span>&nbsp;&nbsp;<span class="ent_text">{event:start}</span>
			</div>
			<div class="expand_button"><a type="js_container_toggler" href="#" title="Show description">+</a></div>
			<a type="ajax_content" class="menu-button-item-blue" href="{event:edit_link}" >Edit</a>
			<a type="inplace" callback_id="{event:id}" target="_self" class="event-delete-button menu-button-item-blue" href="{event:delete_link}" >Delete</a>

			<br class="clear" />
		</div>
		<div class="bottom_col">
			<span class="ent_label">From</span>&nbsp;&nbsp;<span class="ent_text">{event:start}</span>&nbsp;&nbsp;-&nbsp;&nbsp;
			<span class="ent_label">To</span>&nbsp;&nbsp;<span class="ent_text">{event:end}</span>
			<br /><br />
			<span class="ent_label">Description</span>
			<div class="ent_desc">{event:description}</div>
			<br />
	    <!--            <span class="ent_label">Slug</span>&nbsp;&nbsp;<span class="ent_text">{event:slug}</span>
			-->        </div>
		<br class="clear" />
	</li>
	{/events}
</ul>