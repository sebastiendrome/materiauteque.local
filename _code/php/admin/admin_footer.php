
<!-- set js vars from php for file upload max size -->
<script type="text/javascript">
/* vars imported from php, needed within js functions */
var supported_types = new RegExp("^\.(jpe?g?|png|gif|s?html?|txt|mp3|m4a|oga?g?|wav|mp4|m4v|webm|ogv|pdf|docx?|msword|odt)$","i");
var max_upload_size = '<?php echo MAX_UPLOAD_SIZE; ?>';
var max_upload_bytes = <?php echo MAX_UPLOAD_BYTES; ?>;
</script>
<!-- jQuery -->
<script type="text/javascript" src="/_code/js/jquery-3.2.1.min.js" charset="utf-8"></script>
<!-- table sorter -->
<script type="text/javascript" src="/_code/js/jquery.tablesorter.min.js" charset="utf-8"></script>
<!-- common custom js -->
<script type="text/javascript" src="/_code/js/js.js?v=<?php echo $version; ?>" charset="utf-8"></script>
<!-- js for admin -->
<script type="text/javascript" src="/_code/js/admin_js.js?v=<?php echo $version; ?>" charset="utf-8"></script>

