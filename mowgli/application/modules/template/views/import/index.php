<script language="javascript" src="{module:resource}/scripts/template.adminconsole.js"></script>
<script language="javascript">
	$(document).ready(function(){
		$('form#import-new-template').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
	});
</script>
<style>
	form.jqtransformdone label{ font-size:12px; font-weight:bold;}
	tr.alternate_template_hide{ display:none}
	tr.alternate_template_show{ display:table-row}
</style>
<div>
<!--Notification area-->
</div>
<form name="import-new-template" id="import-new-template" action="post.php" method="POST">
  <table class="form-table">
  <tbody>
    <tr><td><span class="table-sub-header">Import Templates</span></td></tr>    
    <tr>
      <td>
      	<input type="checkbox" value="auto_create" name="auto_create_template_chkbox" id="auto_create_template_chkbox" checked="" />
        <div style="margin:8px 0 0; width:200px; float:left">Auto Create Templates</div>
        <br /><br />
      </td>
    </tr>

    <tr>
      <td>
      	<input type="file" name="file_upload" id="file_upload" />
        <div style="margin:8px 0 0; width:200px; float:left">Browse</div>
        <br /><br />
      </td>
    </tr>
    <tr class="seperator-row"><td>&nbsp;</td></tr>
    <tr><td>&nbsp;&nbsp;&nbsp;<input type="button" value="Import templates" /></td></tr>
    <tr class="seperator-row"><td>&nbsp;</td></tr>
</tbody>    
</table>
</form>
