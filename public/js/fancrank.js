
window.fbAsyncInit = function() {
    FB.init({
      appId      : FB_APP_ID, // App ID
      channelUrl : '//' + window.location.host + '/channel.html', // Channel File
      status     : true, // check login status
      cookie     : true, // enable cookies to allow the server to access the session
      xfbml      : true  // parse XFBML
    });

    // Additional initialization code here
  };