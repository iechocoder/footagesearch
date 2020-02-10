<script>
    jQuery(document).ready(function () {

        var user_login_id = jQuery('#user_login_id').val();
        var id = jQuery('#clip_id').val();
        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
        jQuery.ajax({
            url: url,
            type: "POST",
            data: 'id=' + id + '&action=updateViews&user_login_id=' + user_login_id,
            context: document.body,
            success: function () {

            }
        });
    });
</script>

<?php
$newlink = str_replace("s3.amazonaws.com/s3.footagesearch.com", "video.naturefootage.com", $clip['res']);
$newlink1 = str_replace("https", "http", $newlink);
$videolink = explode('?', $newlink1);
?>

<!--
<div class="footagesearch-clips-list-licenses">
    <img src="<?php echo get_template_directory_uri(); ?>/images/royalty_free_46x38.png" width="23"> <?php _e('Royalty Free', 'footagesearch'); ?>
    <img src="<?php echo get_template_directory_uri(); ?>/images/rights_managed_46x38.png" width="23"> <?php _e('Rights Managed', 'footagesearch'); ?>
    <img src="<?php echo get_template_directory_uri(); ?>/images/premium_46x38.png" width="23"> <?php _e('Premium', 'footagesearch'); ?>
    <img src="<?php echo get_template_directory_uri(); ?>/images/gold_46x38.png" width="23"> <?php _e('Gold', 'footagesearch'); ?>
</div>
-->
<div class="clear"></div>
<?php
global $wpdb;
//$con = mysql_connect('54.208.133.139', 'root', 'dUeasQldBNsA');
//$db = mysql_select_db('fsmaster');
//if (!$con) {
//    die('Could not connect: ' . mysql_error());
//}

@session_start();
$_SESSION['cururl'] = "";
unset($_SESSION['cururl']);
$_SESSION['cururl'] = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

