<div id="footagesearch-clip-preview-dialog"></div>
<div class="footagesearch-header-wrapper">
    <h2 class="search-result-description">
        Viewing <span><?php echo $result['from']; ?></span> to <span><?php echo $result['to']; ?></span> of <span><?php echo $result['total']; ?></span> Video Clips
    </h2>
    <?php //var_dump($result);//var_dump($result['solrdata']['query']);?>

</div>
<?php
global $wpdb;
if($_SERVER['environment'] == 'staging') {

  $conn = mysql_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-nfstage");
    if (mysql_errno()) {
        printf("Connect failed: %s\n",mysql_errno());
        exit();
    }

} elseif($_SERVER['environment'] == 'production') {

  $conn = mysql_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-production");
    if (mysql_errno()) {
        printf("Connect failed: %s\n",mysql_errno());
        exit();
    }
}
@session_start();
	$_SESSION['cururl'] = "";
	unset($_SESSION['cururl']);
	$_SESSION['cururl'] = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

function get_client_ip() {
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
       $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
?>
<!-------------------Search Actions-------------------------->
<div class="container sorting">
    <!--Pagination-->
    <div class="grid-left">
        <?php if($pagination) { ?>
            <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
        <?php } ?>
    </div>
    <!--Sort filters-->
    <div class="grid-right">
        <div class="sort-wrapper padding">
            <div class="footagesearch-clips-list-sort-cont">
                <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                    <?php foreach($sort_options as $sort_option) { ?>
                        <option value="<?php echo $sort_option['link']; ?>"<?php if($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort '.$sort_option['label']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="footagesearch-clips-list-actions-cont">
                <select name="footagesearch_clips_list_actions" class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                    <option value="">Actions</option>
                    <option value="select_all">Select all</option>
                    <option value="deselect_all">Select None</option>
                    <option value="add_selected_to_cart">Add Selected to Cart</option>
                    <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                </select>
            </div>
            <div class="footagesearch-clips-list-perpage-cont">
                <form method="post" name="perpage_form1" id="perpage_form1" action="<?php echo $perpage_form_action; ?>">

                    <select name="perpage" onchange='document.getElementById("perpage_form1").submit();' class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                        <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120 Clips Per Page &nbsp;&nbsp;</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="sort-wrapper">
            <div class="footagesearch-clips-list-toggle-view-cont">
                <form method="post" class="footagesearc-list-view-form">
                    <input type="hidden" name="list_view">
                </form>
                <div class="footagesearch-clips-toggle-list-view<?php if(isset($list_view) && $list_view == 'list') echo ' active'; ?>">&nbsp;</div>
                <div class="footagesearch-clips-toggle-grid-view<?php if(!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">&nbsp;</div>
                <!--div class="clearboth"></div-->
            </div>
        </div>
    </div>
</div>

<div class="clearboth"></div>
<!-------------------END Search Actions-------------------------->
<?php if($drag_and_drop_message) { ?>
    <div class="footagesearch-drag-and-drop-message">For your convenience we implemented Drag and Drop feature for clips to be used with Clipbins on this site. Please register to be able to use it.</div>
<?php } ?>

<div class="footagesearch-clips-<?php echo isset($list_view) ? $list_view : 'grid' ?>">
<?php foreach($result['data'] as $key => $clip){
    $clip['description']=str_replace(array('\'','"','<','>'), "`", $clip['description']);
$clip['clip_shortcode_id'] = $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>



<div class="footagesearch-clip draggable-clip<?php if((!isset($list_view) || $list_view == 'grid') && ($key+1) >= 4 && ($key+1)%4 == 0) echo ' last' ?>" id="footagesearch-clip-<?php echo $clip['id']; ?>">
    <div class="footagesearch-clip-wrapper">
    <div class="footagesearch-clip-top">
        <div class="footagesearch-clip-code"><?php echo $clip['code'] ?></div>

        <?php if($clip['license'] == 1){ ?>
            <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">RF</div>
        <?php } elseif ($clip['license'] == 2) { ?>
            <?php if($clip['price_level'] == 4) { ?>
                <div class="footagesearch-clip-license footagesearch-license-gold">GD</div>
            <?php } elseif($clip['price_level'] == 3) { ?>
                <div class="footagesearch-clip-license footagesearch-license-premium">PR</div>
            <?php } else { ?>
                <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">RM</div>
            <?php } ?>
        <?php } ?>
        <!--
            <?php if($clip['duration']) { ?>
                --><div class="footagesearch-clip-duration"><?php echo round($clip['duration'])?>s</div><!--
            <?php } ?>
            -->
        <div class="cart-green" <?php if(!in_array($clip['id'], $in_cart)){?>style="display:none;"<?}?>></div>
        <div class="bin-green" <?php if(!in_array($clip['id'], $in_clipbin)){?>style="display:none;"<?}?>></div>
        <div class="clear"></div>
    </div>
    <div class="check transitiable"></div>
    <div class="footagesearch-clip-inner">
        <div class="info transitiable">
            <a id="footagesearch-clip-offset-<?php echo $result['from'] + $key; ?>"
               href="<?php echo esc_url(get_permalink($clip_holder['ID']) . '/' . $clip['code']); ?>"
               data-bin-id="<?php echo (empty($_REQUEST['bin']))?'':$_REQUEST['bin'];?>">
                <img src="<?php echo  get_template_directory_uri() . '/images/info.png'; ?>" alt="" class="footagesearch-clip-info-icon" title="">
            </a><?php //var_dump($clip['weight']);// rank?> </div>
        <div class="footagesearch-clip-thumb">
            <input type="hidden" value='<?php echo json_encode($clip); ?>'>
            <!--<video id="footagesearch-thumb-player<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" class="video-js vjs-default-skin" preload="auto" width="216" height="120"
                   poster="<?php echo $clip['thumb']; ?>"
                   data-setup="{}">
                <source src="<?php echo $clip['motion_thumb']; ?>" type='video/mp4' />
            </video>-->
            <img src="<?php echo $clip['thumb']; ?>" ><!--Rank : <?php echo $clip['weight'];?>-->
        </div>
        <div class="footagesearch-clip-action transitiable">
            <div class="footagesearch-clip-play-forward-actions">
                <img id="play_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" src="<?php echo  get_template_directory_uri() . '/images/play_icon.png'?>" alt="" class="footagesearch-clip-play-btn" data-clip='<?php echo json_encode(array('id'=>$clip['id'],'title'=>$clip['code'],'description'=>$clip['description'],'preview'=>$clip['preview'], 'motion_thumb'=>$clip['motion_thumb'], 'source_format'=>$clip['source_format_display'])); ?>'><img id="pause_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" src="<?php echo  get_template_directory_uri() . '/images/pause_icon.png'?>" alt="" class="footagesearch-clip-pause-btn" style="display: none;"><img id="forward_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" src="<?php echo  get_template_directory_uri() . '/images/forward_icon.png'?>" alt="" class="footagesearch-clip-forward-btn"><img id="forward3x_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" src="<?php echo  get_template_directory_uri() . '/images/forward3x_icon.png'?>" alt="" class="footagesearch-clip-forward3x-btn">
            </div>
            <script>
			jQuery( document ).on( "click", "#cboxClose2", function() {

 				jQuery.colorbox.close();
				});
			jQuery(document).ready(function(){

				jQuery( ".preview_download" ).hover(function(){
 				jQuery("#footagesearch-clip-preview").css("display","none");
				});
				jQuery( ".info transitiable" ).hover(function(){
				jQuery("#footagesearch-clip-preview").css("display","block");
				});

				jQuery(".inline").colorbox({inline:true, width:"80%",height:"80%"});
				jQuery(".inline1").colorbox({inline:true, width:"30%"});

				jQuery(".down-<?php echo $clip['id']?>").click(function(event) {
                event.preventDefault();
				var id =this.id;
                var link_target = jQuery(this).attr('href');
                var link_text = jQuery(this).text();
				var current_url = "<?php echo $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"]; ?>";
                var request = jQuery.ajax({
                url: "http://www.naturefootage.com/wp-content/plugins/footagesearch/ajax_files/check_ip.php",
                type: "POST",
                data: {ip: id,currents:current_url},

                });

                request.done(function(msg) {
					//alert(msg);
					var response = eval('('+msg+')');

					if(response.beforecount<=4)
					{
						<?php
						$get_ip_count	=	get_client_ip();
			 			$sql_count = "SELECT limit_ip FROM check_ip where limit_ip='".$get_ip_count."'";
						$result_count = mysql_query($sql_count);
						?>
						//alert(<?php echo mysql_num_rows($result_count); ?>);
						var con = parseInt(5)-parseInt(response.aftercount);
						//alert(con);
						jQuery(".contusercont").html("");
						jQuery(".contusercont").html(con);
                    	window.location.href = link_target;
						jQuery.colorbox.close();
					}
					else
					{
						jQuery.colorbox({width:"30%", inline:true, href:"#inline_content1"});
					}
                });

        });


});
		</script>
            <?php
			if ( !is_user_logged_in() )
			{
			?>
            <div class="footagesearch-clip-cart-clipbin-actions">
                <?php echo in_array($clip['id'], $in_clipbin) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']); echo in_array($clip['id'], $in_cart) ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']); ?>
            <?php
			 $get_ip	=	get_client_ip();
			 $sql = "SELECT limit_ip FROM check_ip where limit_ip='".$get_ip."'";
			 $result = mysql_query($sql);
             if (@mysql_num_rows($result) < 5) {
             ?>
                <a class="preview_download inline" data-clip-id="<?php echo $clip['id']?>"  href="<?php //echo $clip['download'].$userstring;?>#inline_content-<?php echo $clip['id']?>"><img src="<?php echo  get_template_directory_uri() . '/images/download_icon.png'?>" alt=""></a>
               <?php } else { ?>
             <a class="preview_download inline1" data-clip-id="<?php echo $clip['id']?>"  href="<?php //echo $clip['download'].$userstring;?>#inline_content1"><img src="<?php echo  get_template_directory_uri() . '/images/download_icon.png'?>" alt=""></a>
            <?php } ?>
                <a class="preview_download" data-clip-id="<?php echo $clip['id']?>"  href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']);?>"><img src="<?php echo  get_template_directory_uri() . '/images/download_icon.png'?>" alt=""></a>
            </div>
            <?php } else {?>
            <div class="footagesearch-clip-cart-clipbin-actions">
                <?php echo in_array($clip['id'], $in_clipbin) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']); echo in_array($clip['id'], $in_cart) ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']); ?>
                <a class="preview_download" data-clip-id="<?php echo $clip['id']?>"  href="<?php (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']);?>"><img src="<?php echo  get_template_directory_uri() . '/images/download_icon.png'?>" alt=""></a>
            </div>
            <?php } ?>

            <div style='display:none'>
			<div id='inline_content-<?php echo $clip['id']?>' style='padding:10px; background:#fff;'>
			<div style="margin:0 auto; padding:20px 20px 80px 20px;">

      		<h2 style="text-align:center; font-weight:bold; font-size:22px; color:#0B5D4B;">
            <img src="http://dev.naturefootage.com/wp-content/uploads/2015/05/naturelogo.png" /><br/>

        <br/>

        FOOTAGE SEARCH COMPING AND PREVIEW USE AGREEMENT</h2>

      		<p style="text-align:center; font-weight:bold; font-size:16px; line-height:25px;"> THIS IS A LEGAL AND BINDING AGREEMENT (THE “AGREEMENT”) BETWEEN YOU (THE “LICENSEE”) AND  FOOTAGE SEARCH (THE “LICENSOR”).

        THIS AGREEMENT APPLIES TO A LICENSE GRANTED VIA THE INTERNET AND IS APPLICABLE TO ALL  MEDIA ASSET PREVIEW CLIPS (THE “PREVIEW CLIPS”), WHETHER DISTRIBUTED AND/OR VIEWED ONLINE OR VIA DVD, CD-ROM, VIDEO TAPE OR IN ANY OTHER FORMAT.

        FOR PURPOSES OF THIS AGREEMENT, THE TERM “PREVIEW CLIPS” SHALL REFER TO ALL DIGITAL  MEDIA WHICH MAY BE PRESENTED IN FILM, VIDEO OR OTHER VISUAL PRESENTATION, TOGETHER WITH ANY  AUDITORY REPRESENTATION RECORDED IN ANY FORMAT, WHICH APPEARS TO LICENSEE IN A PREVIEW FORMAT WHICH IS EMBEDDED WITH THE FOOTAGE SEARCH WATERMARK.  A PREVIEW CLIP CAN BE  DOWNLOADED FROM LICENSOR’S WEBSITES OR DISTRIBUTED ON A STORAGE DEVICE (E.G., CD, DVD, OR ANY OTHER FORMAT). </p>

      		<ol>

        <li><strong>Grant of Rights.</strong> Licensor hereby grants to Licensee in accordance with the terms and subject to the conditions set forth

          below a limited, non-exclusive, non-sublicensable, non-transferable and non-assignable right and license to download,

          receive, view, edit and use the Preview Clips to the limited extent expressly set forth herein. </li>

        <li><strong>Permitted Usage.</strong> The purpose of this license is solely to provide Licensee with an opportunity to review the Preview Clips offline, separate and apart from reviewing them on Licensor’s websites (collectively, the “Website”). Licensee hereby acknowledges that: (i) its license hereunder is personal and may not be shared with any other person, firm or corporation; (ii) it may review the Preview Clips solely for non-commercial testing, sample and layout purposes, (iii) it may not commercially exploit the Preview Clips in any manner, and (iv) it may not use the Preview Clips for any purpose other than as contemplated in this Agreement.</li>

        <li><strong>Limitations on Reproduction.</strong> Licensee acknowledges and agrees that except as specifically provided in this Agreement: (i)

          no Preview Clips which are licensed hereunder and delivered to Licensee in any format (e.g., downloaded file, DVD, video

          tape or CD-ROM) may be shared or copied with any third party and (ii) Licensee may not post, make available or otherwise

          create a network of servers, including placement on the Internet, either with or without a central location, which enables others to access, share or copy the Preview Clips in any manner and in any format.  In addition to the foregoing, Licensee may not

          incorporate the Preview Clips into any electronic or printed template or application, whereby the purpose of such template

          and/or application is to create multiple reproductions and impressions of the Preview Clips on properties such as, without

          limitation, presentation templates, electronic or standard greeting cards, business cards or any other electronic or printed

          matter. Licensee acknowledges and agrees that except as specifically provided in this Agreement: (i)

          no Preview Clips which are licensed hereunder and delivered to Licensee in any format (e.g., downloaded file, DVD, video

          tape or CD-ROM) may be shared or copied with any third party and (ii) Licensee may not post, make available or otherwise

          create a network of servers, including placement on the Internet, either with or without a central location, which enables others to access, share or copy the Preview Clips in any manner and in any format.  In addition to the foregoing, Licensee may not

          incorporate the Preview Clips into any electronic or printed template or application, whereby the purpose of such template

          and/or application is to create multiple reproductions and impressions of the Preview Clips on properties such as, without

          limitation, presentation templates, electronic or standard greeting cards, business cards or any other electronic or printed

          matter.</li>

        <li><strong>Protection of Licensor’s Website & Preview Clips.</strong> (a) Licensee agrees to protect the integrity of Licensor’s Website and all of the components and applications connected thereto (e.g., ClipBin, Order Page), and thereby not: (i) use any network monitoring or discovery software to determine any of the Website architecture; (ii) use any robot, spider, or other automatic device or manual process to monitor or copy the Website or the Preview Clips thereon without Licensor’s prior written permission; (iii) copy, modify, reproduce, republish, distribute, display or transmit for commercial, non-profit or public purposes all or any portion of the Website or the Preview Clips except to the extent permitted above; (iv) remove Licensor’s watermark on any of the Preview Clips or (v) use or otherwise export or re-export the Website or the Preview Clips in violation of the export control laws and regulations of the United States of America.

          (b) Licensee may not decompile, reverse engineer, disassemble or otherwise reduce to human-readable form any of the Preview Clips which are delivered to Licensee via download, DVD, video tape, CD-ROM, or in any other format.  All rights in and to the Preview Clips, the software, the DVDs, the video tapes or the CD-ROMs are protected by United States Copyright laws, internationaltreaty provisions and other applicable laws.</li>

        <li>Electronic Storage. For all of the Preview Clips which Licensee takes delivery via download or on DVD, video tapes, CD-

          ROM or in any other format,  Licensee may only download the Preview Clips onto one (1) computer hard drive or other

          computer medium and may not otherwise make, use or distribute copies of the Preview Clips for any purpose except as

          otherwise provided in this Agreement.  Notwithstanding the foregoing, Licensee shall be permitted to make one (1) backup

          copy of the Preview Clips for security reasons only.  Upon expiration or earlier termination of this Agreement as provided

          below, Licensee shall promptly delete or destroy the Preview Clips from its computer or other electronic storage system.</li>

        <li><strong>Prohibited Use of the Preview Clips.</strong> Licensee acknowledges and agrees that any pornographic, defamatory, libelous or unlawful use of the Preview Clips is expressly and strictly prohibited, whether such use of the Preview Clips is direct, indirect, in context or juxtaposed with the prohibited subject matter.  In addition to the foregoing, Licensor reserves the right to immediately terminate this Agreement and Licensee’s use of the Preview Clips if Licensor, in its sole discretion, believes that the Preview Clips are being used by Licensee in a light unfavorable or damaging to the Preview Clips, Footage Search or the cinematographer.</li>

        <li><strong>Disclaimer of Warranties.</strong><br/>

          <span style="padding:0px 20px 0px 20px;">(a)</span> LICENSEE’S USE OF THE PREVIEW CLIPS IS AT ITS SOLE RISK AND THE PREVIEW CLIPS ARE PROVIDED ON AN "AS IS" AND "AS AVAILABLE" BASIS. TO THE FULLEST EXTENT PERMISSIBLE PURSUANT TO APPLICABLE LAW, FOOTAGE SEARCH AND ITS AFFILIATES, EMPLOYEES, AGENTS, THIRD PARTY CONTENT  PROVIDERS OR LICENSORS EXPRESSLY DISCLAIM ALL WARRANTIES OF ANY KIND, WHETHER EXPRESS OR IMPLIED, INCLUDING, WITHOUT LIMITATION, THE IMPLIED WARRANTIES OF MERCHANTABILITY, OF SATISFACTORY QUALITY, FITNESS FOR A PARTICULAR PURPOSE, OF QUIET ENJOYMENT, AND NON-INFRINGEMENT OF THE PREVIEW CLIPS.<br/><br/>

          <span style="padding:0px 20px 0px 20px;">(b)</span>

          FOOTAGE SEARCH AND ITS AFFILIATES, EMPLOYEES, AGENTS, THIRD PARTY CONTENT PROVIDERS OR LICENSORS MAKE NO WARRANTY THAT (i) THE PREVIEW CLIPS SHALL MEET LICENSEE’S REQUIREMENTS, (ii) THE PREVIEW CLIPS SHALL BE UNINTERRUPTED, TIMELY, SECURE, ERROR-FREE OR FREE OF VIRUSES OR OTHER HARMFUL COMPONENTS, (iii) THE CONTENT OR THE RESULTS THAT MAY BE OBTAINED FROM USE AND/OR REVIEW OF THE PREVIEW CLIPS SHALL BE ACCURATE OR RELIABLE, (iv) THE QUALITY OF ANY OF THE PREVIEW CLIPS LICENSED BY LICENSEE SHALL MEET ITS EXPECTATIONS, AND (V) ANY ERRORS IN THE PREVIEW CLIPS SHALL BE CORRECTED.<br/><br/>

          <span style="padding:0px 20px 0px 20px;">(c)</span>

          NO ADVICE OR INFORMATION, WHETHER ORAL OR WRITTEN, OBTAINED BY LICENSEE FROM FOOTAGE SEARCH, THROUGH OR FROM THE PREVIEW CLIPS, SHALL CREATE ANY WARRANTY NOT EXPRESSLY STATED IN THIS AGREEMENT.<br/><br/>

          <span style="padding:0px 20px 0px 20px;">(d)</span>

          THIS DISCLAIMER OF LIABILITY APPLIES TO ANY DAMAGES OR INJURY CAUSED BY ANY FAILURE OF PERFORMANCE, ERROR, OMISSION, INTERRUPTION, DELETION, DEFECT, DELAY IN OPERATION OR TRANSMISSION, COMPUTER VIRUS, COMMUNICATION LINE FAILURE, THEFT OR DESTRUCTION OR UNAUTHORIZED ACCESS TO, ALTERATION OF, OR USE OF RECORD, WHETHER FOR BREACH OF CONTRACT, TORTIOUS BEHAVIOR, NEGLIGENCE, OR UNDER ANY OTHER THEORY OR CAUSE OF ACTION. </li>

        <li><strong>Limitation of Liability.</strong>  TO THE EXTENT NOT PROHIBITED BY LAW, IN NO EVENT SHALL LICENSOR BE LIABLE FOR PERSONAL INJURY, OR ANY INCIDENTAL, SPECIAL, INDIRECT, CONSEQUENTIAL OR PUNITIVE DAMAGES WHATSOEVER, INCLUDING WITHOUT LIMITATION, DAMAGES FOR LOSS OF PROFITS, LOSS OF DATA, BUSINESS INTERRUPTION OR ANY OTHER COMMERCIAL DAMAGES OR LOSSES, ARISING OUT OF OR RELATED TO LICENSEE’S USE AND/OR REVIEW OR INABILITY TO USE AND/OR REVIEW THE PREVIEW CLIPS, HOWEVER CAUSED, REGARDLESS OF THE THEORY OF LIABILITY(CONTRACT, TORT OR OTHEWISE) AND EVEN IF LICENSOR HAS BEEN ADVISED OF THE POSSIBILITY OF SUCH DAMAGES. </li>

        <li><strong>Ownershi.</strong>  No ownership of copyright, trademark, service mark, trade dress or any other intellectual property or other proprietary right in and to the Preview Clips or the Website shall pass to Licensee by the granting of the license hereunder.  Except as expressly set forth herein, Licensor grants to Licensee no right or license, express or implied, to the Preview Clips. All rights in the Preview Clips not specifically granted in this Agreement are reserved by Licensor.</li>

        <li><strong>Infringement.</strong> Use and/or reproduction of the Preview Clips in a manner not expressly authorized by this Agreement (i) shall constitute an infringement of the proprietary rights of Licensor and/or the Cinematographer, and (ii) shall result in Licensee incurring or being responsible for any damages resulting from any such unauthorized use or reproduction, including without limitation, damages resulting from any claims for infringement of the intellectual property or proprietary rights in and to the Preview Clips.</li>

        <li><strong>Indemnification.</strong>  Licensee agrees that Licensor shall have no liability whatsoever for any use Licensee makes of the Preview Clips.  Licensee shall indemnify and hold Licensor (including its parent, affiliate and subsidiary companies and their respective directors, officers, employees and agents) harmless from and against any claim for damages, losses or any costs (including reasonable attorney’s fees and disbursements) arising in any manner whatsoever from Licensee’s use of the Preview Clips as well as from Licensee’s failure to comply with the terms and conditions of this Agreement.</li>

        <li><strong>Termination.</strong>  This Agreement shall terminate immediately without notice to Licensee if Licensee breaches or fails to

