<script type="text/javascript" src="{module:resource}/scripts/events.functions.js"></script>

<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';

	init=function(){

                mainEventInit( '{module:resource}' );
		slugify( '#name', '#slug');
		tinymce_init();
        }

</script>

<style type="text/css" >
        /* OVER-riding css */
	.ui-datepicker-trigger{
		margin:0px 0 0 10px;
		cursor:pointer;}

</style>

<!-- TEMPORARY End -->

<div id="form-wrapper">
	<div id="form-notification-container">
		<div id="server_error_msgs"></div>
	</div>
	<div id="form">
		<form name="events-form" id="events-form" action="{event:post_url}" method="post" form_type="wm_standard_form" onsubmit="javascript:submitHandler()">
			<input type="hidden" name="action_type" value="{event:action_type}" />
			<input type="hidden" name="event_id" value="{event:id}" />
			<table class="form-table">
				<tr><th class="form_field_label"><label for="name">Name</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{event:name}" id="name" name="name">
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="slug">Slug</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{event:slug}" id="slug" name="slug">
						<div class="form_inline_msgbox"><i style="color: red;">?? auto slug does not work for edit</i></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="venue">Venue</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{event:venue}" id="venue" name="venue">
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="start_date">Start</label></th></tr>
				<tr><td class="field">
						<input type="text" class="form_text_fields date_instance" style="width:68px" value="{event:start}" id="start_date" name="start_date" disabled="disabled" />
						<input type="text" class="form_text_fields time_instance" style="width:45px" value="{event:start:time}" id="start_time" name="start_time" />
						<input type="hidden" value="" id="start" name="start" />
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="end_date">End</label></th></tr>
				<tr><td class="field">
						<input type="text" class="form_text_fields date_instance" style="width:68px" value="{event:end}" id="end_date" name="end_date" disabled="disabled" />
						<input type="text" class="form_text_fields time_instance" style="width:45px" value="{event:end:time}" id="end_time" name="end_time" />
						<input type="hidden" value="" id="end" name="end" />
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="description">Description</label></th></tr>
				<tr><td class="field">
						<textarea id="description" name="description" class="form_textarea_field tinymce_standard">{event:description}</textarea>
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><td class="seperator"></td></tr>
				<tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
					</td></tr>
			</table>
		</form>

	</div>
</div>