function get_client_ip_clip()
{
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if (getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if (getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if (getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if (getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if (getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
    return $ipaddress;
}

$meta = json_decode($clip['metadata']);
$stream = $meta->streams[0];
if (strpos($clip['original_filename'], 'VCW') || strpos($clip['original_filename'], 'VCCW'))
    $clipRotate = true;
?>

<!--<hr class="footagesearch-preview-clip-divider"/>-->
<div class="footagesearch-preview-prev-next">
    <div id="footagesearch-clip-<?php echo $clip['id']; ?>" class="footagesearch-preview-clip"
         style="width: <?php echo $streamW = ($stream->width && $stream->height && (($stream->width < $stream->height && empty($clipRotate)) || ($stream->width > $stream->height && !empty($clipRotate)))) ? '360' : '640'; ?>px;">
        <div class="footagesearch-preview-clip-top">
            <h2><?php echo $clip['code']; ?></h2>
            <?php
            $userdetail = wp_get_current_user();
            $username = $userdetail->user_login;
            $getUserDataByName = "select * from lib_users where login = '" . $username . "' ";
            $getUserResult = execute_query_clip($getUserDataByName);
            $adminUserId = $getUserResult['group_id'];
            ?>

            <?php if ($adminUserId == 1) { ?>

                <script src="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>


                <script>
                    jQuery(document).ready(function () {
                        jQuery('[data-toggle="popover"]').popover({html: true});
                    });
                    function addAdminActions(valCheck) {

                        var clipId = <?php echo $clip['id']; ?>;
                        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
                        jQuery.ajax({
                            url: url,
                            type: "POST",
                            data: 'action=updateAdminAction&id=' + clipId + '&UpdateType=' + valCheck,
                            context: document.body,
                            success: function () {
                                location.reload();

                            }
                        });
                    }
                </script>
                <div class="footagesearch-preview-clip-top marginLeft20">
                    <h2>

                        <?php
                        $getAdminAction = "select admin_action from lib_clips where id = " . $clip['id'] . " ";
                        $getActionrResult = execute_query_clip($getAdminAction);
                        $admin_action = $getActionrResult['admin_action'];


                        if ($admin_action == 1) {
                            $textr = ' (' . 'Added for deletion' . ')   ';
                        } elseif ($admin_action == 2) {
                            $textr = ' (' . 'Added for quality review' . ')   ';
                        } elseif ($admin_action == 3) {
                            $textr = ' (' . 'Added for keywords review' . ')   ';
                        }
                        echo $textr;

                        if ($admin_action != 0) {
                            $clearVal = "<a href='javascript:;' onClick='addAdminActions(0)'>Clear Value</a> </br>";
                        }
                        ?>
                        <a href="javascript:;" data-toggle="popover" data-trigger="focus" title="Actions"
                           data-content="<?= $clearVal; ?><a href='javascript:;' onClick='addAdminActions(1)'>Delete</a> </br><a href='javascript:;'  onClick='addAdminActions(2)'>Quality Review</a> </br><a href='javascript:;'  onClick='addAdminActions(3)'>Keyword Review</a> </br> ">Actions</a>
                        <?php
                        ?>


                    </h2>
                </div>
            <?php } ?>



            <?php if ($adminUserId == 1) { ?>
                <div class="footagesearch-preview-clip-license"><a
                        href="<?php echo get_option('backend_url'); ?>/en/cliplog/edit/<?php echo $clip['id'] ?>">Edit
                        Clip</a></div>
            <?php } ?>


            <?php if ($clip['license'] == 1) { ?>
                <div class="footagesearch-preview-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                    RF
                </div>
            <?php } elseif ($clip['license'] == 2) { ?>
                <?php if ($clip['price_level'] == 4) { ?>
                    <div class="footagesearch-preview-clip-license footagesearch-license-gold">GD</div>
                <?php } elseif ($clip['price_level'] == 3) { ?>
                    <div class="footagesearch-preview-clip-license footagesearch-license-premium">PR</div>
                <?php } else { ?>
                    <div
                        class="footagesearch-preview-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                        RM
                    </div>
                <?php } ?>
            <?php } ?>

            <div class="footagesearch-preview-clip-duration"><?php echo round($clip['duration']) ?> sec</div>
            <div class="clear"></div>
        </div>
        <?php
		
		$user_agent = getenv("HTTP_USER_AGENT");
		if(strpos($user_agent, "Mac") !== FALSE && preg_match('/Firefox/i', $_SERVER['HTTP_USER_AGENT']))
		{
		?>
			
            <div style="height:360px; width:100%;">
                <div id="flashContent">
                </div>
            </div>
            <script type="text/javascript">
                var video_url = encodeURIComponent("<?php echo $videolink[0];//$clip['res']; ?>");
                console.log(video_url);
                var fn = function () {
                    var att = {
                        data: "/wp-content/plugins/footagesearch/swf/main_player.swf",
                        width: "640",
                        height: "360",
                        loop: "true",
                        scale: "showall",
                        volume: 0,
                        quality: "high"
                    };
                    var par = {flashvars: "url=" + video_url};

                    var id = "flashContent";
                    var myObject = swfobject.createSWF(att, par, id);
                };

                swfobject.addDomLoadEvent(fn);
            </script>

        <?php
        } elseif (preg_match('/iPad/i', $_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/iPhone/i', $_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/Android/i', $_SERVER['HTTP_USER_AGENT'])) {
        ?>

            <video id="footagesearch-preview-player<?php echo $clip['id']; ?>"
                   preload="auto"
                   width="100%"
                   height="auto"
                   muted
                   poster="/wp-content/uploads/2015/03/blackonly.png"
                   style="background:transparent no-repeat url('http://dev.naturefootage.com/wp-content/uploads/2015/03/blackonly.png');
                   background-size:100%"
                   controls>
                <source src="<?php echo $videolink[0];//$clip['res']; ?>" type="video/mp4"/>
            </video>

        <?php
        } else {
        ?>

            <video id="footagesearch-preview-player<?php echo $clip['id']; ?>"
                   preload="auto"
                   width="100%"
                   height="auto"
                   muted
                   autoplay="autoplay"
                   controls>
                <source src="<?php echo $videolink[0];//$clip['res']; ?>" type="video/mp4"/>
            </video>

            <?php
        }
        ?>

        <div class="footagesearch-preview-clip-action">
            <!--<div class="footagesearch-clip-preview-play-forward-actions">
                <img id="play_<?php echo $clip['id']; ?>" src="<?php echo get_template_directory_uri(); ?>/images/play_icon.png" alt="" class="footagesearch-clip-preview-play-btn" style="display:none;"><img id="pause_<?php echo $clip['id']; ?>" src="<?php echo get_template_directory_uri(); ?>/images/pause_icon.png" alt="" class="footagesearch-clip-preview-pause-btn"><img id="forward_<?php echo $clip['id']; ?>" src="<?php echo get_template_directory_uri(); ?>/images/forward_icon.png" alt="" class="footagesearch-clip-preview-forward-btn"><img id="forward3x_<?php echo $clip['id']; ?>" src="<?php echo get_template_directory_uri(); ?>/images/forward3x_icon.png" alt="" class="footagesearch-clip-preview-forward3x-btn">
            </div>-->


            <?php
            if (!is_user_logged_in()) {
                ?>
                <div class="footagesearch-clip-cart-clipbin-actions">
                    <?php
                    $sum_query_grid = "select code from lib_clip_rating where code='" . $clip['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating')";
                    $sum_result_grid = count_query_clip($sum_query_grid);
                    $total_likes_grid = $sum_result_grid;
                    $user_grid = wp_get_current_user();
                    $userName_grid = $user_grid->user_login;
                    $getUserDataById = "select * from lib_users where login = '" . $userName_grid . "' ";
                    $run_grid = execute_query_clip($getUserDataById);
                    $userLoginId_grid = $run_grid['id'];
                    $ip_grid = get_client_ip_clip();
                    ?>
                    <input type="hidden" name="user_login_id" value="<?php echo $userLoginId_grid; ?>"
                           id="user_login_id">

                    <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                        <?php
                        if (!empty($userLoginId_grid)) {
                            $query_select_id_grid = "select * from lib_clip_rating where user_id ='" . $userLoginId_grid . "' and code='" . $clip['id'] . "'";
                            $getID = execute_query_clip($query_select_id_grid);
                            $result_select_grid = count_query_clip($query_select_id_grid);
                            if ($result_select_grid) {
                                ?>

                                <div class="inner_like transitiable"><img
                                        onClick="deleteLikes('<?php echo $getID['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                        src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png">
                                </div>
                                <div class="label-likes transitiable"
                                     id="label_likes_grid"><?php echo $total_likes_grid ?> Like(s)
                                </div>
                            <?php } else {
                                ?>
                                <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                        onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getID['id'] ?>');"><img
                                            src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                                </div>
                                <div class="label-likes transitiable"
                                     id="label_likes_grid"><?php echo $total_likes_grid ?> Like(s)
                                </div>
                                <?php
                            }
                        } else {

                            $query_select_ip_grid = "select * from lib_clip_rating where user_id ='" . $ip_grid . "' and code='" . $clip['id'] . "'";
                            $getIpId = execute_query_clip($query_select_ip_grid);
                            $result_select_by_ip_grid = count_query_clip($query_select_ip_grid);
                            if ($result_select_by_ip_grid > 0) {
                                ?>
                                <div class="inner_like transitiable"><img
                                        onClick="deleteLikes('<?php echo $getIpId['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                        src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png">
                                </div>
                                <div class="label-likes transitiable"
                                     id="label_likes_grid"><?php echo $total_likes_grid ?> Like(s)
                                </div>
                                <?php
                            } else {
                                ?>
                                <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                        onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getIpId['id'] ?>');"><img
                                            src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                                </div>
                                <div class="label-likes transitiable"
                                     id="label_likes_grid"><?php echo $total_likes_grid ?> Like(s)
                                </div>
                                <?php
                            }
                        }
                        ?>
                    </div>
                    <?php
                    if ($clip['active'] == 1) {
                        echo ($clip['in_clipbin'] || !empty($_REQUEST['bin'])) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']);
                        echo $clip['in_cart'] ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']);
//                        $get_ip = get_client_ip();
//                        $sql = "SELECT ip_address FROM check_ip where ip_address='" . $get_ip . "'";
//                        $result = mysql_query($sql);
//                        if (mysql_num_rows($result) < 5) {
                        echo '<a class="preview_download" data-clip-id="' . $clip['id'] . '"  href="' . (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']) . '"><img src="' . get_template_directory_uri() . '/images/download_icon.png" alt=""></a>';
                        //  } else {
                        //echo '<a class="preview_download inline1" data-clip-id="' . $clip['id'] . '" target="_blank" href="#inline_content1-' . $clip['id'] . '"><img src="' . get_template_directory_uri() . '/images/download_icon.png" alt=""></a>';
                        //  }
                    }
                    ?>
                </div>
            <?php } else {
            ?>
            <div class="footagesearch-clip-cart-clipbin-actions">
                <?php
                if ($clip['active'] == 1) {
                echo ($clip['in_clipbin'] || !empty($_REQUEST['bin'])) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']);
                echo $clip['in_cart'] ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']);
                ?>
                    <a class="preview_download" data-clip-id="<?php echo $clip['id'] ?>"
                       href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']); ?>"><img
                            src="<?php echo get_template_directory_uri() . '/images/download_icon.png' ?>" alt=""></a>
            </div>
        <?php } ?>

            <?php
            $sum_query_grid = "select code from lib_clip_rating where code='" . $clip['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating')";
            $sum_result_grid = count_query_clip($sum_query_grid);
            $total_likes_grid = $sum_result_grid;


            $user_grid = wp_get_current_user();
            $userName_grid = $user_grid->user_login;
            $getUserDataById = "select * from lib_users where login = '" . $userName_grid . "' ";
            $run_grid = execute_query_clip($getUserDataById);
            $userLoginId_grid = $run_grid['id'];
            $ip_grid = get_client_ip_clip();
            ?>
            <input type="hidden" name="user_login_id" value="<?php echo $userLoginId_grid; ?>" id="user_login_id">

            <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                <?php
                if (!empty($userLoginId_grid)) {
                    $query_select_id_grid = "select * from lib_clip_rating where user_id ='" . $userLoginId_grid . "' and code='" . $clip['id'] . "'";
                    $getgridID = execute_query_clip($query_select_id_grid);
                    $result_select_grid = count_query_clip($query_select_id_grid);
                    if ($result_select_grid) {
                        ?>

                        <div class="inner_like transitiable"><img
                                onClick="deleteLikes('<?php echo $getgridID['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png"></div>
                        <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>
                            Like(s)
                        </div>
                    <?php } else {
                        ?>
                        <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getgridID['id'] ?>');"><img
                                    src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                        </div>
                        <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>
                            Like(s)
                        </div>
                        <?php
                    }
                } else {

                    $query_select_ip_grid = "select * from lib_clip_rating where user_id ='" . $ip_grid . "' and code='" . $clip['id'] . "'";
                    $getipID = execute_query_clip($query_select_ip_grid);
                    $result_select_by_ip_grid = count_query_clip($query_select_ip_grid);
                    if ($result_select_by_ip_grid > 0) {
                        ?>
                        <div class="inner_like transitiable"><img
                                onClick="deleteLikes('<?php echo $getipID['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png"></div>
                        <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>
                            Like(s)
                        </div>
                        <?php
                    } else {
                        ?>
                        <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getipID['id'] ?>');"><img
                                    src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                        </div>
                        <div class="label-likes transitiable" id="label_likes_grid"> Like(s)</div>
                        <?php
                    }
                }
                ?>
            </div>
        </div>
    <?php } ?>

        <div class="clear"></div>

        <!--div class="footagesearch-clip-message-button-block">
                <div class="footagesearch-clip-message-button" data-clip-id="<?php //echo $clip['id'];                    ?>">
                        <img src="<?php //echo get_template_directory_uri();                     ?>/images/add_comment.png" alt="">
                        <span><?php //_e( "Send comment", 'footagesearch' )                     ?><span>
                </div>
        </div-->

    </div>
</div>
<?php if ($prev_clip_link) { ?>
    <a href="<?php echo $prev_clip_link; ?>" class="footagesearch-preview-clip-prev"><img
            src="<?php echo get_template_directory_uri(); ?>/images/prev_btn.jpg"></a>
<?php } ?>
<?php if ($next_clip_link) { ?>
    <a href="<?php echo $next_clip_link; ?>" class="footagesearch-preview-clip-next"><img
            src="<?php echo get_template_directory_uri(); ?>/images/next_btn.jpg"></a>
<?php } ?>
</div>

<!--div class="footagesearch-clip-message-block">
        <textarea class="footagesearch-clip-message-block-textarea"></textarea>
        <div class="clear"></div>
        <button class="footagesearch-clip-message-button-send"><?php //_e( "Send comment", 'footagesearch' )                     ?></button>
</div-->

<hr class="footagesearch-preview-clip-divider"/>

<?php //if($clip['license'] == 1) {    ?>
<!--div class="footagesearch-cart-item-<?php echo $clip['id']; ?> footagesearch-cart-clip-item-<?php echo $clip['id']; ?>">
    <div class="footagesearch-clip-right footagesearch-clip-info footagesearch-preview-clip-info">
        <table>
            <tr>
                <th width="100"><?php _e('Description', 'footagesearch'); ?>:</th>
                <td><?php echo $clip['description']; ?></td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <th><?php _e('License Fee', 'footagesearch'); ?>:</th>
                <td>
<?php
$rf_license_uses = $footagesearch_cart->get_clip_rf_license_uses($clip['id']);
if ($rf_license_uses) {
    ?>
                                                                                                        <select name="license_term[<?php echo $clip['id']; ?>]" class="footagesearch-license-term" onchange="getRFClipPrice(<?php echo $clip['id']; ?>)">
    <?php foreach ($rf_license_uses as $use) { ?>
                                                                                                                                                                                                <option value="<?php echo $use['id']; ?>"<?php if (isset($clip['license_term']) && $clip['license_term'] == $use['id']) echo ' selected="selected"'; ?>><?php if ($use['price']) echo '$' . $use['price'] . ' ';
        echo $use['license']; ?></option>
    <?php } ?>
                                                                                                        </select>
<?php } ?>
                </td>
                <td>&nbsp;</td>
            </tr>

            <tr>
                <th><?php _e('Delivery Options', 'footages'); ?>
                    <td>
<?php
if ($clip['delivery_methods']) {
    if (count($clip['delivery_methods']) > 1) {
        ?>
                                                                                                                                                                                                <select name="delivery_method[<?php echo $clip['id']; ?>]" class="footagesearch-delivery-method" onchange="getDeliveryFormats(<?php echo $clip['id']; ?>, <?php echo $clip['license']; ?>, this)">
        <?php
        list($selected_method) = array_keys($clip['delivery_methods']);
        foreach ($clip['delivery_methods'] as $key => $method) {
            if (isset($method['formats'])) {
                ?>
                                                                                                                                                                                                                                                                                                                                                                            <option value="<?php echo $method['id']; ?>"<?php
                if (isset($clip['delivery_method']) && $clip['delivery_method'] == $method['id']) {
                    $selected_method = $key;
                    echo ' selected="selected"';
                }
                ?>><?php echo $method['title']; ?></option>
                <?php
            }
        }
        ?>
                                                                                                                                                                                                </select>
                                                                                                                                                                                                <br>
                                                                                                                                                                                                <select name="delivery_format[<?php echo $clip['id']; ?>]" class="footagesearch-delivery-format" onchange="getRFClipPrice(<?php echo $clip['id']; ?>)">
                                                                                                                                                                                                    <option value="">-- Select Delivery Option --</option>
        <?php foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) { ?>
                                                                                                                                                                                                                                                                                        <option value="<?php echo $format['id']; ?>"<?php
            if (isset($clip['delivery_format']) && $clip['delivery_format'] == $format['id']) {
                $selected_format = $format_key;
                echo ' selected="selected"';
            }
            ?>><?php if ($clip['license'] != 1) echo '$' . $format['price'] . ' ';
            echo $format['description']; ?></option>
        <?php }
        ?>
                                                                                                                                                                                                </select>
        <?php
    } else {
        list($selected_method) = array_keys($clip['delivery_methods']);
        ?>
                                                                                                                                                                                                <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]" value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">
                                                                                                                                                                                                <select name="delivery_format[<?php echo $clip['id']; ?>]" class="footagesearch-delivery-format" onchange="getRFClipPrice(<?php echo $clip['id']; ?>)">
                                                                                                                                                                                                    <option value="">-- Select Delivery Option --</option>
        <?php foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) { ?>
                                                                                                                                                                                                                                                                                        <option value="<?php echo $format['id']; ?>"<?php
            if (isset($clip['delivery_format']) && $clip['delivery_format'] == $format['id']) {
                $selected_format = $format_key;
                echo ' selected="selected"';
            }
            ?>><?php if ($clip['license'] != 1) echo '$' . $format['price'] . ' ';
            echo $format['description']; ?></option>
        <?php }
        ?>
                                                                                                                                                                                                </select>
    <?php }
    ?>

                                                                                                            <span class="footagesearch-delivery-frame-rate-cont">
    <?php if (isset($selected_format) && isset($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'])) { ?>
                                                                                                                                                                                                    <br>
                                                                                                                                                                                                    <select name="delivery_frame_rate[<?php echo $clip['id']; ?>]" class="footagesearch-delivery-frame-rate">
                                                                                                                                                                                                        <option value="">--Select Frame Rate--</option>
        <?php foreach ($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'] as $frame_rate) { ?>
                                                                                                                                                                                                                                                                                            <option value="<?php echo $frame_rate['id']; ?>"<?php if (isset($clip['delivery_frame_rate']) && $clip['delivery_frame_rate'] == $frame_rate['id']) echo ' selected="selected"'; ?>><?php echo $frame_rate['format']; ?></option>
        <?php }
        ?>
                                                                                                                                                                                                    </select>
    <?php } ?>
                                                                                                                </span>

<?php }
?>
<?php if ($discounts_link) { ?>
                                                                                                            <br><a href="<?php echo $discounts_link; ?>" target="_blank">Discounts</a>
<?php } ?>
                    </td>
                    <td>
                        <div class="footagesearch-license-price-value-cont-wrapper">
                            <div class="footagesearch-license-price-value-cont-bg"<?php if (!$clip['license_price']) echo ' style="display:none;"'; ?>>
                                <div class="footagesearch-license-price-value-cont"><?php if ($clip['license_price']) echo '$' . $clip['license_price']; ?></div>
                            </div>
                        </div>
                    </td>
            </tr>
        </table>
    </div>
    </div-->

<?php //} else {   ?>
<?php $license_description = ''; ?>
<?php if ($clip['active'] == 1) { ?>
    <div
        class="footagesearch-cart-license-use-cont footagesearch-preview-cart-license-use-cont footagesearch-cart-license-use-select">
        <table style="width: 100%">
            <tr>
                <th colspan="2" style="text-align: left;">Pricing Calculator:</th>
            </tr>
            <tr>
                <td class="minW135px padding-left-field-popup"><?php _e('License Fee', 'footagesearch'); ?>:</td>
                <td>
                    <span class="footagesearch-license-price-cont">
                        <?php
                        if ($clip['license'] == 1) {
                            $terms = $footagesearch_cart->get_clip_rf_license_uses($clip['id']);
                            $term = $terms[$clip['license_term']];
                            $clip['license_price'] = ($term['price']) ? $term['price'] : $clip['license_price'];
                        }
                        if ($clip['license_price'] || $clip['total_price']) {
                            if ($clip['license'] == 1) {
                                echo '$' . $clip['license_price'];
                            } else {
                                echo '$' . $clip['total_price'];
                                if ($clip['license_discount'] !== false && $clip['license_old_price'] !== false)
                                    echo ' ($' . $clip['license_old_price'] . ' with ' . $clip['license_discount'] . '% discount)';
                            }
                        } else {
                            if (!empty($clip['display_price'])) {
                                echo '<span><a href="/form-contact-us?modal=1">Contact Us</a> or add clips to Cart to request a Rate Quote</span>';
                            } else {
                                echo '<span>Set License Fee by Choosing License Terms Below</span>';
                            }
                        }
                        ?>
                    </span>
                </td>
            </tr>
            <tr>
                <td class="minW135px padding-left-field-popup">License Terms:</td>
                <!--td width="370"><span class="footagesearch-license-description-cont"><?php echo $license_description ?></span></td-->
                <td>
                    <?php if ($clip['license'] == 1) { ?>
                        <?php
                        $rf_license_uses = $footagesearch_cart->get_clip_rf_license_uses($clip['id']);
                        if ($rf_license_uses) {
                            ?>
                            <select name="license_term[<?php echo $clip['id']; ?>]" data-type="rf"
                                    class="footagesearch-license-term"
                                    onchange="getRFClipPrice(<?php echo $clip['id']; ?>);
                                        getLicenseUseSelect(this, <?php echo $clip['id']; ?>, '<?php echo $clip['duration']; ?>')">
                                <?php foreach ($rf_license_uses as $use) { ?>
                                    <option
                                        value="<?php echo $use['id']; ?>"<?php if (isset($clip['license_term']) && $clip['license_term'] == $use['id']) echo ' selected="selected"'; ?>><?php if ($use['price']) echo '$' . $use['price'] . ' ';
                                        echo $use['license']; ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    <?php } else { ?>
                        <?php if ($license_categories) { ?>
                            <select name="license_category" class="footagesearch-license-category width-select-popup"
                                    onchange="getLicenseUseSelect(this, <?php echo $clip['id']; ?>, '<?php echo $clip['duration']; ?>')">
                                <option value=""><?php _e('-- Select License Category --', 'footagesearch'); ?></option>
                                <?php foreach ($license_categories as $category) { ?>
                                    <option value="<?php echo $category; ?>"
                                            <?php if (isset($cart_license_category) && $cart_license_category == $category) { ?>selected="selected"<?php } ?>><?php echo $category; ?></option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                        <span class="footagesearch-license-use-cont">
                            <?php
                            if (!empty($cart_license_category)) {
                                $uses = $footagesearch_cart->get_license_uses($cart_license_category);
                                if ($uses) {
                                    ?>
                                    <br>
                                    <select name="license_use" class="footagesearch-license-use width-select-popup"
                                            onchange="getLicenseTermSelect(this, <?php echo $clip['id']; ?>, '<?php echo $clip['duration']; ?>', 1)">
                                        <option value="">-- Select Use --</option>
                                        <?php foreach ($uses as $use) { ?>
                                            <option value="<?php echo $use['id']; ?>"
                                                    data='<?php echo json_encode($use); ?>' <?php
                                                    if (isset($cart_license_use) && $cart_license_use == $use['id']) {
                                                    $license_description .= $use['description'];
                                                    ?>selected="selected"<?php } ?>><?php echo $use['use']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <?php
                                }
                            }
                            ?>
                        </span>
                        <span class="footagesearch-license-term-cont">
                            <?php
                            if (isset($cart_license_use)) {
                                $terms = $footagesearch_cart->get_license_terms($cart_license_use);
                                if ($terms) {
                                    ?>
                                    <br>
                                    <select name="license_term" data-type="rm"
                                            class="footagesearch-license-term width-select-popup"
                                            onchange="getClipPrice(<?php echo $clip['id']; ?>);">
                                        <option value="">-- Select Terms --</option>
                                        <?php foreach ($terms as $term) { ?>
                                            <option value="<?php echo $term['id']; ?>"
                                                    data='<?php echo json_encode($term); ?>' <?php
                                                    if (isset($cart_license_term) && $cart_license_term == $term['id']) {
                                                    $license_description .= ';<br>' . $term['territory'] . ' ' . $term['term'] . '.';
                                                    ?>selected="selected"<?php } ?>><?php echo $term['territory'] . ' ' . $term['term']; ?></option>
                                        <?php } ?>
                                    </select>
                                    <?php
                                }
                            }
                            ?>
                        </span>
                        <input type="hidden" class="footagesearch-license-description-input"
                               value="<?php echo $license_description ?>">
                    <?php } ?>
                </td>
                <!--td class="last">
                <?php if ($clip['license_category'] || $clip['license_use'] || $clip['license_term']) { ?>
                    <!--button class="footagesearch-cart-change-license-use">Change License Use</button-->
                <?php } ?>
                <!--/td-->
            </tr>
            <?php if ($clip['license'] != 1) { ?>
                <tr>
                    <td class="minW135px ">License Use:</td>
                    <td width="700"><span
                            class="footagesearch-license-description-cont"><?php echo $license_description ?></span>
                    </td>
                </tr>
                <!--tr class="footagesearch-cart-license-use-select"<?php
                if ($clip['license_category'] || $clip['license_use'] || $clip['license_term']) {
                    echo 'style="display:none;"';
                }
                ?>>
                    <td colspan="3">
                <?php if ($license_categories) { ?>
                                                                                                            <select name="license_category" class="footagesearch-license-category" onchange="getLicenseUseSelect(this, <?php echo $clip['id']; ?>, '<?php echo $clip['duration']; ?>')">
                                                                                                                <option value=""><?php _e('-- Select License Category --', 'footagesearch'); ?></option>
                    <?php foreach ($license_categories as $category) { ?>
                                                                                                                                                                                                    <option value="<?php echo $category; ?>" <?php if (isset($cart_license_category) && $cart_license_category == $category) { ?>selected="selected"<?php } ?>><?php echo $category; ?></option>
                    <?php } ?>
                                                                                                            </select>
                <?php } ?>
                        <span class="footagesearch-license-use-cont">
                <?php
                if (isset($cart_license_category)) {
                    $uses = $footagesearch_cart->get_license_uses($cart_license_category);
                    if ($uses) {
                        ?>
                                                                                                                                                                                                        <br>
                                                                                                                                                                                                        <select name="license_use" class="footagesearch-license-use" onchange="getLicenseTermSelect(this, <?php echo $clip['id']; ?>, '<?php echo $clip['duration']; ?>', 1)">
                                                                                                                                                                                                            <option value="">-- Select Use --</option>
                        <?php foreach ($uses as $use) { ?>
                                                                                                                                                                                                                            <option value="<?php echo $use['id']; ?>" data='<?php echo json_encode($use); ?>' <?php
                            if (isset($cart_license_use) && $cart_license_use == $use['id']) {
                                $license_description .= $use['description'];
                                ?>selected="selected"<?php } ?>><?php echo $use['use']; ?></option>
                        <?php } ?>
                                                                                                                                                                                                        </select>
                        <?php
                    }
                }
                ?>
                            </span>
                            <span class="footagesearch-license-term-cont">
                <?php
                if (isset($cart_license_use)) {
                    $terms = $footagesearch_cart->get_license_terms($cart_license_use);
                    if ($terms) {
                        ?>
                                                                                                                                                                                                        <br>
                                                                                                                                                                                                        <select name="license_term" class="footagesearch-license-term" onchange="getClipPrice(<?php echo $clip['id']; ?>);">
                                                                                                                                                                                                            <option value="">-- Select Terms --</option>
                        <?php foreach ($terms as $term) { ?>
                                                                                                                                                                                                                            <option value="<?php echo $term['id']; ?>" data='<?php echo json_encode($term); ?>' <?php
                            if (isset($cart_license_term) && $cart_license_term == $term['id']) {
                                $license_description .= ';<br>' . $term['territory'] . ' ' . $term['term'] . '.';
                                ?>selected="selected"<?php } ?>><?php echo $term['territory'] . ' ' . $term['term']; ?></option>
                        <?php } ?>
                                                                                                                                                                                                        </select>
                        <?php
                    }
                }
                ?>
                            </span>
                        <input type="hidden" class="footagesearch-license-description-input" value="<?php echo $license_description ?>">
                    </td>
                </tr-->
            <?php } ?>
            <tr>
                <td class="minW135px ">Restrictions:</td>
                <td width="700"><span
                        class="footagesearch-license-restrictions"><?php echo $license_restrictions ?></span></td>
            </tr>
        </table>
    </div>

    <div
        class="footagesearch-cart-item-<?php echo $clip['id']; ?> footagesearch-cart-clip-item-<?php echo $clip['id']; ?>">
        <div class="footagesearch-clip-right footagesearch-clip-info footagesearch-preview-clip-info">
            <table style="width: 100%">
                <!--<tr>
                    <th width="100"><?php _e('Description', 'footagesearch'); ?>:</th>
                    <td><?php echo $clip['description']; ?></td>
                    <td>&nbsp;</td>
                </tr>-->
                <?php if ($clip['license'] != 1) { ?>
                    <tr>
                        <td class="minW135px"><?php _e('Duration Used', 'footagesearch'); ?>:</td>
                        <td width="700" class="footagesearch-license-duration-cont">
                            <?php
                            if (isset($clip['license_use']) && $clip['duration']) {
                                $min_duration = $footagesearch_cart->get_license_use_min_duration($clip['license_use']);
                                $max_duration = $min_duration;
                                if ($max_duration < $clip['duration'])
                                    $max_duration = (int)$clip['duration'];
                                if ($min_duration) {
                                    ?>
                                    <select name="license_duration[<?php echo $clip['id']; ?>]"
                                            class="footagesearch-license-duration width-select-popup "
                                            onchange="getClipPrice(<?php echo $clip['id']; ?>)">
                                        <?php for ($duration = $min_duration; $duration <= $max_duration; $duration++) { ?>
                                            <option value="<?php echo $duration; ?>"
                                                    <?php if (isset($clip['license_duration']) && $clip['license_duration'] == $duration) { ?>selected="selected"<?php } ?>><?php echo $duration; ?>
                                                seconds
                                            </option>
                                        <?php } ?>
                                    </select>
                                    <?php
                                }
                            }
                            ?>
                        </td>
                    </tr>
                <?php } ?>
                <!--Licensed Fee old-->
                <tr>
                    <td class="minW135px"><?php _e('Delivery Options', 'footages'); ?></td>
                    <td><?php //var_dump([$format,$method,$clip]);                  ?>
                        <?php $getPrice = ($clip['license'] == 1) ? 'getRFClipPrice' : 'getClipPrice'; ?>
                        <?php
                        if ($clip['delivery_methods']) {
                            if (count($clip['delivery_methods']) > 1) {
                                ?>
                                <select name="delivery_method[<?php echo $clip['id']; ?>]"
                                        class="footagesearch-delivery-method width-select-popup"
                                        onchange="getDeliveryFormats(<?php echo $clip['id']; ?>, <?php echo $clip['license']; ?>, this, 1)">
                                    <?php
                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    foreach ($clip['delivery_methods'] as $key => $method) {
                                        if (isset($method['formats'])) {
                                            ?>
                                            <option value="<?php echo $method['id']; ?>"<?php
                                            if (isset($clip['delivery_method']) && $clip['delivery_method'] == $method['id']) {
                                                $selected_method = $key;
                                                echo ' selected="selected"';
                                            }
                                            ?>><?php echo $method['title']; ?></option>
                                            <?php
                                        }
                                    }
                                    ?>
                                </select>
                                <br>
                                <select name="delivery_format[<?php echo $clip['id']; ?>]"
                                        class="footagesearch-delivery-format width-select-popup"
                                        onchange="<?php echo $getPrice; ?>(<?php echo $clip['id']; ?>, this)">
                                    <option value="">-- Select Delivery Option --</option>
                                    <?php foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) { ?>
                                        <option value="<?php echo $format['id']; ?>"<?php
                                        if (isset($clip['delivery_format']) && $clip['delivery_format'] == $format['id']) {
                                            $selected_format = $format_key;
                                            echo ' selected="selected"';
                                        }
                                        ?> data-price="<?php echo $format['price']; ?>"
                                                data-description="<?php echo $format['description']; ?>">
                                            <?php
                                            echo $format['description'];
                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
                                                echo ' ($' . $format['price'] . ')';
                                            ?>
                                        </option>
                                    <?php }
                                    ?>
                                </select>
                                <?php
                            } else {
                                list($selected_method) = array_keys($clip['delivery_methods']);
                                ?>
                                <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]"
                                       value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">
                                <select name="delivery_format[<?php echo $clip['id']; ?>]"
                                        class="footagesearch-delivery-format width-select-popup"
                                        onchange="<?php echo $getPrice; ?>(<?php echo $clip['id']; ?>, this)">
                                    <option value="">-- Select Delivery Option --</option>
                                    <?php foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) { ?>
                                        <option value="<?php echo $format['id']; ?>"<?php
                                        if (isset($clip['delivery_format']) && $clip['delivery_format'] == $format['id']) {
                                            $selected_format = $format_key;
                                            echo ' selected="selected"';
                                        }
                                        ?> data-price="<?php echo $format['price']; ?>"
                                                data-description="<?php echo $format['description']; ?>">
                                            <?php
                                            echo $format['description'];
                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
                                                echo ' ($' . $format['price'] . ')';
                                            ?>
                                        </option>
                                    <?php }
                                    ?>
                                </select>
                            <?php }
                            ?>

                            <span class="footagesearch-delivery-frame-rate-cont">
                                <?php if (isset($selected_format) && isset($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'])) { ?>
                                    <br>
                                    <select name="delivery_frame_rate[<?php echo $clip['id']; ?>]"
                                            class="footagesearch-delivery-frame-rate width-select-popup">
                                        <option value="">--Select Frame Rate--</option>
                                        <?php foreach ($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'] as $frame_rate) { ?>
                                            <option
                                                value="<?php echo $frame_rate['id']; ?>"<?php if (isset($clip['delivery_frame_rate']) && $clip['delivery_frame_rate'] == $frame_rate['id']) echo ' selected="selected"'; ?>><?php echo $frame_rate['format']; ?></option>
                                        <?php }
                                        ?>
                                    </select>
                                <?php } ?>
                            </span>

                        <?php }
                        ?>
                        <div><a href="#"
                                class="<?php echo $clip['license'] == 1 ? 'footagesearch-rf-discounts-link' : 'footagesearch-discounts-link'; ?>">Discounts</a>
                        </div>
                        <div style="display: none;" class="footagesearch-discounts-cont"></div>
                    </td>
                    <!--td style="text-align: right;">
                    <!--div class="footagesearch-license-price-value-cont-wrapper">
                        <div class="footagesearch-license-price-value-cont-bg"<?php if (!$clip['total_price']) echo ' style="display:none;"'; ?>>
                            <div class="footagesearch-license-price-value-cont"><?php if ($clip['total_price']) echo '$' . $clip['total_price']; ?></div>
                        </div>
                    </div-->
                    <!--/td-->
                </tr>
                </tr>
            </table>
        </div>

        <div class="clearboth"></div>

    </div>
<?php } ?>

<?php //}  ?>

<?php
//echo '<pre>';
//print_r($clip);
//exit();
?>

<div class="footagesearch-preview-clip-details">
    <h2><?php _e('Clip Details', 'footagesearch'); ?></h2>
    <table>
        <?php
        $clip['source_format_display'] = array();
        if ($clip['source_format']) {
            $clip['source_format_display'][] = $clip['source_format'] . ($clip['camera_chip_size'] ? ' (' . $clip['camera_chip_size'] . ')' : '');
        }
        if ($clip['source_frame_size']) {
            $clip['source_format_display'][] = $clip['source_frame_size'];
        }
        if ($clip['source_frame_rate']) {
            $clip['source_format_display'][] = $clip['source_frame_rate'];
        }
        if ($clip['source_codec']) {
            $clip['source_format_display'][] = $clip['source_codec'];
        }
        if ($clip['bit_depth']) {
            $clip['source_format_display'][] = $clip['bit_depth'];
        }
        if ($clip['color_space']) {
            $clip['source_format_display'][] = $clip['color_space'];
        }
        ?>
        <?php
        $user = wp_get_current_user();
        $userName = $user->user_login;
        $getUserDataById = "select * from lib_users where login = '" . $userName . "' ";
        $run = execute_query_clip($getUserDataById);
        $userLoginId = $run['id'];
        //$ip = get_client_ip();
        ?>
        <input type="hidden" name="user_login_id" id="user_login_id" value="<?php echo $userLoginId; ?>">
        <input type="hidden" name="clip_id" id="clip_id" value="<?php echo $clip['id']; ?>">
        <?php if (!empty($clip['arraySequecne'])) {
            ?>
            <tr>

                <td class="minW135px">Sequence</td>
                <td> <?php foreach ($clip['arraySequecne'] as $key => $data) { ?>
                        <a onclick="window.opener.location.href = ('<?php echo "http://$_SERVER[HTTP_HOST]"; ?>/clipbin?bin=<?php echo $data['id']; ?>&sequence_id=<?php echo $data['id']; ?>');
                            window.close();" href="javascript:;">
                            <?php
                            echo ($key == 0) ? '' : ', ';
                            echo $data['title'];
                            ?>
                        </a>
                    <?php } ?>
                </td>
            </tr>
        <?php }
        ?>
        <?php if ($clip['description']) { ?>
            <tr>
                <td class="minW135px"><?php _e('Description', 'footagesearch'); ?></td>
                <td><?php echo $clip['description']; ?></td>
            </tr>
        <?php } ?>
        <?php if ($clip['license_restrictions']) { ?>
            <tr>
                <td class="minW135px"><?php _e('License Restrictions', 'footagesearch'); ?></td>
                <td><?php echo $clip['license_restrictions']; ?></td>
            </tr>
        <?php } ?>
        <?php if ($clip['source_format_display']) { ?>
            <tr>
                <td class="minW135px"><?php _e('Source Format', 'footagesearch'); ?></td>
                <td><?php echo implode(', ', $clip['source_format_display']); ?></td>
            </tr>
        <?php } ?>
        <?php if ($clip['shot_type_keyword'] || (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00')) { ?>
            <tr>
                <td class="minW135px"><?php _e('The Shot', 'footagesearch'); ?></td>
                <td>
                    <?php
                    if ($clip['shot_type_keyword']) {
                        foreach ($clip['shot_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php if (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00') { ?>
                        <div
                            class="footagesearch-preview-clip-keyword"><?php echo date('d.m.Y', strtotime($clip['film_date'])); ?></div>
                    <?php } ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
        <?php if ($clip['subject_category_keyword'] || $clip['primary_type_keyword'] || $clip['other_type_keyword'] || $clip['appereance_type_keyword'] || $clip['concept_type_keyword']) { ?>
            <tr>
                <td class="minW135px"><?php _e('Subject', 'footagesearch'); ?></td>
                <td>
                    <?php
                    if ($clip['subject_category_keyword']) {
                        foreach ($clip['subject_category_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['primary_type_keyword']) {
                        foreach ($clip['primary_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['other_type_keyword']) {
                        foreach ($clip['other_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['appereance_type_keyword']) {
                        foreach ($clip['appereance_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['concept_type_keyword']) {
                        foreach ($clip['concept_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
        <?php if ($clip['action_type_keyword']) { ?>
            <tr>
                <td class="minW135px"><?php _e('Action', 'footagesearch'); ?></td>
                <td>
                    <?php
                    foreach ($clip['action_type_keyword'] as $keyword) {
                        if ($keyword) {
                            ?>
                            <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                               onclick="window.opener.location.href = this.href;
                                                return false;"
                               class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                            <?php
                        }
                    }
                    ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
        <?php if ($clip['time_type_keyword'] || $clip['habitat_type_keyword'] || $clip['location_type_keyword'] || $clip['country']) { ?>
            <tr>
                <td class="minW135px"><?php _e('Environment', 'footagesearch'); ?></td>
                <td>
                    <?php
                    if ($clip['time_type_keyword']) {
                        foreach ($clip['time_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;" onclick="window.opener.location.href = this.href;
                                                                return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['habitat_type_keyword']) {
                        foreach ($clip['habitat_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php
                    if ($clip['location_type_keyword']) {
                        foreach ($clip['location_type_keyword'] as $keyword) {
                            if ($keyword) {
                                ?>
                                <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                   onclick="window.opener.location.href = this.href;
                                                        return false;"
                                   class="footagesearch-preview-clip-keyword"><?php echo $keyword; ?></a>
                                <?php
                            }
                        }
                    }
                    ?>
                    <?php if ($clip['country']) { ?>
                        <a href="<?php echo(add_query_arg('fs', urlencode($clip['country']), $clips_holder_permalink)); ?>"
                           onclick="window.opener.location.href = this.href;
                                        return false;"
                           class="footagesearch-preview-clip-keyword"><?php echo $clip['country']; ?></a>
                    <?php } ?>
                    <div class="clearfix"></div>
                </td>
            </tr>
        <?php } ?>
    </table>
</div>

<?php

function execute_query_clip($sql)
{
    $con = connect_clip();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    disconnect_clip($con);
    return $row;
}

function count_query_clip($sql)
{
    $con = connect_clip();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_num_rows($result);
    disconnect_clip($con);
    return $row;
}

function connect_clip()
{
    $slaves = array();
    include($_SERVER["DOCUMENT_ROOT"] . '/db-slaves.php');
    $con = mysql_connect($slaves[0]['db_host'], $slaves[0]['db_user'], $slaves[0]['db_password']);
    if (!$con) {
        rigger_error("Problem connecting to server");
    }
    $db = mysql_select_db(BACKEND_DB_NAME, $con);

    if (!$db) {
        trigger_error("Problem selecting database");
    }
    return $con;
}

function disconnect_clip($con)
{
    $discdb = mysql_close($con);
    if (!$discdb) {
        trigger_error("Problem disconnecting database");
    }
}

function execute_update_clip($sql)
{
    $con = connect_clip();
    $result = mysql_query($sql, $con);
    $return_id = mysql_insert_id();
    if (!$result) {
        trigger_error("Problem updating data");
    }
    disconnect_clip($con);
    return $return_id;
}

?>
<style type="text/css">
    #cboxClose {
        display: none !important;
    }

    .popover {
        position: absolute;
        top: 0;
        left: 0;
        z-index: 1060;
        display: none;
        max-width: 276px;
        padding: 1px;
        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
        font-size: 14px;
        font-style: normal;
        font-weight: 400;
        line-height: 1.42857143;
        text-align: left;
        text-align: start;
        text-decoration: none;
        text-shadow: none;
        text-transform: none;
        letter-spacing: normal;
        word-break: normal;
        word-spacing: normal;
        word-wrap: normal;
        white-space: normal;
        background-color: #fff;
        -webkit-background-clip: padding-box;
        background-clip: padding-box;
        border: 1px solid #ccc;
        border: 1px solid rgba(0, 0, 0, .2);
        border-radius: 6px;
        -webkit-box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        box-shadow: 0 5px 10px rgba(0, 0, 0, .2);
        line-break: auto
    }

    .popover.top {
        margin-top: -10px
    }

    .popover.right {
        margin-left: 10px
    }

    .popover.bottom {
        margin-top: 10px
    }

    .popover.left {
        margin-left: -10px
    }

    .popover-title {
        padding: 8px 14px;
        margin: 0;
        font-size: 14px;
        background-color: #f7f7f7;
        border-bottom: 1px solid #ebebeb;
        border-radius: 5px 5px 0 0
    }

    .popover-content {
        padding: 9px 14px
    }

    .popover > .arrow, .popover > .arrow:after {
        position: absolute;
        display: block;
        width: 0;
        height: 0;
        border-color: transparent;
        border-style: solid
    }

    .popover > .arrow {
        border-width: 11px
    }

    .popover > .arrow:after {
        content: "";
        border-width: 10px
    }

    .popover.top > .arrow {
        bottom: -11px;
        left: 50%;
        margin-left: -11px;
        border-top-color: #999;
        border-top-color: rgba(0, 0, 0, .25);
        border-bottom-width: 0
    }

    .popover.top > .arrow:after {
        bottom: 1px;
        margin-left: -10px;
        content: " ";
        border-top-color: #fff;
        border-bottom-width: 0
    }

    .popover.right > .arrow {
        top: 50%;
        left: -11px;
        margin-top: -11px;
        border-right-color: #999;
        border-right-color: rgba(0, 0, 0, .25);
        border-left-width: 0
    }

    .popover.right > .arrow:after {
        bottom: -10px;
        left: 1px;
        content: " ";
        border-right-color: #fff;
        border-left-width: 0
    }

    .popover.bottom > .arrow {
        top: -11px;
        left: 50%;
        margin-left: -11px;
        border-top-width: 0;
        border-bottom-color: #999;
        border-bottom-color: rgba(0, 0, 0, .25)
    }

    .popover.bottom > .arrow:after {
        top: 1px;
        margin-left: -10px;
        content: " ";
        border-top-width: 0;
        border-bottom-color: #fff
    }

    .popover.left > .arrow {
        top: 50%;
        right: -11px;
        margin-top: -11px;
        border-right-width: 0;
        border-left-color: #999;
        border-left-color: rgba(0, 0, 0, .25)
    }

    .popover.left > .arrow:after {
        right: 1px;
        bottom: -10px;
        content: " ";
        border-right-width: 0;
        border-left-color: #fff
    }

    .carousel {
        position: relative
    }

    .carousel-inner {
        position: relative;
        width: 100%;
        overflow: hidden
    }

    .carousel-inner > .item {
        position: relative;
        display: none;
        -webkit-transition: .6s ease-in-out left;
        -o-transition: .6s ease-in-out left;
        transition: .6s ease-in-out left
    }

    #cboxClose {
        display: none !important;
    }

    .footagesearch-clip-play-forward-actions {
        float: left !important;
        min-width: 109px !important;
        text-align: left !important;
    }

    .footagesearch-clip-cart-clipbin-actions {
        margin-left: 0px !important;
    }

    .heartPosition {
        width: 105px;
        float: left;
    }

    .heartPosition img {
        width: 18px;
        margin-top: 2px;
    }

    .inner_like {

        width: 35px;
        float: left;
    }

    .label-likes {
        line-height: 23px;
    }

    .footagesearch-preview-clip h2 {

        padding: 0 10px 0 0;
    }
	
</style>

<?php if (!is_user_logged_in() ) { ?>
<style type="text/css">
.modal .header-social-css {display:block !important;}
.modal #nav-bar .top-bar {display:block !important;}
</style>
<?php } ?>
<script>
    jQuery(document).ready(function () {
        jQuery(document).on("click", "#cboxClose2", function () {
            jQuery.colorbox.close();
        });
        jQuery(".preview_download").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "none");
        });
        jQuery(".info transitiable").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "block");
        });
    });
</script>
<script type="text/javascript">
    function addLikes(id, action, deleteId) {
        jQuery('.demo-table #tutorial-' + id + ' li').each(function (index) {
            jQuery(this).addClass('selected');
            jQuery('#tutorial-' + id + ' #rating').val((index + 1));
            if (index == jQuery('.demo-table #tutorial-' + id + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = jQuery('#user_login_id').val();
        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
        jQuery.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {
                //alert(data);
                var result = data.split(',');
                var response_id = result[1];
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = result[0];
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + id + ' .inner_like').html('<img style="cursor:pointer;" onClick="deleteLikes(\'' + response_id + '\',\'unlike\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        jQuery('#tutorial-' + id + ' .btn-likes').html('<input type="button" title="Like" class="like"  onClick="addLikes(' + id + ',\'like\')" />')
                        likes = likes - 1;
                        break;
                }
                jQuery('#likes-' + id).val(likes);
                if (likes > 0) {
                    jQuery('#tutorial-' + id + ' .label-likes').html(likes + " Like(s)");
                } else {
                    jQuery('#tutorial-' + id + ' .label-likes').html('');
                }
            }
        });
    }

    function deleteLikes(id, action, clipId) {
        jQuery('.demo-table #tutorial-' + clipId + ' li').each(function (index) {
            jQuery(this).addClass('selected');
            jQuery('#tutorial-' + clipId + ' #rating').val((index + 1));
            if (index == jQuery('.demo-table #tutorial-' + clipId + ' li').index(obj)) {
                return false;
            }
        });
        var user_login_id = jQuery('#user_login_id').val();
        var url = '<?php echo get_template_directory_uri(); ?>/ajax_rating.php';
        jQuery.ajax({
            url: url,
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id + '&clip_id=' + clipId,
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {
                var result = data.split(',');
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = result[0];
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-icon.png" />');

                        //likes = likes - 1;
                        break;
                }
                jQuery('#likes-' + clipId).val(likes);
                if (likes > 0) {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                } else {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                }
            }
        });
    }
</script> 