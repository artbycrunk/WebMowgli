<!-- START: Admin front resources -->

<script type="text/javascript">var inAdminPanel = false;</script>
<?php //echo load_resources( 'css', array('bootstrap') ); ?>
<link rel='stylesheet' href='{admin:resource}/css/front.navbar.css' type='text/css' media='screen' />
<link rel='stylesheet' href='{admin:resource}/css/front.adminconsole.css' type='text/css' media='screen' />

<!-- Grab Google CDN's jQuery, with a protocol relative URL; fall back to local if offline -->
<?php echo load_resources('js', array('jquery', 'admin', 'site')); ?>
<script type="text/javascript" src="{admin:resource}/scripts/custom_scripts.js"></script>

<?php echo load_resources('js', 'form'); ?>
<?php echo load_resources('js', 'bootstrap'); ?>

<script type="text/javascript">



</script>

<!-- END: Admin front resources -->