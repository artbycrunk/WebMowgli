<script language="javascript" src="{module:resource}/scripts/resource.adminconsole.js"></script>
<script language="javascript">
        $(document).ready(function(){
                $('form#edit-resource').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
                bindEventHandlerToFormButtons_Edit();
        });
</script>
<!-- START uploadify -->
<!--<link href="{admin:resource}/scripts/uploadify/uploadify.css" type="text/css" rel="stylesheet" />
<script type="text/javascript" src="{admin:resource}/scripts/uploadify/swfobject.js"></script>
<script type="text/javascript" src="{admin:resource}/scripts/uploadify/jquery.uploadify.v2.1.4.min.js"></script>-->
<?php echo load_resources('js', array( 'swfobject', 'uploadify' ) ); ?>
<?php echo load_resources('css', 'uploadify' ); ?>

<script type="text/javascript">
        var response={};
        $(document).ready(function() {
                $('#file_upload').uploadify({
                        'uploader'  : '{admin:resource}/scripts/uploadify/uploadify.swf',
                        'script'    : '{admin:resource}/scripts/uploadify/uploadify.php',
                        'cancelImg' : '{admin:resource}/scripts/uploadify/cancel.png',
                        'folder'    : '{resource:upload_path}', //@KENT-CHANGE: updated to '{resource:upload_path}' -- what is this path for ?
                        'auto'      : false,
                        'multi'     : false,
                        'onAllComplete' : function(event,data) {
                                //alert(data.filesUploaded + ' files uploaded successfully!');

                                //Start polling server to get JSON array and other content
                                $.ajax({
                                        url: '{module:resource}/ajax_handler.php', // @KENT-CHANGE -- what is this path for ?
                                        type: 'POST',
                                        dataType: 'json',
                                        data: 'command=import_site_notifications',
                                        success: function(data, textStatus, XMLHttpRequest){
                                                //alert(data.messages.length+ textStatus+ XMLHttpRequest);
                                                //alert(data)
                                                response=data;
                                                //renderImportingResultsPage();
                                                renderNotifications(response);
                                        },
                                        complete: function(XMLHttpRequest, textStatus){
                                                //alert(data+ textStatus+ XMLHttpRequest)

                                        }
                                });

                                renderNotification('div#notification-area','success','Upload complete.');
                                $('div#notification-area').show();
                                $('div#upload_form').hide();
                        }//END onAllComplete
                });
        });

        /**
         * This function listens for error messages..
         * Display all messges below in categories.
         * Major messages shown in notification boxes.
         */

        function renderNotifications(response){
                var info_,success_,error_,warning_;
                info_=success_=error_=warning_='';
                //console.debug('here');
                $('div#other-notification-area').html('<div id="notification-information"></div>'+'<div id="notification-warning"></div>'+'<div id="notification-success"></div>'+'<div id="notification-error"></div>')
                for(i=0;i<response.info.length;i++){
                        info_+=response.info[i]+'<br>';
                }
                renderNotification('div#notification-information','information',info_);

                for(i=0;i<response.success.length;i++){
                        success_+=response.success[i]+'<br>';
                }
                renderNotification('div#notification-success','success',success_);

                for(i=0;i<response.error.length;i++){
                        error_+=response.error[i]+'<br>';
                }
                renderNotification('div#notification-error','error',error_);

                for(i=0;i<response.warning.length;i++){
                        warning_+=response.warning[i]+'<br>';
                }
                renderNotification('div#notification-warning','warning',warning_);

                $('div#other-notification-area').show();

        }
</script>
<!-- END uploadify -->
<style>
        form.jqtransformdone label{ font-size:12px; font-weight:bold;}

</style>
<h3 style="color:red">This file is corrupt, Needs to be fixed</h3>

<div id="notification-area" style="display:none"></div>
<div id="other-notification-area" style="display:none"></div>
<form name="edit-resource" id="edit-resource" action="{resource:ajax_update_url}" method="POST">
        <table class="form-table">
                <tbody>
                        <tr><td><span class="table-sub-header">Resource details</span></td></tr>

                        <tr>
                                <td><label>Name</label></td>
                        </tr>
                        <tr>
                                <td><input type="text" name="resource_name" size="15"  value="{resource:name}" />
                                        <br /><br /><br />
                                </td>
                        </tr>

                        <!-- $KENT-CHANGE
                                User should NOT change path, store path as hidden variable
                        -->
                        <tr>
                                <td><label>Path</label></td>
                        </tr>

                        <tr>
                                <td><input type="text" name="resource_path" size="15" value="{resource:path}" />
                                        do not let user edit path . . make as hidden
                                        <br /><br /><br />
                                </td>
                        </tr>



                        <tr>
                                <td><label>Url</label></td>
                        </tr>

                        <tr>
                                <td><label><a href="{resource:url}" target="_blank">{resource:url}</a></label></td>
                        </tr>

                        <tr>
                                <td><label>Code</label></td>
                        </tr>
                        <tr>
                                <td>
                                        <textarea cols="100" rows="50" name="resource_code">{resource:code}</textarea>
                                        <br /><br /><br />
                                </td>
                        </tr>

                        <tr>
                                <td>
                                        <label for="file_upload">Import Site</label>
                                        <br />
                                </td>
                        </tr>
                        <tr>
                                <td colspan="2">
                                        <input id="file_upload" name="file_upload" type="file" />
                                </td>
                        </tr>
                        <tr class="seperator-row"><td>&nbsp;</td></tr>
                        <tr><td>&nbsp;&nbsp;&nbsp;<input id="update_resource_button" type="button" value="Update" /></td></tr>
                        <tr class="seperator-row"><td>&nbsp;</td></tr>
                </tbody>
        </table>
</form>
