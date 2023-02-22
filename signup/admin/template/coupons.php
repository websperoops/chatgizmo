<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <div class="pull-right">
    	<p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('co', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g192'];?></a></p>
    </div>
    <div class="clearfix"></div>
    <section class="section">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $SECTION_TITLE;?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <table class="table table-striped table-bordered table-hover table-responsive flip-content w-100 d-block d-md-table">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g56'];?></th>
                                            <th><?php echo $jkl['g42'];?></th>
                                            <th><?php echo $jkl['g65'];?></th>
                                            <th><?php echo $jkl['g188'];?></th>
                                            <th><?php echo $jkl['g189'];?></th>
                                            <th><?php echo $jkl['g190'];?></th>
                                            <th><?php echo $jkl['g191'];?></th>
                                            <th><i class="fa fa-edit"></i></th>
                                            <th><i class="fa fa-trash-o"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (isset($coupons) && !empty($coupons)) foreach ($coupons as $v) { ?>
                                        <tr>
                                            <td><?php echo $v["id"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('co', 'e', $v["id"]);?>"><?php echo $v["title"];?></a></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'e', $v["locationid"]);?>"><?php echo $v["locationid"];?></a></td>
                                            <td><?php echo $v["code"];?></td>
                                            <td><?php echo $v["discount"];?>%</td>
                                            <td><?php echo $v["used"];?></td>
                                            <td><?php echo $v["total"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('co', 'e', $v["id"]);?>"><i class="fa fa-edit"></i></a></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('co', 'd', $v["id"]);?>"><i class="fa fa-trash-o"></i></a></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    </div>
            	</div>
       		</div>
    	</div>
    </section>
</article>

<?php include "footer.php";?>