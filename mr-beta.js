jQuery(function($) {
	$('#mr-beta a.mr-beta_header').click(function(e) {
		e.preventDefault();
		var mrBeta = $('#mr-beta');
		if(mrBeta.hasClass('mr-beta_show')) {
			mrBeta.animate({
				height: 40
			}, 500, 'swing', function() {
				mrBeta.removeClass('mr-beta_show');
				$('a.mr-beta_header span').text('+');
			});
		} else {
			mrBeta.animate({
				height: 390
			}, 300, 'swing', function() {
				$('.mr-beta_todo').focus();
				mrBeta.addClass('mr-beta_show');
				$('a.mr-beta_header span').text('-');
			});
		}
	});
	
	$('.mr-beta_name').focus(function() {
		if($(this).val() == "Your Name") {
			$(this).val('');
		}
	}).blur(function() {
		if($(this).val() == '') {
			$(this).val('Your Name');
		}
	});
	
	$('#mr-beta_submit').click(function(e) {
		e.preventDefault();
		
		var screenshot = null;
		
		$('#mr-beta .mr-beta_notice').text("Capturing screenshot...").show().delay(1000).slideUp(function() {
			$('#mr-beta').hide();
			$(window).scrollTop(0);
			html2canvas( [ document.body ], {
	        	onrendered: function(canvas) {
	        	
	        		//https://github.com/37signals/bcx-api/blob/master/sections/attachments.md
	        		var screenshot = canvas.toDataURL();
	        		
		        	$('#mr-beta').show();
		        	
		        	if(true) { // validate
			
						url = window.location.href.split('/');
						baseurl = "http://" + url[2] + "/wp-admin/admin-ajax.php";
						
						$.ajax({
							type: "POST",
							url: baseurl,
							contentType: "application/x-www-form-urlencoded;charset=UTF-8",
							data: {
								action: "submitTodo",
								todo: $('textarea.mr-beta_todo').val(),
								name: $('input.mr-beta_name').val(),
								currentPage: document.URL,
								screenshot: screenshot
							},
							beforeSend: function() {
								$('#mr-beta .mr-beta_notice').text("One moment...");
								$('#mr-beta .mr-beta_notice').show();
							},
							success: function(data) {
								
								var compliments = [
									"You make me wish I had more middle fingers",
									"Your pre-sneeze face freaks me out.",
									"I'm Charlie Sheen crazy for you.",
									"You look nice today."
								];
								
								var randomNum = Math.ceil(Math.random()*(compliments.length));
								var randomCompliment = compliments[randomNum];
								
								//$('body').append($('<img/>').attr({ id: 'screenshot' }));
								//$('#screenshot').attr('src', screenshot);
								
								//alert(JSON.parse(data));
								
								if(data == 201) {
									$('#mr-beta .mr-beta_todo').val('');
									$('#mr-beta .mr-beta_notice').text(randomCompliment).show().delay(2000).slideUp();
								} else {
									$('#mr-beta .mr-beta_notice').text(data);
									$('#mr-beta .mr-beta_notice').show();
								}
								
								
							}
						});
						
					}
		        	
		        }
	        });
		});
		
	});
});