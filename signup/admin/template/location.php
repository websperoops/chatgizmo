<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <div class="pull-right">
    	<p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('l', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g41'];?></a></p>
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
                                            <th><?php echo $jkl['g43'];?></th>
                                            <th><?php echo $jkl['g44'];?></th>
                                            <th><i class="fa fa-server"></i></th>
                                            <th><i class="fa fa-user-plus"></i></th>
                                            <th><i class="fa fa-edit"></i></th>
                                            <th><i class="fa fa-trash-o"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php if (isset($locations) && !empty($locations)) foreach ($locations as $v) { ?>
                                        <tr>
                                            <td><?php echo $v["id"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'e', $v["id"]);?>"><?php echo $v["title"];?></a></td>
                                            <td><?php echo $v["url"];?></td>
                                            <td><?php echo ($v["lastedit"] ? JAK_base::jakTimesince($v["lastedit"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                            <td><?php if ($v["db_host"]) { ?><a href="<?php echo JAK_rewrite::jakParseurl('l', 'c', $v["id"]);?>"><i class="fa fa-server"></i></a><?php } ?></td>
                                            <td><?php if ($v["lc3hd3"] == 0) { ?><a href="<?php echo JAK_rewrite::jakParseurl('l', 'u', $v["id"]);?>"><i class="fa fa-user-plus"></i></a><?php } ?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'e', $v["id"]);?>"><i class="fa fa-edit"></i></a></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('l', 'd', $v["id"]);?>"><i class="fa fa-trash-o"></i></a></td>
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