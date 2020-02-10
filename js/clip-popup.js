jQuery(document).ready(function () {
    jQuery('.toggle-inline-popup').colorboxClipData();
});

jQuery.fn.colorboxClipData = function (options) {
    options = options || {};
    var videoSelector = options.videoSelector || '.folding-content video';
    var groupSelector = options.groupSelector || '.toggle-inline-popup';
	if( /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
	{ var wdth='100%';}
	if(!/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent) ) 
 	{ var wdth='90%';}
    jQuery(this).colorbox(
        {	
			
			width: wdth,
            rel : groupSelector,
            current : 'video {current} of {total}',
            onComplete : function () {
                var video = document.querySelector(videoSelector);
                videojs(video, {autoplay: true, controls: true})
            }
        }
    );
}