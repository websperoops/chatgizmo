<!-- JavaScript for select all -->
<script>
$(document).ready(function() {

    var clipboard = new ClipboardJS('.clipboard');

	clipboard.on('success', function(e) {

	    $.notify({icon: 'fa fa-check-square', message: '<?php echo addslashes($jkl["g284"]);?>'}, {type: 'success', animate: {
			enter: 'animate__animated animate__fadeInDown',
			exit: 'animate__animated animate__fadeOutUp'
		}});

	    e.clearSelection();
	});
					
});
</script>