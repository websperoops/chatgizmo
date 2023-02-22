<?php include_once 'header.php';?>

<div class="content">

<?php if (JAK_HOLIDAY_MODE != 0) { ?>

<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $jkl["g303"];?> (<?php echo (JAK_HOLIDAY_MODE == 1 ? $jkl["g1"] : $jkl["g305"]);?>)</div>

<?php } ?>

<!-- Link to Apps -->
<?php echo $sett["appboxes"];?>
<!-- end Link to Apps -->

<?php if (!$jakuser->getVar("hours_array")) { ?>

<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
        <p><?php $trans = array("{{clientprofile}}" => JAK_rewrite::jakParseurl('users','edit',JAK_USERID,$opcacheid));
    echo strtr($sett["businesshours"], $trans);?></p>
      </div>
    </div>
  </div>
</div>

<!-- Let's get the client up and running -->
<?php } if (isset($opmain) && !empty($opmain)) { if (strtotime($opmain["paidtill"]) < time()) { ?>

<div class="alert alert-danger" id="expiredmsg"><?php echo $sett["expiredmsgdash"];?></div>

<?php } else { if (strtotime($opmain["trial"]) > time()) { ?>
<div class="row" id="trialmsg">
<div class="col-md-12">
<div class="card">
      <div class="card-body">
<?php 
    $trans = array("{{trialdate}}" => JAK_base::jakTimesince($opmain["trial"], JAK_DATEFORMAT, JAK_TIMEFORMAT));
    echo strtr($sett["trialdate"], $trans);
?>
</div>
</div>
</div>
</div>
<?php } } if (!empty($sett["heldashpmsg"])) { 
    $trans = array("{{paidtill}}" => ($jakosub["paidtill"] != "0000-00-00 00:00:00" ? JAK_base::jakTimesince($jakosub["paidtill"], JAK_DATEFORMAT, JAK_TIMEFORMAT) : 'expired'), "{{clientprofile}}" => JAK_rewrite::jakParseurl('users','edit',JAK_USERID), "{{clientsettings}}" => JAK_rewrite::jakParseurl('settings'), "{{clientwidget}}" => JAK_rewrite::jakParseurl('widget'), "{{clientresponse}}" => JAK_rewrite::jakParseurl('response'), "{{clientproactive}}" => JAK_rewrite::jakParseurl('proactive'), "{{clientbot}}" => JAK_rewrite::jakParseurl('bot'));
    echo strtr($sett["heldashpmsg"], $trans);

  }
?>

<div class="header text-center">
  <h3 class="title"><?php echo $sett["purchasedtitle"];?></h3>
    <p class="category">
      <a href="<?php echo JAK_rewrite::jakParseurl('extend');?>"><?php echo $sett["packageseltitle"];?></a>
    </p>
</div>

<?php if (isset($subscriptions) && !empty($subscriptions)) { ?>

<!-- Purchased subscriptions -->
<div class="row">
  <div class="col-md-12">
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th><?php echo $jkl['g16'];?></th>
          <th><?php echo $jkl["i36"];?></th>
          <th><?php echo $jkl["i37"];?></th>
          <th><?php echo $jkl["i38"];?></th>
          <th><?php echo $jkl["i39"];?></th>
          <th><?php echo $jkl["i40"];?></th>
          <th><?php echo $jkl["g14"];?></th>
          <th><?php echo $jkl['g101'];?></th>
          <th></th>
        </tr>
      </thead>
    <?php $subused = array(); foreach($subscriptions as $s) { ?>
      <tr>
        <td><?php echo ($s["title"] ? $s["title"] : $s["paidfor"]);?></td>
        <td><?php echo $s["amount"].' '.$s["currency"];?></td>
        <td><?php echo $s["paidhow"];?></td>
        <td><?php echo ($s["subscribed"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');?></td>
        <td><?php echo ($s["paidwhen"] ? JAK_base::jakTimesince($s["paidwhen"], "d.m.Y", " g:i a") : "-");?></td>
        <td><?php echo ($s["paidtill"] ? JAK_base::jakTimesince($s["paidtill"], "d.m.Y", " g:i a") : "-");?></td>
        <td><?php echo ($s["success"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');?></td>
        <td><?php echo ($s["active"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>');?></td>
        <td><?php echo ($s["subscribed"] && $s["active"] ? '<a href="'.JAK_rewrite::jakParseurl('extend', 'withdrawal', $s["id"], JAK_USERID).'" class="btn btn-sm btn-danger">'.$jkl['g280'].'</a>' : '');?></td>
      </tr>
    <?php $subused[] = $s["packageid"]; } ?>
    </table>
  </div>
</div>
</div>

<?php } if (isset($packages) && !empty($packages)) { ?>

<div class="header text-center">
  <h3 class="title"><?php echo $sett["packageseltitle"];?></h3>
    <p class="category">
      <span class="badge badge-light text-dark"><?php echo $jkl['i46'];?></span> <span class="badge badge-success text-dark"><?php echo $jkl['i47'];?></span> <span class="badge  badge-dark"><?php echo $jkl['i48'];?></span>
    </p>
</div>

<form method="post" id="buypackage" action="<?php echo $_SERVER['REQUEST_URI'];?>">

<div class="row">
  <?php foreach ($packages as $p) { ?>
  <div class="col-md-4 mb-3 d-flex align-items-stretch">
    <div class="card<?php if ($p["multipleuse"] == 0 && !empty($p["sid"])) { echo ' text-white bg-dark'; } elseif ($p["id"] == $jakosub['packageid']) { echo ' text-dark bg-success';}?>">
    <?php if (!empty($p["previmg"])) { ?><img class="card-img-top img-fluid" src="<?php echo $p["previmg"];?>" alt="<?php echo $p["title"];?>"><?php } ?>
    <div class="card-body">
        <h4 class="card-title text-dark"><?php echo $p["title"];?></h4>
        <p class="card-text"><?php echo $p["description"];?></p>
        <p class="card-text"><?php echo sprintf($jkl['i17'], $p["operators"]);?><br>
        <?php echo sprintf($jkl['i18'], $p["departments"]);?><br>
        <?php echo sprintf($jkl['i58'], $p["chatwidgets"]);?><br>
        <?php echo sprintf($jkl['i19'], ($p["files"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>'));?><br>
        <?php echo sprintf($jkl['i20'], ($p["copyfree"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>'));?><br>
        <?php echo sprintf($jkl['i21'], $p["activechats"]);?><br>
        <?php echo sprintf($jkl['i42'], $p["chathistory"]);?><br>
        <?php echo ($p["islc3"] ? sprintf($jkl['i22'], '<i class="fa fa-check"></i>').'<br>' : '');?>
        <?php echo ($p["ishd3"] ? sprintf($jkl['i23'], '<i class="fa fa-check"></i>').'<br>' : '');?>
        <strong><?php echo sprintf($jkl['i24'], $p["validfor"]);?></strong><br>
        <?php echo sprintf($jkl['i25'], ($p["multipleuse"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-times"></i>'));?><?php if ($p["isfree"] == 0) { ?><br>
        <?php echo sprintf($jkl['i26'], '<input type="checkbox" name="subscribe" id="subscribe-'.$p["id"].'" value="1">');?><?php } ?></p>
        <h5><?php echo sprintf($jkl['i31'], '<span id="price-'.$p["id"].'">'.$p["amount"].'</span> '.$p["currency"]);?></h5>
        <?php if ($p["isfree"] == 0) { ?>
        <div class="form-group">
          <label for="coupon"><?php echo $jkl['i27'];?></label>
          <div class="input-group">
          <input type="text" class="form-control" id="coupon-<?php echo $p["id"];?>" autocomplete="false">
              <span class="input-group-btn">
            <button class="btn btn-default coupon-check" data-cid="<?php echo $p["id"];?>" data-amount="<?php echo $p["amount"];?>" type="button"><i class="jak-loadbtn"></i> <?php echo $jkl['i30'];?></button>
          </span>
          </div>
          <small id="coupon-help-<?php echo $p["id"];?>" class="form-text"></small>
        </div>
        <?php } ?>
    </div>
    <div class="card-footer text-center">
      <?php if ($p["multipleuse"] == 0 && !empty($subused) && in_array($p["id"], $subused)) { ?>
        <a href="javascript:void(0)" class="btn btn-danger"><?php echo $jkl['i41'];?></a>
        <?php } elseif ($p["isfree"] == 1) { ?>
        <a href="javascript:void(0)" class="btn btn-primary paynow" data-packageid="<?php echo $p["id"];?>" data-paymentid="0" data-amount="0" data-currency="<?php echo $sett["currency"];?>" data-paidhow="freeaccess" data-payfor="paymember"><i class="jak-loadbtn"></i> <i class="fa fa-gift"></i> <?php echo $jkl["g333"];?></a>
      <?php } elseif (isset($paygate) && !empty($paygate)) { foreach($paygate as $pg) { if ($pg["packageid"] == $p["id"]) { ?>

        <a <?php echo ($pg["paygateid"] == "bank" ? 'data-toggle="modal" href="'.JAK_rewrite::jakParseurl('extend', 'bank', $pg["id"]).'" data-target="#jakModal" class="btn btn-primary"' : 'href="javascript:void(0)" class="btn btn-primary paynow"');?> data-packageid="<?php echo $p["id"];?>" data-paymentid="<?php echo $pg["id"];?>" data-amount="<?php echo $pg["ccamount"];?>" data-currency="<?php echo $pg["pcurrency"];?>" data-paidhow="<?php echo $pg["paygateid"];?>" data-ccid="<?php echo $pg["ccid"];?>" data-payfor="paymember"><i class="jak-loadbtn"></i> <?php echo $pg["title"];?> <?php echo ($pg["pcurrency"] != $pg["currency"] ? '('.$pg["ccamount"].' '.$pg["pcurrency"].')' : '');?></a>

      <?php } } } ?>
      <input type="hidden" id="discount-<?php echo $p["id"];?>" value="0">
    </div>
  </div>
</div>
<?php } } ?>
</div>

<input type="hidden" name="pgid" id="pgid">
<input type="hidden" name="pid" id="pid">
<input type="hidden" name="paidhow" id="paidhow">
<input type="hidden" name="amount" id="amount">
<input type="hidden" name="check" id="check">
<input type="hidden" name="subscribe" id="subscribe" value="0">

</form>

<?php } if (!empty($sett["addops"])) { ?>

<!-- Additional Operator Accounts -->

<form method="post" id="buyop" action="<?php echo $_SERVER['REQUEST_URI'];?>">

  <div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">

<div class="header text-center">
  <h3 class="title"><?php echo $sett["addoptitle"];?></h3>
  <p class="category">
    <?php echo $sett["addopsmsg"];?>
  </p>
</div>

<div class="row">
  <div class="col-md-3">
        <select name="newops" id="newops" class="selectpicker mt-2" title="<?php echo $sett["addoptitle"];?>">
          <?php if (isset($JAK_USER_ALL) && count($JAK_USER_ALL) < 5) { ?>
          <option value="1" data-price="<?php echo $sett["addops"];?>">1 Operator (<?php echo $sett["addops"].' '.$sett["currency"];?> per Month)</option>
          <?php } if (isset($JAK_USER_ALL) && count($JAK_USER_ALL) < 4) { ?>
          <option value="2" data-price="<?php echo 2*$sett["addops"];?>">2 Operators (<?php echo 2*$sett["addops"].' '.$sett["currency"];?> per Month)</option>
          <?php } if (isset($JAK_USER_ALL) && count($JAK_USER_ALL) < 3) { ?>
          <option value="3" data-price="<?php echo 3*$sett["addops"];?>">3 Operators (<?php echo 3*$sett["addops"].' '.$sett["currency"];?> per Month)</option>
          <?php } if (isset($JAK_USER_ALL) && count($JAK_USER_ALL) < 2) { ?>
          <option value="4" data-price="<?php echo 4*$sett["addops"];?>">4 Operators (<?php echo 4*$sett["addops"].' '.$sett["currency"];?> per Month)</option>
          <?php } if (isset($JAK_USER_ALL) && count($JAK_USER_ALL) < 1) { ?>
          <option value="5" data-price="<?php echo 5*$sett["addops"];?>">5 Operators (<?php echo 5*$sett["addops"].' '.$sett["currency"];?> per Month)</option>
          <?php } ?>
        </select>
      </div>
      <div class="col-md-9">
        <?php if (isset($sett["stripe_publish_key"]) && !empty($sett["stripe_publish_key"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="stripe" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fab fa-cc-stripe"></i> Stripe</a>
        <?php }
        if (isset($sett["paypal_client"]) && !empty($sett["paypal_client"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="paypal" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fab fa-paypal"></i> Paypal</a>
        <?php }
        if (isset($sett["yookassa_id"]) && !empty($sett["yookassa_id"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="yookassa" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> YooKassa</a>
        <?php }
        if (isset($sett["paystack_secret"]) && !empty($sett["paystack_secret"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="paystack" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> Paystack</a>
        <?php }
        if (isset($sett["twoco"]) && !empty($sett["twoco"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="authorize.net" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> Authorize.net</a>
        <?php }
        if (isset($sett["authorize_id"]) && !empty($sett["authorize_id"])) { ?>
          <a href="javascript:void(0)" class="btn btn-success paynowop" data-paidhow="2checkout" data-payfor="payop"><i class="jak-loadbtn"></i> <i class="fas fa-credit-card-front"></i> Verifone (2Checkout)</a>
        <?php } ?>
      </div>
</div>

<input type="hidden" name="paidhowop" id="paidhowop">
<input type="hidden" name="check" id="checkop">
<input type="hidden" name="opcount" id="opcount">

  </div>
</div>
</div>
</div>

</form>

  <!-- Additional Operator Accounts -->
  <?php if ($jakosub['extraoperators'] > 0 && isset($JAK_USER_ALL) && empty($JAK_USER_ALL)) { ?>
    <div class="alert alert-info"><?php echo sprintf($jkl['hd343'], JAK_rewrite::jakParseurl('users', 'new'));?></div>
  <?php } elseif (isset($JAK_USER_ALL) && !empty($JAK_USER_ALL) && is_array($JAK_USER_ALL)) { ?>
<form method="post" id="buyopext" action="<?php echo $_SERVER['REQUEST_URI'];?>">
  <div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-body">
  <div class="table-responsive">
    <table class="table table-striped">
      <thead>
        <tr>
          <th><?php echo $jkl["u"];?></th>
          <th><?php echo $jkl["u1"];?></th>
          <th><?php echo $jkl["u2"];?></th>
          <th><?php echo $jkl['g47'];?></th>
          <th>Expires</th>
          <th>Extend</th>
        </tr>
      </thead>
    <?php foreach($JAK_USER_ALL as $v) { ?>
      <tr id="opid<?php echo $v["id"];?>"<?php if ($v["validtill"] < $JAK_CURRENT_DATE) echo ' class="table-danger"';?>>
        <td><?php echo $v["name"];?></td>
        <td><?php echo $v["email"];?></td>
        <td><?php echo $v["username"];?></td>
        <td><a class="btn btn-default btn-sm" href="<?php echo JAK_rewrite::jakParseurl('users', 'edit', $v["id"], $v["opid"]);?>"><i class="fa fa-pencil"></i></a></td>
        <td id="opexpire<?php echo $v["id"];?>"><?php echo JAK_base::jakTimesince($v["validtill"], $sett['dateformat'], $sett['timeformat']);?></td>
        <td><div class="input-group"><select name="extop" id="extop" class="form-control form-control-sm"><?php for ($i=1; $i < 13; $i++) { ?>
          <option value="<?php echo $i;?>" data-price="<?php echo $i*$sett["addops"];?>" data-opid="<?php echo $v["id"];?>"><?php echo sprintf($jkl['g345'], $i)?> (<?php echo $i*$sett["addops"].' '.$sett["currency"];?>)</option>
        <?php } ?></select>
        <?php if (isset($sett["stripe_publish_key"]) && !empty($sett["stripe_publish_key"])) { ?>
          <span class="input-group-btn">
            <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="stripe" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fab fa-cc-stripe"></i> Stripe</a>
          </span>
        <?php }
        if (isset($sett["paypal_client"]) && !empty($sett["paypal_client"])) { ?>
          <span class="input-group-btn">
          <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="paypal" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fab fa-paypal"></i> Paypal</a>
          </span>
        <?php }
        if (isset($sett["yookassa_id"]) && !empty($sett["yookassa_id"])) { ?>
          <span class="input-group-btn">
          <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="yookassa" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> YooKassa</a>
          </span>
        <?php }
        if (isset($sett["paystack_secret"]) && !empty($sett["paystack_secret"])) { ?>
          <span class="input-group-btn">
          <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="paystack" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> Paystack</a>
          </span>
        <?php }
        if (isset($sett["twoco"]) && !empty($sett["twoco"])) { ?>
          <span class="input-group-btn">
          <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="authorize.net" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fa fa-credit-card"></i> Authorize.net</a>
          </span>
        <?php }
        if (isset($sett["authorize_id"]) && !empty($sett["authorize_id"])) { ?>
          <span class="input-group-btn">
          <a href="javascript:void(0)" class="btn btn-info btn-sm paynowopextend" data-paidhow="2checkout" data-payfor="opextend"><i class="jak-loadbtn"></i> <i class="fas fa-credit-card-front"></i> Verifone (2Checkout)</a>
          </span>
        <?php } ?>
      </span></div></td>
      </tr>
    <?php } ?>
    </table>
  </div>
  </div>
</div>
</div>
</div>

<input type="hidden" name="paidhowopext" id="paidhowopext">
<input type="hidden" name="check" id="checkopext">
<input type="hidden" name="opamountext" id="opamountext">
<input type="hidden" name="opidext" id="opidext">

</form>

  <?php } else { ?>
  <hr>
  <div class="alert alert-info"><?php echo $sett["moreopmsg"];?></div>
  <?php } ?>

  <?php echo $sett["opwarnmsg"];?>
<?php } ?>

</div>

<?php include_once 'footer.php';?>