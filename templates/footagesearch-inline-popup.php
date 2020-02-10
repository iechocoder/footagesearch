<div class="unfolded-content">
    <div class="folding-content __clip-content" data-clip-id="<?php echo $clip['id']; ?>">
    <div class="inlinepopup" style="width:100%; ">
        <div class="inlinepopup-left">
            <div class="footagesearch-preview-clip-top">
                <h5><?php echo $clip['code']; ?></h5>
                <?php if ($clip['license'] == 1) { ?>
                    <div class="footagesearch-preview-clip-license footagesearch-license-<?php echo $clip['license']; ?>"> RF </div>
                <?php } elseif ($clip['license'] == 2) { ?>
                    <?php if ($clip['price_level'] == 4) { ?>
                        <div class="footagesearch-preview-clip-license footagesearch-license-gold">GD</div>
                    <?php } elseif ($clip['price_level'] == 3) { ?>
                        <div class="footagesearch-preview-clip-license footagesearch-license-premium">PR</div>
                    <?php } else { ?>
                        <div
                            class="footagesearch-preview-clip-license footagesearch-license-<?php echo $clip['license']; ?>"> RM </div>
                    <?php } ?>
                <?php } ?>
                <div class="footagesearch-preview-clip-duration"><?php echo round($clip['duration']) ?> sec</div>
                <div class="clear"></div>
            </div>
            <div itemprop="video" itemscope itemtype="http://schema.org/VideoObject">
                <h2 style="display:none;"><?php echo $clip['description']; ?></h2>
                <meta itemprop="duration" content="<?php echo $clip['duration']; ?>S" />
                <meta itemprop="thumbnailUrl" content="<?php echo $clip['thumb']; ?>" />
                <meta itemprop="contentURL" content="<?php echo $clip['preview']; ?>" />
                <meta itemprop="uploadDate" content="<?php echo $clip['creation_date']; ?>" />
                <meta itemprop="height" content="720" />
                <meta itemprop="width" content="1280" />
                <video id="footagesearch-inline-preview-video-<?php echo md5(mt_rand()); ?>"
                       class="video-js vjs-default-skin"
                >
                    <source src="<?php echo $clip['preview']; ?>" type="video/mp4"/>
                </video>
                <span itemprop="description" style="display:none;"><?php echo $clip['description']; ?> </span> </div>
            <div class="footagesearch-clip-cart-clipbin-actions" style="margin-top:10px;">
                <?php
                    echo in_array($clip['id'], $in_clipbin) ? get_remove_from_clipbin_button($clip['id']) : get_add_to_clipbin_button($clip['id']);
                    echo in_array($clip['id'], $in_cart) ? get_remove_from_cart_button($clip['id']) : get_add_to_cart_button($clip['id']);
                ?>

                <a rel="nofollow" class="preview_download" data-clip-id="<?php echo $clip['id'] ?>"
                   data-status="<?= $userLoginId_grid; ?>"
                   href="<?php echo (function_exists('clip_preview_download_link') ? clip_preview_download_link($clip['id']) : $clip['download']) ?>">
                    <img src="<?php echo get_template_directory_uri() . '/images/download_white-32x32.png' ?>"
                         alt="Preview download"
                         title="Preview download"
                    >
                </a>
                        
                        <a href="<?php echo esc_url(get_permalink($clip_holder['ID']) . '/' . $clip['code']); ?>">
                            <img src="<?php echo get_template_directory_uri() . '/images/calculator_white-32x32.png' ?>"
                                 alt="Pricing calculator"
                                 title="Pricing calculator"
                            >
                        </a>
             </div>
        </div>
        <div class="inlinepopup-right">
            <h2 class="inlinepopup-title"><?php echo $clip['description']; ?></h2>
            <p class="price-level" >
                <?php
                    $price = null;
                    if ($clipPriceData) {
                        if (!empty($clipPriceData['price_with_delivery'])) {
                            $price = $clipPriceData['price_with_delivery'];
                        } elseif (!empty($clipPriceData['price'])) {
                            $price = $clipPriceData['price'];
                        }
                    }

                    if (!is_null($price)) {
                        echo sprintf(
                            '%s%s %s (%s)',
                            get_currency_mark(),
                            $price,
                            get_license_name($clip['license']),
                            get_price_level_text($clip['price_level'])
                        );
                        echo '<br/>';
                        if (isset($clipPriceData['category'])) {
                            echo '<span>' . $clipPriceData['category'] . ': </span>';
                        }
                        if (isset($clipPriceData['use_data']['use'])) {
                            echo '<span>' . $clipPriceData['use_data']['use'] . ': </span>';
                        }
                        if (isset($clipPriceData['term_data']['territory'])) {
                            echo '<span>' . $clipPriceData['term_data']['territory'] . ' </span>';
                        }
                        if (isset($clipPriceData['term_data']['term'])) {
                            echo '<span>' . $clipPriceData['term_data']['term'] . ' </span>';
                        }
                    } else { ?>
                        View Pricing Calculator to Set Pricing
                    <? }
                    unset($price);
                ?>
            </p>
            <?php if(!empty($clip['delivery_methods'])) { ?>
                <div class="deliveryoptions">
                    <h3 style="font-size:16px; color:#FFF; font-weight:bold;">Delivery Options:</h3>
                    <ul style="margin-left:0px;">
                        <?php
                        array_walk($clip['delivery_methods'], function($item, $_key) {
                            if (!empty($item['formats'])) {
                                array_walk($item['formats'], function ($format, $__key) {
                                    if (!empty($format['description'])) {
                                        echo '<li>' . $format['description'] . '</li>';
                                    }
                                });
                            }
                        });
                        ?>
                    </ul>
                </div>
            <?php } ?>

            <p style="float:left;">
                <a href="<?php echo esc_url(get_permalink($clip_holder['ID']) . '/' . $clip['code']); ?>">Pricing Options</a>
            </p>
        </div>
        <div style="clear : both;"></div>
        <div class="keywords" style="margin-top:15px;">
            
            <table style="width:100%; border:none;">
                <?php if ($clip['shot_type_keyword'] || (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00')) { ?>
                    <tr>
                        <td class="minWpopuppx" style="color:#FFF;"><?php _e('The Shot:', 'footagesearch'); ?></td>
                        <td>
                            <?php
                            if ($clip['shot_type_keyword']) {
                                foreach ($clip['shot_type_keyword'] as $keyword) {
                                    if ($keyword) {
                                        ?>
                                        <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                           onclick="window.opener.location.href = this.href;
                                                            return false;"
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
                                        <?php
                                    }
                                }
                            }
                            ?>
                            <?php if (!empty($clip['film_date']) && $clip['film_date'] != '0000-00-00') { ?>
                                <div
                                    class="footagesearch-preview-clip-keyword-popup"><?php echo date('d.m.Y', strtotime($clip['film_date'])); ?></div>
                            <?php } ?>
                            <div class="clearfix"></div>
                        </td>
                    </tr>
                    
                <?php } ?>
                <?php if ($clip['subject_category_keyword'] || $clip['primary_type_keyword'] || $clip['other_type_keyword'] || $clip['appereance_type_keyword'] || $clip['concept_type_keyword']) { ?>
                    <tr>
                      <td class="minWpopuppx" style="color:#FFF;"><?php _e('Subject:', 'footagesearch'); ?></td>
                        <td>
                            <?php
                            if ($clip['subject_category_keyword']) {
                                foreach ($clip['subject_category_keyword'] as $keyword) {
                                    if ($keyword) {
                                        ?>
                                        <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                           onclick="window.opener.location.href = this.href;
                                                            return false;"
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                           class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                        <td class="minWpopuppx" style="color:#FFF;"><?php _e('Action:', 'footagesearch'); ?></td>
                        <td>
                            <?php
                            foreach ($clip['action_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                       onclick="window.opener.location.href = this.href;
                                                    return false;"
                                       class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                    <td class="minWpopuppx" style="color:#FFF;"><?php _e('Environment:', 'footagesearch'); ?></td>
                    <td>
                        <?php
                        if ($clip['time_type_keyword']) {
                            foreach ($clip['time_type_keyword'] as $keyword) {
                                if ($keyword) {
                                    ?>
                                    <a href="<?php echo(add_query_arg('fs', urlencode($keyword), $clips_holder_permalink)); ?>"
                                       onclick="window.opener.location.href = this.href;
                                                        return false;"
                                       class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                       class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
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
                                       class="footagesearch-preview-clip-keyword-popup"><?php echo $keyword; ?></a>
                                    <?php
                                }
                            }
                        }
                        ?>
                        <?php if ($clip['country']) { ?>
                            <a href="<?php echo(add_query_arg('fs', urlencode($clip['country']), $clips_holder_permalink)); ?>"
                               onclick="window.opener.location.href = this.href;
                                        return false;"
                               class="footagesearch-preview-clip-keyword-popup"><?php echo $clip['country']; ?></a>
                        <?php } ?>
                        <div class="clearfix"></div>
                    </td>
                    
                    </tr>
            <?php } ?>
           </table>
        </div>
    </div>
</div>
</div>