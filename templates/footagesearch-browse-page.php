<div class="footagesearch-browse-page <?php echo get_post_meta($page->ID, 'browse_page_layout', true); ?>">
    <?php if($video_url = get_post_meta($page->ID, 'browse_page_video_url', true)) { ?>
    <div class="footagesearch-browse-page-video">
        <?php if($overlay_text = get_post_meta($page->ID, 'browse_page_vide_overlay_text', true)) { ?>
            <div class="footagesearch-browse-page-video-overlay" id="footagesearch-browse-page-video-overlay" style="width: <?php echo ($video_width = get_post_meta($page->ID, 'browse_page_video_width', true)) ? $video_width : 432; ?>px;">
                <?php echo $overlay_text; ?>
            </div>
        <?php } ?>
        <video id="footagesearch-browse-page-video" class="video-js vjs-default-skin" controls preload="auto"
               <?php echo ($poster = get_post_meta($page->ID, 'browse_page_social_image', true)) ? 'poster="' . $poster . '"' : '' ?>
               <?php echo get_post_meta($page->ID, 'browse_page_video_autoplay', true) == 'yes' ? 'autoplay' : '' ?>
               <?php echo get_post_meta($page->ID, 'browse_page_video_looping', true) == 'yes' ? 'loop' : '' ?>
               width="<?php echo ($video_width = get_post_meta($page->ID, 'browse_page_video_width', true)) ? $video_width : 432; ?>"
               height="<?php echo ($video_height = get_post_meta($page->ID, 'browse_page_video_height', true)) ? $video_height : 240; ?>"
               data-setup="{}">
            <source src="<?php echo $video_url; ?>" type="video/mp4" />
        </video>
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
        </script>
        <?php if($text_under_video = get_post_meta($page->ID, 'browse_page_text_under_video', true)) { ?>
            <div class="footagesearch-browse-page-video-text">
                <?php echo $text_under_video; ?>
            </div>
        <?php } ?>
        <?php if(get_post_meta($page->ID, 'browse_page_video_sound', true) == 'no') { ?>
            <script type="text/javascript">
                var player = videojs('footagesearch-browse-page-video');
                player.volume(0);
            </script>
        <?php } ?>
    </div>
    <?php } ?>
    <div class="footagesearch-browse-page-content">
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <?php if($category_title = get_post_meta($page->ID, 'browse_page_category_title', true)) { ?>
            <header class="entry-header">
                <h1 class="entry-title"><?php echo $category_title; ?></h1>
            </header>
            <?php } ?>

            <div class="entry-content">
                <?php echo apply_filters('the_content', get_post_field('post_content', $page->ID)); ?>
            </div>

            <footer class="entry-meta">
                <?php edit_post_link( __( 'Edit', 'footagesearch' ), '<span class="edit-link">', '</span>' ); ?>
            </footer>

        </article>
    </div>
    <div class="clear"></div>

    <?php
        $browse_page_lists = wp_get_post_terms($page->ID, 'list');
        if($browse_page_lists){
            foreach($browse_page_lists as $browse_page_list){
                if($section_type = get_metadata('list', $browse_page_list->term_id, 'section_type', true)){

                    switch ($section_type) {
                        case 'cliplist':

                            $query = "
                                SELECT * FROM $wpdb->term_taxonomy
                                LEFT JOIN $wpdb->term_relationships ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
                                LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
                                INNER JOIN (
                                    SELECT t1.list_item_id, t1.meta_value sort_order, t2.meta_value clip_id, t3.meta_value url, t4.meta_value url_type, t5.meta_value list_id, t6.meta_value clip_thumb
                                    FROM $wpdb->list_itemmeta t1
                                    INNER JOIN $wpdb->list_itemmeta t2 ON t1.list_item_id = t2.list_item_id AND t1.meta_key = 'sort_order' AND t2.meta_key = 'clip_id'
                                    INNER JOIN $wpdb->list_itemmeta t3 ON t2.list_item_id = t3.list_item_id AND t3.meta_key = 'url'
                                    INNER JOIN $wpdb->list_itemmeta t4 ON t3.list_item_id = t4.list_item_id AND t4.meta_key = 'url_type'
                                    INNER JOIN $wpdb->list_itemmeta t5 ON t4.list_item_id = t5.list_item_id AND t5.meta_key = 'list_id'
                                    LEFT JOIN $wpdb->list_itemmeta t6 ON t5.list_item_id = t6.list_item_id AND t6.meta_key = 'clip_thumb'
                                ) im ON $wpdb->terms.term_id = im.list_item_id
                                WHERE $wpdb->term_taxonomy.taxonomy = 'list_item' AND list_id = $browse_page_list->term_id
                                ORDER BY sort_order
                            ";

                            $browse_page_list_items = $wpdb->get_results($query, OBJECT);

                            if($browse_page_list_items){
                                echo '<h2 class="footagesearch-browse-page-list-title">' . $browse_page_list->name . '</h2>';
                                echo '<div class="footagesearch-categories-list"><ul>';
                                foreach($browse_page_list_items as $browse_page_list_item_key => $browse_page_list_item){
                                    echo '<li class="footagesearch-category' . (($browse_page_list_item_key > 2 && ((($browse_page_list_item_key + 1) % 4) == 0)) ? ' last' : '') .'">';
                                    echo '<h2><a href="' . $browse_page_list_item->url . '"' . ($browse_page_list_item->url_type == 'new window' ? ' target="_blank"' : '') . '>' . $browse_page_list_item->name . '</a></h2>';
                                    if($browse_page_list_item->clip_id){
                                        echo '<a href="' . $browse_page_list_item->url . '"' . ($browse_page_list_item->url_type == 'new window' ? ' target="_blank"' : '') . '><img src="';
                                        if(!empty($browse_page_list_item->clip_thumb)){
                                            echo preg_replace('/s3:\/\//i', 'http://',$browse_page_list_item->clip_thumb);
                                        }else{
                                            echo (is_numeric($browse_page_list_item->clip_id) ? trim(get_option('backend_url'), '/') . '/data/upload/resources/clip/thumb/' : 'http://s3.footagesearch.com/stills/') .  $browse_page_list_item->clip_id . '.jpg';
                                        }
                                        echo '"></a>';
                                    }
                                    echo '<div class="footagesearch-category-description">' . $browse_page_list_item->description . '</div></li>';
                                }
                                echo '</ul><div class="clear"></div></div>';
                            }

                            break;
                        case 'linklist':

                            $query = "
                                SELECT * FROM $wpdb->term_taxonomy
                                LEFT JOIN $wpdb->term_relationships ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
                                LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
                                INNER JOIN (
                                    SELECT t1.list_item_id, t1.meta_value sort_order, t2.meta_value url, t3.meta_value indention, t4.meta_value list_id
                                    FROM $wpdb->list_itemmeta t1
                                    INNER JOIN $wpdb->list_itemmeta t2 ON t1.list_item_id = t2.list_item_id AND t1.meta_key = 'sort_order' AND t2.meta_key = 'url'
                                    INNER JOIN $wpdb->list_itemmeta t3 ON t2.list_item_id = t3.list_item_id AND t3.meta_key = 'indention'
                                    INNER JOIN $wpdb->list_itemmeta t4 ON t3.list_item_id = t4.list_item_id AND t4.meta_key = 'list_id'
                                ) im ON $wpdb->terms.term_id = im.list_item_id
                                WHERE $wpdb->term_taxonomy.taxonomy = 'list_item' AND list_id = $browse_page_list->term_id
                                ORDER BY sort_order
                            ";

                            $browse_page_list_items = $wpdb->get_results($query, OBJECT);

                            if($browse_page_list_items){
                                $column_count = ceil(count($browse_page_list_items) / 4);
                                echo '<h2 class="footagesearch-browse-page-list-title">' . $browse_page_list->name . '</h2>';
                                echo '<table class="footagesearch-links-list"><tr><td>';
                                foreach($browse_page_list_items as $browse_page_list_item_key => $browse_page_list_item){
                                    echo '<span' . ($browse_page_list_item->indention ? ' style="padding-left: ' . $browse_page_list_item->indention . 'em;"' : '') . '>';
                                    echo '<a href="' . $browse_page_list_item->url . '" title="' . $browse_page_list_item->description . '">' . $browse_page_list_item->name . '</a></span><br>';
                                    if(($browse_page_list_item_key + 1) >= $column_count && ((($browse_page_list_item_key + 1) % $column_count) == 0))
                                        echo '</td><td>';
                                }
                                echo '</td></tr></table>';
                            }

                            break;
                        case 'textarea':

                            $query = "
                                SELECT * FROM $wpdb->term_taxonomy
                                LEFT JOIN $wpdb->term_relationships ON($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
                                LEFT JOIN $wpdb->terms ON($wpdb->term_taxonomy.term_id = $wpdb->terms.term_id)
                                INNER JOIN (
                                    SELECT t1.list_item_id, t1.meta_value sort_order, t2.meta_value content, t3.meta_value list_id
                                    FROM $wpdb->list_itemmeta t1
                                    INNER JOIN $wpdb->list_itemmeta t2 ON t1.list_item_id = t2.list_item_id AND t1.meta_key = 'sort_order' AND t2.meta_key = 'content'
                                    INNER JOIN $wpdb->list_itemmeta t3 ON t2.list_item_id = t3.list_item_id AND t3.meta_key = 'list_id'
                                ) im ON $wpdb->terms.term_id = im.list_item_id
                                WHERE $wpdb->term_taxonomy.taxonomy = 'list_item' AND list_id = $browse_page_list->term_id
                                ORDER BY sort_order
                            ";

                            $browse_page_list_items = $wpdb->get_results($query, OBJECT);

                            if($browse_page_list_items){
                                foreach($browse_page_list_items as $browse_page_list_item){
                                    echo $browse_page_list_item->content;
                                }
                            }

                            break;
                    }


                }

            }
        }
    ?>

</div>