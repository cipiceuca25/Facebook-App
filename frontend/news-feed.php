




<style>

.clear {
	clear:both;	
}

.c1 {
	background:#669933; /* DYNAMIC */
}

.c2 {
	background:#006600;
}

#intro {
	position:relative;	
	height:70px;
	margin-bottom:20px;
}

#intro .portrait {
	position:absolute;
	top:0;
	left:4px;
	border:5px solid #006600; /* DYNAMIC */
	width:65px;
	height:65px;
	z-index:2;
}

#intro .portrait img {
	border:5px solid #FF3366; /* DYNAMIC */
	width:55px;
	height:55px;
}

#intro .title {
	position:absolute;
	top:18px;
	left:0;
	font:bold 20px/100% Arial, Helvetica, sans-serif;
	color:#FFF;
	width:700px;
	height:30px;
	padding:10px 0 0 90px;
	
}

#intro .title img {
	padding:0 3px 1px 3px;
}

#top-posts {
	width:260px;
	float:left;
}

.subtitle {
	font:bold 17px/100% Arial, Helvetica, sans-serif;
	color:#FFF;
	text-transform:uppercase;
	text-align:center;
	height:28px;
	padding:12px 0 0 0;
}

.subtitle img {
	padding:0 3px 1px 3px;
}


/* TOP POSTS BUBBLES */
/*==================================================*/

/***** Common Classes ******/

.bubble {
	width:260px;
	margin-top:10px;
	font:12px Arial, Helvetica, sans-serif;
}

.bubble li {
	padding-top:20px;
	margin-bottom:10px;
	background:url(images/bg-bubble-top.png) top center no-repeat;
}

.bubble .post {
	padding:0 20px;
	color:#FFF;	
}

.bubble .post a {
	display:block;
	color:#FFF;
}

.bubble .post .date {
	font-size:11px;	
}

.bubble .post a:hover {
	color:#EEE;
	text-decoration:none;
}

.bubble .info {
	position:relative;
	font-weight:bold;
}

.bubble .likes {
	font-weight:normal;	
}

.bubble .photo {
	position:absolute;
}

.bubble .photo img {
	width:30px;
	height:30px;	
}

/***** Chat Right Bubbles ******/

.bubble .chat-right {
	background-color:#277676; /* DYNAMIC */
}

.bubble .chat-right .info {
	background:url(images/bg-bubble-bottom-right.png) top center no-repeat;
}

.bubble .chat-right .name {
	padding:25px 73px 0 0;
	text-align:right;
}

.bubble .chat-right .photo {
	top:28px;
	right:37px;
}

/***** Chat Left Bubbles ******/

.bubble .chat-left {
	background-color:#3399CC; /* DYNAMIC */
}

.bubble .chat-left .info {
	background:url(images/bg-bubble-bottom-left.png) top center no-repeat;
}

.bubble .chat-left .name {
	padding:25px 0 0 73px;
	text-align:left;
}

.bubble .chat-left .photo {
	top:28px;
	left:37px;
}




/* FANCRANK FEED */
/*==================================================*/

#fancrank-feed {
	width:520px;
	float:left;
	margin-left:10px;
}

#fancrank-feed .subtitle {
	text-align:left;
	padding-left:10px;
}

.grey-light {
	background:#EEE;	
}

.grey-dark {
	background:#E1E1E1;	
}

/* POST LISTINGS */
/*==================================================*/

.post-list {
	padding-top:10px;	
}

.post-list li {
	padding:10px;	
}

.post-list .photo {
	float:left;
}

.post-list .photo img {
	width:32px;
	height:32px;	
}

.post-list .post {
	float:left;	
	width:450px;
	margin-left:10px;
}

.post-list .post img {
	width:100%;
	display:block;	
}

.post-list .post a {
	display:block;	
}

.post-list .post a:hover {
	text-decoration:none;
}

.post-list .name {
	font-weight:bold;	
}

.post-list .social {
	clear:both;
	padding:5px 0 0 40px;
}

.post-list .likes {
	margin-left:10px;	
}

.post-list .comment {
	margin:0 10px;	
}
</style>









<div id="intro">

	<div class="portrait"><img src="images/fb-photo01.jpg" /></div>
    
    <div class="title c1"><img src="images/icon-star.png" /> Christine's News Feed <img src="images/icon-star.png" /></div>

</div>

