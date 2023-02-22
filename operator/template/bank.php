<div class="modal-header">
	<h4 class="modal-title"><?php echo $pg["title"];?></h4>
    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
</div>
<div class="modal-body">
<div class="padded-box">

<div id="contact-container">

	<div class="row">
		<div class="col-md-6">
			<h4><?php echo $jkl['g355'];?></h4>
			<?php echo nl2br($pg["bank_info"]);?>
		</div>
		<div class="col-md-6">
			<h4><?php echo $jkl['g137'];?></h4>
			<ul>
				<li><?php echo sprintf($jkl['g351'], $pg["packageid"]);?></li>
				<li><?php echo sprintf($jkl['g352'], JAK_USERID);?></li>
				<li><?php echo sprintf($jkl['g353'], JAK_MAIN_LOC);?></li>
			</ul>
			<small><?php echo $jkl['g354'];?>
		</div>
	</div>

	<hr>
	<p><?php echo $jkl['g356'];?></p>

</div>

</div>

</div>
	<div class="modal-footer">
		<a href="javascript:void(0)" class="btn btn-primary paynowbank" data-packageid="<?php echo $pg["packageid"];?>" data-paymentid="<?php echo $pg["id"];?>" data-amount="<?php echo $pg["amount"];?>" data-currency="<?php echo $pg["currency"];?>" data-paidhow="<?php echo $pg["paygateid"];?>" data-ccid="<?php echo $pg["ccid"];?>" data-payfor="paymember" data-dismiss="modal"><i class="jak-loadbtn"></i> <?php echo $jkl['g357'];?></a>
		<button type="button" class="btn btn-default" data-dismiss="modal"><?php echo $jkl["g180"];?></button>
	</div>

<script>
// Extend Membership
$('.paynowbank').on('click', function(e) {

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

	// Populate the hidden fields
	$("#pid").val(pid);
	$("#pgid").val(paygateid);
	$("#paidhow").val(paidhow);
	$("#check").val(pfp);
	$("#ccid").val(ccid);
	$("#subscribe").val(0);

	$("#buypackage").submit();

});
</script>