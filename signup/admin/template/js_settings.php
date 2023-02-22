<script type="text/javascript">
$(document).ready(function(){
    
    $("#loader").hide();
    
    // JavaScript to disable send button and show loading.gif image
    $("#sendTM").click(function() {
    	$("#loader").show();
    	$('#sendTM').attr("disabled", "disabled");
    	$('.jak_form').submit();
    });
                
});
</script>