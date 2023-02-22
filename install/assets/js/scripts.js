var working = false;

function scroll_to_class(element_class, removed_height) {
	var scroll_to = $(element_class).offset().top - removed_height;
	if($(window).scrollTop() != scroll_to) {
		$('html, body').stop().animate({scrollTop: scroll_to}, 0);
	}
}

function bar_progress(progress_line_object, direction) {
	var number_of_steps = progress_line_object.data('number-of-steps');
	var now_value = progress_line_object.data('now-value');
	var new_value = 0;
	if(direction == 'right') {
		new_value = now_value + ( 100 / number_of_steps );
	}
	else if(direction == 'left') {
		new_value = now_value - ( 100 / number_of_steps );
	}
	progress_line_object.attr('style', 'width: ' + new_value + '%;').data('now-value', new_value);

    // The value
    console.log(new_value);

    // Now if we on step 3 let's run the installation wizard in the background
    if (new_value == 70) {

        if (working) return false;
    
        working = true;

        var request = $.ajax({
          async: true,
          url: 'db_install.php',
          type: "POST",
          data: "step=3",
          dataType: "json",
          cache: false
        });
        
        request.done(function(msg) {
            if (msg.status == 1) {
               $('#database_installing').fadeOut();
               $('#database_success').fadeIn();
            } else if (msg.status == 2) {
                $('#database_installing').fadeOut(); 
                $('#database_already').fadeIn();
            } else {
               $('#database_installing').fadeOut(); 
               $('#database_failure').fadeIn();
            }
            
            working = false;
            return true;
            
        });
    }
}

function getonBoard() {
    
    if (working) return false;
    
    working = true;
    
    /* This flag will prevent multiple comment submits: */
    $("#onBoard i").removeClass("fa-paper-plane").addClass("fa-spinner fa-pulse");
    $('#msgError').removeClass("is-invalid");

    var onumber = $('#f1-onumber').val();
    var envname = $('#f1-envname').val();
    var title = $('#f1-title').val();
    var name = $('#f1-name').val();
    var uname = $('#f1-username').val();
    var email = $('#f1-email').val();
    var password = $('#f1-password').val();

    var request = $.ajax({
        async: true,
      url: 'db_user.php',
      type: "POST",
      data: "step=5&onumber="+onumber+"&envname="+envname+"&title="+title+"&name="+name+"&uname="+uname+"&email="+email+"&password="+btoa(password),
      dataType: "json",
      cache: false
    });
    
    request.done(function(data) {
        if (data.status == 1) {
            $('#form-elements-signup, #onBoard').fadeOut();
            $('#form-success-signup').fadeIn();
        } else {
            $('#form-error-signup').fadeIn();
            $('#error_msg_signup').html(data.errors);
        }
        
        $("#onBoard i").removeClass("fa-spinner fa-pulse").addClass("fa-paper-plane");
        
        working = false;
        
    });
    
}

