<?php include "header.php";?>

<article class="content dashboard-page">
    <section class="section">
        <?php if (JAK_MAX_CLIENTS != 0 && $totalu >= JAK_MAX_CLIENTS) { ?>
        <div class="row">
            <div class="col-md-12">
                <div class="alert alert-danger"><?php echo $jkl['e28'];?></div>
            </div>
        </div>
        <?php } ?>
        <div class="row sameheight-container">
            <div class="col col-xs-12 col-sm-12 col-md-6 col-xl-5 stats-col">
                <div class="card sameheight-item stats" data-exclude="xs">
                    <div class="card-block">
                        <div class="title-block">
                            <h4 class="title"><?php echo $jkl['g98'];?></h4>
                            <p class="title-description"><?php echo $jkl['g99'];?></p>
                        </div>
                        <div class="row row-sm stats-container">
                        <div class="col-xs-12 col-sm-6 stat-col">
                            <div class="stat-icon"> <i class="fa fa-users"></i> </div>
                                <div class="stat">
                                    <div class="value"><?php echo $clients;?></div>
                                    <div class="name"><?php echo $jkl['g100'];?></div>
                                </div>
                                <progress class="progress stat-progress" value="75" max="100">
            					<div class="progress">
            						<span class="progress-bar" style="width: 75%;"></span>
            					</div>
            				    </progress>
                            </div>
                            <div class="col-xs-12 col-sm-6 stat-col">
                                <div class="stat-icon"> <i class="fa fa-comments-o"></i> </div>
                                    <div class="stat">
                                        <div class="value"><?php echo $lc3clients;?></div>
                                        <div class="name"><?php echo $jkl['g96'];?></div>
                                    </div>
                                    <progress class="progress stat-progress" value="25" max="100">
            				        <div class="progress">
            				            <span class="progress-bar" style="width: 25%;"></span>
            				        </div>
            				        </progress>
                                </div>
                            <div class="col-xs-12 col-sm-6  stat-col">
                                                <div class="stat-icon"> <i class="fa fa-user-circle-o"></i> </div>
                                                <div class="stat">
                                                    <div class="value"><?php echo $unconfirmed;?></div>
                                                    <div class="name"><?php echo $jkl['g101'];?></div>
                                                </div> <progress class="progress stat-progress" value="60" max="100">
            					<div class="progress">
            						<span class="progress-bar" style="width: 60%;"></span>
            					</div>
            				</progress> </div>
                                            <div class="col-xs-12 col-sm-6  stat-col">
                                                <div class="stat-icon"> <i class="fa fa-ticket"></i> </div>
                                                <div class="stat">
                                                    <div class="value"><?php echo $hd3clients;?></div>
                                                    <div class="name"><?php echo $jkl['g102'];?></div>
                                                </div> <progress class="progress stat-progress" value="34" max="100">
            					<div class="progress">
            						<span class="progress-bar" style="width: 34%;"></span>
            					</div>
            				</progress> </div>
                                            <div class="col-xs-12 col-sm-6  stat-col">
                                                <div class="stat-icon"> <i class="fa fa-globe"></i> </div>
                                                <div class="stat">
                                                    <div class="value"><?php echo $locations;?></div>
                                                    <div class="name"><?php echo $jkl['g39'];?></div>
                                                </div> <progress class="progress stat-progress" value="49" max="100">
            					<div class="progress">
            						<span class="progress-bar" style="width: 49%;"></span>
            					</div>
            				</progress> </div>
                                            <div class="col-xs-12 col-sm-6 stat-col">
                                                <div class="stat-icon"> <i class="fa fa-dollar"></i> </div>
                                                <div class="stat">
                                                    <div class="value"><?php echo $income;?></div>
                                                    <div class="name"><?php echo $jkl['g103'];?></div>
                                                </div> <progress class="progress stat-progress" value="15" max="100">
            					<div class="progress">
            						<span class="progress-bar" style="width: 15%;"></span>
            					</div>
            				</progress> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col col-xs-12 col-sm-12 col-md-6 col-xl-7 history-col">
                                <div class="card sameheight-item items" data-exclude="xs,sm,lg">
                                    <div class="card-header bordered">
                                        <div class="header-block">
                                            <h3 class="title"><?php echo $jkl['g104'];?></h3>
                                        </div>
                                    </div>
                                    <ul class="item-list striped">
                                        <li class="item item-list-header hidden-sm-down">
                                            <div class="item-row">
                                                <div class="item-col item-col-header fixed item-col-img xs"><?php echo $jkl['g56'];?></div>
                                                
                                                <div class="item-col item-col-header item-col-title">
                                                    <div> <span><?php echo $jkl['g25'];?></span> </div>
                                                </div>
                                                <div class="item-col item-col-header item-col-category">
                                                    <div> <span><?php echo $jkl['g17'];?></span> </div>
                                                </div>
                                                <div class="item-col item-col-header item-col-date">
                                                    <div> <span><?php echo $jkl['g105'];?></span> </div>
                                                </div>
                                            </div>
                                        </li>
                    <?php if (isset($lastclients) && !empty($lastclients)) foreach ($lastclients as $v) { ?>
                        <li class="item">
                            <div class="item-row">
                                <div class="item-col fixed item-col-img xs"><?php echo $v["id"];?></div>
                                <div class="item-col item-col-title">
                                    <div>
                                        <a href="<?php echo JAK_rewrite::jakParseurl('c', 'e', $v["id"]);?>" class=""><h4 class="item-title no-wrap"><?php echo $v["username"];?></h4></a>
                                    </div>
                                </div>
                                <div class="item-col item-col-category">
                                    <div> <?php echo $v["email"];?></div>
                                </div>
                                <div class="item-col item-col-date">
                                    <div class="item-heading">Published</div>
                                    <div><?php echo JAK_base::jakTimesince($v["signup"], $sett["dateformat"], $sett["timeformat"]);?></div>
                                </div>
                            </div>
                        </li>
                    <?php } ?>
                    </ul>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            <?php if (isset($tickets) && !empty($tickets)) { ?>
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g145'];?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover flip-content">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g56'];?></th>
                                            <th><?php echo $jkl['g25'];?></th>
                                            <th><?php echo $jkl['g134'];?></th>
                                            <th><?php echo $jkl['g135'];?></th>
                                            <th><i class="fa fa-edit"></i></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($tickets as $t) { ?>
                                        <tr>
                                            <td><?php echo $t["id"];?></td>
                                            <td><?php echo $t["username"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('t', 'a', $t['id']);?>"><?php echo $t["subject"];?></a></td>
                                            <td><?php echo $t["content"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('t', 'a', $t['id']);?>"><i class="fa fa-edit"></i></a></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </section>
                    </div>
                </div>
                <?php } else { ?>
                <div class="alert alert-info"><?php echo $jkl['e26'];?></div>
                <?php } ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
            <?php if (isset($subscriptions) && !empty($subscriptions)) { ?>
                <div class="card">
                    <div class="card-block">
                        <div class="card-title-block"><h3 class="title"><?php echo $jkl['g125'];?></h3></div>
                        <section class="example">
                            <div class="table-flip-scroll">
                                <div class="table-responsive">
                                <table class="table table-striped table-bordered table-hover flip-content">
                                    <thead class="flip-header">
                                        <tr>
                                            <th><?php echo $jkl['g56'];?></th>
                                            <th><?php echo $jkl['g126'];?></th>
                                            <th><?php echo $jkl['g121'];?></th>
                                            <th><?php echo $jkl['g122'];?></th>
                                            <th><?php echo $jkl['g127'];?></th>
                                            <th><?php echo $jkl['g123'];?></th>
                                            <th><?php echo $jkl['g61'];?></th>
                                            <th><?php echo $jkl['g222'];?></th>
                                            <th><?php echo $jkl['g124'];?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach($subscriptions as $v) { ?>
                                        <tr>
                                            <td><?php echo $v["id"];?></td>
                                            <td><a href="<?php echo JAK_rewrite::jakParseurl('c', 'e', $v['userid']);?>"><?php echo $v["userid"];?></a></td>
                                            <td><?php echo $v["amount"].' '.$sett["currency"];?></td>
                                            <td><?php echo $v["paidhow"];?></td>
                                            <td><?php echo (!empty($v["paidfor"]) ? $v["paidfor"] : $v["title"]);?></td>
                                            <td><?php echo ($v["paidwhen"] ? JAK_base::jakTimesince($v["paidwhen"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                            <td><?php echo ($v["paidtill"] ? JAK_base::jakTimesince($v["paidtill"], $sett["dateformat"], $sett["timeformat"]) : "-");?></td>
                                            <td><?php echo ($v["freeplan"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-remove"></i>');?></td>
                                            <td><?php echo ($v["success"] ? '<i class="fa fa-check"></i>' : '<i class="fa fa-exclamation-triangle"></i>');?></td>
                                        </tr>
                                    <?php } ?>
                                    </tbody>
                                </table>
                            </div>
                            </div>
                        </section>
                    </div>
                </div>
                <?php } else { ?>
                <div class="alert alert-info"><?php echo $jkl['e23'];?></div>
                <?php } ?>
            </div>
        </div>
    </section>
</article>

<?php include "footer.php";?>