comply with any provision of this Agreement or the Rules.  However, upon notice of termination by Licensor, Licensee must: (i)

immediately cease using the Preview Clips in any manner and in any format; (ii) promptly return to Licensor any and all DVDs,

video tapes, CD-ROMs or any other media which contain the Preview Clips; and (iii) delete all files containing the Preview

Clips, and any copies and/or derivations thereof, from its computer system.</li>

        <li><strong>Choice of Law; Venue.</strong>  This Agreement will be governed by and construed in accordance with the laws of the State of

California, as applied to agreements entered into and enforced entirely within the State of California between California

residents.  This Agreement shall not be governed by the United Nations Convention on Contracts for the International Sale of

Goods, the application of which is expressly excluded.  All disputes and legal proceedings which may arise hereunder shall be

adjudicated in the County of Alameda in the State of California.</li>

        <li><strong>Assignment.</strong>  This Agreement may not be transferred or assigned by Licensee, whether voluntarily or by operation of law,

without Licensor’s prior written consent.   Licensor may assign its rights and duties hereunder without the prior written consent of Licensee to any person, firm or entity.  This Agreement shall inure to the benefit of, and be binding upon all permitted parties hereto as well as their successors and assigns.</li>

        <li><strong>Miscellaneous.</strong>  Licensee acknowledges that it has read this Agreement, understands its terms and conditions,

