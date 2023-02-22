<script type="text/javascript" src="<?php echo BASE_URL;?>js/daterange.js"></script>
<script type="text/javascript">

$(function() {
	$('#validtill').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minDate: moment().format("DD.MM.YYYY"),
        locale: {
      		format: "DD.MM.YYYY"
    	}
    });

   	$('#validtill').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD.MM.YYYY'));
  	});

  	$('#validtill').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val("");
  	});
});

</script>