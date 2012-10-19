jQuery(document).ready(function($){

	if($(".tagsinput").length > 0){
		$('.tagsinput').tagsInput({width:'auto', height:'auto'});
	}

	$('.scopetag').draggable({
		revert: true
	});
	
	$('.droppable').droppable({
        drop: function (event, ui) {
        	var value = $(ui.draggable).text();
        	var values = $('#tags').val();
        	if(values.search(value) == -1) {
        		$('#tags').addTag(value);
        	}
        }
	});
});