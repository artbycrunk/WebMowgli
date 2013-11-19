<?php echo load_resources('js', array('tables', 'manage')); ?>
<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript">

	// post URL mapping for bulk actions
	var urls = {
		del : '{admin:root}/blog/delete_tags'
	};

	init = function(){
		wm.admin.tables.init();
		blog_manage.tags.init(); };

</script>

<div class="row-fluid">
	<a href="{admin:root}/blog/add_tag" class="btn btn-primary pull-right">Add Tag</a>
</div>

<!-- Section for manage Tags table -->

<div>
	<table id="posts_manage" name="posts_manage" class="tablesorter data_table table table-striped">
		<thead>
			<tr style="border-bottom:1px solid #DDDDDD;">
				<td colspan="5">
					<div class="batch-options">
						<select name="batch_action" id="batch_action" action="{site:root}/blog/tag_batch}">
							<option value="">Bulk Actions</option>
							<option value="del">Delete</option>
						</select>
						<button id="batch_action_submit" class="btn btn-success go-btn">Go!</button>
					</div>
					{if pagination }
					<div class="pagination">
						<ul>
							{pagination}
						</ul>
					</div>
					{/if}
				</td>
			</tr>

			<tr>
				<th class="tbl-checkbox"><input type="checkbox" id="selectall" class="check-all"></th>
				<th class="header" style="width: 500px;">Name</th>
				<th class="header">Slug</th>
				<th class="header">No of Posts</th>
			</tr>
		</thead>
		<tfoot>
			<tr style="border-top:1px solid #DDDDDD;">
				{if pagination }
				<td colspan="5">
					<div class="pagination">
						<ul>
							{pagination}
						</ul>
					</div>
				</td>
				{/if}
			</tr>
			<!--
			<tr>
				<td colspan="5"><span style=" display:block; float:left; font-weight:bold; margin:0 15px 0 0;">Actions</span>
					<a title="Delete selected resources" href="#" type="delete_all_button" class="delete"><i class="icon-trash"></i></a>
				</td>
			</tr>
			-->

		</tfoot>


		<tbody>

			{if tags }

			{tags}
			<tr row_id="{id}" class="res-row row-medium">
				<td><input type="checkbox" class="checkbox"></td>
				<!-- title -->
				<td>{name}
					<!-- row action buttons -->
					<div class="row-buttons" style="display: none">
						<a title="Edit" href="{admin:root}/blog/edit_tag/{id}" type="edit_button" class="edit"><i class="icon-pencil"></i></a>
						<a title="View" href="{url}" target="_blank" class="view"><i class="icon-file"></i></a>
						<a title="Delete" href="{admin:root}/blog/delete_tags/{id}" type="delete_button" target="_self" class="delete"><i class="icon-trash"></i></a>
					</div>
				</td>
				<td>{slug}</td>
				<td>
					{if count > 0 }
					{count}
					{else}
					No Posts
					{/if}
				</td>
			</tr>

			{/tags}

			{else}
			<tr><td></td><td>No Tags created</td><td></td><td></td></tr>
			{/if}


		</tbody>

	</table>
</div>
