<style type="text/css">

	#page-meta-iframe {
		width: 276px;
		height: 295px;
		border: 0px;
	}

	#meta-container {

	}

</style>
<div id="masked"></div>
<div class="border-container" style="display: none;">
	<div id="border-left"></div><div id="border-right"></div><div id="border-top"></div><div id="border-bottom"></div>
	<div id="inplace-edit-btn"><i class="icon-pencil icon-white"></i> Edit</div>
</div>
<div class="navbar navbar-fixed-top navbar-inverse">
	<div class="navbar-inner">
		<li class="clpse-arrow active"><a href="#" id="clpse-arrow" position="open" title="Collapse"><span class="down_arrow" style="display:none">&#9660;</span><span class="up_arrow">&#9650;</span></a></li>
		<div class="container">
			<a class="brand" href="{sidebar:logo_url}" target="_blank">WebMowgli</a>
			<ul class="nav">
				<li class="active"><a href="{site:root}" title="Home"><span>Home</span></a> </li>
				<li><a href="{site:root}/admin/dashboard">Dashboard</a></li>
				<li class="dropdown">
					<a href="#"class="dropdown-toggle" data-toggle="dropdown">
						Page meta
						<b class="caret"></b>
					</a>
					<ul class="dropdown-menu">
						<div id="meta-container" class="navbar-form">
							<iframe id="page-meta-iframe" src="{site:root}/admin/page/edit_meta/{page:id}/?no_wrap=1"></iframe>
						</div>

						<!-- <form class="navbar-form pull-left">

							<a id="page_meta_iframe" class="item_title" href="#" title="Page meta" controller="{site:root}/admin/page/edit_meta/{page:id}/?no_wrap=1">Hello</a>

							<label for="page_title">Page Title</label>
							<input class="form_text_fields" type="text" value="{page:title}" id="page_title" name="page_title" required="required" />
							<label for="page_keywords">Page Keywords <i>seperate with comma (,)</i></label>
							<textarea class="form_textarea_field" id="page_keywords" name="page_keywords">{page:keywords}</textarea>
							<label for="post_slug">Slug</label>
							<input class="form_text_fields" type="text" value="{post_slug}" id="post_slug" name="post_slug">
							<label for="post_body">Body</label>
							<textarea class="form_textarea_field tinymce_simple" name="post_body" id="post_body" cols="80" rows="15">{post_body}</textarea>
							<input type="submit" id="submit-btn" class="btn btn-large" title="Save changes" value="Save" />

						</form>-->
					</ul>
				</li>
			</ul>
			<ul class="nav pull-right">
				<li class="dropdown pull-right">
					<a href="#"class="dropdown-toggle" data-toggle="dropdown">
						{admin:user}
						<b class="caret"></b>
					</a>
					<ul class="dropdown-menu">
						<li><a href="{site:root}/user/change_password">Change password</a></li>
						<li><a href="{site:root}/user/logout">Logout</a></li>
					</ul>
				</li>
			</ul>
		</div>
	</div>
</div>
