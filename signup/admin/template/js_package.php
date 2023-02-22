<script type="text/javascript">
$(document).ready(function() {
 
 // Set mandatory to 0 if not shown
    $('input[type=radio][name=ishd3]').change(function() {
        if (this.value == '1') {
            $('input[type=radio][name=islc3]').val(["0"]);
        }
    });
    $('input[type=radio][name=islc3]').change(function() {
        if (this.value == '1') {
            $('input[type=radio][name=ishd3]').val(["0"]);
        }
    });
          
});
</script>