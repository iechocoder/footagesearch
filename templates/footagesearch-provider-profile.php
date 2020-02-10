<div class="footagesearch-provider-page" data-provider="<?php echo $provider_info['data']['id'];?>">
    <!--Video-->
   <div class="footagesearch-browse-page-video">
        <div id="footagesearch-browse-page-video-overlay" class="footagesearch-browse-page-video-overlay">

            <h1><?php echo $provider_info['data']['meta']['company_name'];
			//$provider_info['data']['fname'].' '.$provider_info['data']['lname']?>
            </h1>
            <?php echo $avatar;?>
            <!--<?php //var_dump($provider_info);?>-->
             <!--<?php //var_dump($banner); ?>-->
			 <?php $url = 'http://' . $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
			 $parts = explode("/", $url);
			 $getuser = get_user_by( 'login', end($parts));  
			 if(end($parts)=='cfinkbeiner')
			 {
				 $mainvideo = "http://s3.footagesearch.com.s3.amazonaws.com/vbanner/cfinkbeiner.mp4";
				 $mainimage = "http://s3.footagesearch.com.s3.amazonaws.com/vbanner/cfinkbeiner.jpeg";
			 }
			 else
			 {
			 	$mainvideo = get_user_meta($getuser->ID,'contrib-main-video', true); 
			 	$mainimage = get_user_meta( $getuser->ID,'contrib-main-image', true); 
			 }
			 ?>
            <!--<?php //var_dump($galleries);?>-->
            <!--<?php //var_dump($galleries_list);?>-->
        </div>
        <div id="category_circles_scroll"></div>
        
		
        <?php
		if($mainvideo!='' && $mainimage!=''){
		$browser = strpos($_SERVER['HTTP_USER_AGENT'], "Chrome");
		$os = strpos($_SERVER['HTTP_USER_AGENT'], "Macintosh; Intel Mac OS X");
		if(preg_match('/iPad/i',$_SERVER['HTTP_USER_AGENT']) ||
		   preg_match('/iPhone/i',$_SERVER['HTTP_USER_AGENT']) ||
		   preg_match('/Android/i',$_SERVER['HTTP_USER_AGENT']) ||
		   preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT']))
		{

        ?>
        
		<div id="video_cotainer">
        <video id="footagesearch-browse-page-video" class="video-js vjs-default-skin"  
        preload="auto"  
        width="100%" 
        height="auto" 
        poster="<?php echo $mainimage;?>"
        style="background:transparent no-repeat url('<?php echo $mainimage;?>');
        background-size:100%"
        controls="true">
        <source src="<?php echo $mainvideo; ?>" type="video/mp4" />
        </video>
        </div>
        <?php
		} elseif($browser !== false && $os !== false) {
		?>	
        <div id="video_cotainer">
        <video id="footagesearch-browse-page-video" class="video-js vjs-default-skin"  
        preload="auto"  
        width="100%" 
        height="auto" 
        poster="<?php echo $mainimage;?>"
        style="background:transparent no-repeat url('<?php echo $mainimage;?>');
        background-size:100%"
        controls="true">
        <source src="<?php echo $mainvideo; ?>" type="video/mp4" />
        </video>
        </div>
		<?php } else {?>
      	<div id="video_cotainer" style="position:relative">
        <img src="http://cdn.naturefootage.com/wp-content/uploads/2015/06/playicon.png" style="border:0; position:absolute; z-index:9999; top:48%; left:48%;" />
        <video id="footagesearch-browse-page-video" class="video-js vjs-default-skin"  
        preload="auto"  
        width="100%" 
        height="auto" 
        poster="<?php echo $mainimage; ?>"
        style="background:transparent no-repeat url('<?php echo $mainimage; ?>');
        background-size:100%; width:1250px;"
        controls="true">
        <source src="<?php echo $mainvideo; ?>" type="video/mp4" />
        </video>
        </div>
        <?php
        }
        ?>
        <script type="text/javascript">
		function overlayTextPlugin(options) {
			this.on('play', function(e) {
				if(options.containerId) {
					var overlayContainer = document.getElementById(options.containerId);
					overlayContainer.style.display = 'none';
					
				}
			});
			this.on('pause', function(e) {
				var overlayContainer = document.getElementById(options.containerId);
				overlayContainer.style.display = 'block';
			});
		};
		videojs.plugin('overlayTextPlugin', overlayTextPlugin);
		var player = videojs('footagesearch-browse-page-video');
		player.overlayTextPlugin({containerId: 'footagesearch-browse-page-video-overlay'});
		
		    jQuery(document).ready(function($){
        $('#video_cotainer').click( function(){
            $('#footagesearch-browse-page-video').get(0).play();
            $('#video_cotainer').unbind('click');
        });
    });
        </script>
        <?php }else{?>
            <img src="<?php /*echo $blankposter;*/ echo $banner['url'] ?>" width="100%" height="100%" />
        <?php } ?>
        <?php
		if(preg_match('/Firefox/i',$_SERVER['HTTP_USER_AGENT'])){
        ?>
        <style type="text/css">
        .vjs-control-bar{ display:block !important;}
        </style>
        <?php } else { ?>
        <style type="text/css">
        .vjs-control-bar{ display:none !important;}
        </style>
        <?php } ?>
        
    </div>
    <div class="clear"></div>
    <!--End Video-->
    <!--Nav-->
    <div id="nav-bar" class="contain-to-grid shrink" >
        <nav class="top-bar" data-topbar="">
            <ul class="title-area">
                <li class="name">
                    <form class="search-form" method="get" action="http://footagesearch-wp.local/stock-video-footage" role="search">
                        <input class="search-field" type="search" name="fs" placeholder="Search Contributor Collection">
                        <input type="submit" id="footagesearch_searchsubmits" value="" class="footagesearch_searchsubmit">
                    </form>
                </li>
                <li class="toggle-topbar menu-icon"><a href="#"><span>Menu</span></a></li>
            </ul>


            <section class="top-bar-section">
                <!-- Left Nav Section -->
                <ul id="primary-menu" class="left"><li id="menu-item-526" class="menu-item"><a href="#bio" class="scroll">About</a>

                    </li>
                    <li id="menu-item-536" class="menu-item "><a href="#galleries" class="scroll">Galleries</a>

                    </li>
                    <li id="menu-item-551" class="menu-item "><a href="#tags" class="scroll">Subjects</a>

                    </li>
                    <li id="menu-item-557" class="menu-item "><a href="/stock-video-footage/+/<?php echo $provider_info['data']['login'];?>">View All Clips</a>

                    </li>
                </ul>
                <!-- Right Nav Section -->
                <ul id="secondary-menu" class="right">
                    <li class="has-form"><form rel="nofollow"  role="search" method="get" id="footagesearch_searchform" action="/stock-video-footage" class="footagesearch_searchform">
                            <input type="text" placeholder="Search Contributor Collection" value="" name="fs" id="footagesearch_s" class="footagesearch_s">
                            <input type="hidden" name="owner" value="<?php echo $provider_info['data']['login']?>">
                            <input type="submit" id="footagesearch_searchsubmit" value="" class="footagesearch_searchsubmit"></form>
                    </li>
                </ul>

            </section></nav>
    </div>
    <!--End Nav-->
    <!--Desc-->
    <div class="footagesearch-browse-page-banner footagesearch-browse-page-banner-rf" id="bio">
        <div class="footagesearch-browse-page-banner-text-rf">
            <h2>Biography</h2>
            &nbsp;
                <div class="biography">
                    <?php
                        $desc=stripcslashes($provider_info['data']['meta']['description']);
                        $desc=nl2br($desc);
                        echo $desc;
                    ?>
                </div>
            <p class="readmore"><span>Read More</span></p>
        </div>
    </div>
    <!--End Desc-->
    <!--Gallerys-->
    <div class="primary_container galleries" id="galleries">
        <div class="separatorDiv">
            <h3 class="footagesearch-browse-page-list-title"><?php echo $galleries_title; ?></h3>
            <div class="readmore minimenu">
                <!--a href="#sharethis">share this</a>
                <span id="view-galleris" <?php echo ($_REQUEST['view_galleries'] == 'All')? '>view featured galleries' : 'class="featured">view all galleries'?></span-->
            </div>
        </div>
        <?php echo $galleries_list; ?>
    </div>
    <!--End Gallerys-->
    <!--Keywords Cloud-->
    <div class="footagesearch-keywords-cloud" id="tags">
        <div class="primary_container cloud">
            <div class="separatorDiv">
                <h3 class="footagesearch-browse-page-list-title">Cloud tags</h3>
                <div class="readmore minimenu">
                    <span id="view-tags" class="">view all tags</span>
                </div>
            </div>
            <?php
            if(!empty($cloud_tags)){
                $topArr=array();
                $otherArr=array();
                foreach($cloud_tags as $k=>$tag){
                    if($k<100){
                        $topArr[]=$tag;
                    }else{
                        $otherArr[]=$tag;
                    }
                }
                function cmp($a, $b)
                {
                    return strcmp($a["keyword"], $b["keyword"]);
                }
                usort($topArr, "cmp");
                if(!empty($otherArr))
                    usort($otherArr, "cmp");
                foreach($topArr as $k=>$tag){ ?>
                    <span class="keyword"
                          style="font-size:<?php $size = 20 + ($tag['count'] * 1.2); echo ($size > 30)?30:$size;?>px;
                                 display: inline-block <?php //echo ($k<100) ? 'inline-blick' : 'none' ?>;
                                "
                          data-clip-id="<?=$tag['id']?>"
                          data-keyword-id="<?=$tag['keyword_id']?>"
                          data-count="<?=$tag['count']?>"
                        >
                        <a href="/stock-video-footage/<?php echo preg_replace('/\s/','-',str_replace(',', '', $tag['keyword']));?>/<?php echo $provider_info['data']['login'];?>"><?php echo $tag['keyword']?></a>
                    </span>
            <?php
                }
                if(!empty($otherArr)){
                    foreach($otherArr as $k=>$tag){ ?>
                        <span class="keyword"
                              style="font-size:<?php $size = 20 + ($tag['count'] * 1.3); echo ($size > 40)?40:$size;?>px;
                                  display: none <?php //echo ($k<100) ? 'inline-blick' : 'none' ?>;
                                  "
                              data-clip-id="<?=$tag['id']?>"
                              data-keyword-id="<?=$tag['keyword_id']?>"
                              data-count="<?=$tag['count']?>"
                            >
                        <a href="/stock-video-footage/<?php echo preg_replace('/\s/','-',str_replace(',', '', $tag['keyword']));?>/<?php echo $provider_info['data']['login'];?>"><?php echo $tag['keyword']?></a>
                    </span>
                    <?php
                    }
                }
            }
            ?>
        </div>
    </div>
    <!--End Keywords Cloud-->

</div>
<style type="text/css">
.footagesearch-provider-page #nav-bar{ margin-top:-10px !important;}
</style>