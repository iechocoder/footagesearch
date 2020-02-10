<div class="footagesearch-shared-pages-list">
<?php
foreach ((array)$pages as $page) { ?>
    <a href="/<?php echo $page['url']; ?>"><?php echo $page['title']; ?></a><br>
<?php } ?>
</div>