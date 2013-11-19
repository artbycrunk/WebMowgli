<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<title>WM Gallery</title>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">

		<?php echo load_resources('css', array('bootstrap', 'admin', 'fancybox', 'jquery-ui')); ?>
		<link rel='stylesheet' href='{module:resource}/css/gallery.main.css' type='text/css' media='screen' />
	</head>

	<body>
		<div id="gallery-container">
			<div class="container-fluid">
				<div id="category-container">
					<div id="category-buttons" class="row-fluid">
						<div class="span12">
							<div class="btn-group">
								<a id="button-categ-edit" class="btn" href="#" title="Edit"><i class="icon-edit"></i></a>
								<a id="button-categ-visible" class="btn" href="#" title="Show/Hide"><i class="icon-eye-open"></i></a>
								<a id="button-categ-trash" class="btn" href="#" title="Delete"><i class="icon-trash"></i></a>
								<a id="button-categ-new" class="btn" href="#" title="New Category"><i class="icon-plus"></i></a>
								<a id="button-image-move" class="btn btn-success" href="#" title="Upload Images" data-toggle="button">Move</a>
							</div>
						</div>
					</div>
					<div id="category-list-container" class="row-fluid">
						<div class="span12">

							<ul id="categ-list" class="thumbnails">
								{if categs}
								{categs}
								<li id="categ__{id}" class="categ-holders highlight-visible-{visible}" data-id="{id}" data-visible="{visible}">
									<div class="thumbnail">
										<img data-src="{uri}" alt="{name}" src="{uri_thumb}">
										<h3>{name}</h3>
										<!--
											Other tags
											{id}, {cover_id}, {name}, {name_url}, {count}, {description}, {alt},
											{uri}, {uri_thumb}, {order}, {created}, {modified}, {visible}
										-->
									</div>
								</li>

								{/categs}
								{else}
								<li class="span2">
									<div class="thumbnail">
										<img data-src="{module:resource}/images/dummy__260x180.png" alt="" src="{module:resource}/images/dummy__260x180.png">
									</div>
								</li>
								{/if}
							</ul>

						</div>
					</div>

					<div id="category-new-categ" class="row-fluid">
						<div class="span12">

						</div>
					</div>


				</div> <!-- END categories container -->

				<div id="image-container" >
					<div id="image-buttons" class="row-fluid">
						<div class="span12">
							<div class="btn-group">
								<a id="button-image-edit" class="btn" href="#" title="Edit"><i class="icon-edit"></i></a>
								<a id="button-image-visible" class="btn" href="#" title="Show/Hide"><i class="icon-eye-open"></i></a>
								<a id="button-image-trash" class="btn" href="#" title="Delete"><i class="icon-trash"></i></a>
								<a id="button-image-new" class="btn" href="#" title="Upload Images"><i class="icon-plus"></i></a>
							</div>
						</div>
					</div>
					<div id="image-list-container" class="row-fluid">
						<div class="span12">
							{if images}
							<ul id="image-list" class="thumbnails">

								{images}
								<!-- @IMP: to edit this template, also edit the dynamic template in wm.gallery.get_image_html(); -->
								<li id="image__{id}" class="image-holders highlight-visible-{visible}"  data-id="{id}" data-parentId="{parent_id}" data-visible="{visible}" data-order='{order}'>
									<div class="thumbnail">
										<!-- @Temporary FancyBox Disabled
											<a type="normal" title="{name}" target="_blank" href="#">
											<img data-src="{uri}" alt="{name}" src="{uri_thumb}">
										</a>
										-->
										<img data-src="{uri}" alt="{name}" src="{uri_thumb}">

									</div>
									<!--
											Other tags
											{id}, {parent_id}, {category}, {type}, {name}, {name_url}, {description}, {alt},
											{uri}, {uri_thumb}, {order}, {created}, {modified}, {visible}
									-->
								</li>

								{/images}
							</ul>
							<div id="image-empty" class="well" style="display: none;">Oops !! No images Added yet. Please add some images here</div>

							{else}
							<ul id="image-list" class="thumbnails"></ul>
							<div id="image-empty" class="well">Oops !! No images Added yet. Please add some images here</div>
							{/if}
						</div>
					</div>

				</div><!-- END images container -->
			</div>
		</div>

		<!-- This container contains Hidden Helping elements required, but Initially Invisible -->
		<div id="hidden-elements" style="display:none;">

			<!-- Div for Confirmation Dialogue box -->
			<div id="dialog-confirm">
				<p>
					<span class="ui-icon ui-icon-alert" style="float:left; margin:0 7px 20px 0;"></span>
					<span id="dialogue-confirm-message"><!--Message will show here--></span>
				</p>
			</div>
		</div>



		<?php echo load_resources('js', array('jquery', 'fancybox', 'jquery-ui', 'bootstrap', 'admin')); ?>

		<script type="text/javascript">

			//			$("#image-list-container").on("focusin", function(){
			//				$('.img_link').fancybox({
			//					'titlePosition'  : 'over',
			//					'transitionIn'	: 'elastic',
			//					'transitionOut'	: 'elastic',
			//					'easingIn'      : 'easeOutBack',
			//					'easingOut'     : 'easeInBack'
			//				});
			//			});

			var site_url = '<?php echo site_url(); ?>';
			$(document).ready(function(){

				wm.gallery.init('.categ_holders', '.image_holders');

			});



		</script>
		<script type="text/javascript" src="{module:resource}/scripts/gallery.functions.js"></script>

	</body>
</html>