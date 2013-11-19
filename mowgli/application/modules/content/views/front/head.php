<!-- START: Front end content editor resources -->
<script type='text/javascript'> var admin_resource_url = '{admin:resource}'; </script>

<link rel='stylesheet' href='{admin:resource}/css/inplace_editor.css' type='text/css' media='screen' />

<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
<?php echo load_resources( 'js', 'jquery', true ); ?>

<!-- OLD CODE -- <script type='text/javascript' src='{admin:resource}/scripts/tiny_mce/jquery.tinymce.js'></script>-->
<!--<script type='text/javascript' src='{admin:resource}/scripts/tiny_mce/tiny_mce.js'></script>-->
<?php echo load_resources( 'js', 'tinymce', true ); ?>
<script type='text/javascript' src='{module:resource}/scripts/content.adminconsole.js'></script>
<!-- END: Front end content editor resources -->
