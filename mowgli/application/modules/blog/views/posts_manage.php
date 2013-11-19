<?php echo load_resources('js', array('tables', 'manage')); ?>
<script type="text/javascript" src="{module:resource}/scripts/blog.functions.js"></script>

<script type="text/javascript" >

	var status = {
		published : 'published',
		draft : 'draft'
	};

	// post URL mapping for bulk actions
	var urls = {
		published : '{admin:root}/blog/set_posts_status',
		draft : '{admin:root}/blog/set_posts_status',
		del : '{admin:root}/blog/delete_posts'
	};

	init = function(){

		wm.admin.tables.init();
		blog_manage.posts.init();
	};
</script>

<style type="text/css">

	a.categ-buttons, a.tag-buttons { color: white; }
</style>

<div class="row-fluid">
	<a href="{admin:root}/blog/add_post" class="btn btn-primary pull-right">Add Post</a>
</div>

<table id="posts_manage" name="posts_manage" class="tablesorter data_table table table-striped">
	<thead>
		<tr style="border-bottom:1px solid #DDDDDD;">
			<td colspan="7">
				<div class="batch-options">
					<select name="batch_action" id="batch_action" action="{template:filtered_table_uri}">
						<option value="">Bulk Actions</option>
						<option value="published">Publish</option>
						<option value="draft">Unpublish</option>
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
			<th class="header" style="width: 300px;">Title</th>
			<th class="header">Author</th>
			<th class="header">Categories</th>
			<th class="header">Tags</th>
			<th class="header">Last Modified</th>
			<th class="header">Status</th>
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

		{if posts }

		{posts}

		<tr row_id="{id}" class="tbl-row row-medium">
			<td><input type="checkbox" class="checkbox"></td>
			<!-- title -->
			<td>{title}
				<div class="row-buttons" style="display: none">
					<a title="View" href="{url}" target="_blank" class=""><i class="icon-file"></i></a>
					<a title="Edit" href="{admin:root}/blog/edit_post/{id}" type="edit_button" class=""><i class="icon-pencil"></i></a>

					{if status = published }
					<a id="status_unpublished__{id}" title="Unpublish" href="{admin:root}/blog/set_posts_status/{id}/{status:draft}" type="status_toggle_button" target="_self" class="toggle_status"><i class="icon-eye-close"></i></a>
					<a id="status_published__{id}" style="display:none" title="Publish" href="{admin:root}/blog/set_posts_status/{id}/{status:published}" type="status_toggle_button" target="_self" class="toggle_status"><i class="icon-eye-open"></i></a>
					{else}
					<a id="status_published__{id}" title="Publish" href="{admin:root}/blog/set_posts_status/{id}/{status:published}" type="status_toggle_button" target="_self" class="toggle_status"><i class="icon-eye-open"></i></a>
					<a id="status_unpublished__{id}" style="display:none" title="Unpublish" href="{admin:root}/blog/set_posts_status/{id}/{status:draft}" type="status_toggle_button" target="_self" class="toggle_status"><i class="icon-eye-close"></i></a>
					{/if}

					<a title="Delete" href="{admin:root}/blog/delete_posts/{id}" type="delete_button" target="_self" class=""><i class="icon-trash"></i></a>
				</div>
			</td>
			<td>{author:name}</td>
			<td>
				{if categs }
				{categs}
				<span class="label"><a class="categ-buttons" href="{admin:root}/blog/edit_categ/{categ:id}" title="Edit category">{categ:name}</a></span>
				{/categs}
				{else}
				No categories
				{/if}
			</td>
			<td>
				{if tags }
				{tags}
				<span class="label"><a class="tag-buttons" href="{admin:root}/blog/edit_tag/{tag:id}" title="Edit tag">{tag:name}</a></span>
				{/tags}
				{else}
				<!--No Tags-->
				{/if}
			</td>
			<td>{updated}</td>
			<td id="status__{id}">{status}</td>
		</tr>

		{/posts}

		{else}
		<tr><td></td><td>No Posts created</td><td></td><td></td><td></td><td></td><td></td></tr>
		{/if}

	</tbody>


<!--	<script type="text/javascript">

		$('.dropdown-toggle').dropdown();

	</script>-->

</table>