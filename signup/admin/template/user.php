<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if (JAK_USERID == 1) { ?>
    <div class="pull-right">
    	<p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('u', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g28'];?></a></p>
    </div>
    <div class="clearfix"></div>
    <?php } ?>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $SECTION_TITLE;?></h3>
                    </div>
                    <section class="example">
                        <div class="table-flip-scroll">
                            <table class="table table-striped table-bordered table-responsive table-hover flip-content w-100 d-block d-md-table">
                                <thead class="flip-header">
                                    <tr>
                                        <th><?php echo $jkl['g24'];?></th>
                                        <th><?php echo $jkl['g25'];?></th>
                                        <th><?php echo $jkl['g26'];?></th>
                                        <th><?php echo $jkl['g27'];?></th>
                                        <th><i class="fa fa-edit"></i></th>
                                        <th><i class="fa fa-trash-o"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php if (isset($users) && !empty($users)) foreach ($users as $v) { ?>
                                    <tr>
                                        <td><?php echo $v["name"];?></td>
                                        <td><a href="<?php echo JAK_rewrite::jakParseurl('u', 'e', $v["id"]);?>"><?php echo $v["username"];?></a></td>
                                        <td><?php echo $v["email"];?></td>
                                        <td><?php echo ($v["lastactivity"] ? JAK_base::jakTimesince($v["lastactivity"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                        <td><?php if (JAK_USERID == 1 || $v["id"] == JAK_USERID) { ?><a href="<?php echo JAK_rewrite::jakParseurl('u', 'e', $v["id"]);?>"><i class="fa fa-edit"></i></a><?php } ?></td>
                                        <td><?php if ($v["id"] != 1) { ?><a href="<?php echo JAK_rewrite::jakParseurl('u', 'd', $v["id"]);?>"><i class="fa fa-trash-o"></i></a><?php } ?></td>
                                    </tr>
                                <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </section>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>