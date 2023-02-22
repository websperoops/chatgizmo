<?php if (JAK_USERID) { ?>
<footer class="footer">
    <div class="footer-block buttons"></div>
    <div class="footer-block author">
        <ul>
            <li>Powered by <a href="https://jakweb.ch">Cloud Chat 3</a> :: <?php echo sprintf($jkl['g220'], $sett["version"]);?> :: <?php echo sprintf($jkl['g221'], JAK_base::jakTimesince($sett["updated"], $sett["dateformat"], $sett["timeformat"]));?></li>
        </ul>
    </div>
</footer>
<?php } if (JAK_USERID) { ?>
</div>
</div>
<?php } ?>

<!-- Reference block for JS -->
<div class="ref" id="ref">
    <div class="color-primary"></div>
    <div class="chart">
        <div class="color-primary"></div>
        <div class="color-secondary"></div>
    </div>
</div>
<script type="text/javascript" src="<?php echo BASE_URL;?>js/vendor.js"></script>
<script type="text/javascript" src="<?php echo BASE_URL;?>js/app.js"></script>

<script type="text/javascript">
<?php if (isset($_SESSION["infomsg"])) { ?>
$.notify({icon: 'fa fa-info-circle', message: '<?php echo addslashes($_SESSION["infomsg"]);?>'}, {type: 'info', animate: {
        enter: 'animated fadeInDown',
        exit: 'animated fadeOutUp'
    }});
<?php } if (isset($_SESSION["successmsg"])) { ?>
$.notify({icon: 'fa fa-check-square-o', message: '<?php echo addslashes($_SESSION["successmsg"]);?>'}, {type: 'success', animate: {
        enter: 'animated fadeInDown',
        exit: 'animated fadeOutUp',
        delay: 10000
    }});
<?php } if (isset($_SESSION["errormsg"])) { ?>
$.notify({icon: 'fa fa-exclamation-triangle', message: '<?php echo addslashes($_SESSION["errormsg"]);?>'}, {type: 'danger', animate: {
        enter: 'animated fadeInDown',
        exit: 'animated fadeOutUp'
    }});
<?php } ?>
</script>

<?php if ($js_file_footer) include_once(APP_PATH.'template/'.$js_file_footer);?>
</body>
</html>