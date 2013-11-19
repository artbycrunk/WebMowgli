<?php echo load_resources( 'js', array('admin','fancybox','tinymce','bootstrap')); ?>

<script type="text/javascript" src="{admin:resource}/scripts/custom_scripts.js"></script>
 <script type="text/javascript" src="{admin:resource}/scripts/tiny_mce/tincymce.functions.jquery.js"></script> 
<!-- <script type="text/javascript" src="{admin:resource}/scripts/custom/editor.wm.js"></script> -->

<!-- jQuery scrollTo Plugin -->
<script type="text/javascript" src="{admin:resource}/scripts/scrollto/jquery.scrollTo-1.4.2-min.js"></script>
<script type="text/javascript">
        $(function () {
                //init scrolling
                // This one is important, many browsers don't reset scroll on refreshes
                // Reset all scrollable panes to (0,0)
                $('#container-wrapper').scrollTo( 0 );
                // Reset the screen to (0,0)
                $.scrollTo( 0 );
                $.scrollTo.defaults.axis = 'xy';
        });
</script>

<!-- jQuery Table sorter Plugin -->
<script type="text/javascript" src="{admin:resource}/scripts/tablesorter/jquery.tablesorter.min.js"></script>

<!-- jQuery Table sorter Plugin -->
<script type="text/javascript" src="{admin:resource}/scripts/jqtransform/jquery.jqtransform.js"></script>

<script src="{admin:resource}/scripts/jquery-ui-1.8.17.custom.min.js"></script>
<script src="{admin:resource}/scripts/jquery.timePicker.min.js"></script>

<?php echo load_resources( 'js', 'form' ); ?>