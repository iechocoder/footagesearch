<?php
@session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');
require_once('../../../../wp-config.php');
if($_SERVER['environment'] == 'staging') {

  	$conn = mysqli_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-nfstage");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}

} elseif($_SERVER['environment'] == 'production') {

  $conn = mysqli_connect("master-aurora-new-cluster.cluster-ciayufran1ab.us-east-1.rds.amazonaws.com", "fsmaster", "FSdbm6512", "fsmaster-production");
	if (mysqli_connect_errno()) {
	    printf("Connect failed: %s\n", mysqli_connect_error());
	    exit();
	}
}
    if (isset($_SESSION['filter_session_array'])) {
        $_SESSION['filter_session_array'] = '';
        unset($_SESSION['filter_session_array']);
    }
    if (isset($_SESSION['keywords_filter_session_array'])) {
        $_SESSION['keywords_filter_session_array'] = '';
        unset($_SESSION['keywords_filter_session_array']);
    }
    $request_params = $_SESSION['request_params'];
    $shortcode_params = $_SESSION['shortcode_params'];
    $result = api_request2($request_params);



    $_SESSION['filter_session_array'] = $result['clips_filters_result'];
    $_SESSION['keywords_filter_session_array'] = $result['keywords_for_filters'];
	$_SESSION['current_words'] = $result['filter']['words']
    $in_clipbin = $_SESSION['in_clipbin'];
    $in_cart = $_SESSION['in_cart'];
    $clip_holder = $_SESSION['clip_holder'];
    $pagination = $_SESSION['pagination'];
    $perpage_form_action = $_SESSION['perpage_form_action'];
    $list_view = $_SESSION['list_view'];
    $userstring = $_SESSION['userstring'];

   foreach ($result['data'] as $key => $clip) {
        $clip['description'] = str_replace(array('\'', '"', '<', '>'), "`", $clip['description']);
        $clip['clip_shortcode_id'] = $shortcode_params['shortcode_id'] . '-' . $clip['id'];


        $sum_query_grid = "select code from lib_clip_rating where code='" . $clip['id'] . "' and (name='user_rating' or name='admin_rating' or name='ip_rating')";
        $sum_result_grid = count_query($sum_query_grid);
        $total_likes_grid = $sum_result_grid;
   //   ob_start();
        ?>


        <div
            class="footagesearch-clip draggable-clip<?php if ((!isset($list_view) || $list_view == 'grid') && ($key + 1) >= 4 && ($key + 1) % 4 == 0) echo ' last' ?>"
            id="footagesearch-clip-<?php echo $clip['id']; ?>">
            <div class="footagesearch-clip-wrapper">
                <div class="footagesearch-clip-top">
                    <div class="footagesearch-clip-code"><?php echo $clip['code'] ?></div>

                    <?php if ($clip['license'] == 1) { ?>
                        <div class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                            RF
                        </div>
                    <?php } elseif ($clip['license'] == 2) { ?>
                        <?php if ($clip['price_level'] == 4) { ?>
                            <div class="footagesearch-clip-license footagesearch-license-gold">GD</div>
                        <?php } elseif ($clip['price_level'] == 3) { ?>
                            <div class="footagesearch-clip-license footagesearch-license-premium">PR</div>
                        <?php } else { ?>
                            <div
                                class="footagesearch-clip-license footagesearch-license-<?php echo $clip['license']; ?>">
                                RM
                            </div>
                        <?php } ?>
                    <?php } ?>
                    <!--
                    <?php if ($clip['duration']) { ?>
                        -->
                    <div class="footagesearch-clip-duration"><?php echo round($clip['duration']) ?>s</div>
                    <!--
                    <?php } ?>
                    -->
                    <div class="cart-green"
                         <?php if (!in_array($clip['id'], $in_cart)) { ?>style="display:none;"<?php } ?>></div>
                    <div class="bin-green"
                         <?php if (!in_array($clip['id'], $in_clipbin)) { ?>style="display:none;"<?php } ?>></div>
                    <div class="clear"></div>
                </div>
                <div class="check transitiable"></div>
                <div class="footagesearch-clip-inner">
                    <div class="info transitiable">
                        <a id="footagesearch-clip-offset-<?php echo $result['from'] + $key; ?>"
                           href="<?php echo esc_url(get_permalink($clip_holder['ID']) . '/' . $clip['code']); ?>"
                           data-bin-id="<?php echo (empty($_REQUEST['bin'])) ? '' : $_REQUEST['bin']; ?>">
                            <img src="<?php echo get_template_directory_uri() . '/images/info.png'; ?>" alt=""
                                 class="footagesearch-clip-info-icon" title="">
                        </a><?php //var_dump($clip['weight']);// rank                                          ?> </div>
                    <div class="footagesearch-clip-thumb">
                        <input type="hidden" value='<?php echo json_encode($clip); ?>'>
                        <!--<video id="footagesearch-thumb-player<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" class="video-js vjs-default-skin" preload="auto" width="216" height="120"
                               poster="<?php echo $clip['thumb']; ?>"
                               data-setup="{}">
                            <source src="<?php echo $clip['motion_thumb']; ?>" type='video/mp4' />
                        </video>-->
                        <img src="<?php echo $clip['thumb']; ?>"><!--Rank : <?php echo $clip['weight']; ?>-->
                    </div>
                    <div class="footagesearch-clip-action transitiable">
                        <div class="footagesearch-clip-play-forward-actions">
                            <?php
                            $clipDivileryMethodData = '';

                            if ($clip['delivery_methods']) {
                                if (count($clip['delivery_methods']) > 1) {

                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    foreach ($clip['delivery_methods'] as $key => $method) {
                                        if (isset($method['formats'])) {
                                            $clipDivileryMethodData .= $method['title'] . " <br> ";
                                        }
                                    }
                                    ?>
                                    <?php
                                    foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) {
                                        $clipDivileryMethodData .= $format['description'] . " <br> ";
//                                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
//                                                                echo ' ($' . $format['price'] . ')';
                                    }
                                    ?>
                                    </select>
                                    <?php
                                } else {
                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    ?>
                                    <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]" value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">

                                    <?php
                                    foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) {
                                        $clipDivileryMethodData .= $format['description'] . " <br> ";
                                    }
                                    ?>

                                <?php }
                                ?>

                                <span class="footagesearch-delivery-frame-rate-cont">
                                    <?php if (isset($selected_format) && isset($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'])) { ?>
                                        <br>


                                        <?php
                                        foreach ($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'] as $frame_rate) {
                                            $clipDivileryMethodData .= $frame_rate['format'] . " <br> ";
                                        }
                                    }
                                    ?>
                                </span>

                            <?php } ?>
                            <?php
                            if (!empty($clip['price_level'])) {
                                if ($clip['price_level'] == 1) {
                                    $priceLevelDisplay = 'Budget';
                                }
                                if ($clip['price_level'] == 2) {
                                    $priceLevelDisplay = 'Standard';
                                }
                                if ($clip['price_level'] == 3) {
                                    $priceLevelDisplay = 'Premium';
                                }
                                if ($clip['price_level'] == 4) {
                                    $priceLevelDisplay = 'Gold';
                                }
                            }
                            ?>

                            <img id="play_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/play_icon.png' ?>" alt=""
                                 class="footagesearch-clip-play-btn"
                                 data-clip='<?php echo json_encode(array('id' => $clip['id'], 'title' => $clip['title'], 'description' => $clip['description'], 'preview' => $clip['preview'], 'motion_thumb' => $clip['motion_thumb'], 'source_format' => $clip['source_format'] . ' ' . $clip['source_frame_size'] . ' ' . $clip['source_frame_rate'], 'country' => $clip['country'], 'location' => $clip['location'], 'dilivery_options' => $clipDivileryMethodData, 'price_level' => $priceLevelDisplay, 'license_restrictions' => $clip['license_restrictions'])); ?>'>
                            <img id="pause_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/pause_icon.png' ?>" alt=""
                                 class="footagesearch-clip-pause-btn" style="display: none;">
                            <img id="forward_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/forward_icon.png' ?>" alt=""
                                 class="footagesearch-clip-forward-btn"><img
                                id="forward3x_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                src="<?php echo get_template_directory_uri() . '/images/forward3x_icon.png' ?>" alt=""
                                class="footagesearch-clip-forward3x-btn">
                            <?php
                            $user_grid = wp_get_current_user();
                            $userName_grid = $user_grid->user_login;
                            $getUserDataById = "select * from lib_users where login = '" . $userName_grid . "' ";
                            $run_grid = execute_query($getUserDataById);
                            $userLoginId_grid = $run_grid['id'];
                            $ip_grid = get_client_ip();
                            ?>
                            <input type="hidden" name="user_login_id" value="<?php echo $userLoginId_grid; ?>"
                                   id="user_login_id">

                            <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                                <?php
                                if (!empty($userLoginId_grid)) {
                                    $query_select_id_grid = "select * from lib_clip_rating where user_id ='" . $userLoginId_grid . "' and code='" . $clip['id'] . "'";
                                    $getID = execute_query($query_select_id_grid);
                                    $result_select_grid = count_query($query_select_id_grid);
                                    if ($result_select_grid) {
                                        ?>

                                        <div class="inner_like transitiable"><img
                                                onClick="deleteLikes('<?php echo $getID['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png">
                                        </div>
                                        <!--                            <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>  Like(s)</div>-->
                                    <?php } else {
                                        ?>
                                        <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                                onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getID['id'] ?>');"><img
                                                    src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                                        </div>
                                        <!--                            <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>  Like(s)</div>-->
                                        <?php
                                    }
                                } else {

                                    $query_select_ip_grid = "select * from lib_clip_rating where user_id ='" . $ip_grid . "' and code='" . $clip['id'] . "'";
                                    $getIpId = execute_query($query_select_ip_grid);
                                    $result_select_by_ip_grid = count_query($query_select_ip_grid);
                                    if ($result_select_by_ip_grid > 0) {
                                        ?>
                                        <div class="inner_like transitiable"><img
                                                onClick="deleteLikes('<?php echo $getIpId['id'] ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png">
                                        </div>
                                        <!--                            <div class="label-likes transitiable" id="label_likes_grid"><?php echo $total_likes_grid ?>  Like(s)</div> -->
                                        <?php
                                    } else {
                                        ?>
                                        <div class="inner_like transitiable"><a href="javascript:void(0)"
                                                                                onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '<?php echo $getIpId['id'] ?>');"><img
                                                    src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"></a>
                                        </div>
                                        <!--                            <div class="label-likes transitiable" id="label_likes_grid">  Like(s)</div>-->
                                        <?php
                                    }
                                }
                                ?>
                            </div>

                        </div>
                            <div class="footagesearch-clip-cart-clipbin-actions">
                                <?php
                                echo in_array($clip['id'], $in_clipbin) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']);
                                echo in_array($clip['id'], $in_cart) ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']);
                                ?>
                                <a class="preview_download" data-clip-id="<?php echo $clip['id'] ?>"
                                   href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']); ?>"><img
                                        src="<?php echo get_template_directory_uri() . '/images/download_icon.png' ?>"
                                        alt=""></a>
                            </div>
                    </div>


                    <div class="clear"></div>
                </div>
            </div>
            <input type="hidden" name="selected_clips[<?php echo $clip['id']; ?>]" value="0"
                   class="footagesearch-clip-input">
        </div>








        <?php if (!isset($list_view) || $list_view == 'grid') { ?>
            <?php if (($key + 1) >= 4 && ($key + 1) % 4 == 0) { ?>
                <!--<div class="clear"></div>-->
            <?php } ?>
        <?php } else { ?>
            <?php
            $user = wp_get_current_user();
            $userName = $user->user_login;
            $getUserDataById = "select * from lib_users where login = '" . $userName . "' ";
            $run = execute_query($getUserDataById);
            $userLoginId = $run['id'];
            $ip = get_client_ip();
            ?>
            <div class="footagesearch-clip-details">
                <table class="demo-table">
                    <?php if ($clip['description']) { ?>
                        <tr>
                            <th><?php _e('Description', 'footagesearch'); ?>:</th>
                            <td><?php echo $clip['description']; ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($clip['license_restrictions']) { ?>
                        <tr>
                            <th><?php _e('License Restriction', 'footagesearch'); ?>:</th>
                            <td><?php echo $clip['license_restrictions']; ?></td>
                        </tr>
                    <?php } ?>

                    <?php if ($clip['country']) { ?>
                        <tr>
                            <th><?php _e('Location', 'footagesearch'); ?>:</th>
                            <td><?php echo $clip['country'] . ", " . $clip['location'];
                                ?></td>
                        </tr>
                    <?php } ?>
                    <?php if ($clip['camera_format']) { ?>
                        <tr>
                            <th><?php _e('Source', 'footagesearch'); ?>:</th>
                            <td><?php echo $clip['camera_format']; ?></td>
                        </tr>
                    <?php } ?>
                    <?php /* if ($clip['master_format']) { ?>
                      <tr>
                      <th><?php _e('Master', 'footagesearch'); ?>:</th>
                      <td><?php echo $clip['master_format']; ?></td>
                      </tr>
                      <?php } */ ?>


                    <?php if ($clip['price_level']) { ?>
                        <tr>
                            <th><?php _e('Price Level', 'footagesearch'); ?>:</th>
                            <td> <?php
                                if (!empty($clip['price_level'])) {
                                    if ($clip['price_level'] == 1) {
                                        echo 'Budget';
                                    }
                                    if ($clip['price_level'] == 2) {
                                        echo 'Standard';
                                    }
                                    if ($clip['price_level'] == 3) {
                                        echo 'Premium';
                                    }
                                    if ($clip['price_level'] == 4) {
                                        echo 'Gold';
                                    }
                                }
                                ?></td>
                        </tr>
                        <?php
                    }
                    $userdetail = wp_get_current_user();
                    $username = $userdetail->user_login;
                    $getUserDataByName = "select * from lib_users where login = '" . $username . "' ";
                    $getUserResult = execute_query($getUserDataByName);
                    $adminUserId = $getUserResult['group_id'];
                    if ($adminUserId == 1) {
                        if ($clip['source_format']) {
                            ?>
                            <tr>
                                <th><?php _e('Source Format', 'footagesearch'); ?>:</th>
                                <td><?php echo $clip['source_format'] . ',' . $clip['source_frame_size'] . ',' . $clip['source_frame_rate']; ?></td>
                            </tr>
                        <?php } ?>

                        <?php if ($clip['delivery_methods']) { ?>
                            <tr>
                                <th><?php _e('Delivery Options', 'footagesearch'); ?></th>
                                <td><?php //var_dump([$format,$method,$clip]);                                                                                   ?>
                                    <?php $getPrice = ($clip['license'] == 1) ? 'getRFClipPrice' : 'getClipPrice'; ?>
                                    <?php
                                    if ($clip['delivery_methods']) {
                                        if (count($clip['delivery_methods']) > 1) {

                                            list($selected_method) = array_keys($clip['delivery_methods']);
                                            foreach ($clip['delivery_methods'] as $key => $method) {
                                                if (isset($method['formats'])) {
                                                    echo $method['title'] . " <br> ";
                                                }
                                            }
                                            ?>

                                            <br>


                                            <?php
                                            foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) {
                                                echo $format['description'] . " <br> ";
//                                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
//                                                                echo ' ($' . $format['price'] . ')';
                                            }
                                            ?>
                                            </select>
                                            <?php
                                        } else {
                                            list($selected_method) = array_keys($clip['delivery_methods']);
                                            ?>
                                            <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]" value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">

                                            <?php
                                            foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) {
                                                echo $format['description'] . " <br> ";
                                            }
                                            ?>

                                        <?php }
                                        ?>

                                        <span class="footagesearch-delivery-frame-rate-cont">
                                            <?php if (isset($selected_format) && isset($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'])) { ?>
                                                <br>


                                                <?php
                                                foreach ($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'] as $frame_rate) {
                                                    echo $frame_rate['format'] . " <br> ";
                                                }
                                            }
                                            ?>
                                        </span>

                                    <?php } ?>
                                </td>
                            </tr>
                            <?php
                        }
                    }
                    ?>
                </table>
            </div>
            <div class="clear"></div>
        <?php } ?>
    <?php }



    function api_request2($params){
        $backend_url = get_option('backend_url');
        $provider_id = get_option('provider_id');
        $frontend_id = get_option('frontend_id');
        /*echo '<pre>';
        var_export( $params );
        echo '</pre>';*/

        if(!empty($params) && isset($params['method']) && $params['method'] && $backend_url && $provider_id && $frontend_id){
            $lang = 'en';
            $apiurl = trim($backend_url, '/') . '/' . $lang . '/fapi/' . $params['method'] . '/provider/' . $provider_id . '/frontend/' . $frontend_id;
            if(!empty($params['query_params'])){
                //$params['query_params']=$this->addslashes_extended($params['query_params']);
                if(isset($params['query_params']['limit']) && $params['query_params']['limit'] === false){
                    return false;
                }
                $query_params = array();
                foreach($params['query_params'] as $param => $value){
                    $query_params[] = $param . '/' . urlencode($value);
                }
                $apiurl .= '/' . implode('/', $query_params);
            }

            //echo '[ ' . $apiurl . ' ]';

            $post_params = array();
            if(!empty($params['post_params'])){
                //$params['post_params']=$this->addslashes_extended($params['post_params']);
                foreach($params['post_params'] as $param => $value){
                    if(is_array($value)){
                        foreach($value as $value_key => $value_item){
                            $post_params[] = $param . '[' . $value_key . ']=' . urlencode($value_item);
                        }
                    }
                    else{
                        $post_params[] = $param . '=' . urlencode($value);
                    }
                }
                $post_params = implode('&', $post_params);
            }
            //echo $post_params.'<br>';
            $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiurl);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 60);
            if($post_params || $params['method'] == 'clips'){
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $post_params);
            }
            else{
                curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
            }
            curl_setopt($ch, CURLOPT_USERAGENT, $agent);

            //DEBUG
            curl_setopt($ch, CURLOPT_COOKIE, "XDEBUG_SESSION=PHPSTORM");


            $result = curl_exec($ch);
            $http_status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if($params['method'] == 'clips'){
                /*echo '<pre>URL:';
                var_dump($apiurl);
                echo 'POST:';
                var_dump($post_params);
                echo 'RESULT:';
                print_r($result);
                echo '</pre><hr>';
                echo $http_status;
                exit();*/
            }
            return $http_status == 200 ? json_decode($result, true) : false;
        }
        else{
            return false;
        }
    }


function execute_query($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    disconnect($con);
    return $row;
}

function count_query($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con) or die(mysql_error());
    if (!$result) {
        trigger_error("Problem slecting data");
    }
    $row = mysql_num_rows($result);
    disconnect($con);
    return $row;
}

function connect()
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

function disconnect($con)
{
    $discdb = mysql_close($con);
    if (!$discdb) {
        trigger_error("Problem disconnecting database");
    }
}

function execute_update($sql)
{
    $con = connect();
    $result = mysql_query($sql, $con);
    $return_id = mysql_insert_id();
    if (!$result) {
        trigger_error("Problem updating data");
    }
    disconnect($con);
    return $return_id;
}
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
