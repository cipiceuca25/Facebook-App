<!doctype html>
<!--[if lt IE 7]> <html class="no-js lt-ie9 lt-ie8 lt-ie7" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js lt-ie9 lt-ie8" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js lt-ie9" lang="en"> <![endif]-->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    
    <title>FanCrank</title>
    
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width">
    
    <link type="text/css" rel="stylesheet" href="css/jquery-ui.php">
    <link type="text/css" rel="stylesheet" href="css/reset.css">
    <link type="text/css" rel="stylesheet" href="css/base.css">
    <script src="js/libs/jquery-1.7.1.min.js"></script>
    <script src="js/libs/jquery-ui-1.8.18.min.js"></script>
    <script src="js/libs/modernizr-2.5.3.min.js"></script>
    
    <script>
	$(function() {
		$( "#tabs" ).tabs({
			ajaxOptions: {
				error: function( xhr, status, index, anchor ) {
					$( anchor.hash ).html(
						"Couldn't load this tab. We'll try to fix this as soon as possible. " +
						"If this wouldn't be a demo." );
				}
			}
		});
	});
	</script>
    
    <style>
	
	
	
	</style>
    
</head>

<body>

<div id="wrapper">

	<!--[if lt IE 7]><p class=chromeframe>Your browser is <em>ancient!</em> <a href="http://browsehappy.com/">Upgrade to a different browser</a> or <a href="http://www.google.com/chromeframe/?redirect=true">install Google Chrome Frame</a> to experience this site.</p><![endif]-->

    <header id="custom"></header>
    
    <div id="tabs">
    
    	<nav>
    
            <ul>
                <li id="tabs-newsfeed"><a href="news-feed.php">News Feed</a></li>
                <li id="tabs-topfans"><a href="top-fans.php">Top Fans</a></li>
                <li id="tabs-profile"><a href="my-profile.php">My Profile</a></li>
                <li id="tabs-awards" class="tab-last"><a href="awards.php">Awards</a></li>
            </ul>
        
        </nav>
        
        <div id="tabs-panels">
        
            <div id="ui-tabs-1">
            
            </div><!--/end #ui-tabs-1 -->
            
            <div id="ui-tabs-2">
            
            </div><!--/end #ui-tabs-2 -->
            
            <div id="ui-tabs-3">
            
            </div><!--/end #ui-tabs-3 -->
            
            <div id="ui-tabs-4">
            
            </div><!--/end #ui-tabs-4 -->
        
        </div><!--/end #tabs-panels -->
                
    </div><!--/end #tabs -->
    
</div><!--/end #wrapper -->
    
</body>

</html>