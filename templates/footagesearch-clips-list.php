<?php
error_reporting(0);
$collections_suffixes = array(
    'Land',
    'Ocean',
    'Adventure'
);
//print_r($slaves);


?>

<div id="footagesearch-clip-preview-dialog" class="naturenewlist"></div>
<div class="footagesearch-header-wrapper">
    <h1 class="search-result-description" style="line-height:1.4rem;">
        <span>
        <?php
            echo do_shortcode(
                '[footagesearch_seo_search_term] [/footagesearch_seo_search_term][footagesearch_seo_contributors_name] [/footagesearch_seo_contributors_name]Stock Footage'
            );
        ?>
        </span>
        <br/>
        <span style="font-size:15px;">Viewing <?php echo $result['from']; ?> to </span>
        <span style="font-size:15px;"><?php echo $result['to']; ?> of </span>
        <span style="font-size:15px;"><?php echo $result['total']; ?> Rights Managed and Royalty Free Video Clips </span>
    </h1>
</div>
<?php
@session_start();
$_SESSION['cururl'] = "";
unset($_SESSION['cururl']);
$_SESSION['cururl'] = $_SERVER["HTTP_HOST"] . $_SERVER["REQUEST_URI"];

function get_client_ip()
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

?>
<!-------------------Search Actions-------------------------->
<div class="container sorting">
    <!--Pagination-->
    <div class="grid-left">
        <?php if ($pagination) { ?>
            <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
        <?php } ?>
    </div>
    <?php
    global $wp_query;
    $search_title = get_the_title();
    $title_exp = explode(">", $search_title);
    $title_match = $title_exp[3];
    $str = implode(' ', array_keys(array_flip(explode(' ', $title_match))));
    $str1 = implode(' ', array_unique(explode(' ', $str)));
    $str_exp_word = explode("<", $str1);
    $word_search = $str_exp_word[0];
    $lastSpacePosition = strrpos($str1, ' ');
    $textWithoutLastWord = substr($str1, 0, $lastSpacePosition);
    $provider_id = get_option('provider_id');
    $words = array();
    if (isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
        $word = $wp_query->query_vars['words'];
        $word_parts = explode('-', $word);
        $last_part = end($word_parts);

        if (in_array($last_part, $this->collections_suffixes)) {
            array_pop($word_parts);
            $word = implode('-', $word_parts);
        }
        $query_vars_word = stripcslashes(str_replace('-', ' ', $word));

//$words[] = $query_vars_word;
    }
    ?>
    <input type="hidden" name="provider_id" id="providerId" value="<?php echo $provider_id ?>">
    <input type="hidden" name="select_found_set_value" id="select_found_set_values" value="">
    <input type="hidden" name="search_names" id="search_name" value='<?php echo $word_search; ?>'>
    <!--Sort filters-->
    <?php
    if ( is_user_logged_in() ) {
        ?>
        <div class="grid-right">
            <div class="sort-wrapper padding">
                <div class="footagesearch-clips-list-sort-cont">
                    <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                        <?php foreach ($sort_options as $sort_option) { ?>
                            <option
                                value="<?php echo $sort_option['link']; ?>"<?php if ($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort ' . $sort_option['label']; ?></option>
                        <?php } ?>
                    </select>
                </div>
                <div class="footagesearch-clips-list-actions-cont">
                    <select name="footagesearch_clips_list_actions"
                            class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                        <option value="">Actions</option>
                        <option value="select_all">Select all</option>
                        <option value="deselect_all">Select None</option>
                        <option value="select_found_set">Select Found Set</option>
                        <option value="add_selected_to_cart">Add Selected to Cart</option>
                        <!--option value="del_selected_to_cart">Remove Selected From Cart</option-->
                        <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                        <!--option value="del_selected_to_clipbin">Remove Selected From Clipbin</option-->
                        <!--option value="preview_cart_download">Download All Preview Clips From Cart</option-->
                    </select>
                </div>
                <div class="footagesearch-clips-list-perpage-cont">
                    <form method="post" name="perpage_form1" id="perpage_form1"
                          action="<?php echo $perpage_form_action; ?>">
                        <!-- Clips per page-->
                        <select name="perpage" onchange='document.getElementById("perpage_form1").submit();'
                                class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                            <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20</option>
                            <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40</option>
                            <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80</option>
                            <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100</option>
                            <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120</option>
                            <option value="200" <?php if ($perpage == 200) echo "selected" ?>>200</option>
                        </select>
                    </form>
                </div>
            </div>
            <?php
            if ( is_user_logged_in() ) {
                ?>
                <div class="sort-wrapper">
                    <div class="footagesearch-clips-list-toggle-view-cont">
                        <form method="post" class="footagesearc-list-view-form">
                            <input type="hidden" name="list_view">
                        </form>
                        <div
                            class="footagesearch-clips-toggle-list-view<?php if (isset($list_view) && $list_view == 'list') echo ' active'; ?>">
                            &nbsp;</div>
                        <div
                            class="footagesearch-clips-toggle-grid-view<?php if (!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">
                            &nbsp;</div>
                        <!--div class="clearboth"></div-->
                    </div>
                </div>
            <?php } ?>

        </div>
    <?php } ?>
</div>

<div class="clearboth"></div>
<!-------------------END Search Actions-------------------------->
<?php if ($drag_and_drop_message) { ?>
    <div class="footagesearch-drag-and-drop-message">For your convenience we implemented Drag and Drop feature for clips
        to be used with Clipbins on this site. Please register to be able to use it.
    </div>
<?php } ?>

<div id="folding-menu-container">
<div class="footagesearch-clips-<?php echo isset($list_view) ? $list_view : 'grid' ?> folding-menu" <?php if (!is_user_logged_in() ) {?>id="clip-list-full"<?php } ?>>
    <?php
    if (isset($_SESSION['filter_session_array'])) {
        $_SESSION['filter_session_array'] = '';
        unset($_SESSION['filter_session_array']);
    }
    if (isset($_SESSION['keywords_filter_session_array'])) {
        $_SESSION['keywords_filter_session_array'] = '';
        unset($_SESSION['keywords_filter_session_array']);
    }

    $_SESSION['filter_session_array'] = $result['clips_filters_result'];
    $_SESSION['keywords_filter_session_array'] = $result['keywords_for_filters'];
    $_SESSION['current_words'] = $result['filter']['words'];

    $userLoginId_grid = $result['user_id'];
    $ip_grid = get_client_ip();

    foreach ($result['data'] as $key => $clip) {
        $clip['description'] = str_replace(array('\'', '"', '<', '>'), "`", $clip['description']);
        $clip['clip_shortcode_id'] = $shortcode_params['shortcode_id'] . '-' . $clip['id'];
        ?>


        <div
            class="forcontentwidth-<?php echo $result['from'] + $key; ?> folding-item footagesearch-clip draggable-clip<?php if ((!isset($list_view) || $list_view == 'grid') && ($key + 1) >= 4 && ($key + 1) % 4 == 0) echo ' last' ?>"
            id="footagesearch-clip-<?php echo $clip['id']; ?>"
            data-clip-id="<?php echo $clip['id']; ?>"
        >
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
                        <span id="footagesearch-clip-offset-<?php echo $result['from'] + $key; ?>"
                           class="toggle-inline-popup"
                           href="/index.php?ajax=true&footagesearch_inlinepopup[clip_id]=<?php echo $clip['id']; ?>"
                           data-bin-id="<?php echo (empty($_REQUEST['bin'])) ? '' : $_REQUEST['bin']; ?>">
                            <img src="<?php echo get_template_directory_uri() . '/images/info.png'; ?>"
                                 alt="Video clip information"
                                 title="Video clip information"
                                 class="footagesearch-clip-info-icon">
                        </span><?php //var_dump($clip['weight']);// rank                                          ?> </div>
                    <div class="footagesearch-clip-thumb">
                        <input type="hidden" value='<?php echo preg_replace('/\'/im','',json_encode($clip)); ?>'>
                        <!--<video id="footagesearch-thumb-player<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>" class="video-js vjs-default-skin" preload="auto" width="216" height="120"
                               poster="<?php echo $clip['thumb']; ?>"
                               data-setup="{}">
                            <source src="<?php echo $clip['motion_thumb']; ?>" type='video/mp4' />
                        </video>-->
                        <img src="<?php echo $clip['thumb']; ?>"
                             alt="<?php echo $clip['description'];?>"
                             title="<?php echo $clip['description'];?>">
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
                                            $clipDivileryMethodData .= $method['title'] . " &nbsp; ";
                                        }
                                    }
                                    ?>
                                    <?php
                                    foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format_key => $format) {
                                        $clipDivileryMethodData .= $format['description'] . " &nbsp; ";
//                                                            if ($clip['license'] != 1 && $clip['license_price'] && $format['price'])
//                                                                echo ' ($' . $format['price'] . ')';
                                    }
                                    ?>
                                    </select>
                                    <?php
                                } else {
                                    list($selected_method) = array_keys($clip['delivery_methods']);
                                    ?>
                                    <input type="hidden" name="delivery_method[<?php echo $clip['id']; ?>]"
                                           value="<?php echo $clip['delivery_methods'][$selected_method]['id']; ?>">

                                    <?php
                                    foreach ($clip['delivery_methods'][$selected_method]['formats'] as $format) {
                                        $clipDivileryMethodData .= $format['description'] . " &nbsp; ";
                                    }
                                    ?>

                                <?php }
                                ?>

                                <span class="footagesearch-delivery-frame-rate-cont">
                                    <?php if (isset($selected_format) && isset($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'])) { ?>
                                        <br>


                                        <?php
                                        foreach ($clip['delivery_methods'][$selected_method]['formats'][$selected_format]['custom_frame_rates'] as $frame_rate) {
                                            $clipDivileryMethodData .= $frame_rate['format'] . " &nbsp; ";
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
                            <!--<input type="hidden" name="deliver_options_data" value="<?php echo json_encode(array('dilivery_options' => $deliveries));?>"/>-->

                            <img id="play_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/play_icon.png' ?>"
                                 alt="Play"
                                 title="Play"
                                 class="footagesearch-clip-play-btn"
                                 data-clip='<?php echo json_encode(array('id' => $clip['id'], 'title' => $clip['title'], 'description' => $clip['description'], 'preview' => $clip['preview'], 'motion_thumb' => $clip['motion_thumb'], 'source_format' => $clip['source_format'] . ' ' . $clip['source_frame_size'] . ' ' . $clip['source_frame_rate'], 'country' => $clip['country'], 'location' => $clip['location'], 'price_level' => $priceLevelDisplay, 'license_restrictions' => $clip['license_restrictions'])); ?>'>
                            <img id="pause_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/pause_icon.png' ?>"
                                 alt="Pause"
                                 title="Pause"
                                 class="footagesearch-clip-pause-btn" style="display: none;">
                            <img id="forward_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                 src="<?php echo get_template_directory_uri() . '/images/forward_icon.png' ?>"
                                 alt="Forward"
                                 title="Forward"
                                 class="footagesearch-clip-forward-btn">
                            <img
                                id="forward3x_<?php echo $shortcode_params['shortcode_id'] . '-' . $clip['id']; ?>"
                                src="<?php echo get_template_directory_uri() . '/images/forward3x_icon.png' ?>"
                                alt="Fast Forward"
                                title="Fast Forward"
                                class="footagesearch-clip-forward3x-btn">
                            <input type="hidden" name="user_login_id" value="<?php echo $userLoginId_grid; ?>"
                                   id="user_login_id">

                            <div id="tutorial-<?php echo $clip['id']; ?>" class="heartPosition">
                                <?php
                                if (!empty($userLoginId_grid)) {
                                    if (isset($result['user_likes'][$clip['id']])) {
                                        ?>
                                        <div class="inner_like">
                                            <img
                                                onClick="deleteLikes('<?php echo $result['user_likes'][$clip['id']]; ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png"
                                                alt="Delete like"
                                                title="Delete like"
                                            >
                                        </div>
                                    <?php } else {
                                        ?>
                                        <div class="inner_like">
                                            <a rel="nofollow" href="javascript:void(0)" onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '');">
                                                <img src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"
                                                     alt="Add like"
                                                     title="Add like"
                                                >
                                            </a>
                                        </div>
                                        <?php
                                    }
                                } else {
                                    if (isset($result['user_likes'][$clip['id']])) {
                                        ?>
                                        <div class="inner_like transitiable">
                                            <img
                                                onClick="deleteLikes('<?php echo $result['user_likes'][$clip['id']]; ?>', 'unlike', '<?php echo $clip['id'] ?>')"
                                                src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png"
                                                alt="Delete like"
                                                title="Delete like"
                                            >
                                        </div>
                                        <?php
                                    } else {
                                        ?>
                                        <div class="inner_like">
                                            <a rel="nofollow" href="javascript:void(0)" onClick="addLikes(<?php echo $clip['id'] ?>, 'like', '');">
                                                <img src="<?php echo get_template_directory_uri() ?>/images/icons/like-icon.png"
                                                     alt="Add like"
                                                     title="Add like"
                                                >
                                            </a>
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
                            <a rel="nofollow" class="preview_download" data-clip-id="<?php echo $clip['id'] ?>"
                               data-status="<?php echo (!empty($userLoginId_grid) ? $userLoginId_grid : $ip_grid); ?>"
                               href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']) ?>">
                                <img
                                    src="<?php echo get_template_directory_uri() . '/images/download_icon.png' ?>"
                                    alt="Preview Download"
                                    title="Preview Download"
                                ></a>
                        </div>
                    </div>


                    <div class="clear"></div>
                </div>

                <?php
                //if (!is_user_logged_in() ) {
                ?>

                <?php if (!isset($list_view) || $list_view == 'grid') { ?>
                    <?php if (($key + 1) >= 4 && ($key + 1) % 4 == 0) { ?>
                        <!--<div class="clear"></div>-->
                    <?php } ?>
                <?php } else { ?>
                    <div class="footagesearch-clip-details">
                        <?php if ($clip['description']) { ?>
                            <div class="descriptionfull">
                                <?php echo substr( $clip['description'],0,60); ?>
                            </div>
                        <?php } ?>

                    </div>
                    <div class="clear"></div>
                <?php } ?>
                <?php //} ?>
            </div>
            <input type="hidden" name="selected_clips[<?php echo $clip['id']; ?>]" value="0" class="footagesearch-clip-input">
        </div>

    <?php } ?>
</div>
</div>
<br>
<!-------------------Search Actions-------------------------->
<?php if ($result['total'] > 10) { ?>
    <div class="container sorting">
        <!--Pagination-->
        <div class="grid-left">
            <?php if ($pagination) { ?>
                <div class="footagesearch-clips-list-pagination"><?php echo $pagination; ?></div>
            <?php } ?>
        </div>
        <!--Sort filters-->
        <?php
        if ( is_user_logged_in() ) {
            ?>
            <div class="grid-right">
                <div class="sort-wrapper padding">
                    <div class="footagesearch-clips-list-sort-cont">
                        <select name="sort" class="footagesearch-clips-list-select footagesearch-clips-list-sort">
                            <?php foreach ($sort_options as $sort_option) { ?>
                                <option
                                    value="<?php echo $sort_option['link']; ?>"<?php if ($sort_option['selected']) echo ' selected="selected"'; ?>><?php echo 'Sort ' . $sort_option['label']; ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="footagesearch-clips-list-actions-cont">
                        <select name="footagesearch_clips_list_actions"
                                class="footagesearch-clips-list-select footagesearch-clips-list-actions">
                            <option value="">Actions</option>
                            <option value="select_all">Select all</option>
                            <option value="deselect_all">Select None</option>
                            <option value="select_found_set">Select Found Set</option>
                            <option value="add_selected_to_cart">Add Selected to Cart</option>
                            <option value="add_selected_to_clipbin">Add Selected to Clipbin</option>
                        </select>
                    </div>
                    <div class="footagesearch-clips-list-perpage-cont">
                        <form method="post" name="perpage_form1" id="perpage_form2"
                              action="<?php echo $perpage_form_action; ?>">
                            <!--Clips per page-->
                            <select name="perpage" onchange='document.getElementById("perpage_form2").submit();'
                                    class="footagesearch-clips-list-select footagesearch-clips-list-perpage">
                                <option value="20" <?php if ($perpage == 20) echo "selected" ?>>20</option>
                                <option value="40" <?php if ($perpage == 40) echo "selected" ?>>40</option>
                                <option value="80" <?php if ($perpage == 80) echo "selected" ?>>80</option>
                                <option value="100" <?php if ($perpage == 100) echo "selected" ?>>100</option>
                                <option value="120" <?php if ($perpage == 120) echo "selected" ?>>120</option>
                                <option value="200" <? if ($perpage == 200) echo "selected" ?>>200</option>
                            </select>
                        </form>
                    </div>
                </div>
                <?php
                if ( is_user_logged_in() ) {
                    ?>
                    <div class="sort-wrapper">
                        <div class="footagesearch-clips-list-toggle-view-cont">
                            <form method="post" class="footagesearc-list-view-form">
                                <input type="hidden" name="list_view">
                            </form>
                            <div
                                class="footagesearch-clips-toggle-list-view<?php if (isset($list_view) && $list_view == 'list') echo ' active'; ?>">
                                &nbsp;</div>
                            <div
                                class="footagesearch-clips-toggle-grid-view<?php if (!isset($list_view) || $list_view == 'grid') echo ' active'; ?>">
                                &nbsp;</div>
                            <!--div class="clearboth"></div-->
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php } ?>
    </div>
<?php } ?>
<div class="clearboth"></div>
<script>
    jQuery(document).on("click", "#cboxClose2", function () {
        jQuery.colorbox.close();
    });
    jQuery(document).ready(function () {

        jQuery(".preview_download").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "none");
        });
        jQuery(".info transitiable").hover(function () {
            jQuery("#footagesearch-clip-preview").css("display", "block");
        });

        jQuery(".inline").colorbox({inline: true, width: "80%", height: "80%"});
        jQuery(".inline1").colorbox({inline: true, width: "30%"});
    });
</script>



<!-------------------END Search Actions-------------------------->
<!-- clipPreviewBox -->
<div id="footagesearch-clip-preview"
     data-term="<?php echo $_SESSION['footagesearch_cart_license_term']; ?>"
     data-format="<?php echo $_SESSION['footagesearch_cart_license_format']; ?>"
     data-use="<?php echo $_SESSION['footagesearch_cart_license_use']; ?>"
     data-category="<?php echo $_SESSION['footagesearch_cart_license_category'] ?>"
     style="display: none;">
    <h6 class="titleHover"></h6>

    <p class="clickImage"></p>
    <br clear="all">
    <video id="" class="video-js vjs-default-skin" preload="auto" width="432" height="auto" loop muted data-setup="{}">
        <source src="" type="video/mp4" />
    </video>
    <p class="description"></p>

    <!--<p class="license_restrictions"></p>-->

    <p style="font-size:15px; padding-top:5px;" class="source_format"></p>

    <p style="font-size:15px; padding-top:20px;" class="delivery_options"></p>

    <!--<p class="price_level"></p>-->
</div>
<div id="prewiew-download-popup"></div>
<style type="text/css">
    /*#cboxClose {*/
        /*display: none !important;*/
    /*}*/

    /*#cboxLoadedContent {*/
        /*height: 400px !important;*/
    /*}*/

    .cancelbtn {
        background: #999 none repeat scroll 0 0;
        bottom: 8px;
        color: rgb(0, 138, 164);
        cursor: pointer;
        padding: 20px;
        position: absolute;
        left: 377px;
        text-align: center;
        z-index: 9999;
        color: #fff;
        border: 1px solid #404040;
    }

    .cancelbtn:hover {
        background: #404040;
        color: #FFF;
        text-decoration: none;
    }

    .downloadvideobtn {
        background: rgb(61, 205, 88) none repeat scroll 0 0;
        bottom: 8px;
        color: rgb(255, 255, 255);
        left: 5px;
        padding: 20px;
        position: absolute;
        z-index: 9999;
        border: 1px solid #217F32;
    }

    .downloadvideobtn:hover {
        background: #217F32;
        color: #FFF;
        text-decoration: none;
    }

    .downloadagreementbtn {
        background: rgb(61, 205, 88) none repeat scroll 0 0;
        bottom: 8px;
        color: #fff;
        cursor: pointer;
        left: 170px;
        padding: 20px;
        position: absolute;
        text-align: center;
        z-index: 9999;
        border: 1px solid #217F32;
    }

    .downloadagreementbtn:hover {
        background: #217F32;
        color: #FFF;
        text-decoration: none;
    }

    .remtext {
        background: #faf9c9 none repeat scroll 0 0;
        border: 1px solid #f0911d;
        bottom: 8px;
        color: #f0911d;
        font-size: 16px;
        padding: 20px;
        position: absolute;
        right: 58px;
        z-index: 9999;
    }

    .clickImage {
        margin: 0px;
        padding: 0px;
        float: right;
    }

    .titleHover {
        margin: 10px 0px 0px 0px;
        padding: 0px;
        float: left;
        width: auto
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
        width: 18px;
        float: right;
        margin-left: 15px;
    }
    .container.sorting{ clear:both !important;}
</style>
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
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id + '&json=2',
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {

                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = data;
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + id + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + id + ' .inner_like').html('<img style="cursor:pointer;" onClick="deleteLikes(\'' + likes.inserted_id + '\',\'unlike\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
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
            data: 'id=' + id + '&action=' + action + '&user_login_id=' + user_login_id,
            type: "POST",
            beforeSend: function () {
                jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/LoaderIcon.gif" />');
            },
            success: function (data) {
                //var likes = parseInt(jQuery('#likes-'+id).val());
                var likes = data;
                switch (action) {
                    case "like":
                        jQuery('#tutorial-' + clipId + ' .btn-likes').html('<img src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-fill.png" />');
                        //likes = likes+1;
                        break;
                    case "unlike":
                        jQuery('#tutorial-' + clipId + ' .inner_like').html('<img style="cursor:pointer;" onClick="addLikes(\'' + clipId + '\',\'like\',\'' + id + '\')" src="<?php echo get_template_directory_uri(); ?>/images/icons/like-icon.png" />');
                        likes = likes - 1;
                        break;
                }
                jQuery('#likes-' + clipId).val(likes);
                if (likes > 0) {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html(likes + " Like(s)");
                } else {
                    jQuery('#tutorial-' + clipId + ' .label-likes').html('');
                }
            }
        });
    }

</script>