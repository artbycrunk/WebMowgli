<script type="text/javascript">
        /*
        var server_message='{default-message}';
        $(document).ready(function(){
                wm.debug('ready')
                nit();
        });
         */
        var server_message='{default-message}';
        init=function(){

                wm.admin.forms.init();

                // hide/show fields specific to temp_type=module
                reset_form_view();
        }

        // will hide/show fields for temp_type=module, on type select
        $("#temp_type").change( reset_form_view );

        function reset_form_view(){

                var tempType = $('#temp_type').val();

                //alert( 'temp type = ' + tempType );

                if( tempType != 'module' ){

                        // hide module and head fields
                        $(".module_fields").hide('slow');
                        //alert( 'hidden' );
                }
                else{
                        $(".module_fields").show('slow');
                        //alert( 'visible' );
                }
        }

</script>
<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="edit-templates" id="edit-templates" action="{admin:root}/template/save" method="post" form_type="wm_standard_form">
                        <input type="hidden" name="action_type" value="{action_type}" />
                        <input type="hidden" name="temp_id" value="{temp_id}" />

                        <table class="form-table">
                                <tr><th class="form_field_label"><label for="temp_name">Name</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{temp_name}" id="temp_name" name="temp_name">
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">name of template</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="temp_type">Type</label></th></tr>
                                <tr><td class="field">
                                                {temp:select:temp_type}
                                                <!--
                                                <select class="form_select_fields" name="temp_type" id="temp_type">
                                                        <option selected value="">Select type</option>
                                                        <option value="page">Page</option>
                                                        <option value="includes">Includes</option>
                                                        <option value="module">Module</option>
                                                </select> -->
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">select the type of template</div>
                                        </td></tr>

				<tr class="module_fields" ><th class="form_field_label"><label for="temp_name">Module</label></th></tr>
				<tr  class="module_fields"><td class="field">
						{temp:select:temp_module_name}
						<!--
						<select class="form_select_fields" name="temp_module_name" id="temp_module_name">
							<option selected value="">Select module</option>
							<option value="module1">Module 1</option>
							<option value="module2">Module 2</option>
							<option value="module3">Module 3</option>
						</select>    -->
						<div class="form_inline_msgbox"></div>
						<div class="form_inline_tip">select the module to which the template belongs to</div>
					</td></tr>

				<tr  class="module_fields"><th class="form_field_label"><label for="temp_head">Head</label></th></tr>
				<tr  class="module_fields"><td class="field">
						<textarea class="form_textarea_field" name="temp_head" id="temp_head" cols="50" rows="15">{temp_head}</textarea>
						<div class="form_inline_msgbox"></div>
						<div class="form_inline_tip">enter html that is required between the &LT;head&GT; . . . &LT;&frasl;head&GT; for this template </div>
					</td></tr>

                                <tr><th class="form_field_label"><label for="temp_html">HTML</label></th></tr>
				<tr><td class="field">
						<textarea class="form_textarea_field" name="temp_html" id="temp_html" cols="80" rows="15">{temp_html}</textarea>
						<div class="form_inline_msgbox"></div>
						<div class="form_inline_tip">enter html or text for this template</div>
					</td></tr>

				<tr><th class="form_field_label"><label for="temp_is_visible">Visibility</label></th></tr>
				<tr><td class="field">
						{temp:checkbox:temp_is_visible} Make this template visible ?
						<div class="form_inline_msgbox"></div>
						<div class="form_inline_tip"></div>
					</td></tr>

				<tr><td class="seperator"></td></tr>
				<tr><td class="field">
						<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
					</td></tr>

			</table>

		</form>
	</div><!-- #form -->
</div>