and agrees to be bound to its terms and conditions.  Licensee further acknowledges that this Agreement contains the entire understanding of the parties hereto with respect to the subject matter hereof, and supercedes any and all prior agreements, understandings,

promises and representations made by either party to the other concerning the subject matter hereof and the terms and

conditions applicable hereto.  Except as otherwise expressly provided herein, any provision of this Agreement may be

amended or modified only with the written consent of Licensor.   In no event shall any terms and conditions set forth in any

invoice, delivery memo, or other correspondence from Licensee or his/her authorized representative change or modify this

Agreement  The failure of either party to enforce its rights under this Agreement at any time and for any period shall not be

construed as a waiver of such rights.  In the event that any of the provisions of this Agreement shall be held by a court of

competent jurisdiction to be unenforceable, such provisions shall be limited or eliminated to the minimum extent necessary so

that this Agreement shall otherwise remain in full force and effect and remain enforceable. All provisions of this Agreement

relating to warranties, proprietary rights, limitation of liability, and indemnification obligations shall survive the termination or expiration of this Agreement. The section headings used herein are included for reference and convenience only and are not to be used in the interpretation of this Agreement.</li>

      </ol>

    </div>
    		<p style="text-align:center">Footage Search, Inc. (ph): 831.375.2313 (fax): 831.621.9559 (email): <a href="mailto:support@footagesearch.com">support@footagesearch.com</a><br/>

 © 2005-2013 Footage Search. All rights reserved.<br/><br/>

 <img src="http://dev.naturefootage.com/wp-content/uploads/2015/05/naturelogo.png" width="500" height="35" />
 </p>

            <div style="display:inline-block;padding-top:20px;">

            <a class="downloadvideobtn down-<?php echo $clip['id']?>" href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']);?>" id="<?php echo get_client_ip(); ?>" class="down-<?php echo $clip['id']?>">Download Video</a>
            <a class="downloadagreementbtn" href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']);?>" id="<?php echo get_client_ip(); ?>" class="down-<?php echo $clip['id']?>">Download Agreement!</a>
       		<div class="cancelbtn" id="cboxClose2">Cancel</div>
            <div class="remtext">
			You have <span class="contusercont">[<?php echo intval(5)-intval(mysql_num_rows($result)); ?>]</span>
            more downloads before you must <a href="/login?action=register">Register</a> or <a href="/login">Login</a>
            </div>
            </div>
			</div>

            </div>
            <!--<div class="footagesearch-clip-rating rating_bar" title="2 ?? 5">
                <div class="rating_value" style="width:<?php echo 2*20; ?>%">&nbsp;</div>
                <ul id="rating-clip-<?php echo $clip['id']; ?>">
                    <li class="rating_1">&nbsp;</li>
                    <li class="rating_2">&nbsp;</li>
                    <li class="rating_3">&nbsp;</li>
                    <li class="rating_4">&nbsp;</li>
                    <li class="rating_5">&nbsp;</li>
                </ul>
            </div>-->
            <div class="clear"></div>
        </div>
    </div>
    </div>
    <input type="hidden" name="selected_clips[<?php echo $clip['id']; ?>]" value="0" class="footagesearch-clip-input">
