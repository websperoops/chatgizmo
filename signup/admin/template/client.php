<?php include "header.php";?>

<article class="content responsive-tables-page">
    <div class="title-block">
        <h1 class="title"><?php echo $SECTION_TITLE;?></h1>
        <p class="title-description"><?php echo $SECTION_DESC;?></p>
    </div>
    <?php if (jak_get_access("c", $jakuser->getVar("permissions"), JAK_SUPERADMINACCESS)) { ?>
    <div class="pull-right">
        <p><a class="btn btn-primary" href="<?php echo JAK_rewrite::jakParseurl('c', 'n');?>"><i class="fa fa-plus-square"></i> <?php echo $jkl['g233'];?></a></p>
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
                            <table class="table table-striped table-bordered table-hover table-responsive flip-content w-100 d-block d-md-table" id="dynamic-data">
                                <thead class="flip-header">
                                    <tr>
                                        <th><?php echo $jkl['g56'];?></th>
                                        <th><?php echo $jkl['g25'];?></th>
                                        <th><?php echo $jkl['g26'];?></th>
                                        <th><?php echo $jkl['g65'];?></th>
                                        <th><?php echo $jkl['g61'];?></th>
                                        <th><?php echo $jkl['g63'];?></th>
                                        <th><?php echo $jkl['g62'];?></th>
                                        <th><i class="fa fa-edit"></i></th>
                                        <th><i class="fa fa-trash-o"></i></th>
                                    </tr>
                                </thead>
                                <tbody>
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