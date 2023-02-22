<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <div class="pull-right">
    	<p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('pa', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g116'];?></a></p>
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
                                            <th><?php echo $jkl['g121'];?></th>
                                            <th><?php echo $jkl['g113'];?></th>
                                            <th><?php echo $jkl['g114'];?></th>
                                            <th><?php echo $jkl['g115'];?></th>
                                            <th><?php echo $jkl['g117'];?></th>
                                            <th><?php echo $jkl['g173'];?></th>
                                            <th><i class="fa fa-edit"></i></th>
                                            <th><i class="fa fa-trash-o"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (isset($packages) && !empty($packages)) foreach ($packages as $v) { ?>
                                        <tr>
                                            <td><?php echo $v["id"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('pa', 'e', $v["id"]);?>"><?php echo $v["title"];?></a></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'e', $v["locationid"]);?>"><?php echo $v["locationid"];?></a></td>
                                            <td><?php echo $v["amount"].' '.$v["currency"];?></td>
                                            <td><?php echo $v["operators"];?></td>
                                            <td><?php echo $v["departments"];?></td>
                                            <td><i class="fa fa-<?php echo ($v["files"] ? 'check' : 'times');?>"></i></td>
                                            <td><i class="fa fa-<?php echo ($v["active"] ? 'check' : 'times');?>"></i></td>
                                            <td><i class="fa fa-<?php echo ($v["copyfree"] ? 'times' : 'check');?>"></i></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('pa', 'e', $v["id"]);?>"><i class="fa fa-edit"></i></a></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('pa', 'd', $v["id"]);?>"><i class="fa fa-trash-o"></i></a></td>
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