<div id="top-posts">

	<div class="subtitle c2">
    
    	<img src="images/icon-star.png" /> Top Posts this Week <img src="images/icon-star.png" />
    
    </div>

	<ul class="bubble">
    	
        <li class="chat-right c3">
        
        	<div class="post">
        
                <a href=""><img src="images/fb-post01.jpg" /><br /><span class="date">Saturday January 27, 2012</span></a>
                
            </div>
            
            <div class="info">
            
            	<div class="name"><a href="">Rosemarie Kunz</a><br /><img src="images/icon-likes.png" /> 4571 <a href="" class="likes">Like</a></div>
                
                <div class="photo"><a href=""><img src="images/fb-photo03.jpg" /></a></div>
            
            </div>
            
		</li>
        
        <li class="chat-left c4">
        
        	<div class="post">
        
                <a href="">just 5th floor... + it's his job, if the pizza place offers delivery. Offer him or her some water as well.
But on the other hand - if you gonna eat pizza, I guess it's better it's you doing the up's and down's<br /><span class="date">Sunday January 28, 2012</span></a>
           
            </div>
            
            <div class="info">
            
            	<div class="name"><a href="">Maria Kennith</a><br /><img src="images/icon-likes.png" /> 4307 <a href="" class="likes">Like</a></div>
                
                <div class="photo"><a href=""><img src="images/fb-photo02.jpg" /></a></div>
                
            </div>
            
		</li>
        
        <li class="chat-right c3">
        
        	<div class="post">
        
                <a href="">hahah saving the puppy lah...from the floods in Thailand..someone took a pic<br /><span class="date">Thursday January 25, 2012</span></a>
                
            </div>
            
            <div class="info">
            
            	<div class="name"><a href="">Tine Jensen</a><br /><img src="images/icon-likes.png" /> 3914 <a href="" class="likes">Like</a></div>
                
                <div class="photo"><a href=""><img src="images/fb-photo04.jpg" /></a></div>
            
            </div>
            
		</li>
        
    </ul>

</div>

<style>


</style>

<div id="fancrank-feed">

	<div class="subtitle c1">
    
    	<img src="images/icon-star.png" /> Fancrank Feed <img src="images/icon-star.png" />
    
    </div>
    
    <ul class="post-list">
    
    	<li class="grey-light">
        
        	<div class="photo"><a href=""><img src="images/fb-photo05.jpg" /></a></div>
        
        	<div class="post"><a href=""><span class="name">Michael Henderson</span> Quisque ut sem sem. In tempor sodales sem, porta interdum ligula ultrices ut. Proin facilisis massa ac augue lacinia vel adipiscing metus aliquam. Morbi eget metus ant Quisque ut sem sem. In tempor sodales sem, porta interdum ligula ultrices ut. Proin facilisis massa ac augue lacinia vel adipiscing metus aliquam. Morbi eget metus ant</a></div>
            
            <div class="social"><img src="images/icon-likes.png" /> 7 <a href="" class="likes">Like</a> <a href="" class="comment">Comment</a> <span class="date">Saturday January 27, 2012</span></div>
        
        </li>
        
        <li class="grey-dark">
        
        	<div class="photo"><a href=""><img src="images/fb-photo02.jpg" /></a></div>
        
        	<div class="post"><a href=""><span class="name">Jen Varden</span> Quisque ut sem sem. In tempor sodales sem, porta interdum ligula ultrices ut. Proin facilisis massa ac augue lacinia vel adipiscing metus aliquam. Morbi eget metus ant Quisque ut sem sem.</a></div>
            
            <div class="social"><img src="images/icon-likes.png" /> 3 <a href="" class="likes">Like</a> <a href="" class="comment">Comment</a> <span class="date">Saturday January 27, 2012</span></div>
        
        </li>
        
        <li class="grey-light">
        
        	<div class="photo"><a href=""><img src="images/fb-photo03.jpg" /></a></div>
        
        	<div class="post"><a href=""><span class="name">Harry Brown</span> Porta interdum ligula ultrices ut. Proin facilisis massa ac augue lacinia vel adipiscing metus aliquam. Quisque ut sem sem. In tempor sodales sem, porta interdum ligula ultrices ut. Proin facilisis massa ac augue lacinia vel adipiscing metus aliquam. Morbi eget metus ant</a></div>
            
            <div class="social"><img src="images/icon-likes.png" /> 12 <a href="" class="likes">Like</a> <a href="" class="comment">Comment</a> <span class="date">Friday January 26, 2012</span></div>
        
        </li>
        
        <li class="grey-dark">
        
        	<div class="photo"><a href=""><img src="images/fb-photo04.jpg" /></a></div>
        
        	<div class="post"><a href=""><span class="name">Kevin Yonta</span> Quisque ut sem sem. In tempor sodales sem, porta intAdipiscing metus aliquam. Morbi eget metus ante, vitae fermentum orci.</a></div>
            
            <div class="social"><img src="images/icon-likes.png" /> 3 <a href="" class="likes">Like</a> <a href="" class="comment">Comment</a> <span class="date">Saturday January 27, 2012</span></div>
        
        </li>
        
    
    </ul>

</div>

