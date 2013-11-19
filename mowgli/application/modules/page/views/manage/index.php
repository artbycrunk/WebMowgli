<script language="javascript" src="{module:resource}/scripts/page.adminconsole.js"></script>

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

<table id="list-all-pages" name="list-all-pages" type="page_list" class="data_table table table-striped">
        <thead>
                <tr>
                        <th class="header">
                                <input type="checkbox" class="check-all">
                        </th>
                        <th class="header">Page name</th>
                        <th class="header">Template used</th>
                        <th class="header">Last modified</th>
                        <!-- <th class="header">Actions</th> -->
                </tr>
        </thead>
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
                        <td colspan="6"><span style=" display:block; float:left; font-weight:bold; margin:0 15px 0 0;">Actions</span>
                                <a title="Delete selected pages" href="#" type="delete_all_button" class="delete"></a>
                                <a title="Publish all selected pages" href="#" type="publish_all_button" class="published"></a>
                                <a title="Unpublish all selected pages" href="#" type="unpublish_all_button" class="unpublished"></a>
                        </td>
                </tr>
        </tfoot>

        <tbody>

                {page:row}

                <tr page_id="{page:id}" class="{page:published_status} page-row res-row">
                        <!--
                            {page:published_status} = published/unpublished.
                            Published pages have  a different background.
                        -->
                        <td><input type="checkbox"></td>
                        <td><a title="Edit Page" href="{page:edit_link}">{page:name}</a>

                                <div class="page-row-buttons" style="display: none">
                                        <a title="{page:published_title}" href="{page:publish_toggle}" type="publish_toggle" class="{page:published_status}" type="ajax_content"></a>
                                        <a title="Open" href="{page:uri}" class="open" target="_blank"></a>
                                        <a title="Edit" href="{page:edit_link}" class="edit" type="ajax_content"></a>
                                        <a title="Delete" href="{page:delete_link}" type="delete_button ajax_content" class="delete"></a>
                                </div>
                        </td>
                        <td><a title="Edit template" href="{template:edit_link}">{template:name}</a></td>
                        <td>{page:modified}</td>
                        <td>

                                <!--
                                    {page:published_title} = Publish this page/UnPublish this page.
                                                                    {page:published_status}=published/unpublished
                                -->
                                <!--
                                <a title="{page:published_title}" href="{page:publish_toggle}" type="publish_toggle" class="{page:published_status}" type="ajax_content"></a>
                                <a title="Open" href="{page:uri}" class="open" target="_blank"></a>
                                <a title="Edit" href="{page:edit_link}" class="edit" type="ajax_content"></a>
                                <a title="Delete" href="{page:delete_link}" type="delete_button ajax_content" class="delete"></a>
                                -->
                        </td>
                </tr>

                {/page:row}

        </tbody>

        <script type="text/javascript">

                $("#list-all-pages").tablesorter();

                $(".page-row").mouseover(function() {
                        //i += 1;
                        $(this).find(".page-row-buttons").toggle();
                }).mouseout(function(){
                        $(this).find(".page-row-buttons").toggle();
                });


        </script>

</table>

