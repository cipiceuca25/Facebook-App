(function($) {

	var settings = {
		data : [],
		autoStart : false,
		controlsPosition : 'TR',
		welcomeMessage : '<h3>Tour</h3><p>Welcome to Fancrank App</p>',
		buttons : {
			next : 'Next',
			prev : 'Previous',
			start : 'Start',
			end : 'End'
		},
		controlsColors : {
			background : 'rgba(71, 101, 142, 0.9)',
			color : '#fff'
		},
		tooltipColors : {
			background : 'rgba(71, 101, 142, 0.9)',//'rgba(255,255,255, 0.80)',
			border : '2px solid rgba(71, 101, 142, 0.9)',
			color : '#fff',
		}
	};

	var options, step, steps;
	var ew, eh, el, et;
	var started = false;
	var direction = 'f';

	var $tooltip = $('<div>', {
		id : 'tourtip',
		class : 'tourtip',
		html : ''
	}).css({
		'display' : 'none',
		'padding' : '8px 18px',
		'position' : 'absolute',
		
		'border-radius' : '5px',
		'font-size' : '12px',
		'box-sizing' : 'border-box',
		'z-index' : 1000
	});

	var methods = {
		init : function(opts) {
			if (started == false) {
				started = true;
				options = $.extend(settings, opts);

				controls = '<div id="tourControls">\
						<div id="tourButtons">\
						<button id="tourPrev" style="display:none" class="btn">'
						+ options.buttons.prev
						+ '</button>\
						<button id="tourNext" class="btn">'
						+ options.buttons.start
						+ '</button>\
						<a href="javascript:void(0);" id="tourEnd" class="btn" style="display:none; float: right">'
						+ options.buttons.end
						+ '</a>\
						</div>\
						<div id="tourText" style="padding:5px">'
						+ options.welcomeMessage
						+ '</div>\
						</div>';
				$controlsCss = {
					'margin-top' : '50px',
					'display' : 'block',
					'position' : 'fixed',
					'width' : '350px',
					'padding' : '10px 20px',
					'border-radius' : '10px',
					'z-index' : 1000,
					
				};
				
				box = '<div id="tourLightBox" style="z-index:999; position:fixed; width:100%;min-height:100%;left:0;top:0"></div>';
				
				$controls = $(controls).css($controlsCss).css(
						options.controlsColors);
				$cpos = methods.getControlPosition(options.controlsPosition);
				$controls.css($cpos);
				$('body').append($controls);
				$('body').append(box);

				$tooltip.css(options.tooltipColors);

				step = -1;
				steps = options.data.length;
				$('body').prepend($tooltip);
				showOverlay();
			}
		},
		next : function() {
			direction = 'f';
			step++;

			if (step == steps) {
				methods.destroy();
			} else {
				$tooltip.hide();
				stepData = options.data[step];
				
				if (step+1 == steps){
					$('#tourPrev').show();
					$('#tourEnd').show();
					$('#tourNext').hide();
				}else if (step <= steps) {
					$('#tourPrev').show();
					$('#tourEnd').show();
					$('#tourNext').show().html(options.buttons.next);
				}

				methods.setTooltip(stepData);
			}
		},
		prev : function() {
			direction = 'b';

			$tooltip.hide();

			if (step < steps) {
				$('#tourNext').show().html(options.buttons.next);
			}

			if (step <= 0) {
				$('#tourPrev').hide();
				$('#tourEnd').hide();
				$('#tourNext').html(options.buttons.start);
				step--;
			} else {
				step--;
				stepData = options.data[step];

				methods.setTooltip(stepData);
			}
		},
		setTooltip : function(stepData) {
			$element = $(stepData.element);

			if (stepData.controlsPosition) {
				methods.setControlsPosition(stepData.controlsPosition);
			}

			if (stepData.tooltip) {
				$tooltip.html(stepData.tooltip);
				text = (typeof stepData.text != 'undefined') ? stepData.text
						: stepData.tooltip;
				$('#tourText').html(text);

				tooltipPos = (typeof stepData.position == 'undefined') ? 'B'
						: stepData.position;
				$pos = methods.getTooltipPosition(tooltipPos, $element);
				
				$tooltip.css({
					'top' : $pos.top + 'px',
					'left' : $pos.left + 'px',
					
				});
				$tooltip.show('fast');
				
				
				/*$.scrollTo($tooltip, 400, {
					offset : -100
				});*/
			}

			if (typeof stepData.callback != 'undefined') {
				if (stepData.callback == 'click') {
					urlRegex = /^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/;
					urlslugRegex = /^[a-z0-9-]+$/;

					if (urlRegex.test($element.attr('href'))
							|| urlslugRegex.test($element.attr('href'))) {
						location.href = $element.attr('href');
					} else {
						$element.trigger('click');
					}
				} else {
					if (typeof eval(stepData.callback) == 'function') {
						eval(stepData.callback);
					}
				}
			}

			if (stepData.next === true) {
				if (direction == 'f') {
					methods.next();
				} else {
					methods.prev();
				}
			}

		},
		setControlsPosition : function(pos) {
			chtml = $controls.html();
			$controls.remove();
			$controls = $(controls).html(chtml);
			$controls = $controls.css($controlsCss).css(options.controlsColors);
			position = methods.getControlPosition(pos);
			$controls.css(position);
			$('body').append($controls);
		},
		getTooltipPosition : function(pos, $e) {
			ew = $element.outerWidth();
			eh = $element.outerHeight();
			el = $element.offset().left;
			et = $element.offset().top;
			
			tw = $tooltip.width() + parseInt($tooltip.css('padding-left'))
					+ parseInt($tooltip.css('padding-right')) + parseInt($tooltip.css('border-width'));
			th = 33;//$tooltip.height() + parseInt($tooltip.css('padding-top')) + parseInt($tooltip.css('padding-bottom'))+ parseInt($tooltip.css('border-width'));
			
			$('.tourArrow').remove();
			tbg = 'rgba(71, 101, 142, 0.9)';//$tooltip.css('background-color');
			
			$upArrow = $('<div class="tourArrow"></div>').css({
				'position' : 'absolute',
				'display' : 'block',
				'width' : '0',
				'height' : '0',
				'border-left' : '5px solid transparent',
				'border-right' : '5px solid transparent',
				'border-bottom' : '5px solid ' + tbg
			});
			$downArrow = $('<div class="tourArrow"></div>').css({
				'position' : 'absolute',
				'display' : 'block',
				'width' : '0',
				'height' : '0',
				'border-left' : '5px solid transparent',
				'border-right' : '5px solid transparent',
				'border-top' : '5px solid ' + tbg
			});
			$rightArrow = $('<div class="tourArrow"></div>').css({
				'position' : 'absolute',
				'display' : 'block',
				'width' : '0',
				'height' : '0',
				'border-top' : '5px solid transparent',
				'border-bottom' : '5px solid transparent',
				'border-left' : '5px solid ' + tbg
			});
			$leftArrow = $('<div class="tourArrow"></div>').css({
				'position' : 'absolute',
				'display' : 'block',
				'width' : '0',
				'height' : '0',
				'border-top' : '5px solid transparent',
				'border-bottom' : '5px solid transparent',
				'border-right' : '5px solid ' + tbg
			});
			switch (pos) {
			case 'BL':
				position = {
					'left' : el,
					'top' : et + eh + 10
				};
				$upArrow.css({
					top : '-5px',
					left : '48%'
				});
				$tooltip.prepend($upArrow);
				break;

			case 'BR':
				position = {
					'left' : el + ew - tw,
					'top' : et + eh + 10
				};
				$upArrow.css({
					top : '-5px',
					left : '48%'
				});
				$tooltip.prepend($upArrow);
				break;
				
			case 'custom':
				position = {
					'left' : el,
					'top' : (et - th) - 10
				};
				$downArrow.css({
					top : th,
					left : '48%'
				});
				$tooltip.append($downArrow);
				break;
			case 'TL-Lowered':
				position = {
					'left' : el,
					'top' : (et - th) + 25
				};
				$downArrow.css({
					top : 33,
					left : '10%'
				});
			
				$tooltip.append($downArrow);
				break;	
			case 'TL':
				position = {
					'left' : el,
					'top' : (et - th) -10
				};
				$downArrow.css({
					top : 33,
					left : '10%'
				});
			
				$tooltip.append($downArrow);
				break;

			case 'TR':
				position = {
					'left' : (el + ew) - tw,
					'top' : et - th - 10
				};
				$downArrow.css({
					top : 33,
					left : '70%'
				});
			
				$tooltip.append($downArrow);
				break;

			case 'RT':
				position = {
					'left' : el + ew + 10,
					'top' : et
				};
				$leftArrow.css({
					left : '-5px'
				});
				$tooltip.prepend($leftArrow);
				break;

			case 'RB':
				position = {
					'left' : el + ew + 10,
					'top' : et + eh - th
				};
				$leftArrow.css({
					left : '-5px'
				});
				$tooltip.prepend($leftArrow);
				break;

			case 'LT':
				position = {
					'left' : (el - tw) - 10,
					'top' : et
				};
				$rightArrow.css({
					right : '-5px'
				});
				$tooltip.prepend($rightArrow);
				break;

			case 'LB':
				position = {
					'left' : (el - tw) - 10,
					'top' : et + eh - th
				};
				$rightArrow.css({
					right : '-5px'
				});
				$tooltip.prepend($rightArrow);
				break;

			case 'B':
				position = {
					'left' : el + ew / 2 - tw / 2,
					'top' : (et + eh) + 10
				};
				$upArrow.css({
					top : '-5px',
					left : '48%'
				});
				$tooltip.prepend($upArrow);
				break;

			case 'L':
				position = {
					'left' : (el - tw) - 10,
					'top' : et + eh / 2 - th / 2
				};
				$rightArrow.css({
					right : '-5px'
				});
				$tooltip.prepend($rightArrow);
				break;

			case 'T':
				position = {
					'left' : el + ew / 2 - tw / 2,
					'top' : (et - th) - 10
				};
				$downArrow.css({
					top : th,
					left : '48%'
				});
				$tooltip.append($downArrow);
				break;
			case 'T-Lowered':
				position = {
					'left' : el + ew / 2 - tw / 2,
					'top' : (et - th) + 10
				};
				$downArrow.css({
					top : th,
					left : '48%'
				});
				$tooltip.append($downArrow);
				break;
			case 'R':
				position = {
					'left' : (el + ew) + 10,
					'top' : et + eh / 2 - th / 2
				};
				$leftArrow.css({
					left : '-5px'
				});
				$tooltip.prepend($leftArrow);
				break;
			}
			
			return position;
		},
		getControlPosition : function(pos) {
			switch (pos) {
			case 'TR':
				pos = {
					'top' : '10px',
					'right' : '10px'
				};
				break;
			case 'custom':
				pos = {
					'top' : '50px',
					'left' : '10px'
				};
				break;
			case 'TL':
				pos = {
					'top' : '10px',
					'left' : '10px'
				};
				break;

			case 'BL':
				pos = {
					'bottom' : '10px',
					'left' : '10px'
				};
				break;

			case 'BR':
				pos = {
					'bottom' : '10px',
					'right' : '10px'
				};
				break;
			}
			return pos;
		},
		destroy : function() {
			$('#tourControls').remove();
			$('#tourtip').remove();
			$tooltip.css({
				'display' : 'none'
			}).html('');
			hideOverlay();
			//$('#dummyPageRow').hide();
			step = -1;
			started = false;
		}
	};

	function showOverlay() {
		var $overlay = '<div id="tour_overlay" class="tourOverlay"></div>';
		$('BODY').prepend($overlay);
	};

	function hideOverlay() {
		$('#tour_overlay').remove();
	};

	$('#tourNext').live('click', function() {		

		switch (step){
			case -1:
				$('#pageTabs').css('z-index','500');
				$('#log-in-out').css('z-index','500');
				
			break;
			case 6:
				
				$('#log-in-out').css('z-index','');
				
				$('#latest-post-container').css('z-index', '500');
				$('#fancrank-feed-container').css('z-index', '500');
				$('#top-post-container').css('z-index', '500');
				$('#top-fan-container').css('z-index', '500');
				
			//	$('#tourControls').css('top', '450px');
			//	FB.Canvas.setSize({ width: 810, height: 2000 });
			//	FB.Canvas.scrollTo(0, 500);
				
			break;
			case 7:
				$('#pageTabs').css('z-index','');
				$('#fancrank-feed-container').css('z-index', '');
				$('#top-post-container').css('z-index', '');
				$('#top-fan-container').css('z-index', '');
			break;
			case 17:
				$('#latest-post-container').css('z-index', '');
				$('#fancrank-feed-container').css('z-index', '500');
				$('#top-post-container').css('z-index', '');
				$('#top-fan-container').css('z-index', '');
				$('#tourControls').css('top', '350px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 350);
			break;
			case 20:

				$('#latest-post-container').css('z-index', '');
				$('#fancrank-feed-container').css('z-index', '');
				$('#top-post-container').css('z-index', '');
				$('#top-fan-container').css('z-index', '500');
				$('#tourControls').css('top', '50px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 0);
			break;
			case 21:
				$('#latest-post-container').css('z-index', '');
				$('#fancrank-feed-container').css('z-index', '');
				$('#top-post-container').css('z-index', '500');
				$('#top-fan-container').css('z-index', '');
				$('#tourControls').css('top', '350px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 350);
			break;
		
			case 22:
				$('#tourControls').css('top', '50px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 0);
				ffb = true;
				ttb = true;
				tcb = true;
				tfdb = true;
				getLeaderboard();

				$('#profile').html('');

				$('#news-feed').html('');
				$('#redeem').html('');
				$('.nav.nav-tabs li:eq(1) a').tab('show'); 
				$('#pageTabs').css('z-index','500');
				$('.top-fan').css('z-index', '500');
				$('.fan-favorite').css('z-index', '500');
				$('.top-talker').css('z-index', '500');
				$('.top-clicker').css('z-index', '500');
				$('.top-followed').css('z-index', '500');
				
			break;
			case 23:
				
				$('#pageTabs').css('z-index','');
				$('.top-fan').css('z-index', '500');
				$('.fan-favorite').css('z-index', '');
				$('.top-talker').css('z-index', '');
				$('.top-clicker').css('z-index', '');
				$('.top-followed').css('z-index', '');
			break;
			case 24:
				$('#tourControls').css('top', '450px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 450);
				$('.top-fan').css('z-index', '');
				$('.fan-favorite').css('z-index', '500');
		
			break;
			case 25:
		
				$('.fan-favorite').css('z-index', '');
				$('.top-talker').css('z-index', '500');
			
			break;
			case 26:
				$('#tourControls').css('top', '750px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 750);
				$('.top-talker').css('z-index', '');
				$('.top-clicker').css('z-index', '500');
			
			break;
			case 27:
				$('.top-clicker').css('z-index', '');
				$('.top-followed').css('z-index', '500');
			break;
			case 29:
				$('#tourControls').css('top', '50px');
				FB.Canvas.setSize({ width: 810, height: 2000 });
				FB.Canvas.scrollTo(0, 0);
				$('.top-followed').css('z-index', '');
				getMyProfile();
				$('#leaderboard').html('');
				$('#news-feed').html('');
				$('#redeem').html('');
				$('.nav.nav-tabs li:eq(2) a').tab('show'); 
				$('#pageTabs').css('z-index','500');
				$('#myprofile').css('z-index', '500');
				$('#recent-activities-container').css('z-index', '500');
			break;
			case 30:
				$('#pageTabs').css('z-index','');
				$('#myprofile').css('z-index', '');
				$('#recent-activities-container').css('z-index', '');
				$('#general-stats-container').css('z-index','500');

			break;
			case 31:
				$('#general-stats-container').css('z-index','');
				$('#level-container h3').css('color', 'white');
				$('#level-container').css('z-index','500');
				
				break;
			case 32:
				$('#level-container').css('z-index','');
				$('#level-container h3').css('color', '');
				$('#points-container').css('z-index','500');
				break;
			case 33:
				$('#points-container').css('z-index','');
				$('#exp-container').css('z-index','500');
				break;
			case 34:
				$('#exp-container').css('z-index','');
				$('#overall-container').css('z-index','500');
				break;
			case 35:
				$('#overall-container').css('z-index','');
				$('#personal-container').css('z-index','500');
				break;
			case 36:
				
				$('#personal-container').css('z-index','');
				$('#social-container').css('z-index','500');
				break;	
			case 37:
				$('#social-container').css('z-index','');
				$('#follow-list-container').css('z-index', '500');
				break;	
			case 39:
				$('#follow-list-container').css('z-index','');
				$('#other-stats-container').css('z-index', '500');
				break;		
			case 40:
				$('#other-stats-container').css('z-index', '');
				$('#recent-activities-container').css('z-index','500');
				break;	
			case 41:
				getRedeem();
				$('#leaderboard').html('');
				$('#news-feed').html('');
				$('#myprofile').html('');
				$('.nav.nav-tabs li:eq(3) a').tab('show'); 
				$('#pageTabs').css('z-index','500');
				break;
			case 42:
				$('#pageTabs').css('z-index','');
				feedLimit = 0;
				getNewsfeed('#news-feed');
				$('.nav.nav-tabs a:first').tab('show');
				$('#leaderboard').html('');
				$('#profile').html('');
				$('#redeem').html('');
				break;
			case 43:
				$('#top-post-container').css('z-index', '500');
				break;
			case 44:
				$('#top-post-container').css('z-index', '');
				userProfile(userId);
				$('.user-profile').css('z-index','500');
				break;
			case 45:
				$('.user-profile').css('z-index','25');
				$('.user-profile').css('display', 'none');
				$('.profile-content').css('display', 'none');
				$('.profile-content').html('');
				break;
			case 46:
				popup_post($('#latestpost').attr('data-post_id'), 10, true);
				$('.user-profile').css('z-index','500');
				break;
			case 47:
				$('.user-profile').css('z-index','25');
				$('.user-profile').css('display', 'none');
				$('.profile-content').css('display', 'none');
				$('.profile-content').html('');
				break;
		}
		methods.next();
		
	});

	$('#tourPrev').live('click', function() {
		methods.prev();
	});

	$('#tourEnd').live('click', function() {
		methods.destroy();
		$('#tourLightBox').remove();
		$('.light-box').css('display', 'none');
		$('#pageTabs').css('z-index','0');
		$('#log-in-out').css('z-index','0');
	});

	$.fn.fancrankTour = function(method) {
		if (methods[method]) {
			return methods[method].apply(this, Array.prototype.slice.call(
					arguments, 1));
		} else if (typeof method === 'object' || !method) {
			return methods.init.apply(this, arguments);
		} else {
			$.error('Method ' + method	+ ' does not exist on jQuery.fancrankTour');
		}
	};

})(jQuery);

// Direct Access
$.fancrankTour = function(opts) {
	$.fn.fancrankTour(opts);
}