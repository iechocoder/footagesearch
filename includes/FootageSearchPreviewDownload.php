<?php

/**
 * * as download link should be processed correctly on all frontend site versions,
 * to avoid creating posts for download on all frontend site version just:
 *  - set slug in the code,
 *  - rewrite url in format /download/clip_id to clip_holder page with download rewrite tag
 *  - use template_redirect to output preview downloading to user
 *
 * Class FootageSearchPreviewDownload
 */
class FootageSearchPreviewDownload
{
    /**
     * @var FootageSearch
     */
    private $footagesearch;
    private $slug;

    public function __construct(FootageSearch $footageSearch, $slug = 'download')
    {
        $this->footagesearch = $footageSearch;
        $this->slug = $slug;

        add_action('init', [$this,'addRewriteRules']);
        add_action( 'template_redirect', [$this, 'processPreviewDownload'] );

    }

    /**
     *
     * get clip preview download link by clip id
     *
     * @param int $clipId
     *
     * @return string
     */
    public function getLink($clipId)
    {
        if (!$clipId) {
            return '';
        }

        return '/'  . $this->getSlug() . '/' . $clipId;
    }

    /**
     *
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     *
     */
    public function addRewriteRules()
    {
        $clipPage = $this->footagesearch->get_clip_holder_page();

        // preview download page rule
        // route download preview on clip_page, handle it with template_redirect
        if ($clipPage) {
            add_rewrite_tag("%clip_id%", '([0-9]+)');
            add_rewrite_tag("%preview_download%", '([0-1])');
            $rule = '^(' . $this->getSlug() . ')/?([^/]*)/?$';
            add_rewrite_rule(
                $rule,
                'index.php?page_id=' . $clipPage['ID'] . '&clip_id=$matches[2]&preview_download=1',
                'top'
            );
            $rules = get_option('rewrite_rules');
            if (!isset($rules[$rule])){
                global $wp_rewrite;
                $wp_rewrite->flush_rules();
            }
        }
        // --
    }

    /**
     * HANDLE PREVIEW DOWNLOAD ACTION
     *
     * @param $atts
     * @param string $content
     */
    public function processPreviewDownload($atts, $content = '')
    {
        global $wp_query;

        $post = get_post();
        $clipPost = $this->footagesearch->get_clip_holder_page();
        if (!($post && $clipPost && $post->ID === $clipPost['ID'] && !empty($wp_query->query_vars['preview_download']))
        ) {
            // process download preview only for clip post with not empty preview_download param
            return ;
        }

        function request($url)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            $curlData = curl_exec($curl);

            curl_close($curl);

            return ($curlData ? json_decode($curlData, true) : false);
        }

        $clipId = $wp_query->query_vars['clip_id'];
        $backendUrl = get_option('backend_url');

        if (!$clipId || !$backendUrl) {
            header("HTTP/1.0 404 Not Found");
            $wp_query->is_404 = true;
            return;
        }

        $url = $backendUrl . '/en/clips/content/' . $clipId . '?no_direct_output=1';

        $content = request($url);

        if (empty($content['preview'])) {
            header("HTTP/1.0 404 Not Found");
            $wp_query->is_404 = true;
            return;
        }

        if (!empty($content['headers'])) {
            foreach ($content['headers'] as $header) {
                header($header);
            }
        }

        readfile($content['preview']);
        die();
    }
}
