<?php echo load_resources('js', 'tables'); ?>
<script language="javascript" src="{module:resource}/scripts/resource.adminconsole.js"></script>

<script type='text/javascript'>

	init = function(){

		wm.admin.tables.init();

		// initialize resources
		//Bind a click event handler to all the delete buttons
		$("table[type=resource_list] tbody tr td a[type=delete_button]").bind('click', function() {// When a Delete action button is clicked...
			deleteResources($(this));
			return false;//Do not continue with default browser action. That is goto #
		});

		//Bind a click event handler to the footer delete button
		$("table[type=resource_list] tfoot tr td a[type=delete_all_button]").bind('click', function() {// When a Delete action button is clicked...
			deleteResources(null);
			return false;//Do not continue with default browser action. That is goto #
		});

	};

</script>

<table id="list-all-css-resources" name="list-all-css-resources" type="resource_list" resource_type="{resource:type}" class="tablesorter data_table table table-striped">
        <!--
        {resource:type} => css/js/images/other
        -->
        <thead>
		<tr>

		</tr>
		<tr style="border-bottom:1px solid #DDDDDD;">
			<td colspan="5">
				<div class="batch-options">
					<!-- <div class="btn-group">
						<a class="btn btn-success dropdown-toggle" data-toggle="dropdown" href="#">
						Bulk Actions
						<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li><a href="#">Edit</a></li>
							<li><a href="#">Delete</a></li>
						</ul>
					</div> -->
					<select name="template-type-selector" id="template-type-selector" action="{template:filtered_table_uri}">

						<!--
						{template:all_filter_selected} = selected/NULL. Only one of the options will be selected at a time.
						-->
						<option value="all">Bulk Actions</option>
						<option value="includes">Edit</option>
						<option value="includes">Delete</option>
					</select>
					<button class="btn btn-success go-btn">Go!</button>
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
			<th>
				<input type="checkbox" id="selectall" class="check-all">
			</th>
			<?php if ($displayImages) { ?> <th class="header">Preview</th> <?php } ?>
			<th class="header">Name</th>
			<?php if ($displayFileType) { ?> <th class="header">filetype?</th> <?php } ?>

			<!-- <th class="header">Path</th> -->
			<th class="header">Last modified</th>
			<!-- <th class="header">Actions</th> -->
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
                <tr>
                        <td colspan="5"><span style=" display:block; float:left; font-weight:bold; margin:0 15px 0 0;">Actions</span>
                                <a title="Delete selected resources" href="#" type="delete_all_button" class="delete"><i class="icon-trash"></i></a>
                        </td>
                </tr>

        </tfoot>


        <tbody>

                {resource:row}

                <tr resource_id="{resource:id}" class="res-row row-medium">
                        <td><input type="checkbox" class="checkbox"></td>
			<?php if ($displayImages) { ?>
				<td>
					<a type="normal" class="img_link" title="{resource:name}" target="_blank" href="{resource:img:src}">
						<img src="{resource:img:src}" class="resize" title="" alt="" />
					</a>
				</td>
			<?php } ?>
                        <td>{resource:name}
                                <div class="row-buttons" style="display: none">
                                        <a title="Replace" href="{resource:replace_link}" class="replace"><i class="icon-pencil"></i></a>
                                        <a title="Delete" href="{resource:delete_link}" type="delete_button" class="delete"><i class="icon-trash"></i></a> </div>
                        </td>
			<?php if ($displayFileType) { ?>
				<td>{resource:filetype}</td>
			<?php } ?>
		<!-- <td>{resource:path}</td> -->
                        <td>{resource:modified}</td>
                </tr>

                {/resource:row}



        </tbody>

        <script type="text/javascript">

                $('.img_link').fancybox({
                        'titlePosition'  : 'over',
                        'transitionIn'	: 'elastic',
                        'transitionOut'	: 'elastic',
                        'easingIn'      : 'easeOutBack',
                        'easingOut'     : 'easeInBack'
                });

		$('.dropdown-toggle').dropdown()

        </script>

</table>