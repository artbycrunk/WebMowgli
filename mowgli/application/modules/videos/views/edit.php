<script type="text/javascript">
	/*
	 * 'General settings' page specific JS
	 */
	var server_message='{default-message}';
//	$(document).ready(function(){
//		init();
//	});
	init=function(){
		wm.admin.forms.init();
		tinymce_init();
	}
</script>
<div id="form-wrapper">
	<div id="form-notification-container">
		<div id="server_error_msgs"></div>
	</div>
	<div id="form">
		<form name="vid-edit" id="vid-edit" action="{video:post_url}" method="post" form_type="wm_standard_form">
			<input type="hidden" name="action_type" value="{video:action_type}" />
			<input type="hidden" name="video_id" value="{video:id}" />
			<table class="form-table">
				<tr><th class="form_field_label"><label for="title">Title</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{video:title}" id="title" name="title">
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="ref_id">Ref Id</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{video:ref_id}" id="ref_id" name="ref_id">
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="name">Image Url</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{video:image_url}" id="image_url" name="image_url">
						<div class="form_inline_msgbox"></div>
					</td></tr>

<!--    <tr><th class="form_field_label"><label for="name">Order</label></th></tr>
    <tr><td class="field">
        <input class="form_text_fields" type="text" value="{video:order}" id="order" name="order">
        <div class="form_inline_msgbox"></div>
    </td></tr>

				-->
				<tr><th class="form_field_label"><label for="is_visible">Visibility</label></th></tr>
				<tr><td class="field">
						{video:checkbox:is_visible} Show this video to visitors ?
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="description">Description</label></th></tr>
				<tr><td class="field">
						<textarea class="form_textarea_field tinymce_standard" name="description" id="description">{video:description}</textarea>
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<!-- Hidden NOT Used
				<tr><th class="form_field_label">Script</th></tr>
				<tr><td class="field">
					    <textarea class="form_textarea_field" name="script" id="script">{video:script}</textarea>
				    <div class="form_inline_msgbox"></div>
				</td></tr>
				-->
				<tr><td class="seperator"></td></tr>
				<tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
					</td></tr>

			</table>

		</form>
	</div><!-- #form -->
</div>
