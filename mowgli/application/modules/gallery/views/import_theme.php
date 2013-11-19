<div class="errors" style="color: red;">
        {admin:error_message}
</div>

<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs" class="form_inline_msgbox alert alert-info"></div>
        </div>
        <div id="form">
                <form name="import-theme" id="import-theme" action="{action_url}" method="post" enctype="multipart/form-data" form_type="wm_upload_form">

                        <table class="form-table">

                                <tr><th class="form_field_label"><label for="zip-file">Upload Theme</label></th></tr>
                                <tr><td class="field">
						<input type="file" name="zip-file" />
<!--                                                <input class="form_text_fields" type="text" value="zip-file" id="zip-file" name="zip-file">-->
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Upload a .zip file for gallery theme</div>
                                        </td></tr>

				<tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Upload" value="Upload" />
                                        </td></tr>
                        </table>

                </form>
        </div><!-- #form -->
</div>