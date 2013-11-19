<script type="text/javascript">
        /*
         * 'Datetime settings' page specific JS
         */
        var server_message='{default-message}';
	//        $(document).ready(function(){
	//                wm.debug('ready')
	//                init();
	//        });
        init=function(){
                //wm.debug('init')
                wm.admin.forms.init();

		var dstCheckObj = $('#dst_used');
		var dstContainerObj = $('#dst_used_container');

                // to show dst_used fields if checked
                if( dstCheckObj.is(':checked') ){
                        dstContainerObj.show();
                }

		// show / hide dst fields when clieked
                dstCheckObj.click(function() { dstContainerObj.toggle(300); });

                // to use value of select box, in text field
                $("#format_date_select").change(function(){
//			wm.debug( 'selected Date : ' + $(this).val() );
			$("#format_date").val( $(this).val() );

		});
                $("#format_time_select").change(function(){

			$("#format_time").val( $(this).val() );
		});

        }

</script>

<div id="form-wrapper">
        <div id="form-notification-container">
                <div id="server_error_msgs"></div>
        </div>
        <div id="form">
                <form name="datetime-settings" id="datetime-settings" action="{admin:root}/settings/datetime_save" method="post" form_type="wm_standard_form">

                        <input type="hidden" name="setting_categ" value="{set:category}" />

                        <table class="form-table">

                                <!-- Timezone -->
                                <tr><th class="form_field_label"><label for="timezone">Timezone</label></th></tr>
                                <tr><td class="field">
                                                <!-- name='timezone' -->
                                                {set:select:timezone}
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Select local timezone</div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="format_date">Date Format</label></th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:format_date}" id="format_date" name="format_date" >
                                                <select class="form_select_fields" name="format_date_select" id="format_date_select">
                                                        <option selected value="">Select format</option>
                                                        <option value="d/m/Y"><?php echo date("d/m/Y"); ?></option>
                                                        <option value="m/d/Y"><?php echo date("m/d/Y"); ?></option>
                                                        <option value="F j, Y"><?php echo date("F j, Y"); ?></option>
                                                        <option value="jS F, Y"><?php echo date("jS F, Y"); ?></option>
                                                        <option value="l, F j, Y"><?php echo date("l, F j, Y"); ?></option>
                                                </select>
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Select format for date, or use <a href="http://php.net/manual/en/function.date.php#refsect1-function.date-parameters" target="_blank">custom formats</a></div>
                                        </td></tr>

                                <tr><th class="form_field_label"><label for="format_time">Time Format</label> </th></tr>
                                <tr><td class="field">
                                                <input class="form_text_fields" type="text" value="{set:value:format_time}" id="format_time" name="format_time" >
                                                <select class="form_select_fields" name="format_time_select" id="format_time_select">
                                                        <option selected value="">Select format</option>
                                                        <option value="g:i a"><?php echo date("g:i a"); ?></option>
                                                        <option value="g:i A"><?php echo date("g:i A"); ?></option>
                                                        <option value="H:i"><?php echo date("H:i"); ?></option>
                                                </select>

                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip">Select format for time, or use <a href="http://php.net/manual/en/function.date.php#refsect1-function.date-parameters" target="_blank">custom formats</a></div>
                                        </td></tr>



                                <tr><th class="form_field_label"><label for="dst_used">Daylight Savings Time</label></th></tr>
                                <tr><td class="field">
                                                {set:checkbox:dst_used} Activate daylight savings time ( DST )
                                                <div class="form_inline_msgbox"></div>
                                                <div class="form_inline_tip"></div>
                                        </td></tr>

                        </table>

                        <div id="dst_used_container" style="display:none">
                                <table class="form-table">

                                        <tr><th class="form_field_label"><label for="dst_offset">DST Offset</label></th></tr>
                                        <tr><td class="field">
                                                        <input class="form_text_fields" type="text" value="{set:value:dst_offset}" id="dst_offset" name="dst_offset" size="15">
                                                        <div class="form_inline_msgbox"></div>
                                                        <div class="form_inline_tip">Enter dst offset in hours ( e.g. +1, -0.5, +1.5 )</div>
                                                </td></tr>

                                </table>
                        </div>

                        <table class="form-table">
                                <tr><td class="seperator"></td></tr>
                                <tr><td class="field">
                                                <input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />
                                        </td></tr>

                        </table>

                </form>
        </div><!-- #form -->
</div>