</div>





    <?php if(!isset($list_view) || $list_view == 'grid') { ?>
    <?php if(($key+1) >= 4 && ($key+1)%4 == 0) { ?>
        <!--<div class="clear"></div>-->
    <?php } ?>
<?php } else { ?>
    <div class="footagesearch-clip-details">
        <table>
            <?php if($clip['description']) { ?>
            <tr>
                <th><?php _e('Description', 'footagesearch'); ?>:</th>
                <td><?php echo $clip['description']; ?></td>
            </tr>
            <?php } ?>
            <?php if($clip['location']) { ?>
            <tr>
                <th><?php _e('Location', 'footagesearch'); ?>:</th>
                <td><?php echo $clip['location']; ?></td>
            </tr>
            <?php } ?>
            <?php if($clip['camera_format']) { ?>
            <tr>
                <th><?php _e('Source', 'footagesearch'); ?>:</th>
                <td><?php echo $clip['camera_format']; ?></td>
            </tr>
            <?php } ?>
            <?php if($clip['master_format']) { ?>
            <tr>
                <th><?php _e('Master', 'footagesearch'); ?>:</th>
                <td><?php echo $clip['master_format']; ?></td>
            </tr>
            <?php } ?>
        </table>
    </div>
    <div class="clear"></div>
<?php } ?>

