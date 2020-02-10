<div class="footagesearch-top-keywords-list">
    <?php
    foreach ((array)$keywords as $keyword) { ?>
        <a href="<?php echo $keyword['link']; ?>" style="font-size:<?php echo $keyword['size']; ?>px"><?php echo $keyword['phrase']; ?></a>
    <?php } ?>
</div>