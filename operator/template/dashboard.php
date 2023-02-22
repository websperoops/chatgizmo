<?php include_once 'header.php';?>

<div class="content">

  <div class="row">
    <div class="col-md-12">
      <div class="card card-stats">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="statistics">
                <div class="info">
                  <div class="icon icon-primary">
                    <i class="fa fa-comment"></i>
                  </div>
                  <h3 class="info-title"><?php echo $sessCtotal;?></h3>
                  <h6 class="stats-title"><?php echo $jkl["stat_s25"];?></h6>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="statistics">
                <div class="info">
                  <div class="icon icon-success">
                    <i class="fa fa-comment-alt-lines"></i>
                  </div>
                  <h3 class="info-title"><?php echo $commCtotal;?></h3>
                  <h6 class="stats-title"><?php echo $jkl['stat_s26'];?></h6>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="statistics">
                <div class="info">
                  <div class="icon icon-danger">
                    <i class="fa fa-ticket-alt"></i>
                  </div>
                  <h3 class="info-title"><?php echo $statsCtotal;?></h3>
                  <h6 class="stats-title"><?php echo $jkl['stat_s10'];?></h6>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="statistics">
                <div class="info">
                  <div class="icon icon-info">
                    <i class="fa fa-users"></i>
                  </div>
                  <h3 class="info-title"><?php echo $visitCtotal;?></h3>
                  <h6 class="stats-title"><?php echo $jkl['stat_s27'];?></h6>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>

<?php if (JAK_HOLIDAY_MODE != 0) { ?>
<div class="alert alert-danger"><i class="fa fa-exclamation-triangle"></i> <?php echo $jkl["g303"];?> (<?php echo (JAK_HOLIDAY_MODE == 1 ? $jkl["g1"] : $jkl["g305"]);?>)</div>
<?php } ?>

<?php if (isset($gcarray) && !empty($gcarray)) { ?>
<div class="row">
  <div class="col-md-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-comments"></i> <?php echo $jkl["m29"];?></h3>
      </div><!-- /.box-header -->
      <div class="card-body">
        <?php foreach ($gcarray as $c) { ?>
          <a class="btn btn-primary btn-sm" href="<?php echo str_replace(JAK_OPERATOR_LOC."/", "", JAK_rewrite::jakParseurl('groupchat', $c["id"], $c["lang"]));?>" target="_blank"><i class="fa fa-comments"></i> <?php echo $c["title"];?></a>&nbsp;
        <?php } ?>
      </div>
    </div>
  </div>
</div>
<?php } ?>

<div class="row">
  <div class="col-lg-4 col-md-6">
    <div class="card card-chart">
      <div class="card-header">
        <h5 class="card-category"><i class="fa fa-users"></i> <?php echo $jkl['stat_s34'];?></h5>
        <h2 class="card-title"><?php echo $visitCtotal;?></h2>
      </div>
      <div class="card-body">
        <div id="worldMap" class="map"></div>
        <?php if (isset($ctlres) && !empty($ctlres)) { ?>
        <div class="table-responsive">
          <table class="table">
            <tbody>
              <?php foreach ($ctlres as $u) { ?>
              <tr>
                <td>
                  <img src="<?php echo BASE_URL;?>img/blank.png" class="flag-big flag-<?php echo $u['countrycode'];?>" alt="<?php echo $u['country'];?>">
                </td>
                <td><?php echo $u["country"];?></td>
                <td class="text-right">
                  <?php echo $u["total_country"];?>
                </td>
                <td class="text-right">
                  <?php if (isset($visitCtotal) && !empty($visitCtotal)) echo number_format(($u["total_country"] * 100) / $visitCtotal, 2);?>%
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="alert alert-info"><?php echo $jkl['i3'];?></div>
      <?php } ?>
      </div>
      <div class="card-footer">
        <div class="stats">
          <a href="<?php echo JAK_rewrite::jakParseurl('uonline');?>"><i class="fa fa-map-marked-alt"></i> <?php echo $jkl['g122'];?></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card card-chart">
      <div class="card-header">
        <h5 class="card-category"><i class="fa fa-comments"></i> <?php echo $jkl['stat_s32'];?></h5>
        <h2 class="card-title"><?php echo count($openChats);?></h2>
      </div>
      <div class="card-body">
        
        <?php if (isset($openChats) && !empty($openChats)) { ?>
        <div class="table-responsive">
          <table class="table">
            <tbody>
              <?php foreach ($openChats as $c) { ?>
              <tr>
                <td><a href="<?php echo JAK_rewrite::jakParseurl('live', $c['id']);?>"><?php echo $c["name"];?></a></td>
                <td class="text-right">
                  <?php echo JAK_base::jakTimesince($c["initiated"], JAK_DATEFORMAT, JAK_TIMEFORMAT);?>
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="alert alert-info"><?php echo $jkl['i5'];?></div>
      <?php } ?>

      </div>
      <div class="card-footer">
        <div class="stats">
          <a href="<?php echo JAK_rewrite::jakParseurl('leads');?>"><i class="fa fa-comments"></i> <?php echo $jkl['stat_s36'];?></a>
        </div>
      </div>
    </div>
  </div>
  <div class="col-lg-4 col-md-6">
    <div class="card card-chart">
      <div class="card-header">
        <h5 class="card-category"><i class="fa fa-mail-bulk"></i> <?php echo $jkl['stat_s33'];?></h5>
        <h2 class="card-title"><?php echo (!empty($openContacts) ? count($openContacts) : 0);?></h2>
      </div>
      <div class="card-body">

        <?php if (isset($openContacts) && !empty($openContacts)) { ?>
        <div class="table-responsive">
          <table class="table">
            <tbody>
              <?php foreach ($openContacts as $c) { ?>
              <tr>
                <td><a href="<?php echo JAK_rewrite::jakParseurl('contacts', 'read', $c['id']);?>"><?php echo $c["name"];?></a></td>
                <td class="text-right">
                  <?php echo JAK_base::jakTimesince($c["sent"], JAK_DATEFORMAT, JAK_TIMEFORMAT);?>
                </td>
              </tr>
            <?php } ?>
            </tbody>
          </table>
        </div>
      <?php } else { ?>
        <div class="alert alert-info"><?php echo $jkl['i5'];?></div>
      <?php } ?>
        
      </div>
      <div class="card-footer">
        <div class="stats">
          <a href="<?php echo JAK_rewrite::jakParseurl('contacts');?>"><i class="fa fa-mail-bulk"></i> <?php echo $jkl['stat_s35'];?></a>
        </div>
      </div>
    </div>
  </div>
</div>

<div class="card">
    <div class="card-header">
      <h3 class="card-title"><i class="fa fa-download"></i> Downloads and Support</h3>
      <div class="card-body">
        <p>Often on the road, download our native apps for Android and iOS to easily serve your clients outside your office. Push notifications included, just setup your business hours in <a href="<?php echo JAK_rewrite::jakParseurl('users','edit',JAK_USERID);?>">your operator profile</a>. Your Live Chat URL is: <strong><?php echo rtrim(BASE_URL_ORIG,"/");?></strong></p>
        <p>Get your native push notifications token and user key from our <a href="https://jakweb.ch/push">Push Server</a>. Use the same login details from when you have purchased a license with us.</p>
      </div>
  </div>
</div>

</div>

<?php include_once 'footer.php';?>