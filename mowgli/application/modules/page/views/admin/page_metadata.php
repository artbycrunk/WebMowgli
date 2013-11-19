<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
        <head>
                <meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
                <title>Edit Page Meta Data</title>

                {admin:includes:head}
		{admin:includes:scripts}
                <!-- admin head sections ends  -->

                <style>
                        /*Over-writing*/
                        table.form-table {
				/*width: 300px;*/
                        }

                        #server_error_msgs {
                                /* margin: 20px 0 10px;*/
                        }

                        #form-wrapper {
                                /* width: 350px;*/
                        }

                        #form {
                                /**/ height: 310px;
                                margin: 0 auto 0 25px;
				/*                                overflow: auto;*/
                        }
                        #submit-btn {
                                /*margin: 0 auto 15px;
                                float: none;*/
                        }
                        body{ background-color :#FFF; height:auto; padding: 0px;}
                </style>

                <!-- Forms -->

                <script type="text/javascript">
                        /*
                         * 'General settings' page specific JS
                         */
                        var server_message='{default-message}';
			//                        $(function () {
			//                                init();
			//                        });
			//                        function init(){
			//                                 wm.admin.forms.init();
			//                        }
			init=function(){

				wm.admin.forms.init();
			}
                </script>
        </head>
        <body class="edit_meta">
                <div id="form-wrapper">
                        <form name="edit_meta-settings" id="edit_meta-settings" action="{site:root}/admin/page/edit_meta_do?no_wrap=1" method="post" form_type="wm_standard_form">
                                <div id="form-notification-container">
                                        <div id="server_error_msgs"></div>
                                </div>
                                <div id="form">
                                        <input type="hidden" value="{page:id}" id="page_id" name="page_id" />
                                        <table class="form-table">
                                                <tr><th class="form_field_label"><label for="page_title">Page Title</label></th></tr>
                                                <tr><td class="field">
                                                                <input class="form_text_fields" type="text" value="{page:title}" id="page_title" name="page_title" required="required" />
                                                                <div class="form_inline_msgbox">Title of page</div>
                                                        </td></tr>

                                                <tr><th class="form_field_label"><label for="page_keywords">Keywords</label></th></tr>
                                                <tr><td class="field">
                                                                <textarea class="form_textarea_field" id="page_keywords" name="page_keywords" style="height: 60px; width: 200px;">{page:keywords}</textarea>
                                                                <div class="form_inline_msgbox">Separate with comma (,)</div>
                                                        </td></tr>

                                                <tr><th class="form_field_label"><label for="page_desc">Description</label></th></tr>
                                                <tr><td class="field">
                                                                <textarea class="form_textarea_field" id="page_desc" name="page_desc" style="height: 60px; width: 200px;">{page:description}</textarea>
                                                                <div class="form_inline_msgbox"></div>
                                                        </td></tr>

						<tr><td class="seperator"></td></tr>
						<tr><td class="field">
								<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
							</td></tr>

                                        </table>

                                </div><!-- #form -->

                        </form>
                </div>

        </body>
</html>
