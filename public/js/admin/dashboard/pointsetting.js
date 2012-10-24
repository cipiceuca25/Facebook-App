jQuery(document).ready(function($){

	var defaultMax = 10;
	var defaultMin = 0;
	var defaultStep = 1;
	
	$('#point_like_normal').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_comment_normal').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_post_normal').spinner({
		min: -10,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_like_admin').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_comment_admin').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_bonus_duration').spinner({
		min: 0,
		max: 180,
		step: 1
	});
	
	$('#point_virginity').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
	$('#point_comment_limit').spinner({
		min: defaultMin,
		max: defaultMax,
		step: defaultStep
	});
	
})