<script type="text/javascript">

        var server_message='{default-message}';

        var default_category ='{default-category}';
        init=function(){
                wm.debug('init_ Gallery_settings form')
                wm.admin.forms.init();
		//tinymce_init();

		use_theme = $('#use_theme');

		use_theme.is(':checked') ? $("#section_current_theme").show() : $("#section_current_theme").hide();

		use_theme.click(function(){

//			alert( 'checked = ' + this.checked );

			$('#section_current_theme').toggle( this.checked );

		});
        }
</script>


<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="gallery-settings" id="gallery-settings" action="{admin:root}/gallery/settings_save" method="post" form_type="wm_standard_form">

                        <table class="form-table">

                                <!-- Thumbnail Sizes (  thumbnail_width, thumbnail_height ) -->
                                <tr><th class="form_field_label"><label for="thumbnail_width">Thumbnail size</label></th></tr>
                                <tr><td class="field">
						<label for="thumbnail_width">width</label>
                                                <input class="form_text_fields" type="text" value="{thumbnail_width}" id="thumbnail_width" name="thumbnail_width">
						<label for="thumbnail_width">height</label>
						<input class="form_text_fields" type="text" value="{thumbnail_height}" id="thumbnail_height" name="thumbnail_height">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">default thumbnail size in pixels</div>
                                        </td></tr>

                                <!-- Use Theme -->
                                <tr><th class="form_field_label"><label for="use_theme">Use Theme</label></th></tr>
                                <tr><td class="field">
                                                {gallery:checkbox:use_theme} use gallery themes ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">use installed themes for gallery display</div>
                                        </td></tr>
			</table>


			<table id="section_current_theme" class="form-table">
				<!-- current_theme -->
				<tr><th class="form_field_label"><label for="current_theme">Current Theme</label></th></tr>
				<tr><td class="field">
						<!-- name='current_theme' -->
						{set:select:current_theme}
						<div class="form_inline_msgbox"></div>
						<div class="form_inline_tip">default theme to use for displaying gallery images</div>
					</td></tr>
			</table>

			<table class="form-table">

                                <!-- Save -->
                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>

                        </table>




                </form>
        </div><!-- #form -->
</div>

