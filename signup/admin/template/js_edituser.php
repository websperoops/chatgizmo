<script type="text/javascript">

$("#avatar").change(function() {
    var avatar = $(this).val();

    // Change avatar
    $("#avatarc").attr("src", "<?php echo BASE_URL_ORIG;?>img/avatars"+avatar);
});

</script>