jQuery(document).ready(function($) {
	
	$('.log-nav a').click(function(e) {
		e.preventDefault();
		var logType = $(this).attr('data-type');
		switch (logType) {
			case 'like' : getLikeLogTable(); break;
			case 'comment' : getCommentLogTable(); break;			
			case 'post' : getPostLogTable(); break;
			case 'pointlog' : getPointLogTable(); break;
			case 'overall'	: getOverallLogTable(); break;
			default : break;
		}	
	});	
});

function getPostLogTable() {
	$('.table').hide();	
	$('#postLogTable').show();
	var postTable = $('#postLogTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bLengthChange": false,
		"bDestroy": true,
		"aoColumns": [
		              { "mData": "fanpage_id",  "bVisible": false },
		              { "mData": "facebook_user_id" },
		              { "mData": "facebook_user_name" },
		              { "mData": "activity_type" },
		              { "mData": "event_object", "bVisible": false},
		              { "mData": "target_user_id", "bVisible": false},
		              { "mData": "target_user_name","bVisible": false },
		              { "mData": "created_time" },
		              { "mData": "message" }
		          ],
		"sAjaxSource": "/admin/dashboard/showlog/" +fanpageId+'?logType=post'
	});
}

function getLikeLogTable() {
	$('.table').hide();
	$('#likeLogTable').show();
	var likeTable = $('#likeLogTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bDestroy": true,
		"bLengthChange": false,
		"aoColumns": [
		              { "mData": "fanpage_id",  "bVisible": false },
		              { "mData": "facebook_user_id" },
		              { "mData": "facebook_user_name" },
		              { "mData": "activity_type" },
		              { "mData": "event_object", "bVisible": false},
		              { "mData": "target_user_id", "bVisible": false},
		              { "mData": "target_user_name","bVisible": false },
		              { "mData": "created_time" },
		              { "mData": "message", "bVisible": false }
		          ],
		"sAjaxSource": "/admin/dashboard/showlog/" +fanpageId+'?logType=like'
	});
}

function getCommentLogTable() {
	$('.table').hide();
	$('#commentLogTable').show();
	var commentTable = $('#commentLogTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bDestroy": true,
		"bLengthChange": false,
		"aoColumns": [
		              { "mData": "fanpage_id",  "bVisible": false },
		              { "mData": "facebook_user_id" },
		              { "mData": "facebook_user_name" },
		              { "mData": "activity_type" },
		              { "mData": "event_object", "bVisible": false},
		              { "mData": "target_user_id", "bVisible": false},
		              { "mData": "target_user_name","bVisible": false },
		              { "mData": "created_time" },
		              { "mData": "message" }
		          ],
		"sAjaxSource": "/admin/dashboard/showlog/" +fanpageId+'?logType=comment'
	});
}

function getPointLogTable() {
	$('.table').hide();
	$('#pointLogTable').show();
	var pointTable = $('#pointLogTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bDestroy": true,
		"bLengthChange": false,
		"aoColumns": [
		              { "mData": "id",  "bVisible": false },
		              { "mData": "fanpage_id", "bVisible": false },
		              { "mData": "object_id" },
		              { "mData": "object_type" },
		              { "mData": "giving_points", "bSortable" : true},
		              { "mData": "bonus" },
		              { "mData": "created_time" },
		              { "mData": "note" }
		          ],
		"sAjaxSource": "/admin/dashboard/showlog/" +fanpageId+'?logType=pointlog'
	});
}

function getOverallLogTable() {
	$('.table').hide();	
	$('#overallLogTable').show();
	var postTable = $('#overallLogTable').dataTable({
		"bProcessing": false,
		"bServerSide": true,
		"bPaginate": false,
		"bFilter": false,
		"bInfo": false,
		"bLengthChange": false,
		"bDestroy": true,
		"aoColumns": [
		              { "mData": "fanpage_id",  "bVisible": false },
		              { "mData": "facebook_user_id" },
		              { "mData": "facebook_user_name" },
		              { "mData": "activity_type" },
		              { "mData": "event_object" },
		              { "mData": "target_user_id" },
		              { "mData": "target_user_name" },
		              { "mData": "created_time" },
		              { "mData": "message" }
		          ],
		"sAjaxSource": "/admin/dashboard/showlog/" +fanpageId+'?logType=overall'
	});
}

