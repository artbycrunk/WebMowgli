<script type="text/javascript" src="{module:resource}/scripts/discography.functions.js"></script>

<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';

	init=function(){

                mainDiscoInit();
		slugify( '#name', '#slug' );
		tinymce_init();
        }

</script>


<div id="form-wrapper">
	<div id="form-notification-container">
		<div id="server_error_msgs"></div>
	</div>
	<div id="form">
		<form name="discography-form" id="discography-form" action="{discography:post_url}" method="post" form_type="wm_standard_form" onsubmit="submitHandler()">
			<input type="hidden" name="action_type" value="{discography:action_type}" />
			<input type="hidden" name="item_id" value="{discography:item:id}" />
			<table class="form-table">

				<tr><th class="form_field_label"><label for="name">Name</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{discography:item:name}" id="name" name="name" required="required">
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="slug">Slug</label></th></tr>
				<tr><td class="field">
						<input class="form_text_fields" type="text" value="{discography:item:slug}" id="slug" name="slug" >
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="parent_id">Album</label></th></tr>
				<tr><td class="field">
						{discography:select:categ:id}<!-- name=parent_id -->
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="name">Visible</label></th></tr>
				<tr><td class="field">
						{discography:checkbox:item:is_visible} Is this visible to visitors ?<!-- name=is_visible -->
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><th class="form_field_label"><label for="description">Description</label></th></tr>
				<tr><td class="field">
						<textarea class="form_textarea_field tinymce_standard" name="description" id="description">{discography:item:description}</textarea>
						<div class="form_inline_msgbox"></div>
					</td></tr>

				<tr><td class="seperator"></td></tr>
				<tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
					</td></tr>

			</table>

		</form>
	</div><!-- #form -->
</div>