<?php } ?>
<div style='display:none'>
<div id='inline_content1' style='padding:10px; background:#fff;'>
<p style="text-align:center;">You have reached your preview download limit of 5 clips. Please register for unlimited preview downloads. Please sign in if you already have an account.</p>
<br/>
<p style="text-align:center;"><a href="/login?action=register">Register</a> | <a href="/login">Login</a></p>
</div>
</div>
<div class="clear"></div>
</div>
<br>
<!-------------------Search Actions-------------------------->
<?php if($result['total']>10){ ?>
<div class="container sorting">
    <!--Pagination-->
    <div class="grid-left">
        <?php if($pagination) { ?>
            <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
        <?php } ?>
    </div>
    <!--Sort filters-->
    <div class="grid-right">
        <div class="sort-wrapper padding">
            <div class="footagesearch-clips-list-sort-cont">
                <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                    <?php foreach($sort_options as $sort_option) { ?>
                        <option value="<?php echo $sort_option['link']; ?>"<?php if($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort '.$sort_option['label']; ?></option>
                    <?php } ?>
                </select>
            </div>
            <div class="footagesearch-clips-list-actions-cont">
                <select name="footagesearch_clips_list_actions" class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                    <option value="">Actions</option>
                    <option value="select_all">Select all</option>
                    <option value="deselect_all">Select None</option>
                    <option value="add_selected_to_cart">Add Selected to Cart</option>
                    <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                </select>
            </div>
            <div class="footagesearch-clips-list-perpage-cont">
                <form method="post" name="perpage_form1" id="perpage_form2" action="<?php echo $perpage_form_action; ?>">

                    <select name="perpage" onchange='document.getElementById("perpage_form2").submit();' class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                        <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100 Clips Per Page &nbsp;&nbsp;</option>
                        <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120 Clips Per Page &nbsp;&nbsp;</option>
                    </select>
                </form>
            </div>
        </div>
        <div class="sort-wrapper">
            <div class="footagesearch-clips-list-toggle-view-cont">
                <form method="post" class="footagesearc-list-view-form">
                    <input type="hidden" name="list_view">
                </form>
                <div class="footagesearch-clips-toggle-list-view<?php if(isset($list_view) && $list_view == 'list') echo ' active'; ?>">&nbsp;</div>
                <div class="footagesearch-clips-toggle-grid-view<?php if(!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">&nbsp;</div>
                <!--div class="clearboth"></div-->
            </div>
        </div>
    </div>
</div>
<?php } ?>
<div class="clearboth"></div>
<!-------------------END Search Actions-------------------------->
<!-- clipPreviewBox -->
<div id="footagesearch-clip-preview"
     data-term="<?php echo $_SESSION['footagesearch_cart_license_term'];?>"
     data-format="<?php echo $_SESSION['footagesearch_cart_license_format'];?>"
     data-use="<?php echo $_SESSION['footagesearch_cart_license_use'];?>"
     data-category="<?php echo $_SESSION['footagesearch_cart_license_category']?>"
     style="display: none;">
    <h6 class="title"></h6>
    <video id="" class="video-js vjs-default-skin" preload="auto" muted width="432" height="auto" data-setup="{}">
        <source src="" type="video/mp4">
    </video>
    <p class="description"></p>
    <p class="source_format"></p>
</div>
<div style='display:none'>
<div id='inline_content1' style='padding:10px; background:#fff;'>
<p style="text-align:center;">You have reached your preview download limit of 5 clips. Please register for unlimited preview downloads. Please sign in if you already have an account.</p>
<br/>
<p style="text-align:center;"><a href="/login?action=register">Register</a> | <a href="/login">Login</a></p>
</div>
</div>
