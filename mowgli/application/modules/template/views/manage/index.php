<script language="javascript" src="{module:resource}/scripts/template.adminconsole.js"></script>

<script type="text/javascript">
        /*
        var server_message='{default-message}';
        $(document).ready(function(){
                wm.debug('ready')
                nit();
        });
         */
        init=function(){
                wm.debug('init')
                $('form#manage-template-1').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
                $('form#manage-template-2').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
                bindEventHandlerToSelector();
        }
</script>
<script language="javascript">
        /*
         $(document).ready(function(){
                $('form#manage-template-1').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
                $('form#manage-template-2').jqTransform({imgPath:'{admin:resource}/scripts/jqtransform/img/'});
                bindEventHandlerToSelector();
        }); */
</script>
<style>
        form.jqtransformdone label{ font-size:12px; font-weight:bold;}
</style>

<style>
        a.unpublished,a.published,a.edit,a.open,a.delete
        { display:block; width:16px; height:16px; float:left; margin: 0 5px 0 0;}
        a.unpublished,a.published{ background:url("{module:resource}/images/page_white_hide.png") 0 0 no-repeat;}
        a.unpublished{}
        a.published{}
        a.edit{background:url("{module:resource}/images/page_white_edit.png") 0 0 no-repeat;}
        a.open{background:url("{module:resource}/images/page_white_open.png") 0 0 no-repeat;}
        a.delete{background:url("{module:resource}/images/page_white_delete.png") 0 0 no-repeat;}
</style>
<form name="manage-template-1" id="manage-template-1" current_filter="{template:current_filter}" current_page="{template:current_page_id}">
        <!--
        {template:current_filter} => all/page/includes
        {template:current_page_id} => Used for pagination
        -PAGINATION: When user clicks on next page, then the AJAX function will send current_page_id,current_filter as POST data to url in href.
        -->
        <div style="margin:0 0 25px 0;">
                <table>
                        <tr><td><label>Show only this type of template</label></td></tr>
                        <tr><td>
                                        <select name="template-type-selector" id="template-type-selector" action="{template:filtered_table_uri}" style="width:200px">
                                                <!--
                                                {template:all_filter_selected} = selected/NULL. Only one of the options will be selected at a time.
                                                -->
                                                <option {template:all_filter_selected} value="all">All</option>
                                                <option {template:page_filter_selected} value="page">Page Templates</option>
                                                <option {template:inc_filter_selected} value="includes">Include Templates</option>
                                        </select>
                                </td></tr>
                </table>
        </div>
</form>
<table id="list-all-templates" name="list-all-templates" type="template_list" class="data_table table table-striped">
        <thead>
                <tr>
                        <th class="header"> <a title="Delete" href="#"></a>
                                <input type="checkbox" class="check-all">
                        </th>
                        <th class="header">Template name</th>
			<th class="header">Type</th>
			<th class="header">Module Name</th>
                        <th class="header">Usage</th>
                        <th class="header">Last modified</th>
                </tr>
        </thead>

        <tbody>

                {template:row}

                <tr page_id="{template:id}" class="templ-row">
                        <td><input type="checkbox"></td>
                        <td>{template:name}
                                <div class="templ-row-buttons" style="display: none">
                                        <a title="Edit" href="{template:edit_link}" class="edit" type="ajax_content"></a>
                                        <a title="Delete" href="{template:delete_link}" type="delete_button" class="delete"></a>
                                </div>
                        </td>
			<td>{template:type}</td>
			<td>{template:module}</td>
                        <td>{template:usage}</td>
                        <td>{template:modified}</td>
                </tr>

                {/template:row}


        </tbody>

        <tfoot>
                <tr style="border-top:1px solid #DDDDDD;">
                        <td colspan="5">
                                <div class="pagination">
                                        <ul>
                                                {pagination}
                                        </ul>
                                </div>
                        </td>
                </tr>
                <tr>
                        <td colspan="2"><span style=" display:block; float:left; font-weight:bold; margin:0 15px 0 0;">Actions</span>
                                <a title="Delete selected templates" href="#" type="delete_all_button" class="delete"></a>
                        </td>
                        <td colspan="3">
                                <form id="manage-template-2" name="manage-template-2">
                                        &nbsp;&nbsp;<input name="add" type="button" value="Add Template" href="{template:add_link}" />
                                        &nbsp;&nbsp;<input name="import" type="button" value="Import Templates" href="{template:import_link}" />
                                </form>
                        </td>
                </tr>
        </tfoot>

        <script type="text/javascript">

                $("#list-all-templates").tablesorter();

                $(".templ-row").mouseover(function() {
                        //i += 1;
                        $(this).find(".templ-row-buttons").toggle();
                }).mouseout(function(){
                        $(this).find(".templ-row-buttons").toggle();
                });


        </script>

</table>