function saveDB() {
    
    if (working) return false;
    
    working = true;
    
    /* This flag will prevent multiple comment submits: */
    $('#form-error').fadeOut();
    $("#saveDB i").removeClass("fa-save").addClass("fa-spinner fa-pulse");
    $('#msgError').removeClass("is-invalid");

    var dbhost1 = $('#f1-dbhost1').val();
    var dbport1 = $('#f1-dbport1').val();
    var dbuser1 = $('#f1-dbuser1').val();
    var dbpass1 = $('#f1-dbpass1').val();
    var dbname1 = $('#f1-dbname1').val();
    var dbhost2 = $('#f1-dbhost2').val();
    var dbport2 = $('#f1-dbport2').val();
    var dbuser2 = $('#f1-dbuser2').val();
    var dbpass2 = $('#f1-dbpass2').val();
    var dbname2 = $('#f1-dbname2').val();
    var cc3domain = $('#f1-cc3domain').val();
    var upath = $('#f1-upath').val();
    var ssl = $('#f1-ssl').val();
    var rewrite = $('#f1-rewrite').val();

    var request = $.ajax({
        async: true,
      url: 'db_conn.php',
      type: "POST",
      data: "step=2&dbhost1="+dbhost1+"&dbport1="+dbport1+"&dbuser1="+dbuser1+"&dbpass1="+dbpass1+"&dbname1="+dbname1+"&dbhost2="+dbhost2+"&dbport2="+dbport2+"&dbuser2="+dbuser2+"&dbpass2="+dbpass2+"&dbname2="+dbname2+"&cc3domain="+cc3domain+"&upath="+upath+"&ssl="+ssl+"&rewrite="+rewrite,
      dataType: "json",
      cache: false
    });
    
    request.done(function(data) {
        if (data.status == 1) {
            $('#form-elementsdb, #saveDB').hide();
            $('#form-success, #checkSRV').fadeIn();
            $("#saveDB i").removeClass("fa-spinner fa-pulse").addClass("fa-save");
            $('.f1 input[type="text"], .f1 input[type="password"], .f1 textarea').removeClass('input-error');
        } else {
            $('#form-error').fadeIn();
            $('#error_msg').html(data.errors);
            $("#saveDB i").removeClass("fa-spinner fa-pulse").addClass("fa-save");
        }
        
        working = false;
        
    });
    
}

jQuery(document).ready(function() {

    $('.form-dbconn').submit(function(e){
        e.preventDefault();
        saveDB();
    });

    $('.form-onboard').submit(function(e){
        e.preventDefault();
        getonBoard();
    });
	
    /*
        Fullscreen background
    */
    $.backstretch("assets/img/1.jpg");
    /*
        Form
    */
    $('.f1 fieldset:first').fadeIn('slow');
    
    $('.f1 input[type="text"], .f1 input[type="password"], .f1 textarea').on('focus', function() {
    	$(this).removeClass('input-error');
    });
    
    // next step
    $('.f1 .btn-next').on('click', function() {
    	var parent_fieldset = $(this).parents('fieldset');
    	var next_step = true;
    	// navigation steps / progress steps
    	var current_active_step = $(this).parents('.f1').find('.f1-step.active');
    	var progress_line = $(this).parents('.f1').find('.f1-progress-line');
    	
    	// fields validation
    	parent_fieldset.find('input[type="text"], input[type="password"], textarea').each(function() {
    		if( $(this).val() == "" ) {
    			$(this).addClass('input-error');
    			next_step = false;
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    	// fields validation
    	
    	if( next_step ) {
    		parent_fieldset.fadeOut(400, function() {
    			// change icons
    			current_active_step.removeClass('active').addClass('activated').next().addClass('active');
    			// progress bar
    			bar_progress(progress_line, 'right');
    			// show next step
	    		$(this).next().fadeIn();
	    		// scroll window to beginning of the form
    			scroll_to_class( $('.f1'), 20 );
	    	});
    	}
    	
    });
    
    // previous step
    $('.f1 .btn-previous').on('click', function() {
    	// navigation steps / progress steps
    	var current_active_step = $(this).parents('.f1').find('.f1-step.active');
    	var progress_line = $(this).parents('.f1').find('.f1-progress-line');
    	
    	$(this).parents('fieldset').fadeOut(400, function() {
    		// change icons
    		current_active_step.removeClass('active').prev().removeClass('activated').addClass('active');
    		// progress bar
    		bar_progress(progress_line, 'left');
    		// show previous step
    		$(this).prev().fadeIn();
    		// scroll window to beginning of the form
			scroll_to_class( $('.f1'), 20 );
    	});
    });
    
    // submit
    $('.f1').on('submit', function(e) {
    	
    	// fields validation
    	$(this).find('input[type="text"], input[type="password"], textarea').each(function() {
    		if( $(this).val() == "" ) {
    			e.preventDefault();
    			$(this).addClass('input-error');
    		}
    		else {
    			$(this).removeClass('input-error');
    		}
    	});
    	// fields validation
    	
    });
    
});
