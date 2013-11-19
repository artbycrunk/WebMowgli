<div class="errors" style="color: red;">
        {admin:error_message}
</div>
<!--<form action="{site:root}/admin/import/import_site_do" method="POST" enctype="multipart/form-data">

        Enter default page if any <i>(Eg. index.html, some/folder/home.html)</i><br/>
        <input type="text" name="default_file" value="" /><br/><br/>

        <input type="checkbox" name="create-page" {add_template:create-page} checked="checked" /> Automatically create pages<br/><br/>

        Upload site (zip) <br/>
        <input type="file" name="zip-file" /><br/><br/>

        <input type="submit" name="submit" value="Upload Site" />
</form>-->

<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs" class="form_inline_msgbox alert alert-info"></div>
        </div>
        <div id="form">
                <form name="import-theme" id="import-theme" action="{site:root}/admin/import/import_site_do" method="post" enctype="multipart/form-data" form_type="wm_upload_form">

                        <table class="form-table">

				<tr><th class="form_field_label"><label for="default_file">Default file</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="" id="default_file" name="default_file">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Enter default page if any <i>(Eg. index.html, some/folder/home.html)</i></div>
                                        </td></tr>

				<tr><th class="form_field_label"><label for="post_is_comments">Pages</label></th></tr>
                                <tr><td class="field">
                                                <input type="checkbox" name="create-page" checked="checked" /> Automatically create pages ?
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">If checked, pages will be created with the same name as the template being used</div>
                                        </td></tr>


                                <tr><th class="form_field_label"><label for="zip-file">Upload Site</label></th></tr>
                                <tr><td class="field">
						<input type="file" name="zip-file" />
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Upload a .zip file containing website templates and resources.</div>
                                        </td></tr>

				<tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Upload" value="Upload" />
                                        </td></tr>
                        </table>

                </form>
        </div><!-- #form -->
</div>
