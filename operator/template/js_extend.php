<!-- JavaScript for select all -->
<script>

// on click event
$(".coupon-check").on('click', function(e) {
	e.preventDefault();

	$(this).find(".jak-loadbtn").addClass("fa fa-spinner fa-spin");

	var cid = $(this).data("cid");
	var amount = $(this).data("amount");
	var cval = $("#coupon-"+cid).val();

	if (cval.length == 0) {
		$("#coupon-help-"+cid).addClass("text-danger").html("<?php echo $jkl['i32'];?>");
		$(this).find(".jak-loadbtn").removeClass("fa fa-spinner fa-spin");
		return true;
	} else {
		jak_coupon_validation(cid, cval, amount, false, false);
		$(this).find(".jak-loadbtn").removeClass("fa fa-spinner fa-spin");
		return true;
	}
});

// Extend Membership
$('.paynow').on('click', function(e) {

	e.preventDefault();

	$(this).find(".jak-loadbtn").addClass("fa fa-spinner fa-spin");

	var subscribe = false;
	var _this = $(this);
	var pid = $(this).data("packageid");
	var paygateid = $(this).data("paymentid");
	var amount = $(this).data("amount");
	var currency = $(this).data("currency");
	var paidhow = $(this).data("paidhow");
	var ccid = $(this).data("ccid");
	var pfp = $(this).data("payfor");
	var cval = $("#coupon-"+pid).val();

	// Populate the hidden fields
	$("#pid").val(pid);
	$("#pgid").val(paygateid);
	$("#paidhow").val(paidhow);
	$("#cval").val(cval);
	$("#check").val(pfp);
	$("#ccid").val(ccid);

	if ($('#subscribe-'+pid).is(':checked')) {
		$("#subscribe").val(1);
	} else {
		$("#subscribe").val(0);
	}

	$("#buypackage").submit();

});

// New Operators
$('.paynowop').on('click', function(e) {

	e.preventDefault();

	$(this).find(".jak-loadbtn").addClass("fa fa-spinner fa-spin");

	// This
	var _this = $(this);

	// paidhow
	var paidhow = $(this).data("paidhow");
	var pfp = $(this).data("payfor");

	// Get the amount of operators to add
	var ops = $("#newops").find(':selected').val();

	if (!ops || ops == 0) {
		$(this).find(".jak-loadbtn").removeClass("fa fa-spinner fa-spin");
		return false;
	}

	$("#paidhowop").val(paidhow);
	$("#opcount").val(ops);
	$("#checkop").val(pfp);

	$("#buyop").submit();

});

// Extend Operators
$('.paynowopextend').on('click', function(e) {

	e.preventDefault();

	$(this).find(".jak-loadbtn").addClass("fa fa-spinner fa-spin");

	// This
	var _this = $(this);

	// paidhow
	var paidhow = $(this).data("paidhow");
	var pfp = $(this).data("payfor");

	// Get the operator to update
	var opid = $("#extop").find(':selected').data("opid");

	// Get the price
	var extamount = $("#extop").find(':selected').data("price");

	// Get the months
	var extmonths = $("#extop").val();

	$("#paidhowopext").val(paidhow);
	$("#opidext").val(opid);
	$("#opamountext").val(extmonths);
	$("#checkopext").val(pfp);

	$("#buyopext").submit();

});

function jak_coupon_validation(id, value, amount, subscribe, checkout) {

	//send the post to .php
	return $.ajax({
		url: "<?php echo $_SERVER['REQUEST_URI'];?>",
		type: "POST",
		data: "check=coupon&pid=" + id + "&coupon=" + value + "&amount=" + amount + "&subscribe=" + subscribe + "&checkout=" + checkout,
		dataType: "json",
		cache: false
	}).done(function(data) {

		if (checkout) {
			if (data.status == 1) {
				return data;
			} else if (data.status == 2) {
				window.location = data.redirect;
			} else {
				$("#coupon-help-"+id).removeClass('text-success').addClass("text-danger").html(data.ctext);
				return false;
			}
		} else {
			if (data.status == 1) {
				$("#discount-"+id).val(data.newprice);
				$("#price-"+id).html(data.newprice);
				$("#coupon-help-"+id).removeClass('text-danger').addClass("text-success").html(data.ctext);
			} else {
				$("#coupon-help-"+id).removeClass('text-success').addClass("text-danger").html(data.ctext);
			}
			return true;
		}
 	});
}

ls.main_url = "<?php echo BASE_URL_ADMIN;?>";
ls.orig_main_url = "<?php echo BASE_URL_ORIG;?>";
ls.main_lang = "<?php echo JAK_LANG;?>";
</script>