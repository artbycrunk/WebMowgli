<?php echo load_resources('js', array('tables', 'manage')); ?>
<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript" >

	// visibility of category ( Visible / Hidden ), should be same case as loaded from db
	var visibility = {
		visible : 'Visible',
		hidden : 'Hidden'
	};

	// comments of category ( Enabled / Disabled ), should be same case as loaded from db
	var comments = {
		enabled : 'Enabled',
		disabled : 'Disabled'
	};

	// post URL mapping for bulk actions
	var urls = {
		visibility_show : '{admin:root}/blog/toggle_categs_visibility',
		visibility_hide : '{admin:root}/blog/toggle_categs_visibility',
		comments_enable : '{admin:root}/blog/toggle_categs_comments',
		comments_disable : '{admin:root}/blog/toggle_categs_comments',
		del : '{admin:root}/blog/delete_categs'
	};

	init = function(){
		wm.admin.tables.init();
		blog_manage.categs.init();
	};



</script>

<div class="row-fluid">
	<a href="{admin:root}/blog/add_categ" class="btn btn-primary pull-right">Add Category</a>
</div>

<!-- Section for manage categories table -->

<div>
	<table id="posts_manage" name="posts_manage" class="tablesorter data_table table table-striped">
		<thead>
			<tr style="border-bottom:1px solid #DDDDDD;">
				<td colspan="7">
					<div class="batch-options">
						<select name="batch_action" id="batch_action" action="{site:root}/blog/categ_batch}">
							<option value="">Bulk Actions</option>
							<option value="visibility_show">Make Visible</option>
							<option value="visibility_hide">Make Invisible</option>
							<option value="comments_enable">Enable Comments</option>
							<option value="comments_disable">Disable Comments</option>
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
				<th class="header" style="width: 300px;">Name</th>
				<th class="header">No of Posts</th>
				<th class="header">Last Modified</th>
				<th class="header">Comments</th>
				<th class="header">Visibility</th>
			</tr>
		</thead>
		<tfoot>
			<tr style="border-top:1px solid #DDDDDD;">
				{if pagination }
				<td colspan="7">
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
			{if categories }

			{categories}

			<tr row_id="{id}" class="res-row row-medium">
				<td><input type="checkbox" class="checkbox"></td>
				<!-- title -->
				<td>{name}
					<!-- row action buttons -->
					<div class="row-buttons" style="display: none">
						<a title="View" href="{url}" target="_blank" class="view"><i class="icon-file"></i></a>
						<a title="Edit" href="{admin:root}/blog/edit_categ/{id}" type="edit_button" class="edit"><i class="icon-pencil"></i></a>

						{if is_visible = 1 }
						<a id="visibility_hidden__{id}" title="Make Invisible" href="{admin:root}/blog/toggle_categs_visibility/{id}" type="visibility_toggle_button" target="_self" class="toggle_visibility"><i class="icon-eye-close"></i></a>
						<a style="display:none;" id="visibility_visible__{id}" title="Make Visible" href="{admin:root}/blog/toggle_categs_visibility/{id}" type="visibility_toggle_button" target="_self" class="toggle_visibility"><i class="icon-eye-open"></i></a>
						{else}
						<a id="visibility_visible__{id}" title="Make Visible" href="{admin:root}/blog/toggle_categs_visibility/{id}" type="visibility_toggle_button" target="_self" class="toggle_visibility"><i class="icon-eye-open"></i></a>
						<a style="display:none;" id="visibility_hidden__{id}" title="Make Invisible" href="{admin:root}/blog/toggle_categs_visibility/{id}" type="visibility_toggle_button" target="_self" class="toggle_visibility"><i class="icon-eye-close"></i></a>
						{/if}

						{if is_comments = 1 }
						<a id="comments_disable__{id}" title="Disable Comments" href="{admin:root}/blog/toggle_categs_comments/{id}" type="comments_toggle_button" target="_self" class="toggle_comments"><i class="icon-ban-circle"></i></a>
						<a style="display:none;" id="comments_enable__{id}" title="Enable Comments" href="{admin:root}/blog/toggle_categs_comments/{id}" type="comments_toggle_button" target="_self" class="toggle_comments"><i class="icon-ok-circle"></i></a>
						{else}
						<a id="comments_enable__{id}" title="Enable Comments" href="{admin:root}/blog/toggle_categs_comments/{id}" type="comments_toggle_button" target="_self" class="toggle_comments"><i class="icon-ok-circle"></i></a>
						<a style="display:none;" id="comments_disable__{id}" title="Disable Comments" href="{admin:root}/blog/toggle_categs_comments/{id}" type="comments_toggle_button" target="_self" class="toggle_comments"><i class="icon-ban-circle"></i></a>
						{/if}

						{if is_special != 1 }
						<a title="Delete" href="{admin:root}/blog/delete_categs/{id}" type="delete_button" target="_self" class="delete"><i class="icon-trash"></i></a>
						{/if}
					</div>
				</td>
				<td>
					{if count > 0 }
					{count}
					{else}
					No Posts
					{/if}
				</td>
				<td>{updated}</td>
				<td id="comments__{id}">
					{if is_comments = 1 }
					Enabled
					{else}
					Disabled
					{/if}
				</td>
				<td id="visible__{id}">
					{if is_visible = 1 }
					Visible
					{else}
					Hidden
					{/if}
				</td>
			</tr>

			{/categories}

			{else}
			<tr><td></td><td>No Categories created</td><td></td><td></td><td></td><td></td></tr>
			{/if}

		</tbody>

	</table>
</div>
