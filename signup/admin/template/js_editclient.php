<script src="<?php echo BASE_URL;?>js/daterange.js"></script>
<script>

$(function() {
	$('#paidtill, #trial').daterangepicker({
        singleDatePicker: true,
        showDropdowns: true,
        autoUpdateInput: false,
        minDate: moment().format("DD.MM.YYYY"),
        locale: {
      		format: "DD.MM.YYYY"
    	}
    });

   	$('#paidtill, #trial').on('apply.daterangepicker', function(ev, picker) {
      $(this).val(picker.startDate.format('DD.MM.YYYY'));
  	});

  	$('#paidtill, #trial').on('cancel.daterangepicker', function(ev, picker) {
      $(this).val("");
  	});
});

</script>