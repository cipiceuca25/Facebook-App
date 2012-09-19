jQuery(document).ready(function($) {

	if ($(".wizard").length > 0) {
		$(".wizard").formwizard(
				{
					formPluginEnabled : true,
					validationEnabled : true,
					focusFirstInput : false,
					validationOptions : {
						highlight : function(label) {
							$(label).closest('.control-group')
									.addClass('error');
						},
						success : function(label) {
							label.addClass('valid').closest(
									'.control-group').addClass(
									'success');
						}
					},
					formOptions : {
						success : function(data) {
							alert(data);
						},
						beforeSubmit : function(data) {
							var data = $('#badgeForm :text').fieldSerialize();
							alert(data);
						},
						dataType : 'json',
						resetForm : true
					}
				});
	}

})