function renderFBScript() {
	(function(d, s, id) {
		  var js, fjs = d.getElementsByTagName(s)[0];
		  if (d.getElementById(id)) return;
		  js = d.createElement(s); js.id = id;
		  js.src = "//connect.facebook.net/en_US/all.js#xfbml=1&appId=" + appId;
		  fjs.parentNode.insertBefore(js, fjs);
		}(document, 'script', 'facebook-jssdk'));
}

	

jQuery(document).ready(function($){
	renderFBScript();
	if(user_id != '' && pageaccessToken != '') {
		FB.api('/' + user_id + '/feed?access_token='+ pageaccessToken, {limit:5} , function(response){
		       if (response && response.data && response.data.length){

		            var ul = document.getElementById('pagefeed');
		          for (var j=0; j<response.data.length; j++){
		            var feed = response.data[j],
		            li = document.createElement('li'),
		             a = document.createElement('a');
		            a.innerHTML = feed.message;
		            a.href = feed.link;
		            li.appendChild(a);
		            ul.appendChild(li);
		          }
		        }
		});
	};

});

	/*
	FB.ui(
			  {
			    method: 'feed',
			    name: 'Facebook Dialogs',
			    link: 'http://developers.facebook.com/docs/reference/dialogs/',
			    picture: 'http://fbrell.com/f8.jpg',
			    caption: 'Reference Documentation',
			    description: 'Dialogs provide a simple, consistent interface for applications to interface with users.'
			  },
			  function(response) {
			    if (response && response.post_id) {
			      alert('Post was published.');
			    } else {
			      alert('Post was not published.');
			    }
			  }
			);

	});
	*/
