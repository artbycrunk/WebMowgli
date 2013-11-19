<style>
	#album_list {
		list-style: none;
	}
	.album_item {
		background: none repeat scroll 0 0 #FFFFFF;
		border: 1px solid #D6D6D6;
		border-radius: 13px 13px 13px 13px;
		margin: 0 0 15px;
		padding: 8px;
	}

	.draghandle {
		background: -moz-linear-gradient(center top , #FFFFFF 0%, #F2F2E8 100%) repeat scroll 0 0 transparent;
		border: 1px solid #DFDFDF;
		border-radius: 3px 3px 3px 3px;
		box-shadow: 0 2px 2px #D6D6D6;
		cursor: move;
		display: block;
		font-size: 16px;
		height: 25px;
		margin: 0 0 10px;
		padding: 12px 0 0 10px;
		position: relative;
	}

	.draghandle .drag_tooltip {
		background: none repeat scroll 0 0 white;
		border: 1px solid #437DF7;
		color: blue;
		display: none;
		font-size: 10px;
		padding: 3px;
		position: absolute;
		right: 20px;
		top: -18px;
		width: 75px;
	}

	.draghandle:hover .drag_tooltip{ display:block}

	.vid_title_label{
		font-size: 13px;
		color:#828080;
	}
	.vid_title{
		color: #5C5C5C;
		margin: 0 0 0 6px;
		text-shadow: 0 1px 2px #FCFCFC;
	}

	.menu-button-item-blue{
		border-radius: 14px 14px 14px 14px;
		min-height: 13px;
		padding: 6px 6px 4px;
		width: 43px;
		float: right;
		margin:-5px 5px 0 0;
	}


	.left_col {
		display: block;
		float: left;
		height: auto;
		margin: 15px 0 0;
		width: 150px;
	}

	.left_col img{
		border: 3px solid #FFFFFF;
		box-shadow: 0 3px 3px 2px #CFCFCF;
		color: red;
		margin: 10px 0 0 10px;
	}
	.right_col{
		float: left;
		width: 80%;
	}

	.ref_id,.vid_url{
		color: #5F62FF;
	}
	.ref_id{
		font-weight: bold;
	}
	span.vid_label_{
		display: none;
		margin: 10px 0 3px;
	}
	.vid_script ,
	.vid_desc {
		max-height: 300px;
		border: 1px solid #EDEDED;
		color: #6D6D6D;
		font-size: 13px;
		overflow: auto;
		padding: 5px;
		width: 98%;
	}

	/*Over-writing*/
	.vid_script{ display:none}
	.vid_desc {height: 90px;}

	.vid_url_path_container {
		border: 1px solid #A4CDFF;
		color: #2D71C4;
		display: block;
		float: right;
		padding: 4px;
		text-decoration: none;
		margin:0 0 5px 0;
	}

	.vid_container{ display:none}

	.expand_button {
		float: right;
		font-size: 25px;
		margin: -7px 10px 0 15px;
	}

	.expand_button a {
		background-color: #4D90FE;
		background-image: -moz-linear-gradient(center top , #81B1FC, #4288F8);
		border: 1px solid #AAAAAA;
		border-radius: 5px 5px 5px 5px;
		box-shadow: 0 1px 0 rgba(255, 255, 255, 0.5) inset;
		color: #FAFAFA;
		cursor: pointer;
		display: inline-block;
		font-family: "Helvetica Neue","Helvetica",Arial,Verdana,sans-serif;
		font-size: 18px;
		font-weight: bold;
		line-height: 1;
		padding: 2px 6px;
		position: relative;
		text-align: center;
		text-decoration: none;
		text-shadow: 0 -1px 1px rgba(0, 0, 0, 0.28);
		width: 10px;
	}

	.expand_button a:hover {
		background-color: #375AE8;
		background-image: -moz-linear-gradient(center top , #4D90FE, #357AE8);
		border: 1px solid #3079ED;
		color:#FFF;
	}

	ul#album_list li.ui-sortable-helper{
		-moz-box-shadow: -3px 2px 6px #888888;
		box-shadow: -3px 2px 6px #888888;
	}

	.visible_1{opacity:1}
	.visible_0{ opacity:0.75}

	.vid_song_cnt{ margin:0 0 0 5px; font-size:11px;}
</style>
<script>

	var DURATION_slide=800;
	init=function(){

		wm.debug('alb manage: init called!');

		// @Lloyd : handle Delete button click
		$('#album_list a.categ-delete-button').click(function(e){

			e.preventDefault();

			var songObj = $(this);
			var itemId = songObj.attr('callback_id');

			//wm.debug('Delete button clicked : id = ' + itemId);

			confirm_action({

				message : "Permanently delete album? \nThis will also delete all the songs under this album.",
				success : function(){

					wm.admin.utility.ajax.post({

						url : songObj.attr('href'),
						on_success : function(){

							$('li[alb_id='+itemId+']').hide(1500).remove();

						}

					});
				}
			});
		});


		makeVidListSortable();
		var v_left=($('.draghandle:first').width()-$('.draghandle:first .drag_tooltip').width())/2;
		$('.draghandle .drag_tooltip').css({left:v_left});

		$('div.expand_button a[type=js_container_toggler]').bind('click',function(event){
			event.stopPropagation();
			if($(this).html()=='+'){$(this).html('-').attr('title','Hide description');$(this).parents('.album_item').children('.vid_container').slideDown(DURATION_slide);}
			else{$(this).html('+').attr('title','Show description');$(this).parents('.album_item').children('.vid_container').slideUp(DURATION_slide);}
			return false;
		})

	}
	var lastDroppedDiscography_cat,sort_start_alb_id;
	function makeVidListSortable(){

		$("ul#album_list").sortable({
			handle: 'div.draghandle',
			revert: true,
			start: function(event, ui){
				wm.debug('sortable> start');
				//ev=ui;

				sort_start_alb_id = $(ui.helper).attr('alb_id');
				lastDroppedDiscography_cat =sort_start_alb_id;
				depthBeforeDrag=getDepthDetails('album',sort_start_alb_id);
				wm.debug('depthBeforeDrag.depth:'+depthBeforeDrag.depth+',alb_id:'+sort_start_alb_id)

				//Make the z-index higher than properties page
				wm.debug('start moving alb_id'+sort_start_alb_id);
				wm.debug('sortable> start [END]');
			},
			stop: function(event, ui) {
				wm.debug('sortable> stop[done sorting]');
				var new_id,change=true;
				var depthAfterDrag=getDepthDetails('album',sort_start_alb_id);
				if(depthAfterDrag.found) {
					if( depthBeforeDrag.depth < depthAfterDrag.depth){
						//downward
						new_id=depthAfterDrag.prevId;
					} else if( depthBeforeDrag.depth > depthAfterDrag.depth){
						//upward
						new_id=depthAfterDrag.nextId;
					} else if(depthBeforeDrag.depth==depthAfterDrag.depth){
						change=false;
					}
					wm.debug('vid depthBeforeDrag.depth:'+depthBeforeDrag.depth+',depthAfterDrag.depth:'+depthAfterDrag.depth+',sort_start_alb_id:'+sort_start_alb_id);
					wm.debug('vid new_id:'+new_id+',old id:'+lastDroppedDiscography_cat+',change:'+change+',depthAfterDrag.found:'+depthAfterDrag.found);
					if(change){
						wm.debug('postSortOrder>');
						postSortOrder(lastDroppedDiscography_cat,new_id,wm.get_url(Discography_cat_post_uri['Discography_cat']['sort_order']));
					}
				}
			} ,
			change: function(event, ui) { wm.debug('sortable> change')},
			update:function(event, ui) { wm.debug('update sorting')}
		});
	}

	var Discography_cat_post_uri = {
		Discography_cat: {
			sort_order:'admin/discography/reorder_categs/'
		}
	}

</script>

<ul id="album_list">
	{discography:categs}
	<li class="album_item visible_{discography:categ:is_visible}" alb_id="{discography:categ:id}" order="{discography:categ:order}">
		<div class="draghandle">
			<span class="drag_tooltip">Drag to re-order</span>
			<span class="vid_title_label">Title</span><span class="vid_title">{discography:categ:name}</span>
			<span class="vid_song_cnt">({discography:categ:count} songs)</span>
			<div class="expand_button"><a type="js_container_toggler" href="#">+</a></div>
			<a class="menu-button-item-blue" href="{discography:categ:edit_link}" >Edit</a>
			<a type="inplace" target="_self" callback_id="{discography:categ:id}" class="categ-delete-button menu-button-item-blue" href="{discography:categ:delete_link}" >Delete</a><br/><br/>
		</div>
		<div class="vid_container">
			<div class="left_col">
				<img alt="No Image" src="{discography:categ:image_url}" />
			</div>
			<div class="right_col">
				<span class="vid_label_">Description</span>
				<div class="vid_desc">{discography:categ:description}</div>
				<span class="vid_label_">Visible : {discography:categ:is_visible}</span>
				<span class="vid_label_">Created : {discography:categ:created}</span>
				<!--                Slug : {discography:categ:slug}<br/>
				-->
				<br /><br />
				<a href="{discography:categ:buy_url}" target="_blank">Buy Url</a>&nbsp;&nbsp;&nbsp;
				<a href="{discography:categ:download_url}" target="_blank">Download Url</a>

			</div>
			<br class="clear" />
		</div>
	</li>
	{/discography:categs}
</ul>
