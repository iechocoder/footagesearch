<?php
/*
Plugin Name: FootageSearch
Description: Plugin for browsing assets categories from footagesearch.com.
Author: Ivan Manachin
Version: 1.0
*/

if (!defined('FS_ABSPATH')) {
    define('FS_ABSPATH', dirname(__FILE__));
}

include_once  FS_ABSPATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'functions.php';
include_once  FS_ABSPATH . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'FootageSearchPreviewDownload.php';

$GLOBALS['footage_search'] = new FootageSearch();

if ( !function_exists( 'theme_my_login' ) ) :
function get_footagesearch_search_form($echo = true){
    global $footage_search;
    $footage_search->get_footagesearch_search_form($echo);
}
endif;



class FootageSearch {

    var $version = '1.0';

    var $available_filters = array();

    var $current_clip;

    var $licenses_names_map = array(
        'royalty-free-video-clips' => 1
    );

    var $licenses_names = array(
        1 => 'Royalty Free',
        2 => 'Rights Managed'
    );


    var $prices_levels_map = array(
        'budget' => 1,
        'standard' => 2,
        'premium' =>  3,
        'exclusive' => 4
    );

    var $prices_levels_names = array(
        1 => 'Budget',
        2 => 'Standard',
        3 => 'Premium',
        4 => 'Exclusive'
    );

    var $format_category_map = array(
        'ultra-hd-video' => 'Ultra HD',
        '3d-video' => '3D'
    );

    var $collections_suffixes = array(
        'Land',
        'Ocean'/*,
        'Adventure'*/
    );

    /**
     * brand names by id, used at least in seo search teerm shortcode
     *
     * @var array
     */
    private $brandMap = [
        2 => 'NatureFlix',
    ];


    /**
     * @var FootageSearchPreviewDownload
     */
    private $previewDownload;

    function __construct() {

        $this->settings();

        $this->previewDownload = new FootageSearchPreviewDownload($this);

        add_action('init', array(&$this, 'handle_footagesearch_request'));
        add_action('init', array(&$this, 'handle_footagesearch_inline_popup'));
        add_action('init', array(&$this, 'register_post_types'));
        add_action('init', array(&$this, 'register_taxonomies'));

        //Add shortcode
        add_shortcode('footagesearch', array(&$this, 'shortcode_footagesearch'));
        add_shortcode('footagesearch_clip', array(&$this, 'shortcode_footagesearch_clip'));
        add_shortcode('footagesearch_provider_profile', array(&$this, 'shortcode_footagesearch_provider_profile'));
        add_shortcode('footagesearch_provider_list', array(&$this, 'shortcode_footagesearch_provider_list'));
        add_shortcode('footagesearch_browse_page', array(&$this, 'shortcode_footagesearch_browse_page'));


        /* ФОРМЫ ФРОНТЕНДА [ ContactUs, ShotRequest ] : START *********************************************************/

        add_shortcode( 'footagesearch_form_contactus', array( &$this, 'shortcode_footagesearch_form_contactus' ) );
        add_shortcode( 'footagesearch_form_shotrequest', array( &$this, 'shortcode_footagesearch_form_shotrequest' ) );

        /* ФОРМЫ ФРОНТЕНДА [ ContactUs, ShotRequest ] : END ***********************************************************/

        //Add admin menu
        if(is_admin()) {
            add_action('admin_menu', array(&$this, 'admin_menu'));
        }

        //Load scripts for front-end
        add_action('wp_enqueue_scripts', array(&$this, 'wp_scripts'));
        add_action('wp_head', array(&$this, 'wp_head'));
        //Load scripts for back-end
        add_action('admin_enqueue_scripts', array(&$this, 'wp_admin_scripts'));


        //Add settings
        add_filter('admin_init', array(&$this , 'admin_init'));

        //Activation
        register_activation_hook(__FILE__, array(&$this, 'activate'));

        //Uninstall
        if (function_exists('register_uninstall_hook'))
            register_uninstall_hook(__FILE__, array(&$this, 'uninstall'));

        //Title filter
        add_filter( 'the_title', array( &$this, 'the_title' ), 10, 2 );
        //add_filter( 'wp_title', array( &$this, 'wp_title' ), 10, 2 );
//		add_filter( 'wp_title', array( &$this,'wp_top_title') , 8, 2 );
        add_filter( 'aioseop_description', array( &$this, 'meta_description' ));
        add_filter( 'aioseop_keywords', array( &$this, 'meta_keywords' ));

        add_filter('the_posts', array(&$this, 'shared_page'), -10);

        add_action('init', array(&$this,'add_rewrite_rules'));

        //add_action('init', array(&$this, 'change_page_permalink'), -1);

        //add_filter('show_admin_bar', '__return_false');
        add_action('init', array(&$this, 'remove_admin_bar'), 0);
        add_action('wp_login', array(&$this, 'wp_login'), 10, 2);
        add_action('wp_logout', array(&$this, 'wp_logout'));
        add_action('after_setup_theme', array(&$this, 'auto_login'));
        add_action('wp_footer', array(&$this, 'wp_footer'));

        //if(is_admin()){
        remove_action("admin_color_scheme_picker", "admin_color_scheme_picker");

        add_filter('user_contactmethods', array(&$this, 'update_contact_methods'), 10, 1);
        add_action('personal_options', array(&$this, 'hide_personal_options'));

        remove_filter('sanitize_title', 'sanitize_title_with_dashes');
        add_filter( 'sanitize_title', array(&$this, 'sanitize_title_with_dashes'));
	    wp_enqueue_style('frontendcss', plugins_url('/footagesearch/css/nature-green.css'), 'css');

        $this->add_seo_filters();

    }

    private function add_seo_filters()
    {
        add_filter( 'the_seo_framework_do_shortcodes_in_description', '__return_true' );
        add_filter( 'the_seo_framework_do_shortcodes_in_title', '__return_true' );
        // do not use object cache with SEO Framework plugin. It is incompatible with Object cache via memcached
        add_filter( 'the_seo_framework_use_object_cache', '__return_false' );

        add_shortcode('footagesearch_seo_search_term', array(&$this, 'shortcode_footagesearch_seo_search_term'));
        add_shortcode('footagesearch_seo_clip_name', array(&$this, 'shortcode_footagesearch_seo_clip_name'));
        add_shortcode('footagesearch_seo_contributors_name', array(&$this, 'shortcode_footagesearch_seo_contributors_name'));
        add_shortcode('footagesearch_seo_clips_licence', array(&$this, 'shortcode_footagesearch_seo_clips_licence'));
    }


    /**
     * save searchTerm value to prevent executing same code couple of times
     *
     * @var string
     */
    private $searchTerm = null;

    /**
     * @return string
     */
    private function getSearchTerm()
    {
        global $wp_query;

        $searchTerm = '';
        if(!empty($wp_query->query_vars['words']) || !empty($_REQUEST['fs'])) {
            $searchTerm = $wp_query->query_vars['words'] ?: $_REQUEST['fs'];
            $searchTerm = str_replace(['+','-'], ' ', $searchTerm);
            $searchTerm = ucwords($searchTerm);
        }

        return $searchTerm;
    }

    /**
     * shortcode for search request string
     *
     * @param array $atts
     * @param string $content
     *
     * @return mixed|string
     */
    public function shortcode_footagesearch_seo_search_term($atts = [], $content = '')
    {
        // if search term is not defined yet
        if (is_null($this->searchTerm)) {
            global $wp_query;

            $available_filters = $this->get_available_filters();
            $current_filters = $this->get_current_filters($available_filters);

            // get category(format) filter, implode it to string
            $category = '';
            if (!empty($current_filters['format_category'])) {
                $category = implode(', ', $current_filters['format_category']);
            }

            // get brand name filter value
            $brandMap = $this->brandMap;
            $brand = '';
            if (!empty($current_filters['brand']) && is_array($current_filters['brand'])) {
                array_walk($current_filters['brand'], function ($brandId) use (&$brand, $brandMap) {
                    if (array_key_exists($brandId, $brandMap)) {
                        $brand = $brandMap[$brandId];
                        return;
                    }
                });
            }
            unset($brandMap);
            // --

            // not a search result page, then search term is empty
            $searchTerm = $this->getSearchTerm();

            // define search term, so this will done once per request
            $this->searchTerm = trim(implode(' ', [$searchTerm, $category, $brand]));
        }

        return $this->searchTerm . $content;
    }

    /**
     * save clipName value to prevent execution same code couple of times
     *
     * @var string
     */
    private $clipName = null;

    /**
     * shortcode for clip name
     *
     * @param array $atts
     * @param string $content
     */
    public function shortcode_footagesearch_seo_clip_name($atts = [], $content = '')
    {
        if (is_null($this->clipName)) {
            global $wp_query;

            $title = '';

            if (!empty($wp_query->query_vars['clip_code'])) {
                if (!$this->current_clip) {
                    $this->current_clip = $this->get_clip($wp_query->query_vars['clip_code']);
                }

                if (!empty($this->current_clip['description'])) {
                    $title = $this->current_clip['description'];
                } elseif (!empty($this->current_clip['title'])) {
                    $title = $this->current_clip['title'];
                }
            }

            $this->clipName = $title;
        }

        return $this->clipName . $content;
    }

    /**
     * clip licence value
     *
     * @var string
     */
    private $clipLicence = null;

    /**
     * shortcode for licence value to use in meta tags, based on clip info
     *
     * @param array $atts
     * @param string $content
     */
    public function shortcode_footagesearch_seo_clips_licence($atts = [], $content = '')
    {
        if (is_null($this->clipLicence)) {
            $clipLicense = '';
            if (!$this->current_clip) {
                global $wp_query;
                $this->current_clip = $this->get_clip($wp_query->query_vars['clip_code']);
            }

            if (!empty($this->current_clip['license']) && !empty($this->licenses_names[$this->current_clip['license']])
            ) {
                $clipLicense = $this->licenses_names[$this->current_clip['license']];
            }

            $this->clipLicence = $clipLicense;
        }

        return $this->clipLicence . $content;
    }

    /**
     * save contributors name to prevent execution same code couple of time
     *
     * @var string
     */
    private $contributorsName = null;

    /**
     * shortcode for contributors name
     *
     * @param array $atts
     * @param string $content
     */
    public function shortcode_footagesearch_seo_contributors_name($atts = [], $content = '')
    {
        if (is_null($this->contributorsName)) {
            global $wp_query;

            $name = '';

            if (!empty($wp_query->query_vars['owner']) || !empty($wp_query->query_vars['profile'])) {
                $login = !empty($wp_query->query_vars['owner'])
                    ? $wp_query->query_vars['owner']
                    : $wp_query->query_vars['profile'];
                $login = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-_]%i', '', $login));
                $result = $this->api_request(['method' => 'get_user_by_login', 'post_params' => compact('login')]);
                $nameParts = [];
                if (!empty($result['data']['fname'])) {
                    $nameParts[] = $result['data']['fname'];
                }
                if (!empty($result['data']['lname'])) {
                    $nameParts[] = $result['data']['lname'];
                }
                $name = implode(' ', $nameParts);
            }

            $this->contributorsName = $name;
        }

        return $this->contributorsName . $content;
    }

    /**
     * @return FootageSearchPreviewDownload
     */
    public function previewDownload()
    {
        return $this->previewDownload;
    }

//    function custom_front_page($wp_query) {
//        if($wp_query->get("page_id") == get_option("page_on_front")) {
//            $wp_query->set("post_type", array('page', 'browse_page'));
//            //$wp_query->set("posts_per_page", -1);
//            //$wp_query->set('post_status', 'public');
//            //$wp_query->set("page_id", $wp_query->get("page_id"));
//            $wp_query->is_page = false;
//            $wp_query->is_single = true;
//        }
//    }


    function settings() {
//        if(get_option('footagesearch_options') == ''){
//            $options['items_perpage'] = '';
//            $options['clips_holder'] = '';
//            $options['provider_profile_page'] = '';
//            update_option('footagesearch_options', $options);
//        }

        $options['items_perpage'] = '';
        $options['clips_holder'] = '';
        $options['provider_profile_page'] = '';
        $this->settings = $options;

        $this->settings = get_option('footagesearch_options');
    }

    function register_post_types(){
        $labels = array(
            'name'                => _x('Browse pages', 'Post Type General Name', 'footagesearch'),
            'singular_name'       => _x('Browse page', 'Post Type Singular Name', 'footagesearch'),
            'menu_name'           => __('Browse page', 'footagesearch'),
            'parent_item_colon'   => __('Parent Browse page:', 'footagesearch'),
            'all_items'           => __('All Browse pages', 'footagesearch'),
            'view_item'           => __('View Browse page', 'footagesearch'),
            'add_new_item'        => __('Add New Browse page', 'footagesearch'),
            'add_new'             => __('New Browse page', 'footagesearch'),
            'edit_item'           => __('Edit Browse page', 'footagesearch'),
            'update_item'         => __('Update Browse page', 'footagesearch'),
            'search_items'        => __('Search Browse pages', 'footagesearch'),
            'not_found'           => __('No Browse pages found', 'footagesearch'),
            'not_found_in_trash'  => __('No Browse pages found in Trash', 'footagesearch'),
        );
        $args = array(
            'label'               => __( 'browse page', 'footagesearch' ),
            'description'         => __( 'Browse pages', 'footagesearch' ),
            'labels'              => $labels,
            'supports'            => array('title','editor','thumbnail'),
            'taxonomies'          => array(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => plugins_url('footagesearch/images/browse_page.png'),
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite'             => array('slug'=>'stock-footage','with_front'=>true)
        );
       //register_post_type('browse_page', $args);
        add_permastruct('browse_page', "{$args['rewrite']['slug']}/%browse_page%.htm", $args['rewrite']);


        $labels = array(
            'name'                => _x('Browse pages', 'Post Type General Name', 'footagesearch'),
            'singular_name'       => _x('Browse page', 'Post Type Singular Name', 'footagesearch'),
            'menu_name'           => __('Video Browse page', 'footagesearch'),
            'parent_item_colon'   => __('Parent Browse page:', 'footagesearch'),
            'all_items'           => __('All Browse pages', 'footagesearch'),
            'view_item'           => __('View Browse page', 'footagesearch'),
            'add_new_item'        => __('Add New Browse page', 'footagesearch'),
            'add_new'             => __('New Browse page', 'footagesearch'),
            'edit_item'           => __('Edit Browse page', 'footagesearch'),
            'update_item'         => __('Update Browse page', 'footagesearch'),
            'search_items'        => __('Search Browse pages', 'footagesearch'),
            'not_found'           => __('No Browse pages found', 'footagesearch'),
            'not_found_in_trash'  => __('No Browse pages found in Trash', 'footagesearch'),
        );
        $args = array(
            'label'               => __( 'video browse page', 'footagesearch' ),
            'description'         => __( 'Video Browse pages', 'footagesearch' ),
            'labels'              => $labels,
            'supports'            => array('title','editor','thumbnail'),
            'taxonomies'          => array(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => plugins_url('footagesearch/images/browse_page.png'),
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite'             => array('slug'=>'stock-video','with_front'=>true)
        );
       //register_post_type('video_browse_page', $args);
        add_permastruct('video_browse_page', "{$args['rewrite']['slug']}/%video_browse_page%.htm", $args['rewrite']);



        $labels = array(
            'name'                => _x('Video reel pages', 'Post Type General Name', 'footagesearch'),
            'singular_name'       => _x('Video reel page', 'Post Type Singular Name', 'footagesearch'),
            'menu_name'           => __('Video reel page', 'footagesearch'),
            'parent_item_colon'   => __('Parent Video reel page:', 'footagesearch'),
            'all_items'           => __('All Video reel pages', 'footagesearch'),
            'view_item'           => __('View Video reel page', 'footagesearch'),
            'add_new_item'        => __('Add New Video reel page', 'footagesearch'),
            'add_new'             => __('New Video reel page', 'footagesearch'),
            'edit_item'           => __('Edit Video reel page', 'footagesearch'),
            'update_item'         => __('Update Video reel page', 'footagesearch'),
            'search_items'        => __('Search Video reel pages', 'footagesearch'),
            'not_found'           => __('No Video reel pages found', 'footagesearch'),
            'not_found_in_trash'  => __('No Video reel pages found in Trash', 'footagesearch'),
        );
        $args = array(
            'label'               => __( 'video reel page', 'footagesearch' ),
            'description'         => __( 'Video reel pages', 'footagesearch' ),
            'labels'              => $labels,
            'supports'            => array('title','editor','thumbnail'),
            'taxonomies'          => array(),
            'hierarchical'        => false,
            'public'              => true,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_nav_menus'   => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => 5,
            'menu_icon'           => plugins_url('footagesearch/images/browse_page.png'),
            'can_export'          => true,
            'has_archive'         => true,
            'exclude_from_search' => false,
            'publicly_queryable'  => true,
            'capability_type'     => 'page',
            'rewrite'             => array('slug'=>'video-reels','with_front'=>true)
        );
       //register_post_type('video_reel_page', $args);
        add_permastruct('video_reel_page', "{$args['rewrite']['slug']}/%video_reel_page%.htm", $args['rewrite']);
    }

    function register_taxonomies() {

        global $wpdb;

        $labels = array(
            'name'                       => _x('Lists', 'taxonomy general name', 'footagesearch'),
            'singular_name'              => _x('List', 'taxonomy singular name', 'footagesearch'),
            'search_items'               => __('Search Lists', 'footagesearch'),
            'popular_items'              => __('Popular Lists', 'footagesearch'),
            'all_items'                  => __('All Lists', 'footagesearch'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit List', 'footagesearch'),
            'update_item'                => __('Update List', 'footagesearch'),
            'add_new_item'               => __('Add New List', 'footagesearch'),
            'new_item_name'              => __('New List Name', 'footagesearch'),
            'separate_items_with_commas' => __('Separate lists with commas', 'footagesearch'),
            'add_or_remove_items'        => __('Add or remove lists', 'footagesearch'),
            'choose_from_most_used'      => __('Choose from the most used lists', 'footagesearch'),
            'not_found'                  => __('No lists found.', 'footagesearch'),
            'menu_name'                  => __('Lists', 'footagesearch'),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array('slug' => 'list'),
        );

        //register_taxonomy('list', array('browse_page', 'video_browse_page', 'video_reel_page'), $args);

        $wpdb->listmeta = $wpdb->prefix . 'listmeta';


        $labels = array(
            'name'                       => _x('List Items', 'taxonomy general name', 'footagesearch'),
            'singular_name'              => _x('List Item', 'taxonomy singular name', 'footagesearch'),
            'search_items'               => __('Search List Items', 'footagesearch'),
            'popular_items'              => __('Popular List Items', 'footagesearch'),
            'all_items'                  => __('All List Items', 'footagesearch'),
            'parent_item'                => null,
            'parent_item_colon'          => null,
            'edit_item'                  => __('Edit List Item', 'footagesearch'),
            'update_item'                => __('Update List Items', 'footagesearch'),
            'add_new_item'               => __('Add New List Item', 'footagesearch'),
            'new_item_name'              => __('New List Item Name', 'footagesearch'),
            'separate_items_with_commas' => __('Separate list items with commas', 'footagesearch'),
            'add_or_remove_items'        => __('Add or remove list items', 'footagesearch'),
            'choose_from_most_used'      => __('Choose from the most used list items', 'footagesearch'),
            'not_found'                  => __('No list items found.', 'footagesearch'),
            'menu_name'                  => __('List Items', 'footagesearch'),
        );

        $args = array(
            'hierarchical'          => true,
            'labels'                => $labels,
            'show_ui'               => true,
            'show_admin_column'     => true,
            'update_count_callback' => '_update_post_term_count',
            'query_var'             => true,
            'rewrite'               => array('slug' => 'list_item'),
        );

       //register_taxonomy('list_item', array('browse_page', 'video_browse_page', 'video_reel_page'), $args);

        $wpdb->list_itemmeta = $wpdb->prefix . 'list_itemmeta';
    }

    function list_add_form_fields($tag){
        ?>

        <div class="form-field">
            <label for="list_meta[sort_order]"><?php _e('Display Order', 'footagesearch') ?></label>
            <input type="text" name="list_meta[sort_order]" id="list_meta[sort_order]" />
        </div>

        <div class="form-field">
            <label for="list_meta[section_type]"><?php _e('Section Type', 'footagesearch') ?></label>
            <select name="list_meta[section_type]" id="list_meta[section_type]">
                <option value="cliplist">Clip list</option>
                <option value="linklist">Link list</option>
                <option value="textarea">Textarea</option>
            </select>
        </div>

    <?php
    }

    function list_edit_form_fields($tag){

        $list_meta = get_metadata('list', $tag->term_id);

        ?>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_meta[sort_order]"><?php _e('Display Order', 'footagesearch') ?></label>
            </th>
            <td>
                <input type="text" name="list_meta[sort_order]" id="list_meta[sort_order]" value="<?php echo esc_attr($list_meta['sort_order'][0]); ?>" />
            </td>
        </tr>


        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_meta[section_type]"><?php _e('Section Type', 'footagesearch') ?></label>
            </th>
            <td>
                <select name="list_meta[section_type]" id="list_meta[section_type]">
                    <option value="cliplist"<?php if($list_meta['section_type'][0] == 'cliplist') echo ' selected'; ?>>Clip list</option>
                    <option value="linklist"<?php if($list_meta['section_type'][0] == 'linklist') echo ' selected'; ?>>Link list</option>
                    <option value="textarea"<?php if($list_meta['section_type'][0] == 'textarea') echo ' selected'; ?>>Textarea</option>
                </select>
            </td>
        </tr>

    <?php
    }

    function save_list_meta($term_id) {

        if (isset($_POST['list_meta'])) {
            foreach($_POST['list_meta'] as $meta_key => $meta_value){
                $meta_value = esc_attr($meta_value);
                update_metadata('list', $term_id, $meta_key, $meta_value);
            }
        }
    }

    function list_item_add_form_fields($tag){

        $lists = get_terms('list', array('hide_empty' => false));

        ?>

        <div class="form-field">
            <label for="list_item_meta[sort_order]"><?php _e('Sort Order', 'footagesearch') ?></label>
            <input type="text" name="list_item_meta[sort_order]" id="list_item_meta[sort_order]" />
        </div>

        <div class="form-field">
            <label for="list_item_meta[clip_id]"><?php _e('Clip ID', 'footagesearch') ?></label>
            <input type="text" name="list_item_meta[clip_id]" id="list_item_meta[clip_id]" />
            <p class="description">Only for clip list type</p>
        </div>

        <div class="form-field">
            <label for="list_item_meta[url]"><?php _e('Destination URL', 'footagesearch') ?></label>
            <input type="text" name="list_item_meta[url]" id="list_item_meta[url]" />
        </div>

        <div class="form-field">
            <label for="list_item_meta[indention]"><?php _e('Indention', 'footagesearch') ?></label>
            <input type="text" name="list_item_meta[indention]" id="list_item_meta[indention]" />
            <p class="description">Only for link list type</p>
        </div>

        <div class="form-field">
            <label for="list_item_meta[content]"><?php _e('Content', 'footagesearch') ?></label>
            <?php wp_editor('', 'list_item_meta[content]', array('textarea_rows' => 10)); ?>
            <p class="description">Only for textarea type</p>
        </div>

        <div class="form-field">
            <label for="list_item_meta[url_type]"><?php _e('URL Type', 'footagesearch') ?></label>
            <select name="list_item_meta[url_type]" id="list_item_meta[url_type]">
                <option value="regular">Regular</option>
                <option value="popup">Popup</option>
                <option value="new window">New Window</option>
            </select>
            <p class="description">Only for clips list type</p>
        </div>

        <?php if($lists) { ?>

            <div class="form-field">
                <label for="list_item_meta[list_id]"><?php _e('List', 'footagesearch') ?></label>
                <select name="list_item_meta[list_id]" id="list_item_meta[list_id]">
                    <option value="0"></option>
                    <?php foreach($lists as $list_item) { ?>
                        <option value="<?php echo $list_item->term_id; ?>"><?php echo $list_item->name; ?></option>
                    <?php } ?>
                </select>
            </div>

        <?php } ?>

        <?php
    }

    function list_item_edit_form_fields($tag){

        $list_item_meta = get_metadata('list_item', $tag->term_id);
        $lists = get_terms('list', array('hide_empty' => false));

        $clip_id = $list_item_meta['clip_id'][0];
        $clip_code = $clip_id;
        if ($clip_id && is_numeric($clip_id)) {
            $clip_code = $this->get_clip_code_by_id($clip_id);
        }

        ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[sort_order]"><?php _e('Sort Order', 'footagesearch') ?></label>
            </th>
            <td>
                <input type="text" name="list_item_meta[sort_order]" id="list_item_meta[sort_order]" value="<?php echo esc_attr($list_item_meta['sort_order'][0]); ?>" />
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[clip_id]"><?php _e('Clip ID', 'footagesearch') ?></label>
            </th>
            <td>
                <input type="text" name="list_item_meta[clip_id]" id="list_item_meta[clip_id]" value="<?php echo esc_attr($clip_code); ?>" />
                <p class="description">Only for clips list type</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[url]"><?php _e('Destination URL', 'footagesearch') ?></label>
            </th>
            <td>
                <input type="text" name="list_item_meta[url]" id="list_item_meta[url]" value="<?php echo esc_attr($list_item_meta['url'][0]); ?>" />
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[indention]"><?php _e('Indention', 'footagesearch') ?></label>
            </th>
            <td>
                <input type="text" name="list_item_meta[indention]" id="list_item_meta[indention]" value="<?php echo esc_attr($list_item_meta['indention'][0]); ?>" />
                <p class="description">Only for link list type</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[content]"><?php _e('Content', 'footagesearch') ?></label>
            </th>
            <td>
                <label for="list_item_meta[content]"><?php _e('Content', 'footagesearch') ?></label>
                <?php wp_editor($list_item_meta['content'][0], 'list_item_meta[content]', array('textarea_rows' => 10)); ?>
                <p class="description">Only for textarea type</p>
            </td>
        </tr>

        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[url_type]"><?php _e('URL Type', 'footagesearch') ?></label>
            </th>
            <td>
                <select name="list_item_meta[url_type]" id="list_item_meta[url_type]">
                    <option value="regular"<?php if($list_item_meta['url_type'][0] == 'regular') echo ' selected'; ?>>Regular</option>
                    <option value="popup"<?php if($list_item_meta['url_type'][0] == 'popup') echo ' selected'; ?>>Popup</option>
                    <option value="new window"<?php if($list_item_meta['url_type'][0] == 'new window') echo ' selected'; ?>>New Window</option>
                </select>
                <p class="description">Only for clips list type</p>
            </td>
        </tr>

        <?php if($lists) { ?>
        <tr class="form-field">
            <th scope="row" valign="top">
                <label for="list_item_meta[list_id]"><?php _e('List', 'footagesearch') ?></label>
            </th>
            <td>
                <select name="list_item_meta[list_id]" id="list_item_meta[list_id]">
                    <option value="0"></option>
                    <?php foreach($lists as $list_item) { ?>
                        <option value="<?php echo $list_item->term_id; ?>"<?php if($list_item->term_id == $list_item_meta['list_id'][0]) echo ' selected'; ?>><?php echo $list_item->name; ?></option>
                    <?php } ?>
                </select>
            </td>
        </tr>
        <?php } ?>

    <?php
    }

    function save_list_item_meta($term_id) {

        if (isset($_POST['list_item_meta'])) {
            foreach($_POST['list_item_meta'] as $meta_key => $meta_value){
                if ($meta_key == 'clip_id' /*&& is_numeric($meta_value)*/) {
                    $meta_value = $this->get_clip_id_by_code($meta_value);
                    $thumb_url = $this->get_clip_thumb($meta_value);
                    update_metadata('list_item', $term_id, 'clip_thumb', $thumb_url);
                }
                $meta_value = $meta_key == 'content' ? $meta_value : esc_attr($meta_value);
                update_metadata('list_item', $term_id, $meta_key, $meta_value);
            }
        }
    }

    function handle_footagesearch_request(){
        if (isset($_REQUEST['footagesearch_action'])){
            $action = $_REQUEST['footagesearch_action'];
            switch ($action) {
                case 'all_galleries':
                    $this->get_all_galleries();
                    break;
                case 'all_categories':
                    $this->get_all_categories();
                    break;
                case 'add_follower':
                    $current_user = wp_get_current_user();
                    if(0 != $current_user->ID){
                        $this->add_follower($current_user->user_login);
                    }
                    break;

                /*case 'savePrepageUser':
                    $this->savePrepageUser();
                    break;*/
                default : $this->$action(); break;
            }
        }
    }

    /**
     * handle request to load ajax inline popup
     */
    public function handle_footagesearch_inline_popup() {
        if (!empty($_REQUEST['footagesearch_inlinepopup'])) {

            // get clip data by api call if cached value is not enabled
            if (empty($this->current_clip)) {
                $this->current_clip = $this->get_clip(
                    $_REQUEST['footagesearch_inlinepopup']['clip_id']
                );
            }

            // get current clip value
            $clip = $this->current_clip;

            // get clipbin value
            global $footagesearch_clipbin;
            $in_clipbin = array();
            $clipbin_content = $footagesearch_clipbin->get_current_bin_content();
            if($clipbin_content){
                foreach($clipbin_content as $clipbin_item){
                    $in_clipbin[] = $clipbin_item['id'];
                }
            }
            // --
            
            // get in cart value
            $in_cart = array();
            if(!empty($_SESSION['footagesearch_cart'])){
                foreach($_SESSION['footagesearch_cart'] as $cart_item){
                    $in_cart[] = $cart_item['id'];
                }
            }
            // --

            // get clip holder value
            $clip_holder = false;
            if($this->settings['clip_holder']){

                if(is_numeric($this->settings['clip_holder'])){
                    $clip_holder = get_page((int)$this->settings['clip_holder'], ARRAY_A);
                }
                else{
                    $clip_holder = get_page_by_path($this->settings['clip_holder'], ARRAY_A);
                }

            }
            
            $clips_holder = $this->get_clips_holder_page();
            $clips_holder_permalink = get_permalink($clips_holder['ID']);
            // --

            //Get clip ecommerce info, used to output deleivery format data
            $ecommerce = api_request(array('method' => 'cartclip', 'post_params' => array('clip_id' => $clip['id'])));
            if($ecommerce && $ecommerce['data']) {
                $clip = array_merge($clip, $ecommerce['data']);
            }
            //

            // get clip price, by licence and selected options before. 10 seconds used directly, as it is used
            // in js api call in video-controls.js
            global $footagesearch_cart;
            $clipPriceData = $footagesearch_cart->get_clip_price_data($clip['id'], $clip['license'],['duration' => 10]);

            $template = $this->fsec_get_template_file_path('footagesearch-inline-popup.php');

            $output = '';
            if($template){
                ob_start();
                include_once($template);
                $output = ob_get_contents();
                ob_end_clean();
            }

//            header('Content-Type: application/json');
//            echo json_encode(['html' => $output]);
            echo $output;
            die();
        }
    }


    function add_browse_page_box(){
        add_meta_box(
            'browse_page_meta',
            __('Browse page details', 'footagesearch'),
            array(&$this,'browse_page_meta_box'),
            'browse_page'
        );
        add_meta_box(
            'browse_page_meta',
            __('Video Browse page details', 'footagesearch'),
            array(&$this,'browse_page_meta_box'),
            'video_browse_page'
        );
        add_meta_box(
            'video_reel_page_meta',
            __('Video reel page details', 'footagesearch'),
            array(&$this,'video_reel_page_meta_box'),
            'video_reel_page'
        );
        add_meta_box(
            'browse_page_meta',
            __('Browse page details', 'footagesearch'),
            array(&$this,'browse_page_meta_box'),
            'post'
        );

    }


    function browse_page_meta_box( $post ) {

        wp_nonce_field(plugin_basename( __FILE__ ), 'footagesearch');

        echo '<table class="form-table">';

        $value = get_post_meta($post->ID, 'browse_page_thumbnail_url', true);
        echo '<tr><th><label for="browse_page_thumbnail_url">';
        _e('Thumbnail URL', 'footagesearch' );
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_thumbnail_url" name="browse_page_thumbnail_url" value="'.esc_attr($value).'" /></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_layout', true);
        echo '<tr><th><label for="browse_page_thumbnail_url">';
        _e('Body Layout Options', 'footagesearch' );
        echo '</label> ';
        ?>
        <td>
        <input type="radio" name="browse_page_layout" value="text_left_movie_right"<?php if($value == 'text_left_movie_right' || !$value) echo ' checked';?>> Text Left, Movie Right<br>
        <input type="radio" name="browse_page_layout" value="movie_top_text_bottom"<?php if($value == 'movie_top_text_bottom') echo ' checked';?>> Movie Top / Text Bottom<br>
        <input type="radio" name="browse_page_layout" value="no_movie"<?php if($value == 'no_movie') echo ' checked';?>> No Movie at all</td></tr>
        <?php

        $value = get_post_meta($post->ID, 'browse_page_category_title', true);
        echo '<tr><th><label for="browse_page_category_title">';
        _e('Page Category Title', 'footagesearch' );
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_category_title" name="browse_page_category_title" value="'.esc_attr($value).'" />';
        echo '<p class="description">Shown at the top of the body area</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_show_in_widget', true);
        echo '<tr><th><label for="browse_page_show_in_widget">';
        _e('Show in widget', 'footagesearch' );
        echo '</label></th> ';
        ?>
        <td>
            <input type="checkbox" name="browse_page_show_in_widget" value="yes"<?php if($value == 'yes') echo ' checked';?>></td></tr>
        <?php


        $value = get_post_meta($post->ID, 'browse_page_sort', true);
        echo '<tr><th><label for="browse_page_sort">';
        _e('Sort', 'footagesearch' );
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_sort" name="browse_page_sort" value="'.esc_attr($value).'" /></td></tr>';

        echo '</table>';

        echo '<h4>';
        _e('Video', 'footagesearch');
        echo '</h4> ';

        echo '<table class="form-table">';

        $value = get_post_meta($post->ID, 'browse_page_video_url', true);
        echo '<tr><th><label for="browse_page_video_url">';
        _e('Video URL', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_video_url" name="browse_page_video_url" value="'.esc_attr($value).'" /></td></tr>';


        $value = get_post_meta($post->ID, 'browse_page_video_width', true);
        echo '<tr><th><label for="browse_page_video_width">';
        _e('Width', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_video_width" name="browse_page_video_width" value="'.esc_attr($value).'" />';
        echo '<p class="description">A normal video is set to 432 X 240</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_video_height', true);
        echo '<tr><th><label for="browse_page_video_height">';
        _e('Height', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_video_height" name="browse_page_video_height" value="'.esc_attr($value).'" />';
        echo '<p class="description">A normal video is set to 432 X 240</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_video_autoplay', true);
        echo '<tr><th><label for="browse_page_video_autoplay">';
        _e('Autoplay', 'footagesearch' );
        echo '</label></th> ';
        ?>
        <td>
            <input type="checkbox" name="browse_page_video_autoplay" value="yes"<?php if($value == 'yes') echo ' checked';?>></td></tr>
        <?php

        $value = get_post_meta($post->ID, 'browse_page_video_looping', true);
        echo '<tr><th><label for="browse_page_video_looping">';
        _e('Looping', 'footagesearch' );
        echo '</label></th> ';
        ?>
        <td>
        <input type="radio" name="browse_page_video_looping" value="yes"<?php if($value == 'yes' || !$value) echo ' checked';?>> Loop Movie<br>
        <input type="radio" name="browse_page_video_looping" value="no"<?php if($value == 'no') echo ' checked';?>> No Looping</td></tr>
        <?php

        $value = get_post_meta($post->ID, 'browse_page_video_sound', true);
        echo '<tr><th><label for="browse_page_video_sound">';
        _e('Audio', 'footagesearch' );
        echo '</label></th> ';
        ?>
        <td>
        <input type="radio" name="browse_page_video_sound" value="no"<?php if($value == 'no') echo ' checked';?>> Mute<br>
        <input type="radio" name="browse_page_video_sound" value="yes"<?php if($value == 'yes' || !$value) echo ' checked';?>> Sound On</td></tr>
        <tr><td>
        <?php

        $value = get_post_meta($post->ID, 'browse_page_vide_overlay_text', true);
        echo '<label for="browse_page_vide_overlay_text">';
        _e('Overlay text', 'footagesearch');
        echo '</label></td><td>';
        echo wp_editor($value, "browse_page_vide_overlay_text", array(
       'textarea_name' => 'browse_page_vide_overlay_text',
                'tinymce' => true));

        //echo '</td></tr>';

     //echo '<td><textarea id="browse_page_vide_overlay_text" name="browse_page_vide_overlay_text">'.esc_attr($value).'</textarea></td></tr>';

        echo '</table>';

        echo '<h4>';
        _e('Facebook / Social Media Links', 'footagesearch');
        echo '</h4> ';

        echo '<table class="form-table">';

        $value = get_post_meta($post->ID, 'browse_page_social_title', true);
        echo '<tr><th><label for="browse_page_social_title">';
        _e('Title', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_title" name="browse_page_social_title" value="'.esc_attr($value).'" />';
        echo '<p class="description">Title of the Video, Google Plus\'s name field</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_social_description', true);
        echo '<tr><th><label for="browse_page_social_description">';
        _e('Description', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_description" name="browse_page_social_description" value="'.esc_attr($value).'" />';
        echo '<p class="description">Description of the Video, Google Plus\'s description</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_social_url', true);
        echo '<tr><th><label for="browse_page_social_url">';
        _e('URL', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_url" name="browse_page_social_url" value="'.esc_attr($value).'" />';
        echo '<p class="description">URL of the Page the OG code and video is able to be played on</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_social_facebook_id', true);
        echo '<tr><th><label for="browse_page_social_facebook_id">';
        _e('Facebook ID', 'footagesearch');

        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_facebook_id" name="browse_page_social_facebook_id" value="'.esc_attr($value).'" />';
        echo '<p class="description">ID for facebook application</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_social_image', true);
        echo '<tr><th><label for="browse_page_social_image">';
        _e('Image', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_image" name="browse_page_social_image" value="'.esc_attr($value).'" />';
        echo '<p class="description">URL to the direct path of the video Thumbnail Image Same for Google Plus</p></td></tr>';

        $value = get_post_meta($post->ID, 'browse_page_social_video', true);
        echo '<tr><th><label for="browse_page_social_video">';
        _e('Video', 'footagesearch');
        echo '</label></th> ';
        echo '<td><input type="text" id="browse_page_social_video" name="browse_page_social_video" value="'.esc_attr($value).'" />';
        echo '<p class="description">URL to the direct path of the video</p></td></tr>';

        echo '</table><br><br>';

        $value = get_post_meta($post->ID, 'browse_page_text_under_video', true);
        echo '<label for="browse_page_text_under_video">';
        _e('Area under Movie, for adding links below movie, additional text, etc...', 'footagesearch');
        echo '</label>';
        echo wp_editor($value, "browse_page_text_under_video", array('textarea_rows' => 10));

    }

    function browse_page_save_meta($post_id) {

        // First we need to check if the current user is authorised to do this action.
        if ( 'page' == $_REQUEST['post_type'] || 'browse_page' == $_REQUEST['post_type'] || 'video_browse_page' == $_REQUEST['post_type'] || 'video_reel_page' == $_REQUEST['post_type']) {
            if (!current_user_can('edit_page', $post_id))
                return;
        } else {
            if (!current_user_can('edit_post', $post_id))
                return;
        }

        if (!isset( $_POST['footagesearch'] ) || ! wp_verify_nonce($_POST['footagesearch'], plugin_basename( __FILE__ ) ) )
            return;

        $post_ID = $_POST['post_ID'];
        if(!isset($_POST['browse_page_show_in_widget']))
            $_POST['browse_page_show_in_widget'] = 'no';
        if(!isset($_POST['browse_page_video_autoplay']))
            $_POST['browse_page_video_autoplay'] = 'no';

        foreach($_POST as $field => $value){
            if(strpos($field, 'browse_page') !== false){
                $meta_key = $field;
                if ($meta_key != 'browse_page_vide_overlay_text'){
                    $meta_value = sanitize_text_field($value);
                }else{
                    $meta_value = $value;
                }
                add_post_meta($post_ID, $meta_key, $meta_value, true) or
                update_post_meta($post_ID, $meta_key, $meta_value);
            }
        }

    }

    function add_browse_pages_to_dropdown( $select )
    {
        if (FALSE === strpos($select, 'page_on_front'))
            return $select;

        $browse_pages = get_posts(array('post_type' => 'browse_page', 'numberposts' => -1));

        if (!$browse_pages)
            return $select;

        $browse_pages_options = walk_page_dropdown_tree($browse_pages, 0,
            array(
                'depth' => 0
            ,  'child_of' => 0
            ,  'selected' => get_option('page_on_front')
            ,  'echo' => 0
            ,  'name' => 'page_on_front'
            ,  'id' => ''
            ,  'show_option_none' => ''
            ,  'show_option_no_change' => ''
            ,  'option_none_value' => ''
            )
        );

        return str_replace( '</select>', $browse_pages_options . '</select>', $select );
    }

    // Adding a new rule and tags
    function add_rewrite_rules(){
        /**
         * WP_Rewrite
         */
        global $wp_rewrite;
        // get existed rules
        $rewriteRules = $wp_rewrite->wp_rewrite_rules();
        // flag ti determine if we should update rwrite rules
        $updateRules = false;

        $page = $this->get_clips_holder_page();
        $permalink = get_permalink($page['ID']);
        $permalink = str_replace('/', '', parse_url($permalink, PHP_URL_PATH));

        add_rewrite_tag("%license_name%", '([^/]+)');
        add_rewrite_tag("%words%", '([^/]+)');
        add_rewrite_tag("%owner%", '([^/]+)');
        add_rewrite_tag("%gallery%", '([^/]+)');
        $rule = '^(' . $permalink . '|' . implode('|', array_keys($this->licenses_names_map)) . ')/?([^/]*)/?([^/]*)/?';
        add_rewrite_rule($rule, 'index.php?page_id=' . $page['ID'] . '&license_name=$matches[1]&words=$matches[2]&owner=$matches[3]','top');
        $updateRules = $updateRules || !array_key_exists($rule, $rewriteRules);

        add_rewrite_tag("%price_level%", '([^/]+)');
        $rule = '^(' . implode('|', array_keys($this->prices_levels_map)) . ')/?([^/]*)/?([^/]*)/?';
        add_rewrite_rule($rule, 'index.php?page_id=' . $page['ID'] . '&price_level=$matches[1]&words=$matches[2]&owner=$matches[3]','top');
        $updateRules = $updateRules || !array_key_exists($rule, $rewriteRules);

        add_rewrite_tag("%format_category%", '([^/]+)');
        $rule = '^(' . implode('|', array_keys($this->format_category_map)) . ')/?([^/]*)/?([^/]*)/?';
        add_rewrite_rule($rule, 'index.php?page_id=' . $page['ID'] . '&format_category=$matches[1]&words=$matches[2]&owner=$matches[3]','top');
        $updateRules = $updateRules || !array_key_exists($rule, $rewriteRules);

        $clip_page = $this->get_clip_holder_page();
        $clip_permalink = get_permalink($clip_page['ID']);
        $clip_permalink = str_replace('/', '', parse_url($clip_permalink, PHP_URL_PATH));

        add_rewrite_tag("%clip_code%", '([^/]+)');
        $clip_rule = '^(' . $clip_permalink . ')/?([^/]*)/?';
        add_rewrite_rule($clip_rule, 'index.php?page_id=' . $clip_page['ID'] . '&clip_code=$matches[2]','top');
        $updateRules = $updateRules || !array_key_exists($clip_rule, $rewriteRules);

        //provider page
        add_rewrite_tag('%profile%', '([^/]+)');
        $pageId = $this->settings['provider_profile_page'];
        $provideProfileRule = 'contributor\/profile\/(.+)';
        add_rewrite_rule($provideProfileRule, 'index.php?page_id=' . $pageId . '&profile=$matches[1]','top');
        $updateRules = $updateRules || !array_key_exists($provideProfileRule, $rewriteRules);
        unset($provideProfileRule);

        // flush_rules() if our rules are not yet included
        if ($updateRules){
            $wp_rewrite->flush_rules();
        }
    }

    function change_page_permalink() {
        global $wp_rewrite;
        if ( strstr($wp_rewrite->get_page_permastruct(), '.html') != '.html' )
            $wp_rewrite->page_structure = $wp_rewrite->page_structure . '.html';
    }

    function the_title($title, $post_id = 0){
        if (is_admin())
            return $title;

        if (($this->is_clips_page($post_id) || $this->is_clip_page($post_id)) && in_the_loop()){
            $new_title = $this->get_title();
            if($new_title)
                $title = $new_title;
        }
        return $title;
    }

    function wp_top_title( $title, $sep ) {
        global $page;

        if ($this->is_clips_page($page) || $this->is_clip_page($page)){
             $new_title = $this->get_meta_title();
            if($new_title)
                $title = $new_title . ' ';
        }
        return $title;
    }

    function meta_description($description) {
        global $page;

        if ($this->is_clips_page($page) || $this->is_clip_page($page)) {
            $new_description = $this->get_meta_description();
            if($new_description)
                $description = __('Nature Footage comprehensive stock video footage of', 'footagesearch') . ' '
                    . strtolower($new_description) . '. ' . $description;
        }
        return $description;
    }

    function meta_keywords($keywords) {
        global $page;

        if ($this->is_clips_page($page) || $this->is_clip_page($page)) {
            $new_keywords = $this->get_meta_keywords();
            if($new_keywords)
                $keywords = strtolower($new_keywords) . ', ' . $keywords;
        }
        return $keywords;
    }

    function get_title() {
        global $wp_query;
        $title = '';
        if(isset($wp_query->query_vars['clip_code']) && $wp_query->query_vars['clip_code']){
            if ($this->current_clip)
                $clip = $this->current_clip;
            else {
                $clip = $this->get_clip($wp_query->query_vars['clip_code']);
            }
            if($clip){
                $this->current_clip = $clip;
                $title = $clip['description'] ? $clip['description'] : $clip['title'];
            }
        }
        else{

            global $wp_query;
            if(isset($wp_query->query_vars['format_category']) && $wp_query->query_vars['format_category']) {
                if (is_array($wp_query->query_vars['format_category'])) {
                    $title_parts[] = implode(' ', $wp_query->query_vars['format_category']);
                }
                else {
                    if (isset($this->format_category_map[$wp_query->query_vars['format_category']])) {
                        $title_parts[] = $this->format_category_map[$wp_query->query_vars['format_category']];
                    }
                }
            }
            if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
                $word = urldecode($wp_query->query_vars['words']);
//                $word_parts = explode('-', $word);
//                $last_part = end($word_parts);
//                if (in_array($last_part, $this->collections_suffixes)) {
//                    array_pop($word_parts);
//                    $word = implode('-', $word_parts);
//                }
                $title_parts[] = str_replace('-', ' ', $word);
            }

            if(isset($_REQUEST['fs']) && $_REQUEST['fs'])
                $title_parts[] = stripcslashes(urldecode($_REQUEST['fs']));

            if(isset($wp_query->query_vars['gallery']) && $wp_query->query_vars['gallery']){
                $gallery=$this->get_gallery($wp_query->query_vars['gallery']);
                $title_parts[] = $gallery['title'].' Gallery by '.$gallery['company_name'];//.' from '.$wp_query->query_vars['gallery'].' gallery';
            }

            if(isset($wp_query->query_vars['owner']) && $wp_query->query_vars['owner']){
                $provider=$this->get_backend_user_by_login($wp_query->query_vars['owner']);
                $title_parts[] = ' from '.$provider['data']['fname'].' '.$provider['data']['lname'];
            }

            if (isset($wp_query->query_vars['license_name']) && $wp_query->query_vars['license_name']
                && isset($this->licenses_names_map[$wp_query->query_vars['license_name']])) {
                $license_id = $this->licenses_names_map[$wp_query->query_vars['license_name']];
                if (isset($this->licenses_names[$license_id])) {
                    $title_parts[] = $this->licenses_names[$license_id];
                }
            }
            elseif (isset($_REQUEST['license'])) {
                if (is_array($_REQUEST['license'])) {
                    $licenses = array();
                    foreach ($_REQUEST['license'] as $license) {
                        if (isset($this->licenses_names[$license])) {
                            $licenses[] = $this->licenses_names[$license];
                        }
                    }
                    $title_parts[] = implode(' ', $licenses);
                }
            }

            if(isset($wp_query->query_vars['price_level']) && $wp_query->query_vars['price_level']) {
                if (is_array($wp_query->query_vars['price_level'])) {
                    $price_levels = array();
                    foreach ($wp_query->query_vars['price_level'] as $level) {
                        if (isset($this->prices_levels_names[$level])) {
                            $price_levels[] = $this->prices_levels_names[$level];
                        }
                    }
                    $title_parts[] = implode(' ', $price_levels);
                }
                else {
                    if (isset($this->prices_levels_map[$wp_query->query_vars['price_level']])) {
                        if (isset($this->prices_levels_names[$this->prices_levels_map[$wp_query->query_vars['price_level']]])) {
                            $title_parts[] = $this->prices_levels_names[$this->prices_levels_map[$wp_query->query_vars['price_level']]];
                        }
                    }
                }
            }

            if($title_parts)
                $title = '<span style="font-weight: 100">'.__('Search Results', 'footagesearch') . ':</span> <strong id="pagetitle">' . stripcslashes(urldecode(ucwords(implode(' ', $title_parts))).'</strong>');
        }
        return $title;
    }

    function get_meta_title() {
        global $wp_query;
        $title = '';
        if(isset($wp_query->query_vars['clip_code']) && $wp_query->query_vars['clip_code']){
            if ($this->current_clip)
                $clip = $this->current_clip;
            else {
                $clip = $this->get_clip($wp_query->query_vars['clip_code']);
            }
            if($clip){
                $this->current_clip = $clip;
                $title = $clip['description'] ? $clip['description'] : $clip['title'];
            }
        }
        else{

            global $wp_query;
            if(isset($wp_query->query_vars['format_category']) && $wp_query->query_vars['format_category'] && !is_array($wp_query->query_vars['format_category'])) {
                if (isset($this->format_category_map[$wp_query->query_vars['format_category']])) {
                    $title_parts[] = $this->format_category_map[$wp_query->query_vars['format_category']];
                }

            }
            if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
                $word = $wp_query->query_vars['words'];
                $title_parts[] = str_replace('-', ' ', $word);
            }

            if(isset($_REQUEST['fs']) && $_REQUEST['fs'])
                $title_parts[] = stripcslashes ($_REQUEST['fs']);
            if(isset($wp_query->query_vars['owner']) && $wp_query->query_vars['owner']){
                $provider=$this->get_backend_user_by_login($wp_query->query_vars['owner']);
                $title_parts[] = ' by '.$provider['data']['fname'].' '.$provider['data']['lname'];
            }


            if($title_parts)
                $title = urldecode(ucwords(implode(' ', $title_parts)));
        }
        return $title;
    }

    function get_meta_description() {
        global $wp_query;
        $description_parts = array();
        if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
            $word = $wp_query->query_vars['words'];
            $description_parts[] = str_replace('-', ' ', $word);
        }
        if(isset($_REQUEST['fs']) && $_REQUEST['fs']) {
            $description_parts[] = stripcslashes (urldecode($_REQUEST['fs']));
        }
        return implode(' ', $description_parts);
    }

    function get_meta_keywords() {
        global $wp_query;
        $description_parts = array();
        if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
            $word = $wp_query->query_vars['words'];
            $description_parts[] = str_replace('-', ' ', $word);
        }
        if(isset($_REQUEST['fs']) && $_REQUEST['fs']) {
            $description_parts[] = stripcslashes (urldecode($_REQUEST['fs']));
        }
        return implode(' ', $description_parts);
    }

    function is_clips_page($page_id = '') {
        if(empty($page_id)){
            global $wp_query;
            if($wp_query->is_page)
                $page_id = $wp_query->get_queried_object_id();
        }

        if(is_numeric($this->settings['clips_holder'])){
            $is_clips_page = ($page_id == $this->settings['clips_holder']);
        }
        else{
            $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            $is_clips_page = ($page_id == $clips_holder['ID']);
        }

        return $is_clips_page;
    }

    function is_clip_page($page_id = '') {
        if(empty($page_id)){

            global $wp_query;
            if($wp_query->is_page)
                $page_id = $wp_query->get_queried_object_id();
        }

        if(is_numeric($this->settings['clip_holder'])){
            $is_clip_page = ($page_id == $this->settings['clip_holder']);
        }
        else{
            $clip_holder = get_page_by_path($this->settings['clip_holder'], ARRAY_A);
            $is_clip_page = ($page_id == $clip_holder['ID']);
        }

        return $is_clip_page;
    }


    function remove_admin_bar() {
        show_admin_bar(false);
//        wp_deregister_script( 'admin-bar' );
//        wp_deregister_style( 'admin-bar' );
//        remove_action( 'init', '_wp_admin_bar_init' );
//        remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
//        remove_action( 'admin_footer', 'wp_admin_bar_render', 1000 );
        // maybe also: 'wp_head'
        foreach ( array( 'admin_head' ) as $hook ) {
            add_action(
                $hook,
                create_function(
                    '',
                    "echo '<style>
    #wpadminbar { display: none;}
    html.wp-toolbar { padding-top: 0px !important; }</style>';"
                )
            );
        }
    }

//    function wp_login($user_login, $user){
//        $all = array();
//        if(isset($_COOKIE['wp-settings-' . $user->ID])){
//            parse_str($_COOKIE['wp-settings-' . $user->ID], $all);
//        }
//        $all['mfold'] = 'f';
//        setcookie('wp-settings-' . $user->ID, http_build_query($all), time() + YEAR_IN_SECONDS, SITECOOKIEPATH);
//        set_user_setting('mfold', 'f');
//
//        if(in_array('administrator', $user->roles)){
//            if (strpos($_SERVER['HTTP_USER_AGENT'], 'Safari') && !strpos($_SERVER['HTTP_USER_AGENT'], 'Chrome')) {
//                $provider_id = get_option('provider_id');
//                $token = get_option('user_token');
//                $backend_url = trim(get_option('backend_url'), '/') . '/en/login?login=' . urlencode( $user_login ) . '&id=' . $provider_id . '&token='
//                    . $token . '&redirect_url=http://' . $_SERVER['HTTP_HOST'] . '/login/';
//                header('Location: ' . $backend_url);
//                exit();
//            }
//        }
//    }

    function wp_login($user_login, $user){
        $all = array();
        if(isset($_COOKIE['wp-settings-' . $user->ID])){
            parse_str($_COOKIE['wp-settings-' . $user->ID], $all);
        }
        $all['mfold'] = 'f';
        setcookie('wp-settings-' . $user->ID, http_build_query($all), time() + YEAR_IN_SECONDS, SITECOOKIEPATH);
        set_user_setting('mfold', 'f');

        $redirect_to = 'http://' . $_SERVER['HTTP_HOST'] . '/login/';
        $redirect_to = apply_filters('login_redirect', $redirect_to, isset($_SESSION['redirect_to']) ? $_SESSION['redirect_to'] : '', null);
        if(isset($_SESSION['redirect_to']))
            unset($_SESSION['redirect_to']);

        $token = $_SESSION['user_token'];
        if(isset($_POST['redirect_to']) AND ($_POST['redirect_to'] != '')){
            $redirect_to = $_POST['redirect_to'];
        }

        $backend_url = trim(get_option('backend_url'), '/') . '/en/login?login=' . urlencode($user_login)
            . '&token=' . $token . '&redirect_url=' . urlencode($redirect_to);
        header('Location: ' . $backend_url);
        exit();
    }

    function wp_logout(){
        $backend_url = trim(get_option('backend_url'), '/') . '/en/login?logout=1&redirect_url=http://' . $_SERVER['HTTP_HOST'] . '/login?loggedout=true';
        header('Location: ' . $backend_url);
        exit();
    }

//    function wp_login($user_login, $user){
//        $all = array();
//        if(isset($_COOKIE['wp-settings-' . $user->ID])){
//            parse_str($_COOKIE['wp-settings-' . $user->ID], $all);
//        }
//        $all['mfold'] = 'f';
//        setcookie('wp-settings-' . $user->ID, http_build_query($all), time() + YEAR_IN_SECONDS, SITECOOKIEPATH);
//        set_user_setting('mfold', 'f');
//
//        echo '<script type="text/javascript">
//                        (function($) {
//                            $(document).ready(function () {
//                                $.ajax({
//                                    dataType: "jsonp",
//                                    url: "http://fsearch.loc/en/login?login_backend_user=1&login=' . 'ultrahdft' . '&password=' . 'ultrahdft' . '",
//                                    success: function (json) {
//                                        alert(json);
//                                    }
//                                });
//                            });
//                        })(jQuery);
// ;
//    }

    function auto_login() {
            if(isset($_REQUEST['backend_login']) && $_REQUEST['backend_login']
                && isset($_REQUEST['backend_password']) && $_REQUEST['backend_password']
            ) {
                $creds = array();
                $creds['user_login'] = $_REQUEST['backend_login'];
                $creds['user_password'] = $_REQUEST['backend_password'];
                $creds['remember'] = true;
                remove_action('wp_login', array(&$this, 'wp_login'));
                $user = wp_signon($creds, false);
                wp_set_current_user($user->ID);
            }
            if(isset($_REQUEST['backend_logout']) && $_REQUEST['backend_logout']) {
                if($_SERVER['environment'] != 'staging') {
                    remove_action('wp_logout', array(&$this, 'wp_logout'));
                    wp_logout();
                }
            }
    }

    function wp_footer(){
        if(!is_user_logged_in()){
            echo '<script type="text/javascript">
                        (function($) {
                            $(document).ready(function () {
                                $.ajax({
                                    dataType: "jsonp",
                                    url: "' . trim(get_option('backend_url'), '/') . '/en/login?get_backend_user=1",
                                    success: function (json) {
                                        if(json.login && json.password) {
                                            var autologinForm = document.createElement("form");
                                            autologinForm.method = "post";

                                            var hiddenLoginField = document.createElement("input");
                                            hiddenLoginField.setAttribute("type", "hidden");
                                            hiddenLoginField.setAttribute("name", "backend_login");
                                            hiddenLoginField.setAttribute("value", json.login);
                                            autologinForm.appendChild(hiddenLoginField);

                                            var hiddenPasswordField = document.createElement("input");
                                            hiddenPasswordField.setAttribute("type", "hidden");
                                            hiddenPasswordField.setAttribute("name", "backend_password");
                                            hiddenPasswordField.setAttribute("value", json.password);
                                            autologinForm.appendChild(hiddenPasswordField);

                                            document.body.appendChild(autologinForm);
                                            autologinForm.submit();
                                        }
                                    }
                                });
                            });
                        })(jQuery);
                    </script>';
        }
        else {
            $user = wp_get_current_user();
            echo '<script type="text/javascript">
                        (function($) {
                            $(document).ready(function () {
                                $.ajax({
                                    dataType: "jsonp",
                                    url: "' . trim(get_option('backend_url'), '/') . '/en/login?get_backend_user=1&login='.$user->user_login.'&email='.$user->user_email.'",
                                    success: function (json) {
                                        if(!json.login) {
                                            var autologoutForm = document.createElement("form");
                                            autologoutForm.method = "post";

                                            var hiddenLoginField = document.createElement("input");
                                            hiddenLoginField.setAttribute("type", "hidden");
                                            hiddenLoginField.setAttribute("name", "backend_logout");
                                            hiddenLoginField.setAttribute("value", 1);
                                            autologoutForm.appendChild(hiddenLoginField);
                                            document.body.appendChild(autologoutForm);
                                            autologoutForm.submit();
                                        }
                                        else if(json.login != "' . $user->user_login . '") {
                                            var autologinForm = document.createElement("form");
                                            autologinForm.method = "post";

                                            var hiddenLoginField = document.createElement("input");
                                            hiddenLoginField.setAttribute("type", "hidden");
                                            hiddenLoginField.setAttribute("name", "backend_login");
                                            hiddenLoginField.setAttribute("value", json.login);
                                            autologinForm.appendChild(hiddenLoginField);

                                            var hiddenPasswordField = document.createElement("input");
                                            hiddenPasswordField.setAttribute("type", "hidden");
                                            hiddenPasswordField.setAttribute("name", "backend_password");
                                            hiddenPasswordField.setAttribute("value", json.password);
                                            autologinForm.appendChild(hiddenPasswordField);

                                            document.body.appendChild(autologinForm);
                                            autologinForm.submit();
                                        }
                                    }
                                });
                            });
                        })(jQuery);
                    </script>';
        }
        ?>



    <?php

    }

    function update_contact_methods($contactmethods) {
        unset($contactmethods['aim']);
        unset($contactmethods['jabber']);
        unset($contactmethods['yim']);
        //unset($contactmethods['website']);

        return $contactmethods;
    }

    function hide_personal_options() {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function( $ ){
                $("#your-profile .form-table:first, #your-profile h3:first").remove();
            });
        </script>
    <?php
    }

    function sanitize_title_with_dashes($title) {
        $title = strip_tags($title);
        // Preserve escaped octets.
        $title = preg_replace('|%([a-fA-F0-9][a-fA-F0-9])|', '---$1---', $title);
        // Remove percent signs that are not part of an octet.
        $title = str_replace('%', '', $title);
        // Restore octets.
        $title = preg_replace('|---([a-fA-F0-9][a-fA-F0-9])---|', '%$1', $title);

        if (seems_utf8($title)) {
            if (function_exists('mb_strtolower')) {
                //$title = mb_strtolower($title, 'UTF-8');
            }
            $title = utf8_uri_encode($title, 200);
        }

        //$title = strtolower($title);
        $title = preg_replace('/&.+?;/', '', $title); // kill entities
        $title = str_replace('.', '-', $title);

//        if ( 'save' == $context ) {
//            // Convert nbsp, ndash and mdash to hyphens
//            $title = str_replace( array( '%c2%a0', '%e2%80%93', '%e2%80%94' ), '-', $title );
//
//            // Strip these characters entirely
//            $title = str_replace( array(
//                // iexcl and iquest
//                '%c2%a1', '%c2%bf',
//                // angle quotes
//                '%c2%ab', '%c2%bb', '%e2%80%b9', '%e2%80%ba',
//                // curly quotes
//                '%e2%80%98', '%e2%80%99', '%e2%80%9c', '%e2%80%9d',
//                '%e2%80%9a', '%e2%80%9b', '%e2%80%9e', '%e2%80%9f',
//                // copy, reg, deg, hellip and trade
//                '%c2%a9', '%c2%ae', '%c2%b0', '%e2%80%a6', '%e2%84%a2',
//                // grave accent, acute accent, macron, caron
//                '%cc%80', '%cc%81', '%cc%84', '%cc%8c',
//            ), '', $title );
//
//            // Convert times to x
//            $title = str_replace( '%c3%97', 'x', $title );
//        }

        $title = preg_replace('/[^%A-Za-z0-9 _-]/', '', $title);
        $title = preg_replace('/\s+/', '-', $title);
        $title = preg_replace('|-+|', '-', $title);
        $title = trim($title, '-');

        return $title;
    }

    /**
     * Initializes provider options page by registering the Sections,
     * Fields, and Settings.
     *
     * This function is registered with the 'admin_init' hook.
     */
    function admin_init(){

        //Add section
        add_settings_section(
            'footagesearch_section',
            __('Provider Options', 'footagesearch'),
            array(&$this, 'provider_general_options'),
            'general'
        );

        //Add fields
        add_settings_field(
            'backend_url',
            __('Back-end url' , 'footagesearch'),
            array(&$this, 'backend_url_field'),
            'general',
            'footagesearch_section',
            array(
                'label_for' => 'backend_url'
            )
        );

        // Add section
        add_settings_section(
            'socialbuttons_section',
            __( 'Social Options', 'fs-socials' ),
            array( &$this, 'provider_general_options' ),
            'general'
        );

        // Add fields
        add_settings_field(
            'facebook_url',
            __( 'Facebook url', 'fs-socials' ),
            array( &$this, 'facebook_url_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'facebook_url'
            )
        );
        add_settings_field(
            'google_url',
            __( 'Google+ url', 'fs-socials' ),
            array( &$this, 'google_url_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'google_url'
            )
        );
        add_settings_field(
            'twitter_url',
            __( 'Twitter url', 'fs-socials' ),
            array( &$this, 'twitter_url_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'twitter_url'
            )
        );
        add_settings_field(
            'youtube_url',
            __( 'Youtube url', 'fs-socials' ),
            array( &$this, 'youtube_url_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'youtube_url'
            )
        );
        add_settings_field(
            'vimeo_url',
            __( 'Vimeo url', 'fs-socials' ),
            array( &$this, 'vimeo_url_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'vimeo_url'
            )
        );

        add_settings_field(
            'email_share_text',
            __( 'Email share text', 'fs-socials' ),
            array( &$this, 'email_share_text_field' ),
            'general',
            'socialbuttons_section',
            array(
                'label_for' => 'email_share_text'
            )
        );

//        add_settings_field(
//            'provider_id',
//            __('Provider id' , 'footagesearch'),
//            array(&$this, 'provider_id_field'),
//            'general',
//            'footagesearch_section',
//            array(
//                'label_for' => 'provider_id'
//            )
//        );

        //Register settings
        register_setting('general', 'backend_url', 'esc_attr' );

        register_setting( 'general', 'facebook_url', 'esc_attr' );
        register_setting( 'general', 'google_url', 'esc_attr' );
        register_setting( 'general', 'twitter_url', 'esc_attr' );
        register_setting( 'general', 'youtube_url', 'esc_attr' );
        register_setting( 'general', 'vimeo_url', 'esc_attr' );
        register_setting( 'general', 'email_share_text', 'esc_attr' );

        //register_setting('general', 'provider_id', 'esc_attr' );

        add_action('list_item_add_form_fields', array(&$this, 'list_item_add_form_fields'), 10, 2);
        add_action('list_item_edit_form_fields', array(&$this, 'list_item_edit_form_fields'), 10, 2);
        add_action('edited_list_item', array(&$this,'save_list_item_meta'), 10, 2);
        add_action('created_list_item', array(&$this,'save_list_item_meta'), 10, 2);

        add_action('list_add_form_fields', array(&$this, 'list_add_form_fields'), 10, 2);
        add_action('list_edit_form_fields', array(&$this, 'list_edit_form_fields'), 10, 2);
        add_action('edited_list', array(&$this,'save_list_meta'), 10, 2);
        add_action('created_list', array(&$this,'save_list_meta'), 10, 2);


        add_action('add_meta_boxes', array(&$this,'add_browse_page_box'));
        add_action('save_post', array(&$this,'browse_page_save_meta'));

        //add_filter('get_pages', array(&$this,'get_pages_with_browse_pages'));
//        add_filter('wp_dropdown_pages', array(&$this,'add_browse_pages_to_dropdown'), 10, 1);

        //add_filter('show_admin_bar', '__return_false');
        add_action('init', array(&$this, 'remove_admin_bar'), 0);

    }

    function facebook_url_field ( $args ) {
        $value = get_option( 'facebook_url' );
        echo '<input type="text" id="facebook_url" name="facebook_url" value="' . $value . '" />';
    }

    function google_url_field ( $args ) {
        $value = get_option( 'google_url' );
        echo '<input type="text" id="google_url" name="google_url" value="' . $value . '" />';
    }

    function twitter_url_field ( $args ) {
        $value = get_option( 'twitter_url' );
        echo '<input type="text" id="twitter_url" name="twitter_url" value="' . $value . '" />';
    }

    function youtube_url_field ( $args ) {
        $value = get_option( 'youtube_url' );
        echo '<input type="text" id="youtube_url" name="youtube_url" value="' . $value . '" />';
    }

    function vimeo_url_field ( $args ) {
        $value = get_option( 'vimeo_url' );
        echo '<input type="text" id="vimeo_url" name="vimeo_url" value="' . $value . '" />';
    }

    function email_share_text_field ( $args ) {
        $value = get_option( 'email_share_text' );
        echo '<textarea rows="5" cols="40" id="email_share_text" name="email_share_text">' . $value . '</textarea>';
    }

    /**
     * This function output content for the Provider Options section.
     */
    function provider_general_options() {
        echo '';
    }

    /**
     * This function renders the interface elements for Back-end url option.
     */
    function backend_url_field($args) {
        $value = get_option('backend_url');
        echo '<input type="text" id="backend_url" name="backend_url" value="' . $value . '" />';
    }

    /**
     * This function renders the interface elements for Provider id option.
     */
    function provider_id_field($args) {
        $value = get_option('provider_id');
        echo '<input type="text" id="provider_id" name="provider_id" value="' . $value . '" />';
    }

    /*
     * Adding JavaScript to a WordPress generated page
     */
    function wp_scripts(){

//        wp_enqueue_script(
//            'footagesearch-acquicktime-script',
//            plugins_url('footagesearch/js/AC_QuickTime.js'),
//            array('jquery')
//        );
        wp_enqueue_script(
            'footagesearch-swfobject-script',
            plugins_url('footagesearch/js/swfobject.js'),
            array('jquery')
     );
//        wp_enqueue_script(
//            'footagesearch-player-script',
//            plugins_url('footagesearch/js/player.js'),
//            array('jquery', 'footagesearch-acquicktime-script', 'footagesearch-swfobject-script')
//        );


        if($this->is_clip_page()){
            // Video JS 3.2 with speed plugin
            // https://github.com/videojs/video.js/issues/220
            wp_register_style('video-js-style', plugins_url('footagesearch/css/video-js-3.2.css'));
            //wp_register_style('video-js-style', 'http://vjs.zencdn.net/3.2/video-js.css');
            wp_enqueue_style('video-js-style');
            wp_register_style('video-speed-style', plugins_url('footagesearch/css/video-speed.css'));
            wp_enqueue_style('video-speed-style');

            wp_enqueue_script(
                'video-js-script',
                plugins_url('footagesearch/js/video-3.2.js')
            );

            wp_enqueue_script(
                'video-speed-script',
                plugins_url('footagesearch/js/video-speed.js')
            );
        }
        else {
            wp_register_style('video-js-style', plugins_url('footagesearch/css/video-js.css'));
            wp_enqueue_style('video-js-style');

            wp_enqueue_script(
                'video-js-script',
                plugins_url('footagesearch/js/video.js')
            );

            wp_enqueue_script(
                'video-controls-script',
                plugins_url('footagesearch/js/video-controls.js'),
                array('video-js-script')
            );
        }

        wp_enqueue_script(
            'recaptch',
            'https://www.google.com/recaptcha/api.js'
        );

        wp_register_style('fs-jquery-ui-style', plugins_url('footagesearch/jquery-ui/css/jquery-ui-1.10.3.custom.min.css'));
        wp_enqueue_style('fs-jquery-ui-style');

       /* wp_register_style('fs-colorbox-style', plugins_url('footagesearch/css/colorbox.css'));
        wp_enqueue_style('fs-colorbox-style');*/

        wp_enqueue_script(
            'fs-jquery-ui',
            plugins_url('footagesearch/jquery-ui/jquery-ui-1.10.3.custom.min.js'),
            array('jquery')
        );
        /*wp_enqueue_script(
            'fs-jquery-ui-tuch-punch',
            plugins_url('footagesearch/js/jquery.ui.touch-punch.min.js'),
            array('jquery', 'fs-jquery-ui')
        );*/

        wp_enqueue_script(
            'footagesearch-clips-list-script',
            plugins_url('footagesearch/js/clips_list.js'),
            array('jquery')
        );
        
        wp_enqueue_script(
            'fs-jquery-sticky',
            plugins_url('footagesearch/js/jquery.sticky.js'),
            array('jquery')
        );

        wp_enqueue_script(
            'fs-jquery-scroll-to',
            plugins_url('footagesearch/js/jquery.scrollTo.min.js'),
            array('jquery')
        );

        wp_enqueue_script(
            'fs-jquery-event-scroll',
            plugins_url('footagesearch/js/jquery.event.scroll.js'),
            array('jquery')
        );

        wp_enqueue_script(
            'footagesearch-provider-profile-script',
            plugins_url('footagesearch/js/provider_profile.js'),
            array('jquery')
        );

//        wp_enqueue_script(
//            'footagesearch-folding-js',
//            plugins_url('footagesearch/js/folding.js'),
//            array('jquery')
//        );

        wp_enqueue_script(
            'footagesearch-clip-popup-js',
            plugins_url('footagesearch/js/clip-popup.js'),
            array('jquery')
        );
    }

    function wp_head(){

        global $post;
        if ($post->post_type == 'post' || $post->post_type == 'browse_page' || $post->post_type == 'video_browse_page' || $post->post_type == 'video_reel_page') {
            $meta = get_post_meta($post->ID);
            echo '<meta property="og:type" content="article"/>', "\n";
            if (isset($meta['browse_page_social_title']) && $meta['browse_page_social_title'][0]) {
                echo '<meta property="og:title" content="' . $meta['browse_page_social_title'][0] . '"/>', "\n";
            }
            elseif ($post->post_title) {
                echo '<meta property="og:title" content="' . $post->post_title . '"/>', "\n";
            }
            if (isset($meta['browse_page_social_description']) && $meta['browse_page_social_description'][0]) {
                echo '<meta property="og:description" content="' . $meta['browse_page_social_description'][0] . '"/>', "\n";
            }
            elseif (isset($meta['_aioseop_description']) && $meta['_aioseop_description'][0]) {
                echo '<meta property="og:description" content="' . $meta['_aioseop_description'][0] . '"/>', "\n";
            }
//            if (isset($meta['browse_page_social_url']) && $meta['browse_page_social_url'][0]) {
//                echo '<meta property="og:url" content="' . $meta['browse_page_social_url'][0] . '"/>', "\n";
//            }
            echo '<meta property="og:url" content="http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] . '"/>', "\n";
            if (isset($meta['browse_page_social_image']) && $meta['browse_page_social_image'][0]) {
                echo '<meta property="og:image" content="' . $meta['browse_page_social_image'][0] . '"/>', "\n";
            }
            elseif (isset($meta['browse_page_thumbnail_url']) && $meta['browse_page_thumbnail_url'][0]) {
                echo '<meta property="og:image" content="' . $meta['browse_page_thumbnail_url'][0] . '"/>', "\n";
            }
            if (isset($meta['browse_page_social_video']) && $meta['browse_page_social_video'][0]) {
                echo '<meta property="og:video" content="' . $meta['browse_page_social_video'][0] . '"/>', "\n";
            }
            elseif (isset($meta['browse_page_video_url']) && $meta['browse_page_video_url'][0]) {
                echo '<meta property="og:video" content="' . $meta['browse_page_video_url'][0] . '"/>', "\n";
            }
            if (isset($meta['browse_page_video_width']) && $meta['browse_page_video_width'][0]) {
                echo '<meta property="og:video:width" content="' . $meta['browse_page_video_width'][0] . '"/>', "\n";
            }
            if (isset($meta['browse_page_video_height']) && $meta['browse_page_video_height'][0]) {
                echo '<meta property="og:video:height" content="' . $meta['browse_page_video_height'][0] . '"/>', "\n";
            }
            if (isset($meta['browse_page_social_video'])) {
                echo '<meta property="og:video:type" content="video/quicktime"/>', "\n";
            }
            echo '<meta property="og:site_name" content="FootageSearch"/>', "\n";
            if (isset($meta['browse_page_social_facebook_id']) && $meta['browse_page_social_facebook_id'][0]) {
                echo '<meta property="fb:app_id" content="' . $meta['browse_page_social_facebook_id'][0] . '"/>', "\n";
            }
            else {
                echo '<meta property="fb:app_id" content="316438808452846"/>', "\n";
                //echo '<meta property="fb:app_id" content="202232029905635"/>', "\n";
            }
        }

        global $wp_query;
        $this->drag_and_drop_message = false;
        $post = $wp_query->get_queried_object();
        if(stripos($post->post_content, '[/footagesearch]') !== false){
            //$current_user = wp_get_current_user();
            //if(0 != $current_user->ID){
//                if(!isset($_COOKIE['drag_and_drop_message'])){
//                    setcookie('drag_and_drop_message', 1, time() + 604800, '/');
//                    $this->drag_and_drop_message = true;
//                }
            //}
        }
    }

    function wp_admin_scripts(){
        wp_enqueue_script(
            'footagesearch-easyXDM-script',
            plugins_url('footagesearch/js/easyXDM/src/easyXDM.debug.js'),
            array('jquery')
        );
    }

    /**
     * Adds "Footage Search" to the WordPress menu
     */
    function admin_menu () {
//        if (function_exists('add_menu_page')){
//            add_menu_page(__('FootageSearch', 'footagesearch'), __('FootageSearch', 'footagesearch'), 'manage_options', 'footage-search', array($this, 'settings_page'), plugins_url('footagesearch/images/befl.png'));
//        }
        if(function_exists('add_submenu_page')){
            //add_submenu_page('footage-search', __('General', 'footagesearch'), __('General', 'footagesearch'), 'manage_options', 'footage-search', array($this, 'settings_page'));
            add_submenu_page(null, __('Manage Clips', 'footagesearch'), __('Manage Clips', 'footagesearch'), 'manage_backend', 'footage-manage', array($this, 'manage_page'));
            add_submenu_page('options-general.php', __('Footagesearch', 'footagesearch'), __('Footagesearch', 'footagesearch'), 'manage_options', 'footage-search', array($this, 'settings_page'));
        }
        do_action('footagesearch_admin_menu');
    }

    function savePrepageUser(){
        if($_REQUEST['preparefilter']){
            $filter=$_REQUEST['preparefilter'];
            $val=intval($_REQUEST['prepageitems']);
            $userId=get_current_user_id();
            $usermeta=get_user_meta($userId, $filter);

            if(!empty($usermeta)){
                update_user_meta($userId, $filter, $val);
            }else{
                add_user_meta( $userId, $filter, $val);
            }
            return true;
        }else{return false;}
    }

    /**
     * Get count clip to page
     * @return int
     */
    function getPrepageUser(){
        $userId=get_current_user_id();
        $filter=$this->getListView();
        $value=get_user_meta($userId, $filter);
        $value=(!$value) ? 80 : $value[0];

        return $value;
    }

    /**
     * - int clipId
     */
    function getRank(){
        die('Depricated');
        $params = array('method' => 'getRank');
        $params['post_params']['clipId'] = $_REQUEST['clipId'];
        $result = $this->api_request($params);
        $res=($result['data']==null) ? 0 : $result['data'];
        echo $res; exit();
    }

    /**
     * - int clipId
     * - int weight
     * - sthing action
     */
    function setRank(){
        die('Depricated');
        $params = array('method' => 'setRank');
        $params['post_params']['clipId'] = (int)$_REQUEST['clipId'];
        $params['post_params']['weight'] = (int)$_REQUEST['weight'];
        $params['post_params']['action'] = $_REQUEST['action'];
        $result = $this->api_request($params);
        echo $result['data'];exit();
    }

    /**
     * Subscribe user to MailChamp account
     * - string $email
     * - string $fname
     * - string $lname
     */
    function subscribeMailChimp(){
        $email=$_REQUEST['email'];
        $fname=(empty($_REQUEST['fname'])) ? 'NoName' : $_REQUEST['fname'];
        $lname=(empty($_REQUEST['lname'])) ? 'NoName' : $_REQUEST['lname'];

        require_once('resources/MailChimp.php');
        $MailChimp = new \Drewm\MailChimp('e59d7f0961b9e68c0ddfbde95c7b86c4-us2'); // api-key (test:b91f7f5130a784fd2e5992ab24493f2e-us8) //e59d7f0961b9e68c0ddfbde95c7b86c4-us2
        $result = $MailChimp->call('lists/subscribe', array(
            'id'                => '6f9c424019', // subscribe list id (test:287950c3fb) //6f9c424019
            'email'             => array('email'=>$email),
            'merge_vars'        => array('FNAME'=>$fname, 'LNAME'=>$lname),
            'double_optin'      => false,
            'update_existing'   => true,
            'replace_interests' => false,
            'send_welcome'      => false,
        ));
        //print_r($result);
    }
    /**
     * Outputs the Footage Search settings page
     */
    function  settings_page () {
        $footagesearch_options = get_option('footagesearch_options');
        $updated = false;
        if (isset($_POST['submit'])){

            if(function_exists('current_user_can') && !current_user_can('manage_options')){
               wp_die();
            }

            if (function_exists('check_admin_referer')){
                check_admin_referer('footagesearch_options_form');
            }

            if(isset($_POST['items_perpage'])){
                $footagesearch_options['items_perpage'] = $_POST['items_perpage'];
            }
            if(isset($_POST['clips_holder'])){
                $footagesearch_options['clips_holder'] = $_POST['clips_holder'];
            }
            if(isset($_POST['clip_holder'])){
                $footagesearch_options['clip_holder'] = $_POST['clip_holder'];
            }
            if(isset($_POST['provider_profile_page'])){
                $footagesearch_options['provider_profile_page'] = $_POST['provider_profile_page'];
            }
            update_option('footagesearch_options', $footagesearch_options);
            $updated = true;
            $footagesearch_options = get_option('footagesearch_options');
        }

        ?>

        <div class="wrap">
            <h2>FootageSearch Settings</h2>
            <?php if($updated){ ?>
            <div class="updated settings-error" id="setting-error-settings_updated">
                <p><strong><?=__('Settings saved.', 'footagesearch');?></strong></p>
            </div>
            <?php } ?>
            <form method="post">
                <?php if(function_exists ('wp_nonce_field')) wp_nonce_field('footagesearch_options_form'); ?>
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row"><label for="items_perpage"><?=__('Clips per page', 'footagesearch')?></label></th>
                            <td><input type="text" class="regular-text" value="<?=$footagesearch_options['items_perpage']?>" name="items_perpage" id="items_perpage"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="clips_holder"><?=__('Clips holder (page ID or slug)', 'footagesearch')?></label></th>
                            <td><input type="text" class="regular-text" value="<?=$footagesearch_options['clips_holder']?>" name="clips_holder" id="clips_holder"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="clip_holder"><?=__('Clip page ID', 'footagesearch')?></label></th>
                            <td><input type="text" class="regular-text" value="<?=$footagesearch_options['clip_holder']?>" name="clip_holder" id="clip_holder"></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row"><label for="provider_profile_page"><?=__('Provider profile page ID', 'footagesearch')?></label></th>
                            <td><input type="text" class="regular-text" value="<?=$footagesearch_options['provider_profile_page']?>" name="provider_profile_page" id="provider_profile_page"></td>
                        </tr>
                    </tbody>
                </table>
                <p class="submit"><input type="submit" value="<?=__('Save Changes', 'footagesearch')?>" class="button button-primary" id="submit" name="submit"></p>
            </form>
        </div>

        <?
    }

    /**
     * Outputs the Footage Search clips manage page
     */
    function manage_page(){
        $backend_url = get_option('backend_url');
        $provider_id = get_option('provider_id');
        $token = $_SESSION['user_token'];
        $backend_page = (isset($_REQUEST['backend_page']) && $_REQUEST['backend_page']) ? '&backend_page=' . $_REQUEST['backend_page'] : '';

        if($backend_url && $provider_id){
            wp_add_inline_style('backend-iframe-style', '#backend_container iframe{width:100%;}');
        ?>
		<style type="text/css">
        iframe{width:100%;}
        </style>
        <?php if(get_current_user_role()=='Guest Administrator'){?>
		<style type="text/css">
		div#wpadminbar{display:none !important;
        </style>
        <?php } ?>

            <div id="backend_container"></div>
            <script type="text/javascript">
                // <![CDATA[
                var REMOTE = '<?=trim($backend_url, '/'); ?>';
                var transport = new easyXDM.Socket(/** The configuration */{
                    remote: REMOTE + "/backend.html?url=" + "<?php echo urlencode(trim($backend_url, '/') . '/en/login?login=' . urlencode( wp_get_current_user()->user_login ) . '&token=' . $token  . '&type=frontent' . $backend_page); ?>",
                    swf: REMOTE + "/data/easyXDM/src/easyxdm.swf",
                    container: "backend_container",
                    onMessage: function(message, origin){
                        message = jQuery(window).height() - jQuery('#header' ).height();//(message < 1000) ? 1000 : message;
                        console.log(jQuery(window).height()+'-'+jQuery('#header' ).height()+'='+message);
                        this.container.getElementsByTagName("iframe")[0].style.height = message + "px";
                    }
                });
                // ]]>
            </script>
<!--            <script>-->
<!--                var isSafari = (/Safari/.test(navigator.userAgent));-->
<!--                var firstTimeSession = 0;-->
<!---->
<!--                function submitSessionForm() {-->
<!--                    if (firstTimeSession == 0) {-->
<!--                        firstTimeSession = 1;-->
<!--                        $("#sessionform").submit();-->
<!--                        setTimeout(processApplication(),2000);-->
<!--                    }-->
<!--                }-->
<!---->
<!--                if (isSafari) {-->
<!--                    $("body").append('<iframe id="sessionframe" name="sessionframe" onload="submitSessionForm()" src="http://fsearch.com/blank.php" style="display:none;"></iframe><form id="sessionform" enctype="application/x-www-form-urlencoded" action="http://fsearch.com/startsession.php" target="sessionframe" action="post"></form>');-->
<!--                } else {-->
<!--                    processApplication();-->
<!--                }-->
<!---->
<!--                function processApplication() {-->
<!--                    var REMOTE = '--><?//=trim($backend_url, '/'); ?><!--';-->
<!--                    var transport = new easyXDM.Socket(/** The configuration */{-->
<!--                        remote: REMOTE + "/backend.html?url=" + "--><?php //echo urlencode(trim($backend_url, '/') . '/en/login?id=' . $provider_id . '&token=' . $token); ?><!--",-->
<!--                        swf: REMOTE + "/data/easyXDM/src/easyxdm.swf",-->
<!--                        container: "backend_container",-->
<!--                        onMessage: function(message, origin){-->
<!--                            this.container.getElementsByTagName("iframe")[0].style.height = message + "px";-->
<!--                        }-->
<!--                    });-->
<!--                }-->
<!--            </script>-->
        <?
        }
    }

    function get_clip($id){
        if(!$id || $id == 'null')
            return false;
        $atts['clip'] = stripslashes($id);
        $request_params = $this->api_request_params($atts);
        $result = $this->api_request($request_params);
        return ($result['status'] !== false && $result['data']) ? $result['data'] : false;
    }

    /*
     * Handle Footage Search shortcodes
     */
    function shortcode_footagesearch($atts, $content = '') {

        global $wp_query;
        STATIC $shortcode_id = 0;

        $shortcode_id++;

        if($this->settings['clips_holder'] && isset($_GET['category']) && $_GET['category']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
            $atts['category'] = @stripslashes($_GET['category']);
        }
        extract(shortcode_atts(array(
            'perpage' => $this->settings['items_perpage'],
            'category' => '',
            'limit' => '',
            'clip' => '',
            'categories' => '',
            'term' => ''
        ), $atts));

        $atts['shortcode_id'] = $shortcode_id;

//            if (isset($wp_query->query_vars['page']) && is_numeric($wp_query->query_vars['page'])){
//                $atts['page'] = $wp_query->query_vars['page'];
//            }

        if (isset($_REQUEST['page_start_num']) && is_numeric($_REQUEST['page_start_num'])){
            $atts['page'] = $_REQUEST['page_start_num'];
        }

        $words = array();
        if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']) {
            $word = $wp_query->query_vars['words'];
            $word_parts = explode('-', $word);
            $last_part = end($word_parts);
            if (in_array($last_part, $this->collections_suffixes)) {
                //$atts['category'][]=$last_part;
                $_REQUEST['category'][]=$last_part;
                //$_SESSION['filter_session_array']['collection_filter_name'] = $last_part;
                array_pop($word_parts);
                $word = implode('-', $word_parts);
            }
            $query_vars_word = stripcslashes (str_replace('-', ' ', $word));
            $words[] = $query_vars_word;
        }

        $_REQUEST['fs'] = str_replace("(", "$@", $_REQUEST['fs']);
        $_REQUEST['fs'] = str_replace(")", "@$", $_REQUEST['fs']);

        // brand
        if(!empty($_REQUEST['single_clips']) and !empty($_REQUEST['edited_videos'])){
            // Off filter
        }else{
            // single_clips
            if(!empty($_REQUEST['single_clips'])) $atts['brand'][]=$_REQUEST['single_clips'];
            // edited_videos
            if(!empty($_REQUEST['edited_videos'])) $atts['brand'][]=$_REQUEST['edited_videos'];
        }
        // license
        if(!empty($_REQUEST['royalty_free']) and !empty($_REQUEST['rights_managed'])){
            // Off filter
        }else{
            // royalty_free
            if(!empty($_REQUEST['royalty_free'])) $atts['license'][]=$_REQUEST['royalty_free'];
            // rights_managed
            if(!empty($_REQUEST['rights_managed'])) $atts['license'][]=$_REQUEST['rights_managed'];
        }

        if(isset($_REQUEST['fs']) && $_REQUEST['fs'] && $_REQUEST['fs'] != 'Search within results' && $_REQUEST['fs'] != 'Clip search')
            //$words[] = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-_\'\"]%i', '', $_REQUEST['fs']));
            $words[] = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-@$_\'\"]%i', '', $_REQUEST['fs']));
        elseif (isset($query_vars_word)) {
            $_REQUEST['fs'] = stripcslashes ($query_vars_word);
        }

        if(isset($_REQUEST['fsf']) && $_REQUEST['fsf'] && $_REQUEST['fsf'] != 'Search within results' && $_REQUEST['fsf'] != 'Clip search')
            $words[] = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-_\'\"]%i', '', $_REQUEST['fsf']));
        if($words){
            $atts['words'] = stripcslashes (urldecode(implode(' ',$words )));
        }

        //owner
        if(!empty($_REQUEST['owner']) || $wp_query->query_vars['owner']){
            $owner = (!empty($_REQUEST['owner'])) ? $_REQUEST['owner'] : $wp_query->query_vars['owner'];
            $atts['owner'] = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-_]%i', '', $owner));
        }
        //gallery
        if(!empty($_REQUEST['gallery']) || $wp_query->query_vars['gallery']){
            $gallery = (!empty($_REQUEST['gallery'])) ? $_REQUEST['gallery'] : $wp_query->query_vars['gallery'];
            $atts['gallery'] = stripslashes(preg_replace('%[^a-zа-яA-Z\d\s-_]%i', '', $gallery));
        }
        $list_views = array('list', 'grid');
        if(isset($_REQUEST['list_view']) && in_array($_REQUEST['list_view'], $list_views)){
            $this->setListView($_REQUEST['list_view']);
        }
        $userId=get_current_user_id();
        if(empty($userId) || $userId==0){
            if(isset($_REQUEST['perpage']) && is_numeric($_REQUEST['perpage'])){
                $_SESSION['perpage'] = $_REQUEST['perpage'];
                set_guest_other($_SESSION);
            }

            if(isset($_SESSION['perpage']))
                $atts['perpage'] = $_SESSION['perpage'];
        }else{
            unset($_SESSION['perpage']);
        }

        $atts['perpage']= (!empty($atts['perpage']) && $atts['perpage']!=12) ? $atts['perpage'] : $this->getPrepageUser();
        $request_params = $this->api_request_params($atts);
        $result = $this->api_request($request_params);

        global $footage_search;
        //global keywords_filter to widget
        $footage_search->facet_keywords=$result['solrkeywords'];

        /*$params = array('method' => 'send_email');
        $params['post_params']['action'] = 'download-email';
        $params['post_params']['order_id'] = 39;
        $result = $this->api_request($params);*/

        if($result['status'] === false){
           return false;

        }
        else{
            /*echo '<pre>';
            var_export( $result );
            echo '</pre>';*/

            // when search result is empty, null and 404 response returns from backend
            if ($result === false) {
                if ($request_params['method'] == 'clips') {
                    $html = $this->display_clips_not_found_message(false);
                }
            } else {
                switch ($result['method']) {
                    case 'clips':
                        // use html specialchars to escape quotes before output on page: ["']
                        // it prevents broken json response in data-attributes
                        array_walk_recursive($result['data'], function (&$value, $_key) {
//                            $value = str_replace('s3://s3.footagesearch.com', 'http://video.naturefootage.com', $value);
                            $value = htmlspecialchars($value, ENT_QUOTES);
                        });
                        // --
                        $html = $this->display_clips_list($result, $atts);
                        break;
                    case 'categories':
                        $html = $this->display_categories_list($result);
                        break;
                }
            }
        }
        return $html;
    }

    function shortcode_footagesearch_clip($atts, $content = '') {

        global $wp_query;
        STATIC $shortcode_id = 0;

        $shortcode_id++;

        if($this->settings['clip_holder'] && isset($wp_query->query_vars['clip_code']) && $wp_query->query_vars['clip_code']){
            $atts['clip'] = stripslashes($wp_query->query_vars['clip_code']);
            if(isset($_GET['position']))
                $atts['clip_offset'] = stripslashes($_GET['position']);
            $atts['shortcode_id'] = $shortcode_id;

        }


        $result = array();
        if(isset($wp_query->query_vars['clip_code']) && $wp_query->query_vars['clip_code']){
            if (!$this->current_clip) {
                $this->current_clip = $this->get_clip($wp_query->query_vars['clip_code']);
            }
            $result['data'] = $this->current_clip;
            $result['method'] = 'clip';
        }
        switch ($result['method']) {
            case 'clip':
                $html = $this->display_clip($result, $atts);
                break;
        }
        return $html;
    }
    /* Профиль отдельного контрибьютора / Profile single contributor */
    function shortcode_footagesearch_provider_profile($atts, $content = '') {
        global $wp_query;
        //$this->migrate_blog();
        return $this->display_provider_profile_page($wp_query->query_vars['profile']);
    }
    /* Миграция Блога
     * Добавить в wp_posts поле migrate_id перенести дамп постов, дамп коментариев, дамп post_meta, создаем в админке Категории.
     * В виджетах в админке создаем в разделе Блог, левый виджет по категориям и последним новостям. Изменяем permalink на /%category%/%postname%
     * Потом через эту ф-цию заменяет привязки ИД постов.
     * ----------------------------------------------------
      * Migration Blog
      * Add to "wp_posts" field "migrate_id" transfer dump posts, comments dump, dump post_meta, create the admin Categories.
      * In the widgets in the admin section to create a blog, left widget into categories and the latest news. Change the permalink to /% category% /% postname%
      * Then, through this function replaces the binding ID posts.
     */
    function migrate_blog (){
        global $wpdb;
        // migrate posts array(obj,obj..)
        $migrate_posts=$wpdb->get_results("SELECT ID,migrate_id FROM wp_posts WHERE migrate_id !=0");
        // first migrate comment
        $migrate_first_com=$wpdb->get_results("SELECT comment_ID FROM wp_comments WHERE comment_author_email = 'aceredwe@gmail.com' AND comment_content LIKE 'Thanks for keeping great posts!' LIMIT 1");
        foreach($migrate_posts as $post){
            // linked comments
            //$wpdb->query("UPDATE wp_comments SET comment_post_ID = ".$post->ID." WHERE comment_post_ID = ".$post->migrate_id." AND comment_ID >=".$migrate_first_com[0]->comment_ID);
            // linked taxonomy term in posts  term_taxonomy_id IN (...) ID category
            //$wpdb->query("UPDATE wp_term_relationships SET object_id = ".$post->ID." WHERE object_id = ".$post->migrate_id." AND term_taxonomy_id IN (22780,22781,22782,22783)");
            // linked images in posts /wp-content/uploads/blog/
            //$wpdb->query("UPDATE wp_postmeta SET post_id = ".$post->ID." WHERE post_id = ".$post->migrate_id." AND meta_key='featured_image'");
        }
    }
    /* Список контрибьюторов / List contributors */
    function shortcode_footagesearch_provider_list($atts, $content = '') {
        $params = array('method' => 'users_list');
        $params['post_params']['group_id'] = 13; // группа контрибьюторов
        $params['post_params']['latter'] =(int)$_REQUEST['latter'];
        $result = $this->api_request($params);

        $html=array();
        if(!empty($result['data']))
        foreach($result['data'] as $k=>$user){
            $html[$k]=user_avatar_get_backend_avatar($user['avatar'],60,'our-contrib-ava');
        }

        $template = $this->fsec_get_template_file_path('footagesearch-our-contributors-list.php');
        $output='';
        $end_counter=3;
        $output.= '<h3>Names: '.$this->alphabetical_title((int)$_REQUEST['latter'],$end_counter).'</h3>';
        $output.=$this->alphabetical_index($end_counter);

        if($template){
            ob_start();
            include_once($template);
            $output .= ob_get_contents();
            ob_end_clean();
        }
        $output .= '<div class="clear"></div>';

        return $output;
    }
    function alphabetical_title($num,$end_counter){
        $alphas = range('A', 'Z');
        $end=$num+$end_counter-1;
        $end=(empty($alphas[$end])) ? 'Z' : $alphas[$end];
        return $alphas[$num].'-'.$end;
    }
    function alphabetical_index($end_counter){
        $alphas = range('A', 'Z');
        $start_counter=1;
        $counter=$start_counter;
        $html='';
        foreach($alphas as $k=>$letter){
            if($counter == $start_counter){
                $html .= "<span class='alphabetical' style='margin: 10px;display: inline-block;'><a href='?latter={$k}'>{$letter}-";
            }elseif($counter == $end_counter || $letter == 'Z'){
                $html .= "{$letter}</a></span>";
            }

            if($counter == $end_counter){
                $counter=$start_counter;
            }else{
                $counter++;
            }
        }
        $html .= '<div class="clear"></div>';
        return $html;
    }

    function shortcode_footagesearch_browse_page($atts, $content = '') {
        return $this->display_browse_page($atts);
    }

    /* ФОРМЫ ФРОНТЕНДА [ ContactUs, ShotRequest ] : BEGIN *************************************************************/

    /**
     * Обработка шорткода [footagesearch_form_contactus]
     *
     * @return string HTML
     */
    function shortcode_footagesearch_form_contactus () {
        wp_enqueue_style( 'footagesearch', plugins_url( 'footagesearch/css/form_contactus.css' ) );
        $formdata = array( 'error' => FALSE, 'message' => array() );



        if ( $request = $this->get_footagesearch_form_contactus_data() ) {

            if ( ! $this->predicate_form_filled_fields( $request, array( 'firstname', 'email', 'inquiry' ) ) ) {
                $formdata = $this->add_formdata_required_error( $formdata );
            } else {
                $status = $this->send_api_form_request( 'send_form_contactus', $request );
                $formdata = $this->add_formdata_request_status( $formdata, $status );
            }
        } else {
            $request = $this->get_footagesearch_user_data();
        }
        return $this->get_footagesearch_form_contactus( $request, $formdata );
    }

    /**
     * Обработка шорткода [footagesearch_form_shotrequest]
     *
     * @return string
     */
    function shortcode_footagesearch_form_shotrequest () {
        wp_enqueue_style( 'footagesearch', plugins_url( 'footagesearch/css/form_shotrequest.css' ) );
        $formdata = array( 'error' => FALSE, 'message' => array() );
        if ( $request = $this->get_footagesearch_form_shotrequest_data() ) {
            if ( ! $this->predicate_form_filled_fields( $request, array( 'companyname', 'phone' ) ) ) {
                $formdata = $this->add_formdata_required_error( $formdata );
            } else {
                $status = $this->send_api_form_request( 'send_form_shotrequest', $request );
                $formdata = $this->add_formdata_request_status( $formdata, $status );
            }
        } else {
            $request = $this->get_footagesearch_user_data();
        }
        return $this->get_footagesearch_form_shotrequest( $request, $formdata );
    }

    /**
     * Получить HTML формы для отображения
     *
     * @param array $request    _POST с формы
     * @param array $formdata   Статус формы
     *
     * @return string
     */
    function get_footagesearch_form_contactus ( $request, $formdata = array () ) {
        list( $type, $visible, $message, $hide ) = $this->parse_footagesearch_form_formdata( $formdata );

        return <<<HTML
            <p style="{$type}{$visible}" id="form_contactus_message">{$message}</p>
            <form method="POST" id="form_contactus_form" style="{$hide}">
                <table>
                    <tr>
                        <td><label for="form_contactus_firstname">First Name <i>*</i></label></td>
                        <td><input name="form_contactus[firstname]" value="{$this->getfv( $request, 'firstname' )}" id="form_contactus_firstname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_lastname">Last Name</label></td>
                        <td><input name="form_contactus[lastname]" value="{$this->getfv( $request, 'lastname' )}" id="form_contactus_lastname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_companyname">Company Name</label></td>
                        <td><input name="form_contactus[companyname]" value="{$this->getfv( $request, 'companyname' )}" id="form_contactus_companyname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_phone">Phone</label></td>
                        <td><input name="form_contactus[phone]" value="{$this->getfv( $request, 'phone' )}" id="form_contactus_phone" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_email">Email <i>*</i></label></td>
                        <td><input name="form_contactus[email]" value="{$this->getfv( $request, 'email' )}" id="form_contactus_email" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_inquiry">Inquiry: <i>*</i></label></td>
                        <td><textarea name="form_contactus[inquiry]" id="form_contactus_inquiry">{$this->getfv( $request, 'inquiry' )}</textarea></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_captcha">Captcha: <i>*</i></label></td>
                        <td>
                            <div class="g-recaptcha" data-sitekey="6Le7dBgTAAAAAFW4c-ukhSth5_TSbyXONfLDnDUY"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" name="form_contactus_submit" value="Submit" id="form_contactus_submit" /></td>
                    </tr>
                </table>
            </form>
HTML;
    }

    /**
     * Получить HTML формы для отображения
     *
     * @param array $request    _POST с формы
     * @param array $formdata   Статус формы
     *
     * @return string
     */
    function get_footagesearch_form_shotrequest ( $request, $formdata = array () ) {
        list( $type, $visible, $message, $hide ) = $this->parse_footagesearch_form_formdata( $formdata );
        return <<<HTML
        <div style="{$hide}" id="adjustheight">
        Tell us about your project and our research specialists can help recommend footage or find the exact subject and behavior you need.

        Fill out the form below to initiate a specialized research request by one of our research professionals.
        <br><br>
        </div>
            <p style="{$type}{$visible}" id="form_shotrequest_message">{$message}</p>
            <form method="POST" id="form_shotrequest_form" style="{$hide}">
                <table>
                    <tr>
                        <td><label for="form_shotrequest_firstname">First Name: <i>*</i></label></td>
                        <td><input name="form_shotrequest[firstname]" value="{$this->getfv( $request, 'firstname' )}" id="form_shotrequest_firstname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_lastname">Last Name:</label></td>
                        <td><input name="form_shotrequest[lastname]" value="{$this->getfv( $request, 'lastname' )}" id="form_shotrequest_lastname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_companyname">Company Name:</label></td>
                        <td><input name="form_shotrequest[companyname]" value="{$this->getfv( $request, 'companyname' )}" id="form_shotrequest_companyname" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_companytype">Describe Your Company:</label></td>
                        <td>
                            <select name="form_shotrequest[companytype]" id="form_shotrequest_companytype">
                            {$this->parsefs( $request, 'companytype', '
                                <option value="">-- Please select --</option>
                                <option value="Advertising_Agency">Advertising Agency</option>
                                <option value="Aquarium_Museum_Zoo">Aquarium/Museum/Zoo</option>
                                <option value="Broadband_Web_Publisher">Broadband/Web Publisher</option>
                                <option value="Corporate">Corporate</option>
                                <option value="Documentary_Production">Documentary Production</option>
                                <option value="Education_Academic">Education Academic</option>
                                <option value="Education_Advocacy_Not_for_Profit">Education Advocacy (Not-for-Profit)</option>
                                <option value="Feature_Film_Production">Feature Film Production</option>
                                <option value="Freelancer_Researcher">Freelancer/Researcher</option>
                                <option value="Government">Government</option>
                                <option value="Independent_Film_Production">Independent Film Production</option>
                                <option value="News">News</option>
                                <option value="Personal_Community">Personal/Community</option>
                                <option value="Political_Campaigns">Political Campaigns</option>
                                <option value="Religious_Institution">Religious Institution</option>
                                <option value="Sports_Production">Sports Production</option>
                                <option value="TV_Production">TV Production</option>
                            ' )}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_jobtitle">Job Title:</label></td>
                        <td><input name="form_shotrequest[jobtitle]" value="{$this->getfv( $request, 'jobtitle' )}" id="form_shotrequest_jobtitle"></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_country">Country:</label></td>
                        <td>
                        <input name="form_shotrequest[country]" value="{$this->getfv( $request, 'country' )}" id="form_shotrequest_country">
                        <!--
                            <select name="form_shotrequest[country]" id="form_shotrequest_country">
                            {$this->parsefs( $request, 'country', '
                                <option>--Select--</option>
                                <option value="United States">United States</option>
                                <option value="Afghanistan">Afghanistan</option>
                                <option value="Albania">Albania</option>
                                <option value="Algeria">Algeria</option>
                                <option value="American Samoa">American Samoa</option>
                                <option value="Andorra">Andorra</option>
                                <option value="Angola">Angola</option>
                                <option value="Anguilla">Anguilla</option>
                                <option value="Antarctica">Antarctica</option>
                                <option value="Antigua And Barbuda">Antigua And Barbuda</option>
                                <option value="Argentina">Argentina</option>
                                <option value="Armenia">Armenia</option>
                                <option value="Aruba">Aruba</option>
                                <option value="Australia">Australia</option>
                                <option value="Austria">Austria</option>
                                <option value="Azerbaijan">Azerbaijan</option>
                                <option value="Bahamas">Bahamas</option>
                                <option value="Bahrain">Bahrain</option>
                                <option value="Bangladesh">Bangladesh</option>
                                <option value="Barbados">Barbados</option>
                                <option value="Belarus">Belarus</option>
                                <option value="Belgium">Belgium</option>
                                <option value="Belize">Belize</option>
                                <option value="Benin">Benin</option>
                                <option value="Bermuda">Bermuda</option>
                                <option value="Bhutan">Bhutan</option>
                                <option value="Bolivia">Bolivia</option>
                                <option value="Bosnia And Herzegowina">Bosnia And Herzegowina</option>
                                <option value="Botswana">Botswana</option>
                                <option value="Bouvet Island">Bouvet Island</option>
                                <option value="Brazil">Brazil</option>
                                <option value="British Indian Ocean Territory">British Indian Ocean Territory</option>
                                <option value="Brunei Darussalam">Brunei Darussalam</option>
                                <option value="Bulgaria">Bulgaria</option>
                                <option value="Burkina Faso">Burkina Faso</option>
                                <option value="Burundi">Burundi</option>
                                <option value="Cambodia">Cambodia</option>
                                <option value="Cameroon">Cameroon</option>
                                <option value="Canada">Canada</option>
                                <option value="Cape Verde">Cape Verde</option>
                                <option value="Cayman Islands">Cayman Islands</option>
                                <option value="Central African Republic">Central African Republic</option>
                                <option value="Chad">Chad</option>
                                <option value="Channel Islands">Channel Islands</option>
                                <option value="Chile">Chile</option>
                                <option value="China">China</option>
                                <option value="Christmas Island">Christmas Island</option>
                                <option value="Cocos (keeling) Islands">Cocos (keeling) Islands</option>
                                <option value="Colombia">Colombia</option>
                                <option value="Comoros">Comoros</option>
                                <option value="Congo">Congo</option>
                                <option value="Cook Islands">Cook Islands</option>
                                <option value="Costa Rica">Costa Rica</option>
                                <option value="Cote Divoire">Cote Divoire</option>
                                <option value="Croatia (local Name: Hrvatska)">Croatia (local Name: Hrvatska)</option>
                                <option value="Cyprus">Cyprus</option>
                                <option value="Czech Republic">Czech Republic</option>
                                <option value="Denmark">Denmark</option>
                                <option value="Djibouti">Djibouti</option>
                                <option value="Dominica">Dominica</option>
                                <option value="Dominican Republic">Dominican Republic</option>
                                <option value="East Timor">East Timor</option>
                                <option value="Ecuador">Ecuador</option>
                                <option value="Egypt">Egypt</option>
                                <option value="El Salvador">El Salvador</option>
                                <option value="Equatorial Guinea">Equatorial Guinea</option>
                                <option value="Eritrea">Eritrea</option>
                                <option value="Estonia">Estonia</option>
                                <option value="Ethiopia">Ethiopia</option>
                                <option value="Falkland Islands (malvinas)">Falkland Islands (malvinas)</option>
                                <option value="Faroe Islands">Faroe Islands</option>
                                <option value="Fiji">Fiji</option>
                                <option value="Finland">Finland</option>
                                <option value="France">France</option>
                                <option value="France, Metropolitan">France, Metropolitan</option>
                                <option value="French Guiana">French Guiana</option>
                                <option value="French Polynesia">French Polynesia</option>
                                <option value="French Southern Territories">French Southern Territories</option>
                                <option value="Gabon">Gabon</option>
                                <option value="Gambia">Gambia</option>
                                <option value="Georgia">Georgia</option>
                                <option value="Germany">Germany</option>
                                <option value="Ghana">Ghana</option>
                                <option value="Gibraltar">Gibraltar</option>
                                <option value="Greece">Greece</option>
                                <option value="Greenland">Greenland</option>
                                <option value="Grenada">Grenada</option>
                                <option value="Guadeloupe">Guadeloupe</option>
                                <option value="Guam">Guam</option>
                                <option value="Guatemala">Guatemala</option>
                                <option value="Guinea">Guinea</option>
                                <option value="Guinea-bissau">Guinea-bissau</option>
                                <option value="Guyana">Guyana</option>
                                <option value="Haiti">Haiti</option>
                                <option value="Heard And McDonald Islands">Heard And McDonald Islands</option>
                                <option value="Honduras">Honduras</option>
                                <option value="Hong Kong">Hong Kong</option>
                                <option value="Hungary">Hungary</option>
                                <option value="Iceland">Iceland</option>
                                <option value="India">India</option>
                                <option value="Indonesia">Indonesia</option>
                                <option value="Iran">Iran</option>
                                <option value="Ireland">Ireland</option>
                                <option value="Israel">Israel</option>
                                <option value="Italy">Italy</option>
                                <option value="Jamaica">Jamaica</option>
                                <option value="Japan">Japan</option>
                                <option value="Jordan">Jordan</option>
                                <option value="Kazakhstan">Kazakhstan</option>
                                <option value="Kenya">Kenya</option>
                                <option value="Kiribati">Kiribati</option>
                                <option value="Korea, South">Korea, South</option>
                                <option value="Kuwait">Kuwait</option>
                                <option value="Kyrgyzstan">Kyrgyzstan</option>
                                <option value="Lao Peoples Democratic Rep.">Lao Peoples Democratic Rep.</option>
                                <option value="Latvia">Latvia</option>
                                <option value="Lebanon">Lebanon</option>
                                <option value="Lesotho">Lesotho</option>
                                <option value="Liberia">Liberia</option>
                                <option value="Libyan Arab Jamahiriya">Libyan Arab Jamahiriya</option>
                                <option value="Liechtenstein">Liechtenstein</option>
                                <option value="Lithuania">Lithuania</option>
                                <option value="Luxembourg">Luxembourg</option>
                                <option value="Macau">Macau</option>
                                <option value="Macedonia">Macedonia</option>
                                <option value="Madagascar">Madagascar</option>
                                <option value="Malawi">Malawi</option>
                                <option value="Malaysia">Malaysia</option>
                                <option value="Maldives">Maldives</option>
                                <option value="Mali">Mali</option>
                                <option value="Malta">Malta</option>
                                <option value="Marshall Islands">Marshall Islands</option>
                                <option value="Martinique">Martinique</option>
                                <option value="Mauritania">Mauritania</option>
                                <option value="Mauritius">Mauritius</option>
                                <option value="Mayotte">Mayotte</option>
                                <option value="Mexico">Mexico</option>
                                <option value="Micronesia">Micronesia</option>
                                <option value="Moldova, Republic Of">Moldova, Republic Of</option>
                                <option value="Monaco">Monaco</option>
                                <option value="Mongolia">Mongolia</option>
                                <option value="Montserrat">Montserrat</option>
                                <option value="Morocco">Morocco</option>
                                <option value="Mozambique">Mozambique</option>
                                <option value="Myanmar">Myanmar</option>
                                <option value="Namibia">Namibia</option>
                                <option value="Nauru">Nauru</option>
                                <option value="Nepal">Nepal</option>
                                <option value="Netherlands">Netherlands</option>
                                <option value="Netherlands Antilles">Netherlands Antilles</option>
                                <option value="New Caledonia">New Caledonia</option>
                                <option value="New Zealand">New Zealand</option>
                                <option value="Nicaragua">Nicaragua</option>
                                <option value="Niger">Niger</option>
                                <option value="Nigeria">Nigeria</option>
                                <option value="Niue">Niue</option>
                                <option value="Norfolk Island">Norfolk Island</option>
                                <option value="Northern Mariana Islands">Northern Mariana Islands</option>
                                <option value="Norway">Norway</option>
                                <option value="Oman">Oman</option>
                                <option value="Pakistan">Pakistan</option>
                                <option value="Palau">Palau</option>
                                <option value="Panama">Panama</option>
                                <option value="Papua New Guinea">Papua New Guinea</option>
                                <option value="Paraguay">Paraguay</option>
                                <option value="Peru">Peru</option>
                                <option value="Philippines">Philippines</option>
                                <option value="Pitcairn">Pitcairn</option>
                                <option value="Poland">Poland</option>
                                <option value="Portugal">Portugal</option>
                                <option value="Puerto Rico">Puerto Rico</option>
                                <option value="Qatar">Qatar</option>
                                <option value="Reunion">Reunion</option>
                                <option value="Romania">Romania</option>
                                <option value="Russian Federation">Russian Federation</option>
                                <option value="Rwanda">Rwanda</option>
                                <option value="S. Georgia &amp; S. Sandwich Islands">S. Georgia &amp; S. Sandwich Islands</option>
                                <option value="Saint Kitts And Nevis">Saint Kitts And Nevis</option>
                                <option value="Saint Lucia">Saint Lucia</option>
                                <option value="Saint Vincent &amp; The Grenadines">Saint Vincent &amp; The Grenadines</option>
                                <option value="Samoa">Samoa</option>
                                <option value="San Marino">San Marino</option>
                                <option value="Sao Tome And Principe">Sao Tome And Principe</option>
                                <option value="Saudi Arabia">Saudi Arabia</option>
                                <option value="Senegal">Senegal</option>
                                <option value="Seychelles">Seychelles</option>
                                <option value="Sierra Leone">Sierra Leone</option>
                                <option value="Singapore">Singapore</option>
                                <option value="Slovakia (slovak Republic)">Slovakia (slovak Republic)</option>
                                <option value="Slovenia">Slovenia</option>
                                <option value="Solomon Islands">Solomon Islands</option>
                                <option value="Somalia">Somalia</option>
                                <option value="South Africa">South Africa</option>
                                <option value="Spain">Spain</option>
                                <option value="Sri Lanka">Sri Lanka</option>
                                <option value="St. Helena">St. Helena</option>
                                <option value="St. Pierre And Miquelon">St. Pierre And Miquelon</option>
                                <option value="Sudan">Sudan</option>
                                <option value="Suriname">Suriname</option>
                                <option value="Svalbard &amp; Jan Mayen Islands">Svalbard &amp; Jan Mayen Islands</option>
                                <option value="Swaziland">Swaziland</option>
                                <option value="Sweden">Sweden</option>
                                <option value="Switzerland">Switzerland</option>
                                <option value="Syrian Arab Republic">Syrian Arab Republic</option>
                                <option value="Taiwan">Taiwan</option>
                                <option value="Tajikistan">Tajikistan</option>
                                <option value="Tanzania, United Republic Of">Tanzania, United Republic Of</option>
                                <option value="Thailand">Thailand</option>
                                <option value="Togo">Togo</option>
                                <option value="Tokelau">Tokelau</option>
                                <option value="Tonga">Tonga</option>
                                <option value="Trinidad And Tobago">Trinidad And Tobago</option>
                                <option value="Tunisia">Tunisia</option>
                                <option value="Turkey">Turkey</option>
                                <option value="Turkmenistan">Turkmenistan</option>
                                <option value="Turks And Caicos Islands">Turks And Caicos Islands</option>
                                <option value="Tuvalu">Tuvalu</option>
                                <option value="U.S. Minor Outlying Islands">U.S. Minor Outlying Islands</option>
                                <option value="Uganda">Uganda</option>
                                <option value="Ukraine">Ukraine</option>
                                <option value="United Arab Emirates">United Arab Emirates</option>
                                <option value="United Kingdom">United Kingdom</option>
                                <option value="United States">United States</option>
                                <option value="Uruguay">Uruguay</option>
                                <option value="Uzbekistan">Uzbekistan</option>
                                <option value="Vanuatu">Vanuatu</option>
                                <option value="Vatican City State (holy See)">Vatican City State (holy See)</option>
                                <option value="Venezuela">Venezuela</option>
                                <option value="Vietnam">Vietnam</option>
                                <option value="Virgin Islands (british)">Virgin Islands (british)</option>
                                <option value="Virgin Islands (u.s.)">Virgin Islands (u.s.)</option>
                                <option value="Wallis And Futuna Islands">Wallis And Futuna Islands</option>
                                <option value="Western Sahara">Western Sahara</option>
                                <option value="Yemen">Yemen</option>
                                <option value="Yugoslavia">Yugoslavia</option>
                                <option value="Zaire">Zaire</option>
                                <option value="Zambia">Zambia</option>
                                <option value="Zimbabwe">Zimbabwe</option>
                            ' )}
                            </select>-->
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_state">State/Province:</label></td>
                        <td><input name="form_shotrequest[state]" value="{$this->getfv( $request, 'state' )}" id="form_shotrequest_state"></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_phone">Phone:</label></td>
                        <td><input name="form_shotrequest[phone]" value="{$this->getfv( $request, 'phone' )}" id="form_shotrequest_phone"></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_website">Company Website:</label></td>
                        <td><input name="form_shotrequest[website]" value="{$this->getfv( $request, 'website' )}" id="form_shotrequest_website"></td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_email">Email Address: <i>*</i></label></td>
                        <td><input name="form_shotrequest[email]" value="{$this->getfv( $request, 'email' )}" id="form_shotrequest_email"></td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label for="form_shotrequest_production_description">Description of your Current Production:</label>
                            <textarea name="form_shotrequest[production_description]" id="form_shotrequest_production_description">{$this->getfv( $request, 'production_description' )}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2">
                            <label for="form_shotrequest_footage_details">Details of Footage Required:</label>
                            <textarea name="form_shotrequest[footage_details]" id="form_shotrequest_footage_details">{$this->getfv( $request, 'footage_details' )}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_format">Minimum Source Format?</label></td>
                        <td>
                            <select name="form_shotrequest[format]" id="form_shotrequest_format">
                            {$this->parsefs( $request, 'format', '
                                <option>-No Preference-</option>
                                <option value="Standard Definition">Standard Definition</option>
                                <option value="High Definition">High Definition</option>
                                <option value="35mm Film">35mm Film</option>
                                <option value="4K">4K</option>
                            ' )}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_preview_deadline">Deadline for Preview Material?</label></td>
                        <td>
                            <select name="form_shotrequest[preview_deadline]" id="form_shotrequest_preview_deadline">
                            {$this->parsefs( $request, 'preview_deadline', '
                                <option value="no rush" selected="">-No Rush-</option>
                                <option value="Today">Today</option>
                                <option value="1-3 Days">1-3 Days</option>
                                <option value="1 Week">1 Week</option>
                                <option value="2-4 Weeks">2-4 Weeks</option>
                            ' )}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_master_deadline">Deadline for Master Footage?</label></td>
                        <td>
                            <select name="form_shotrequest[master_deadline]" id="form_shotrequest_master_deadline">
                            {$this->parsefs( $request, 'master_deadline', '
                                <option selected="">-No Rush-</option>
                                <option value="Today">Today</option>
                                <option value="1-3 Days">1-3 Days</option>
                                <option value="1 Week">1 Week</option>
                                <option value="2-4 Weeks">2-4 Weeks</option>
                            ' )}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_license">License Usage Required?</label></td>
                        <td>
                            <select name="form_shotrequest[license]" id="form_shotrequest_license">
                            {$this->parsefs( $request, 'license', '
                                <option>--Select License Category--</option>
                                <option value="Advertising">Advertising</option>
                                <option value="Corporate / Government">Corporate / Government</option>
                                <option value="Documentary &amp; Editorial">Documentary &amp; Editorial</option>
                                <option value="Educational">Educational</option>
                                <option value="Entertainment">Entertainment</option>
                                <option value="Screener">Screener</option>
                            ' )}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td><label for="form_shotrequest_budget">Anticipated Budget:</label></td>
                        <td><input name="form_shotrequest[budget]" value="{$this->getfv( $request, 'budget' )}" id="form_shotrequest_budget" /></td>
                    </tr>
                    <tr>
                        <td><label for="form_contactus_inquiry">Captcha: <i>*</i></label></td>
                        <td>
                            <div class="g-recaptcha" data-sitekey="6Le7dBgTAAAAAFW4c-ukhSth5_TSbyXONfLDnDUY"></div>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="2"><input type="submit" name="form_shotrequest_submit" value="Submit" /></td>
                    </tr>
                </table>
            </form>
HTML;
    }

    /**
     * Короткий вариант вызова get_footagesearch_form_value()
     */
    function getfv ( $request, $name, $default = NULL ) {
        return $this->get_footagesearch_form_value( $request, $name, $default );
    }

    /**
     * Получить значение формы
     *
     * @param array $request    _POST-данные формы
     * @param string $name      Название поля формы
     * @param mixed $default    Значение по умолчанию
     *
     * @return mixed
     */
    function get_footagesearch_form_value ( $request, $name, $default = NULL ) {
        return ( isset( $request[ $name ] ) && ! empty( $request[ $name ] ) ) ? trim( $request[ $name ] ) : $default;
    }

    /**
     * Получить все данные формы [footagesearch_form_contactus]
     *
     * @return array|bool
     */
    function get_footagesearch_form_contactus_data () {
        if ( isset( $_REQUEST[ 'form_contactus_submit' ] ) && !empty( $_REQUEST[ 'form_contactus' ] ) ) {
            return $_REQUEST[ 'form_contactus' ];
        }
        return FALSE;
    }

    /**

     * Получить все данные формы [footagesearch_form_shotrequest]
     *
     * @return array|bool
     */
    function get_footagesearch_form_shotrequest_data () {
        if ( isset( $_REQUEST[ 'form_shotrequest_submit' ] ) && !empty( $_REQUEST[ 'form_shotrequest' ] ) ) {
            return $_REQUEST[ 'form_shotrequest' ];
        }
        return FALSE;
    }

    /**
     * Преобразовать статус формы для list( $type, $visible, $message )
     *
     * @param array $formdata
     *
     * @return array
     */
    function parse_footagesearch_form_formdata ( $formdata = array () ) {
        return array (
            0 => ( isset( $formdata[ 'error' ] ) && $formdata[ 'error' ] == TRUE ) ? 'color: red;' : 'color: green;',
            1 => ( isset( $formdata[ 'message' ] ) && ! empty( $formdata[ 'message' ] ) ) ? NULL : 'display: none;',
            2 => ( isset( $formdata[ 'message' ] ) && ! empty( $formdata[ 'message' ] ) ) ? implode( '<br />' . PHP_EOL, $formdata[ 'message' ] ) : NULL,
            3 => ( isset( $formdata[ 'complete' ] ) && $formdata[ 'complete' ] ) ? 'display: none;' : NULL
        );
    }

    /**
     * Выполнить REST-запрос с данными формы
     *
     * @param string $method    Метод контроллера фронтенда
     * @param array  $data      Данные формы
     *
     * @return array
     */
    function send_api_form_request ( $method, $data ) {
        $user = wp_get_current_user();
        $data[ 'user_id' ] = ( isset( $user->ID ) ) ? $user->ID : 0;
        $data[ 'user_login' ] = ( isset( $user->ID ) ) ? $user->user_login : NULL;
        $data[ 'timestamp' ] = date( 'Y-m-d H:i:s' );
        return $this->api_request( array( 'method' => $method, 'post_params' => $data ) );
    }

    /**
     * Добавить к статусу формы статус REST-запроса
     *
     * @param array $formdata   Статус формы
     * @param array $status     Статус REST-запроса
     *
     * @return array
     */
    function add_formdata_request_status ( $formdata, $status ) {
        if ( $status && isset( $status[ 'status' ] ) && strtolower( $status[ 'status' ] ) == 'ok' ) {
            $formdata[ 'message' ][] = 'Thank You, Your information has been received.';
            $formdata[ 'complete' ] = TRUE;
        } else {
            $formdata[ 'error' ] = TRUE;
            $formdata[ 'message' ][] = 'Request error... :(';
        }
        return $formdata;
    }

    /**
     * Добавить к статусу формы ошибку незаполненных полей формы
     *
     * @param array $formdata
     *
     * @return array
     */
    function add_formdata_required_error ( array $formdata ) {
        $formdata[ 'error' ] = TRUE;
        $formdata[ 'message' ][] = 'Please fill all required fields.';
        return $formdata;
    }

    /**
     * Предикат-проверка заполненных полей формы
     *
     * @param array $request
     * @param array $list
     *
     * @return bool
     */
    function predicate_form_filled_fields ( $request, $list = array () ) {
        $result = FALSE;

        function getCurlData($url)
        {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($curl, CURLOPT_TIMEOUT, 15);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            $curlData = curl_exec($curl);

            curl_close($curl);

            return $curlData;
        }

        $recaptcha=$_POST['g-recaptcha-response'];

        if(!empty($recaptcha)){

        $google_url="https://www.google.com/recaptcha/api/siteverify";
        $secret='6Le7dBgTAAAAAOHvru4RKFlofGqh-3mCBrW_kz6n';
        $ip=$_SERVER['REMOTE_ADDR'];
        $url=$google_url."?secret=".$secret."&response=".$recaptcha."&remoteip=".$ip;

            $res=getCurlData($url);
            $res= json_decode($res, true);


        if($res['success']){

        if ( is_array( $list ) ) {
            foreach ( $list as $name ) {
                if ( ! isset( $request[ $name ] ) || empty( $request[ $name ] ) ) {
                    return FALSE;
                }
            }
            $result = TRUE;
        }

        }
        }


        return $result;
    }

    /**
     * Короткий вызов parse_footagesearch_form_select()
     */
    function parsefs ( $request, $name, $html ) {
        return $this->parse_footagesearch_form_select( $request, $name, $html );
    }

    /**
     * Парсит и устанавливает selected для выбранной опции формы
     *
     * @param array  $request   _POST-данные формы
     * @param string $name      Название опции
     * @param string $html      Опции селекта формы
     *
     * @return string
     */
    function parse_footagesearch_form_select ( $request, $name, $html ) {
        $value = $this->get_footagesearch_form_value( $request, $name, '' );
        if ( $value ) {
            $pattern = '|<option value=\"(.*)\">|Ui';
            $matches = array ();
            if ( preg_match_all( $pattern, $html, $matches ) && ! empty( $matches ) ) {
                list( $fulllist, $values ) = $matches;
                foreach ( $values as $key => $current ) {
                    if ( $value === trim( $current ) ) {
                        $search = $fulllist[ $key ];
                        $replace = rtrim( $fulllist[ $key ], '>' ) . ' selected>';
                        $html = str_replace( $search, $replace, $html );
                        break;
                    }
                }
            }
        }
        return $html;
    }

    /**
     * Получение данных с профиля пользователя для форм
     *
     * @return array
     */
    function get_footagesearch_user_data () {
        if ( is_user_logged_in() && ( $user = wp_get_current_user() ) && $user->ID ) {
            $usermeta = get_user_meta( $user->ID );
            $firstname = ( isset( $usermeta[ 'first_name' ] ) && isset( $usermeta[ 'first_name' ][ 0 ] ) ) ? $usermeta[ 'first_name' ][ 0 ] : NULL;
            $lastname = ( isset( $usermeta[ 'last_name' ] ) && isset( $usermeta[ 'last_name' ][ 0 ] ) ) ? $usermeta[ 'last_name' ][ 0 ] : NULL;
            $email = ( isset( $user->user_email ) ) ? $user->user_email : NULL;
            $website = ( isset( $user->user_url ) ) ? $user->user_url : NULL;
            $company_name = ( isset( $usermeta[ 'company_name' ] ) && isset( $usermeta[ 'company_name' ][ 0 ] ) ) ? $usermeta[ 'company_name' ][ 0 ] : NULL;
            $phone = ( isset( $usermeta[ 'phone' ] ) && isset( $usermeta[ 'phone' ][ 0 ] ) ) ? $usermeta[ 'phone' ][ 0 ] : NULL;
            $country = ( isset( $usermeta[ 'country' ] ) && isset( $usermeta[ 'country' ][ 0 ] ) ) ? $usermeta[ 'country' ][ 0 ] : NULL;
            return array(
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'email'     => $email,
                'website'   => $website,
                'companyname' => $company_name,
                'phone' => $phone,
                'country' => $country
            );
        }
        return array();
    }

    /* ФОРМЫ ФРОНТЕНДА [ ContactUs, ShotRequest ] : END ***************************************************************/

    function activate(){
        global $wpdb;

        $type = 'list_item';
        $table_name = $wpdb->prefix . $type . 'meta';
        $this->create_metadata_table($table_name, 'list_item');

        $type = 'list';
        $table_name = $wpdb->prefix . $type . 'meta';
        $this->create_metadata_table($table_name, 'list');

        flush_rewrite_rules();

        add_role(
            'guest_administrator',
            __('Guest Administrator'),
            array(
                'read' => true
            )
        );

        add_role(
            'backend_administrator',
            __('Backend Administrator'),
            array(
                'read' => true
            )
        );

        $role = get_role('administrator');
		/*if(is_object($mp_roles) && method_exists($wp_roles,'add_cap'))
		{
			$wp_roles->add_cap($role, 'manage_backend');
		}*/
        $role->add_cap('manage_backend');

        $role = get_role('guest_administrator');
		/*if(is_object($mp_roles) && method_exists($wp_roles,'add_cap'))
		{
			$wp_roles->add_cap($role, 'manage_backend');
		}*/
        $role->add_cap('manage_backend');

        $role = get_role('backend_administrator');
		/*if(is_object($mp_roles) && method_exists($wp_roles,'add_cap'))
		{
			$wp_roles->add_cap($role, 'manage_backend_directly');
		}*/
        $role->add_cap('manage_backend_directly');
        //$role->remove_cap('manage_backend');

    }

    function create_metadata_table($table_name, $type) {
        global $wpdb;

        if (!empty ($wpdb->charset))
            $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
        if (!empty ($wpdb->collate))
            $charset_collate .= " COLLATE {$wpdb->collate}";

        $sql = "CREATE TABLE IF NOT EXISTS {$table_name} (
        meta_id bigint(20) NOT NULL AUTO_INCREMENT,
        {$type}_id bigint(20) NOT NULL default 0,

        meta_key varchar(255) DEFAULT NULL,
        meta_value longtext DEFAULT NULL,

        UNIQUE KEY meta_id (meta_id)
    ) {$charset_collate};";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    function uninstall(){
        delete_option('footagesearch');
    }

    /*
     * Outputs clips list by shortcode parameters
     */
    private function display_clips_list($result, $shortcode_params){
        get_guest_bin();
        get_guest_cart();

        $output = '';
        $pagination = '';



        if(count($result['data'])){

            global $footagesearch_clipbin;
            $bins_list = $footagesearch_clipbin->get_bins_list();
            $current_bin = $_SESSION['footagesearch_current_bin'];
            $sort_options = $this->get_sort_options();
            $in_clipbin = array();
            $clipbin_content = $footagesearch_clipbin->get_current_bin_content();
            if($clipbin_content){
                foreach($clipbin_content as $clipbin_item){
                    $in_clipbin[] = $clipbin_item['id'];
                }
            }
            $in_cart = array();
            if(!empty($_SESSION['footagesearch_cart'])){
                foreach($_SESSION['footagesearch_cart'] as $cart_item){
                    $in_cart[] = $cart_item['id'];
                }
            }
            if($this->settings['clip_holder']){

                if(is_numeric($this->settings['clip_holder'])){
                    $clip_holder = get_page((int)$this->settings['clip_holder'], ARRAY_A);
                }
                else{
                    $clip_holder = get_page_by_path($this->settings['clip_holder'], ARRAY_A);
                }

            }

            if(isset($shortcode_params['perpage']) && $shortcode_params['perpage']){
                $perpage = intval($shortcode_params['perpage']);
                $pagination = $this->display_pagination($result['total'], $shortcode_params);
                $perpage_form_action = remove_query_arg('page_start_num', $_SERVER['REQUEST_URI']);
            }

            $list_view = $this->getListView();

            $drag_and_drop_message = $this->drag_and_drop_message;
            //$drag_and_drop_message = true;

            $template = $this->fsec_get_template_file_path('footagesearch-clips-list.php');
            if($template){
                global $wp_query;

                // contributors search title crutch
                if (!empty($wp_query->query['owner']) && !empty($wp_query->query['words'])) {
                    $searchString = str_replace('-', ' ', $wp_query->query['words']);
                    $owner = get_user_by('login', $wp_query->query['owner']);
                    if (isset($owner->first_name) || isset($owner->last_name)) {
                        $contributorsSearchTitle = sprintf(
                            '%s Stock Footage by %s',
                            $searchString,
                            $owner->first_name . ' ' . $owner->last_name
                        );
                    }
                }
                // --

                ob_start();
                include_once($template);
                $output .= ob_get_contents();
                ob_end_clean();
            }
            $output .= '<div class="clear"></div>';
        }

        return $output;
    }

    /**
     * generate clips not found template and return it as string
     * @return string
     */
    private function display_clips_not_found_message($header404 = true)
    {
        $output = "";
        $template = $this->fsec_get_template_file_path('footagesearch-clips-not-found.php');
        if ($template) {
            $searchTerm = $this->getSearchTerm();
            ob_start();
            include_once($template);
            $output .= ob_get_contents();
            ob_end_clean();
        }

        if ($header404) {
            header("HTTP/1.0 404 Not Found");
        }

        return $output;
    }

    function setListView($listView){
        $current_user = wp_get_current_user();
        if($current_user->ID){
            update_user_meta( $current_user->ID, 'list_view', $listView );
        }
        else{
            set_guest_other($_SESSION);
            $_SESSION['list_view'] = $listView;
        }
    }

    function getListView(){
        $current_user = wp_get_current_user();
        if($current_user->ID){
            $list_view = get_the_author_meta( "list_view", $current_user->ID ) ? get_the_author_meta( "list_view", $current_user->ID ) : 'grid';
            return $list_view;
        }
        else{
            get_guest_other();
            return isset($_SESSION['list_view']) ? $_SESSION['list_view'] : 'list';
        }
    }

    /*
     * Output provider profile page
     */
    function display_provider_profile_page($user_login=false){
        if(!$user_login) return false;

        $params = array('method' => 'get_user_by_login');
        $params['post_params']['login'] = $user_login;
        $provider_info = $this->api_request($params);
        $user_id=$provider_info['data']['id'];
        $this->update_views_statistic($user_id);
        $provider_statistic = $this->get_provider_statistic($user_id);

        $avatar = user_avatar_get_backend_avatar($provider_info['data']['meta']['avatar'], 150);
        $banner = user_avatar_get_banner($provider_info);

        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }
        $galleries_data = $this->get_galleries(1,$user_id);
        $galleries = $galleries_data['galleries'];
        $cloud_tags = $galleries_data['cloud_tags'];

        if($galleries && $clips_holder){
            foreach ($galleries as &$gallery) {
                $gallery['link'] = add_query_arg('gallery', $gallery['id'], get_permalink($clips_holder['ID']));
            }
        }
        $galleries_title = __('Featured Galleries', 'footagesearch');
        $galleries_list_template = $this->fsec_get_template_file_path('footagesearch-provider-galleries-list.php');
        $galleries_list = '';
        if($galleries_list_template){
            ob_start();
            include_once($galleries_list_template);
            $galleries_list = ob_get_contents();
            ob_end_clean();
        }
        $categories = $this->get_categories(1);
        if($categories && $clips_holder){
            foreach ($categories as &$cat) {
                $cat['link'] = add_query_arg('category', ($cat['uri'] ? $cat['uri'] : $cat['id']), get_permalink($clips_holder['ID']));
            }
        }
        $categories_list_template = $this->fsec_get_template_file_path('footagesearch-provider-categories-list.php');
        $categories_list = '';
        if($categories_list_template){
            ob_start();
            include_once($categories_list_template);
            $categories_list = ob_get_contents();
            ob_end_clean();
        }
        if($clips_holder)
            $search_action = get_permalink($clips_holder['ID']);
        else
            $search_action = '';

        $output = '';
        $template = $this->fsec_get_template_file_path('footagesearch-provider-profile.php');
        if($template){
            ob_start();
            include_once($template);
            $output .= ob_get_contents();
            ob_end_clean();
        }
        $output .= '<div class="clear"></div>';
        return $output;
    }

    function display_contributor_galleries(){
        $view_galleries=(int)($_REQUEST['view_galleries']=='All');
        $tmp=$view_galleries;
        $user_id=$_REQUEST['profile'];
        $galleries_data = $this->get_galleries($view_galleries,$user_id);
        $galleries = $galleries_data['galleries'];
        $cloud_tags = $galleries_data['cloud_tags'];
        $view_galleries=($view_galleries)? 'Featured' : 'All';
        $galleries_title = __($view_galleries, 'footagesearch');
        $galleries_list_template = $this->fsec_get_template_file_path('footagesearch-provider-galleries-list.php');
        $galleries_list = '';

        // Create link on gallery
        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }
        if($galleries && $clips_holder){
            foreach ($galleries as &$gallery) {
                $gallery['link'] = add_query_arg('gallery', $gallery['id'], get_permalink($clips_holder['ID']));
            }
        }

        if($galleries_list_template){
            ob_start();
            include_once($galleries_list_template);
            $galleries_list = ob_get_contents();
            ob_end_clean();
        }
        $res=(object)array('test'=>$tmp,'galleries'=>$galleries_list,'title'=>$galleries_title,'tags'=>$cloud_tags);
        echo json_encode($res); exit;die();
    }

    /**
     * return provider_info in backend
     * @param mix $user_login - in backend
     * @return mix array
     */
    function get_backend_user_by_login($user_login){
        $params = array('method' => 'get_user_by_login');
        $params['post_params']['login'] = $user_login;
        $provider_info = $this->api_request($params);
        return $provider_info;
    }
    /**
     * update user_meta in backend
     * @param mix $user_id - in backend
     */
    function update_metadata_user($user_id){
        $params = array('method' => 'get_user_by_id');
        $params['post_params']['id'] = $user_id;
        $provider_info = $this->api_request($params);
        global $wpdb;
        $frontUserMeta=$wpdb->get_results('SELECT um.meta_key,um.meta_value FROM '.$wpdb->prefix.'usermeta AS um JOIN '.$wpdb->prefix.'users AS u ON u.id=um.user_id WHERE u.user_login="'.$provider_info['data']['login'].'"','ARRAY_A');
        if(!empty($frontUserMeta)){
            $params = array('method' => 'update_metauser');
            $params['post_params']['id'] = $user_id;
            $params['post_params']['meta'] = json_encode($frontUserMeta);
            $provider_info = $this->api_request($params);
        }
    }

    function display_browse_page($atts) {
        $content = '';
        $page = get_page_by_path(str_replace('.htm', '', $atts['url']), OBJECT, 'browse_page');
        if($page) {
            global $wpdb;
            $browse_page_template = $this->fsec_get_template_file_path('footagesearch-browse-page.php');
            if($browse_page_template){
                ob_start();
                include_once($browse_page_template);
                $content = ob_get_contents();
                ob_end_clean();
            }
        }
        return $content;

//        $id = $atts['id'];
//
//        $the_query = new WP_Query('page_id='.$id . '&post_type=browse_page');


//        $output = "";
//        while ( $the_query->have_posts() ) :
//            $the_query->the_post();
//            $output .= get_the_title();
//            $output .= get_the_content();
//        endwhile;
//        wp_reset_postdata();
//        return $output;

//        while ( $the_query->have_posts() ) {
//            $the_query->the_post();
////            if($title == true){
////                the_title();
////            }
//            return 123;
//            the_content();
//        }
//        wp_reset_postdata();
    }

    function get_galleries($featured = 0, $user_id=false){
        $user_id= (!$user_id) ? 1 : $user_id;
        $params = array('method' => 'provider_galleries');
        $params['query_params']['user_id'] = $user_id;
        if($featured)
            $params['query_params']['featured'] = 1;
        $result = $this->api_request($params);
        $galleries = array();
        if($result && $result['data']){
            $galleries = $result['data'];
        }
        return array('galleries'=>$galleries, 'cloud_tags'=>$result['cloud_tags']);
    }

    function get_gallery($gallery_id){
        $params = array('method' => 'provider_gallery');
        $params['query_params']['gallery_id'] = $gallery_id;
        $result = $this->api_request($params);
        return $result['data'];
    }

    function get_categories($featured = 0){
        $params = array('method' => 'categories');
        if($featured)
            $params['query_params']['featured'] = 1;
        $result = $this->api_request($params);
        $categories = array();
        if($result && $result['data']){
            $categories = $result['data'];
        }
        return $categories;
    }

    function get_all_galleries(){
        $galleries = $this->get_galleries();
        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }
        if($galleries && $clips_holder){
            foreach ($galleries as &$gallery) {
                $gallery['link'] = add_query_arg('gallery', $gallery['id'], get_permalink($clips_holder['ID']));
            }
        }
        $galleries_title = __('Galleries', 'footagesearch');
        $galleries_list_template = $this->fsec_get_template_file_path('footagesearch-provider-galleries-list.php');
        $galleries_list = '';
        if($galleries_list_template){
            ob_start();
            include_once($galleries_list_template);
            $galleries_list = ob_get_contents();
            ob_end_clean();
        }
        if(isset($_GET['ajax']) && $_GET['ajax'] == 'true'){
            $data['success'] = true;
            $data['galleries_list'] = $galleries_list;
            $response = json_encode($data);
            header("Content-Type: application/json");
            echo $response;
            exit();
        }
    }

    function get_all_categories(){
        $categories = $this->get_categories();
        if($categories){
            $clips_holder = false;
            if($this->settings['clips_holder']){

                if(is_numeric($this->settings['clips_holder'])){
                    $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
                }
                else{
                    $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
                }

            }
            if($clips_holder){
                foreach ($categories as &$item) {
                    $item['link'] = add_query_arg('category', ($item['uri'] ? $item['uri'] : $item['id']), get_permalink($clips_holder['ID']));
                }
            }

        }
        $categories_list_template = $this->fsec_get_template_file_path('footagesearch-provider-categories-list.php');
        $categories_list = '';
        if($categories_list_template){
            ob_start();
            include_once($categories_list_template);
            $categories_list = ob_get_contents();
            ob_end_clean();
        }
        if(isset($_GET['ajax']) && $_GET['ajax'] == 'true'){
            $data['success'] = true;
            $data['categories_list'] = $categories_list;
            $response = json_encode($data);
            header("Content-Type: application/json");
            echo $response;
            exit();
        }
    }

    /*
     * Outputs pagination
     */
    public function display_pagination($total, $shortcode_params){
        $pagination = '';
        if($total && $shortcode_params){
            if(isset($shortcode_params['limit']) && $shortcode_params['limit'] && $total > $shortcode_params['limit']){
                $total_pages = ceil($shortcode_params['limit'] / $shortcode_params['perpage']);
            }
            else{
                $total_pages = ceil($total / $shortcode_params['perpage']);
            }

            if($total_pages > 1){
                global $wp_rewrite, $wp_query;
    //            $wp_query->query_vars['paged'] > 1 ? $current = $wp_query->query_vars['paged'] : $current = 1;
    //            $pagination = array(
    //                'base' => @add_query_arg('page','%#%'),
    //                'format' => '',
    //                'total' => $total_pages,
    //                'current' => $current,
    //                'prev_text' => __('« Previous'),
    //                'next_text' => __('Next »'),
    //                'end_size' => 1,
    //                'mid_size' => 2,
    //                'show_all' => true,
    //                'type' => 'plain'
    //            );
    //            if ( $wp_rewrite->using_permalinks() )
    //                $pagination['base'] = user_trailingslashit( trailingslashit( remove_query_arg( 's', get_pagenum_link( 1 ) ) ) . 'page/%#%/', 'paged' );
    //            if ( !empty( $wp_query->query_vars['s'] ) )
    //                $pagination['add_args'] = array( 's' => get_query_var( 's' ) );
    //            $pagination = paginate_links( $pagination );

                $args = array(
                    'base'         => @add_query_arg('page_start_num','%#%'),
                    'format'       => '',
                    'total'        => $total_pages,
                    'current'      => (isset($shortcode_params['page']) && $shortcode_params['page']) ? $shortcode_params['page'] : 1,
                    'show_all'     => False,
                    'end_size'     => 4,
                    'mid_size'     => 1,
                    'prev_next'    => True,
                    'prev_text'    => __('<'),
                    'next_text'    => __('>'),
                    'type'         => 'plain',
                    'add_args'     => False,
                    'add_fragment' => ''
                );

                $pagination = paginate_links($args);
                // add rel nofollow to pagination links
                $pagination = str_replace('<a ', '<a rel="nofollow" ', paginate_links($args));
            }
        }
        return $pagination;
    }

    /*
     * Outputs single clip
     */
    private function display_clip($result, $shortcode_params){


        $output = '';

        if($result['data'] && isset($result['data']['res']) && $result['data']['res']){
            $clip = $result['data'];
            $clip['clip_shortcode_id'] = $shortcode_params['shortcode_id'] . '-' . $clip['id'];
            $clip['subject_category'] = $clip['subject_category'] ? explode(',', $clip['subject_category']) : '';
            $clip['primary_subject'] = $clip['primary_subject'] ? explode(',', $clip['primary_subject']) : '';
            $clip['other_subject'] = $clip['other_subject'] ? explode(',', $clip['other_subject']) : '';
            $clip['appearance'] = $clip['appearance'] ? explode(',', $clip['appearance']) : '';
            $clip['concept'] = $clip['concept'] ? explode(',', $clip['concept']) : '';
            $clip['shot_type'] = $clip['shot_type'] ? explode(',', $clip['shot_type']) : '';
            $clip['actions'] = $clip['actions'] ? explode(',', $clip['actions']) : '';
            $clip['location'] =  $clip['location'] ? explode(',', $clip['location']) : '';
            $clip['time'] =  $clip['time'] ? explode(',', $clip['time']) : '';
            $clip['habitat'] =  $clip['habitat'] ? explode(',', $clip['habitat']) : '';
            $clip['in_clipbin'] = false;
            $clip['in_cart'] = false;
            global $footagesearch_clipbin;
            global $footagesearch_cart;
            $clipbin_content = $footagesearch_clipbin->get_current_bin_content();
            if($clipbin_content){
                foreach($clipbin_content as $clipbin_item){
                    if($clip['id'] == $clipbin_item['id']){
                        $clip['in_clipbin'] = true;
                        break;
                    }
                }
            }
            if(isset($_SESSION['footagesearch_cart'])){
                foreach($_SESSION['footagesearch_cart'] as $cart_item){
                    if($clip['id'] == $cart_item['id']){
//                        echo '<pre>';
//                        print_r($cart_item);
//                        exit();
                        $clip['in_cart'] = true;
                        if(isset($cart_item['delivery_format']) && $cart_item['delivery_format'])
                            $clip['delivery_format'] = $cart_item['delivery_format'];
                        if(isset($cart_item['delivery_factor']) && $cart_item['delivery_factor'])
                            $clip['delivery_factor'] = $cart_item['delivery_factor'];
                        if(isset($cart_item['delivery_price']) && $cart_item['delivery_price'])
                            $clip['delivery_price'] = $cart_item['delivery_price'];
                        break;
                    }
                }
            }

            if(isset($clip['price']) && $clip['price'] == 0){
                unset($clip['price']);
            }


            //Get clip ecommerce info
            $result = api_request(array('method' => 'cartclip', 'post_params' => array('clip_id' => $clip['id'])));
            $clip['license_term'] = $_SESSION['footagesearch_cart_license_rf'];
            if($result && $result['data']){
                $clip = array_merge($clip, $result['data']);

                // Set default delivery option
                if (!$clip['delivery_format'] && $clip['delivery_methods']) {
                    foreach ($clip['delivery_methods'] as $method) {
                        if ($clip['delivery_format']) break;
                        if ($method['formats']) {
                            foreach ($method['formats'] as $format){
                                if ($format['default']) {
                                    $clip['delivery_format'] = $format['id'];
                                    $clip['delivery_factor'] = $format['price_factor'];
                                    $clip['delivery_method'] = $method['id'];
                                    break;
                                }
                            }
                        }
                    }
                }

                if($clip['license'] == 1){
                    $rf_license_uses = $footagesearch_cart->get_clip_rf_license_uses($clip['id']);
                    $first_rf_license = reset($rf_license_uses);
                    $clip['license_term'] = $first_rf_license['id'];
                    $clip['price'] = $first_rf_license['price'];
                    $clip['license_term'] = $_SESSION['footagesearch_cart_license_rf'];
                    $license_price = false;
                    $license_discount = false;
                    $license_old_price = false;
                    $discount = false;

                    if(isset($clip['price'])){
                        $license_price = $clip['price'];
                        if(isset($clip['delivery_factor']) && $clip['delivery_factor'])
                            $clip['price'] = $license_price = $clip['price'] * $clip['delivery_factor'];
                        $discount = $footagesearch_cart->get_count_discount();
                        if($discount){
                            $license_old_price = number_format($clip['price'], 2);
                            $license_price = $clip['price'] - (($clip['price'] / 100) * $discount['discount']);
                            $license_discount = $discount['discount'];
                        }
                        $clip['license_price'] = number_format($license_price, 2);
                    }

                }else{
                    if(isset($_SESSION['footagesearch_cart_license_category']))
                        $clip['license_category'] = $_SESSION['footagesearch_cart_license_category'];
                    if(isset($_SESSION['footagesearch_cart_license_use']))
                        $clip['license_use'] = $_SESSION['footagesearch_cart_license_use'];
                    if(isset($_SESSION['footagesearch_cart_license_term']))
                        $clip['license_term'] = $_SESSION['footagesearch_cart_license_term'];

                    $min_duration = $footagesearch_cart->get_license_use_min_duration($clip['license_use']);
//                    if($clip['duration'] >= $min_duration)
//                        $clip['license_duration'] = $min_duration;
//                    else
//                        $clip['license_duration'] = (int)$clip['duration'];
                    $clip['license_duration'] = $min_duration;

                    $display_price = true;
                    if(!current_user_can('backend_administrator')) {
                        $selected_use = $footagesearch_cart->get_license_use($clip['license_use']);
                        if($selected_use && $selected_use['display'] == 0)
                            $display_price = false;
                    }
                    $clip['display_price']=$selected_use['display'];
                    if($display_price) {
                        //$price = $footagesearch_cart->get_clip_price($clip['id'], $clip['license_use'], $clip['license_term']);
                        $price = $footagesearch_cart->get_clip_price_by_format($clip['id'], $clip['license_use'], $clip['license_term'], $clip['delivery_format']);
                        if($price) {
                            $clip['price'] = $price['license_free_price'];
                            $clip['delivery_price'] = (isset($price['delivery_price']) && $price['delivery_price'] > 0) ? $price['delivery_price'] : 0;
                        }
                    }




                    $license_categories = $footagesearch_cart->get_license_categories();
                    $cart_license_category = $_SESSION['footagesearch_cart_license_category'];
                    $cart_license_use = $clip['license_use'] = $_SESSION['footagesearch_cart_license_use'];
                    $cart_license_term = $_SESSION['footagesearch_cart_license_term'];


                    $clip['license_price'] = false;
                    $clip['license_discount'] = false;
                    $clip['license_old_price'] = false;
                    $clip['total_price'] = false;
                    $discount = false;
                    if(isset($clip['price'])){
//                        if(isset($clip['delivery_factor']) && $clip['delivery_factor'])
//                            $clip['price'] = $clip['price'] * $clip['delivery_factor'];

                        if(!isset($clip['license_duration'])){
                            $clip['license_duration'] = (int)$clip['duration'];
                        }

                        if(isset($clip['license_duration'])){
//                            $discount = $footagesearch_cart->get_duration_discount($clip['license_duration']);
                            $clip['license_price'] = $clip['price'] * $clip['license_duration'];
//                            if($discount){
//                                $clip['license_old_price'] = number_format($clip['license_price'], 2);
//                                $clip['license_price'] = $clip['license_price'] - (($clip['license_price'] / 100) * $discount['discount']);
//                                $clip['license_discount'] = $discount['discount'];
//                            }
                            $clip['total_price'] = $clip['license_price'];
                            if(isset($clip['delivery_price']))
                                $clip['total_price'] = $clip['total_price'] + $clip['delivery_price'];

                            $clip['license_price'] = number_format($clip['license_price'], 2);
                            $clip['total_price'] = number_format($clip['total_price'], 2);
                        }
                    }


                }

            }

            //Get next/prev link
            $prev_clip_link = '';
            $next_clip_link = '';
            if(isset($shortcode_params['clip_offset'])){
                $res = $this->get_next_prev_clip($clip['id'], (int)$shortcode_params['clip_offset']);

                if($res){
                    $clip_holder = false;
                    if($this->settings['clip_holder']){

                        if(is_numeric($this->settings['clip_holder'])){
                            $clip_holder = get_page((int)$this->settings['clip_holder'], ARRAY_A);
                        }
                        else{
                            $clip_holder = get_page_by_path($this->settings['clip_holder'], ARRAY_A);
                        }
                        if($clip_holder){
                            if($res['prev_clip_code']){
                                $prev_clip_link = add_query_arg(array('position' => $shortcode_params['clip_offset'] - 1), get_permalink($clip_holder['ID']) . '/' . $res['prev_clip_code']);
                                if(isset($_REQUEST['modal']) && $_REQUEST['modal'])
                                    $prev_clip_link = add_query_arg('modal', 1, $prev_clip_link);

                            }
                            if($res['next_clip_code']){
                                $next_clip_link = add_query_arg(array('position' => $shortcode_params['clip_offset'] + 1), get_permalink($clip_holder['ID']) . '/' . $res['next_clip_code']);
                                if(isset($_REQUEST['modal']) && $_REQUEST['modal'])
                                    $next_clip_link = add_query_arg('modal', 1, $next_clip_link);
                            }
                        }
                    }
                }
            }
            $clips_holder = $this->get_clips_holder_page();
            $clips_holder_permalink = get_permalink($clips_holder['ID']);

            // Строка с логином пользователя, для ссылки скачивания превью
            $user = wp_get_current_user();
            if ( $user->user_login ) {
                $userstring = '/' . (string) $user->user_login;
            } else {
                $userstring = '';
            }

        }

        $discounts_content = $footagesearch_cart->discounts_content();

         $template = $this->fsec_get_template_file_path('footagesearch-clip.php');
	     //$template1 = $this->fsec_get_template_file_path('footagesearch-clips-list.php');
        if($template){
            ob_start();
            include_once($template);
            $output .= ob_get_contents();
            ob_end_clean();
        }

		/*if($template1){
            ob_start();
            include_once($template1);
            $output .= ob_get_contents();
            ob_end_clean();
        }*/

        $output .= '<div class="clear"></div>';
        return $output;
    }



    /*
     * Outputs categories list
     */
    private function display_categories_list($result){
        $html = '';
        if(count($result['data'])){
            $clips_holder = false;
            if($this->settings['clips_holder']){

                if(is_numeric($this->settings['clips_holder'])){
                    $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
                }
                else{
                    $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
                }

            }
            $html .= '<div class="footagesearch-categories-list">';
            $this->get_categories_level($result['data'], $html, $clips_holder);
            $html .= '<div class="clear"></div>';
            $html .= '</div>';
        }
        return $html;
    }

    private function get_categories_level($cats, &$html, $clips_holder){
        if($cats){
            $html .= '<ul>';
            $i = 0;
            foreach($cats as $cat){
                $i++;
                $is_last = '';
                if(($i % 4) == 0 && $i >= 4){
                    $is_last = ' last';
                }
                $html .= '<li class="footagesearch-category' . $is_last . '"><h2>';
                if($clips_holder){
                    $html .= '<a href="' . esc_url(add_query_arg('category', ($cat['uri'] ? $cat['uri'] : $cat['id']), get_permalink($clips_holder['ID']))) . '">' . $cat['title'] . '</a>';
                }
                $html .= '</h2>';
                if($cat['thumb']){
                    if($clips_holder){
                        $html .= '<a href="' . esc_url(add_query_arg('category', ($cat['uri'] ? $cat['uri'] : $cat['id']), get_permalink($clips_holder['ID']))) . '">
                                <img src="' . $cat['thumb'] . '" alt="' . $cat['title'] . '" width="231" height="112"></a>';
                    }
                    else{
                        $html .= '<img src="' . $cat['thumb'] . '" alt="' . $cat['title'] . '" width="231" height="112">';
                    }
                }
                $html .= '<div class="footagesearch-category-description">' . $cat['meta_desc'] . '&nbsp;</div>';
//                if($cat['child']){
//                    $this->get_categories_level($cat['child'], $html, $clips_holder);
//                }
                $html .= '</li>';
            }
            $html .= '</ul>';
        }
    }

    function get_footagesearch_search_form($echo = true){
        do_action( 'get_footagesearch_search_form' );
        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }
        if($clips_holder){
//            global $wp_query;
//            $current_page_id = $wp_query->post->ID;
//            if($current_page_id == $clips_holder['ID']){
//                $action = '';
//            }
//            else{
//                $action = get_permalink($clips_holder['ID']);
//            }
            $action = get_permalink($clips_holder['ID']);
        }
        else{
            $action = '';
        }

        $search_form_template = locate_template('footagesearch_search_form.php');
        if ( '' != $search_form_template ) {
            require($search_form_template);
            return;
        }

        $form = '<form rel="nofollow" role="search" method="get" id="footagesearch_searchform" action="' . esc_url($action) . '" class="footagesearch_searchform">
                    <div><label class="screen-reader-text" for="footagesearch_s">' . __('Search for:' , 'footagesearch') . '</label>
                    <input type="text" value="' . stripcslashes (esc_attr($_REQUEST['fs'])) . '" name="fs" id="footagesearch_s" class="footagesearch_s" />
                    <input type="submit" id="footagesearch_searchsubmit" value="'. esc_attr__('Search') .'" class="footagesearch_searchsubmit" />
                    </div>
                </form>';

        if ($echo)
            echo apply_filters('get_footagesearch_search_form', $form);
        else
            return apply_filters('get_footagesearch_search_form', $form);
    }

    function get_footagesearch_categories_widget(){
        $result = $this->api_request(array('method' => 'categories'));
        $html = '';
        if(count($result['data'])){
            $clips_holder = false;
            if($this->settings['clips_holder']){

                if(is_numeric($this->settings['clips_holder'])){
                    $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
                }
                else{
                    $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
                }

            }
            $html .= '<div class="footagesearch-widget-categories-list">';
            $html .= '<ul>';
            $i = 0;
            foreach($result['data'] as $cat){
                $i++;
                $is_last = '';
                if(($i % 3) == 0 && $i >= 3){
                    $is_last = ' last';
                }
                $html .= '<li class="footagesearch-wirget-category' . $is_last . '">';
                if($cat['thumb']){
                    if($clips_holder){
                        $html .= '<a href="' . esc_url(add_query_arg('category', ($cat['uri'] ? $cat['uri'] : $cat['id']), get_permalink($clips_holder['ID']))) . '">
                            <img src="' . $cat['thumb'] . '" alt="' . $cat['title'] . '" width="50" height="50"></a>';
                    }
                    else{
                        $html .= '<img src="' . $cat['thumb'] . '" alt="' . $cat['title'] . '" width="50" height="50">';
                    }
                }
                $html .= '<h2>';
                if($clips_holder){
                    $html .= '<a href="' . esc_url(add_query_arg('category', ($cat['uri'] ? $cat['uri'] : $cat['id']), get_permalink($clips_holder['ID']))) . '">' . $cat['title'] . '</a>';
                }
                $html .= '</h2>';
                $html .= '</li>';
                if(($i % 3) == 0 && $i >= 3){
                    $html .= '<div class="clear"></div>';
                }
            }
            $html .= '</ul>';
            $html .= '<div class="clear"></div>';
            $html .= '</div>';
        }
        return $html;
    }

    function get_clips_holder_page(){
        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }
        return  $clips_holder;
    }

    function get_clip_holder_page(){
        $clip_holder = false;
        if($this->settings['clip_holder']){
            if(is_numeric($this->settings['clip_holder'])){
                $clip_holder = get_page((int)$this->settings['clip_holder'], ARRAY_A);
            }
            else{
                $clip_holder = get_page_by_path($this->settings['clip_holder'], ARRAY_A);
            }
        }
        return  $clip_holder;
    }

    function get_available_filters(){
        if($this->available_filters)
            return $this->available_filters;
        $filters = '';
        $params = array('method' => 'search_filters');
        $result = $this->api_request($params);
        if($result && $result['data']){
            $filters = $result['data'];
            $this->available_filters = $filters;
        }
        return $filters;
    }
    function search_keywords_filter($filters){
        global $footage_search;
        if(!empty($footage_search->facet_keywords)){
            foreach($footage_search->facet_keywords as $section=>$keywordsArr){
                $filters[$section]['options']=array();
                foreach($keywordsArr as $keyword=>$v){
                    $word=array();
                    $word['value']= $v;//$keyword;
                    $word['label']= $v;//$keyword;
                    $word['section']= $section;
                    $filters[$section]['options'][]=$word;
                    //echo '<table><tr><td>'.$keyword.'  '.$v; echo '</td><td>'; var_dump($filters[$section]); echo '</td><td>'.'</td></tr></table>';
                }
            }
        }
        return $filters;
    }
    function get_search_filters(){

        function implode_param_values(&$item, $key, $glue = ','){
            if($glue == '=')
                $item = $key . '=' . $item;
            else
                $item = implode($glue, $item);
        }

        $base = '/';
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
            $base = get_permalink($clips_holder['ID']);
        }

        $filters = array();

        $filters = $this->get_available_filters();
        $filters = $this->search_keywords_filter($filters);;
        $selected_filters = $this->get_current_filters($filters);


        if($filters && is_array($filters)){
            foreach($filters as $filter_name => &$filter){
                if($filter['options'] && is_array($filter['options'])){
                    foreach($filter['options'] as &$option){
                        $selected = false;
                        if(isset($selected_filters[$filter_name]) && in_array($option['value'], $selected_filters[$filter_name])){
                            $option['selected'] = true;
                            $selected = true;
                            unset($filter['collapsed']);
                        }
                        $option_url_params = $selected_filters;
                        if($option_url_params && $selected){
                            $selected_option_id = array_search($option['value'], $option_url_params[$filter_name]);
                            unset($option_url_params[$filter_name][$selected_option_id]);
                            if(!$option_url_params[$filter_name])
                                unset($option_url_params[$filter_name]);
                        }
                        else{
                            $option_url_params[$filter_name][] = $option['value'];
                        }
                        if($option_url_params){
                            array_walk($option_url_params, 'implode_param_values');
                            array_walk($option_url_params, 'implode_param_values', '=');
                        }
                        $option_url_params_str = implode('&', $option_url_params);
                        $option['url'] = $base;
                        if($option_url_params_str)
                            $option['url'] .= '?' . $option_url_params_str;
                    }
                }
                elseif(isset($selected_filters[$filter_name]) && is_array($selected_filters[$filter_name])){
                    $filter['value'] = $selected_filters[$filter_name][0];
                }
            }
        }

//        echo '<pre>';
//        print_r($filters);
//        exit();

        return $filters;

    }

    function get_available_sort_options(){
        $options = array(
            'code' => array(
                'label' => 'Clip Code',
                'display' => 1,
                'direction' => 'asc'
            ),
            'duration' => array(
                'label' => 'Duration',
                'display' => 1,
                'direction' => 'asc'
            ),
            'price_level' => array(
                'label' => 'Price Level',
                'display' => 1,
                'direction' => 'asc'
            ),
            'like_count' => array(
                'display' => 0,
                'direction' => 'desc'
            ),
            'weight' => array(
                'label' => 'Weight',
                'display' => 0,
                'direction' => 'desc'
            ),
            'format_sort' => array(
                'label' => 'Format sort',
                'display' => 0,
                'direction' => 'asc'
            )
        );
        return $options;
    }

    function get_sort_options(){

        $available_options = $this->get_available_sort_options();
        $parts = parse_url($_SERVER['REQUEST_URI']);
        $selected_option = isset($_GET['sort']) ? $_GET['sort'] : '';
        $params = $_GET;
        if(isset($params['sort']))
            unset($params['sort']);
        $sort_options = array();
        $sort_options[] = array(
            'name' => '',
            'label' => 'Relevance',
            'link' => $parts['path'] . '?' . http_build_query($params)
        );
        if($available_options && is_array($available_options)){
            foreach($available_options as $option_name => $option){
                if($option['display']){
                    $selected = false;
                    if($option_name == $selected_option){
                        $option['selected'] = true;
                        $selected = true;
                    }
                    $params = $_GET;
                    $params['sort'] = $option_name;
                    $option['link'] = $parts['path'] . '?' . http_build_query($params);
                    $option['name'] = $option_name;
                    $sort_options[] = $option;
                }
            }
        }
        return $sort_options;
    }

    function get_top_keywords($limit = 20){
        $keywords = array();
        $sizes = array(10, 11, 12, 13, 14);
        $clips_holder = false;
        if($this->settings['clips_holder']){
            if(is_numeric($this->settings['clips_holder'])){
                $clips_holder = get_page((int)$this->settings['clips_holder'], ARRAY_A);
            }
            else{
                $clips_holder = get_page_by_path($this->settings['clips_holder'], ARRAY_A);
            }
        }

        $params = array('method' => 'top_keywords');
        $params['post_params']['limit'] = $limit;
        $result = $this->api_request($params);
        if($result && $result['data']){
            $keywords = $result['data'];
            $max = $keywords[0]['times'];
            foreach($keywords as &$keyword) {
                $coef = $keyword['times'] / $max;
                if($coef > 0.8) $weight = 4;
                elseif ($coef < 0.8 && $coef >= 0.6) $weight = 3;
                elseif ($coef < 0.6 && $coef >= 0.4) $weight = 2;
                elseif ($coef < 0.4 && $coef >= 0.2) $weight = 1;
                else $weight = 0;
                $keyword['size'] = $sizes[$weight];
                if($clips_holder)
                    $keyword['link'] = add_query_arg('fs', urlencode($keyword['phrase']), get_permalink($clips_holder['ID']));
            }
        }
        return $keywords;
    }

    function get_current_filters($filters = array()){
        $selected_filters = array();
        foreach($_REQUEST as $param => $value){
            if($filters && !array_key_exists($param, $filters)){
                continue;
            }
            if(is_array($value))
                $selected_filters[$param] = $value;
            elseif($value){
                $value_arr = explode(',', $value);
                $selected_filters[$param] = $value_arr;
            }
        }

        //Add license param from wp_query
        global $wp_query;
        if(isset($wp_query->query_vars['license_name']) && $wp_query->query_vars['license_name']
            && isset($this->licenses_names_map[$wp_query->query_vars['license_name']])){

            $license_id = $this->licenses_names_map[$wp_query->query_vars['license_name']];
            if(isset($selected_filters['license'])){
                if(!in_array($license_id, $selected_filters['license'])){
                    $selected_filters['license'][] = $license_id;
                }
            }
            else{
                $selected_filters['license'][] = $license_id;
            }
        }

        //Add price level from wp_query
        if(isset($wp_query->query_vars['price_level']) && !is_array($wp_query->query_vars['price_level']) && $wp_query->query_vars['price_level']
            && isset($this->prices_levels_map[$wp_query->query_vars['price_level']])){

            $price_level_id = $this->prices_levels_map[$wp_query->query_vars['price_level']];
            if(isset($selected_filters['license'])){
                if(!in_array($price_level_id, $selected_filters['price_level'])){
                    $selected_filters['price_level'][] = $price_level_id;
                }
            }
            else{
                $selected_filters['price_level'][] = $price_level_id;
            }
        }

        //Add format category from wp_query
        if(isset($wp_query->query_vars['format_category']) && !is_array($wp_query->query_vars['format_category']) && $wp_query->query_vars['format_category']
            && isset($this->format_category_map[$wp_query->query_vars['format_category']])){

            $format_category = $this->format_category_map[$wp_query->query_vars['format_category']];
            if(isset($selected_filters['format_category'])){
                if(!in_array($format_category, $selected_filters['format_category'])){
                    $selected_filters['format_category'][] = $format_category;
                }
            }
            else{
                $selected_filters['format_category'][] = $format_category;
            }
        }

        // Add collection from 'word' parameter of wp_query
        if(isset($wp_query->query_vars['words']) && $wp_query->query_vars['words']){
            $word = $wp_query->query_vars['words'];
            $word_parts = explode('-', $word);
            $collection = end($word_parts);
            if (in_array($collection, $this->collections_suffixes)) {
                if(isset($selected_filters['collection'])){
                    if(!in_array($collection, $selected_filters['collection'])){
                        $selected_filters['collection'][] = $collection;
                    }
                }
                else{
                    $selected_filters['collection'][] = $collection;
                }
            }
        }

        return $selected_filters;
    }

    function update_views_statistic($user_id=false){
        $views_count = 0;
        $params = array('method' => 'update_provider_views');
        $params['post_params']['provider'] = (!$user_id) ? 1 : $user_id;
        $params['post_params']['remote_addr'] = $_SERVER['REMOTE_ADDR'];
//        $current_user = wp_get_current_user();
//        if(0 != $current_user->ID){
//
//        }
        return $result = $this->api_request($params);
        if($result && $result['data']){
            $views_count = $result['data'];
        }
        return $views_count;
    }

    function add_follower($login){
        $follower_count = 0;
        $params = array('method' => 'add_provider_follower');
        $params['post_params']['user'] = $login;
        $result = $this->api_request($params);
        if($result && $result['data']){
            $follower_count = $result['data'];
        }
        if(isset($_GET['ajax']) && $_GET['ajax'] == 'true'){
            $data['success'] = true;
            $data['followers_count'] = $follower_count;
            $response = json_encode($data);
            header("Content-Type: application/json");
            echo $response;
            exit();
        }
        else
            return $follower_count;
    }

    function get_provider_statistic($user_id=false){
        $statistic = array();
        $user_id= (!$user_id) ? 1 : $user_id;
        $params = array('method' => 'get_provider_statistic');
        $params['post_params']['provider']= $user_id;
        $result = $this->api_request($params);

        if($result && $result['data']){
            $statistic = $result['data'];
        }
        return $statistic;
    }

    function get_next_prev_clip($clip_id, $clip_offset){
        $request_params['method'] = 'next_prev_clip';
        if(isset($_SESSION['search_filter']) && $_SESSION['search_filter'])
            $request_params['post_params'] = $_SESSION['search_filter'];
        if(isset($_SESSION['search_sort']) && $_SESSION['search_sort'])
            $request_params['post_params']['sort'] = $_SESSION['search_sort'];
        $request_params['post_params']['clip_offset'] = $clip_offset;
        $request_params['post_params']['clip_id'] = $clip_id;
        $result = $this->api_request($request_params);
        return ($result && $result['data']) ? $result['data'] : false;
    }

    function fsec_get_template_file_path($file){
        if(file_exists(get_template_directory() . '/' . $file)){
            return $file_path = get_template_directory() . '/' . $file;
        }

        elseif(file_exists(dirname(__FILE__) . '/templates/' . $file)){
            return $file_path = dirname(__FILE__) . '/templates/' . $file;
        }
        return false;
    }

    function shared_page($posts){
        global $wp;
        global $wp_query;

        global $shared_page_detect;

        if(!get_page_by_path($wp->request)){
            $shared_page_url = strtolower($wp->request);
            $shared_page = false;
            $params = array(
                'method' => 'shared_page',
                'post_params' => array('url' => $shared_page_url)
            );
            $result = $this->api_request($params);
            if($result['status'] !== false && $result['data']){
                $shared_page = $result['data'];
            }

            if(!$shared_page)
                return $posts;
        }
        else
            return $posts;

        if (!$shared_page_detect && !empty($shared_page['title'])) {
            $shared_page_detect = true;

            $post = new stdClass;
            $post->post_author = 1;
            $post->post_name = $shared_page_url;
            $post->guid = get_bloginfo('wpurl') . '/' . $shared_page_url;
            $post->post_title = $shared_page['title'];
            $post->shared_page = true;
            $post->post_content = str_replace('src="/data/upload/content', 'src="' . get_option('backend_url') . '/data/upload/content', $shared_page['body']);
            $post->ID = -999;
            $post->post_type = 'page';
            $post->post_status = 'static';
            $post->comment_status = 'closed';
            $post->ping_status = 'open';
            $post->comment_count = 0;
            $post->post_date = current_time('mysql');
            $post->post_date_gmt = current_time('mysql', 1);

            update_post_meta($post->ID, '_aioseop_description', $shared_page['meta_description']);
            update_post_meta($post->ID, '_aioseop_keywords', $shared_page['meta_keywords']);

            $posts = NULL;
            $posts[] = $post;
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            unset($wp_query->query["error"]);
            $wp_query->query_vars["error"] = "";
            $wp_query->is_404 = false;
        }
        return $posts;
    }

    function get_shared_pages($type = ''){
        $params = array(
            'method' => 'shared_pages',
            'post_params' => array('type' => $type)
        );
        $result = $this->api_request($params);
        $pages = array();
        if($result['status'] !== false && $result['data']){
            $pages = $result['data'];
        }
        return $pages;
    }

    function get_shared_pages_types(){
        $params = array('method' => 'shared_pages_types');
        $result = $this->api_request($params);
        $types = array();
        if($result['status'] !== false && $result['data']){
            $types = $result['data'];
        }
        return $types;
    }

    function get_user_storage_account($login){
        $account = false;
        $params = array(
            'method' => 'user_storage_account',
            'post_params' => array('user' => $login)
        );
        $result = $this->api_request($params);
        if($result['status'] !== false && $result['data']){
            $account = $result['data'];
        }
        return $account;
    }

    function get_clip_code_by_id($clip_id) {
        $params = array(
            'method' => 'clip_code_by_id',
            'post_params' => array('id' => $clip_id)
        );
        $result = $this->api_request($params);
        if($result['status'] !== false && $result['data']){
            return $result['data'];
        }
        else {
            return false;
        }
    }

    function get_clip_id_by_code($clip_code) {
        $params = array(
            'method' => 'clip_id_by_code',
            'post_params' => array('code' => $clip_code)
        );
        $result = $this->api_request($params);
        if($result['status'] !== false && $result['data']){
            return $result['data'];
        }
        else {
            return false;
        }
    }

    function get_clip_thumb($clip_id) {
        $params = array(
            'method' => 'clip_thumb',
            'post_params' => array('id' => $clip_id)
        );
        $result = $this->api_request($params);
        if($result['status'] !== false && $result['data']){
            return $result['data'];
        }
        else {
            return false;
        }
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
    function get_guest_id(){
        $current_user = wp_get_current_user();
        if(!$current_user->user_login){
            if(empty($_COOKIE['guest_id'])){
                $guest_id=md5(uniqid(time()+rand(1,999),1));
                setcookie('guest_id',$guest_id,time()+(60*60*48)); // 2 days live limit
            }
        }else{
            unset($_COOKIE['guest_id']);
        }
        return $_COOKIE['guest_id'];
    }

    function download_prewiew_gost(){
        $params = array(
            'method' => 'download_preview',
            'post_params' => array('ip' => $this->get_client_ip(),'clip_id'=>$_REQUEST['clip_id'],'referer'=>$_REQUEST['referer'])
        );
        $result = $this->api_request($params);
        header( "Content-Type: application/json" );
        echo json_encode(array('content'=>stripcslashes(trim($result['data'])),'limitSuccess'=>$result['limit'])); exit();
    }

    /*
     * Api functions
     */
    function api_request_params($shortcode_params){
        $request_params = array();
        $post_params = array();
        $user = wp_get_current_user();

        if(is_array($shortcode_params)){
            $query_params = array();
            if(isset($shortcode_params['categories'])){
                $request_params['method'] = 'categories';
                if(isset($shortcode_params['category']) && $shortcode_params['category']){
                    $query_params['category'] = $shortcode_params['category'];
                }
            }
            elseif(isset($shortcode_params['clip']) && $shortcode_params['clip']){
                $request_params['method'] = 'clip';
                $query_params['id'] = $shortcode_params['clip'];
                // Добавляем данные логгирования
                $post_params[ 'user_login' ] = ( $user->ID != 0 ) ? $user->user_login : '';
            }
            else{
                $request_params['method'] = 'clips';
                if(isset($shortcode_params['category']) && $shortcode_params['category']){
                    $query_params['category'] = $shortcode_params['category'];
                }
                if(isset($shortcode_params['words']) && $shortcode_params['words']){
                    $query_params['words'] = $shortcode_params['words'];
                }

                // Apply filters
                $available_filters = $this->get_available_filters();
                $current_filters = $this->get_current_filters($available_filters);
                $post_params = $current_filters;

                // brand
                if(!empty($shortcode_params['brand'])) $post_params['brand']=$shortcode_params['brand'];
                // license
                if(!empty($shortcode_params['license'])) $post_params['license']=$shortcode_params['license'];
                //Save current filter to session for next/prev navigation on clip page
                unset($_SESSION['search_filter']);
                $_SESSION['search_filter'] = $current_filters;
                $_SESSION['search_filter']['words'] = $shortcode_params['words'];

                if(isset($shortcode_params['words']) && $shortcode_params['words']){
                    // Добавляем данные логгирования
                    $post_params[ 'user_login' ] = ( $user->ID != 0 ) ? $user->user_login : '';
                }
                if(isset($shortcode_params['owner'])){
                    // Добавляем владельца клипов
                    $post_params[ 'owner' ] = $shortcode_params['owner'];
                }

            }
            //Limit
            if(isset($shortcode_params['perpage']) && $shortcode_params['perpage']){
                $perpage = $shortcode_params['perpage'];
                if(isset($shortcode_params['page']) && $shortcode_params['page']){
                    $page = $shortcode_params['page'] - 1;
                }
                else{
                    $page = 0;
                }
                $from = $page * $perpage;
                if(isset($shortcode_params['limit']) && $shortcode_params['limit']){
                    if($from < $shortcode_params['limit']){
                        if(($from + $perpage) <= $shortcode_params['limit']){
                            $query_params['limit'] = $perpage;
                            $query_params['from'] = $from;
                        }
                        else{
                            $perpage = $shortcode_params['limit'] - $from;
                            $query_params['limit'] = $perpage;
                            $query_params['from'] = $from;
                        }
                    }
                    else{
                        $query_params['limit'] = false;
                    }
                }
                else{
                    $query_params['limit'] = $perpage;
                    $query_params['from'] = $from;
                }

            }
            elseif(isset($shortcode_params['limit']) && $shortcode_params['limit']){
                $query_params['limit'] = $shortcode_params['limit'];
            }

            $available_sort_options = $this->get_available_sort_options();
            $post_params['sort'] = array();
            if(isset($shortcode_params['sort']) && $shortcode_params['sort'] && array_key_exists($shortcode_params['sort'], $available_sort_options)){
                $post_params['sort'][] = $shortcode_params['sort']  . ' ' . $available_sort_options[$shortcode_params['sort']]['direction'];
            }
            if(isset($_REQUEST['sort']) && $_REQUEST['sort'] && array_key_exists($_REQUEST['sort'], $available_sort_options)){
                $post_params['sort'][] = $_REQUEST['sort']  . ' ' . $available_sort_options[$_REQUEST['sort']]['direction'];
            }

            //Format sort
            $post_params['sort'][] = 'format_sort asc';
            //Rank clips sort
            $post_params['sort'][] = 'weight desc';

            //Save current sort to session for next/prev navigation on clip page
            unset($_SESSION['search_sort']);
            if($post_params['sort'])
                $_SESSION['search_sort'] = $post_params['sort'];

            $request_params['query_params'] = $query_params;
            $request_params['post_params'] = $post_params;
        }

        return $request_params;
    }

    function addslashes_extended($arr_r){
        if(is_array($arr_r)){
            foreach ($arr_r as $key => $val){
                is_array($val) ? addslashes_extended($val):$arr_r[$key]=addslashes(preg_replace('/\\\\/','',$val));
            }
            unset($val);
        }else if(is_object($arr_r)){
            $objectProperties = get_object_vars($arr_r);
            foreach($objectProperties as $key => $value){
                is_object($value) ? addslashes_extended($value):$arr_r->{$key}=addslashes(preg_replace('/\\\\/','',$value));
            }
        }
        return $arr_r;
    }

    function api_request($params){
        $backend_url = get_option('backend_url');
        $provider_id = get_option('provider_id');
        $frontend_id = get_option('frontend_id');
        /*echo '<pre>';
        var_export( $params );
        echo '</pre>';*/

        if(!empty($params) && isset($params['method']) && $params['method'] && $backend_url && $provider_id && $frontend_id){
            $lang = 'en';
            $apiurl = trim($backend_url, '/') . '/' . $lang . '/fapi/' . $params['method'] . '/provider/' . $provider_id . '/frontend/' . $frontend_id;
			// (!) do not add any output here, it brokes ajax requests
            if(!empty($params['query_params'])){
                //$params['query_params']=$this->addslashes_extended($params['query_params']);
                if(isset($params['query_params']['limit']) && $params['query_params']['limit'] === false){
                    return false;
                }
                $query_params = array();
                foreach($params['query_params'] as $param => $value){
                    $query_params[] = $param . '/' . @urlencode($value);
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
            curl_setopt($ch, CURLOPT_TIMEOUT, 90);
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
            /*echo '<!--METHOD_API: '.$params['method'].' <pre>URL:';
            var_dump($apiurl);
            echo 'POST:';
            var_dump($post_params);
            echo 'RESULT:';
            print_r(json_decode($result));
            echo '</pre><hr>';
            echo $http_status.'-->';
            /*if($params['method'] == 'clips'){
                echo 'METHOD_API:<pre>URL:';
                var_dump($apiurl);
                echo 'POST:';
                var_dump($post_params);
                echo 'RESULT:';
                echo(json_decode($result)->data);
                print_r(json_decode($result)->time);
                echo '</pre><hr>';
                echo $http_status.'>';
                //exit();
            }*/
            return $http_status == 200 ? json_decode($result, true) : false;
        }
        else{
            return false;
        }
    }
}


/*
 * Footage Search widget
 * Outputs search form
 */
class FootageSearchWidget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'footagesearch_widget', // Base ID
            'Footage Search',
            array(
                'description' => __('A form for clips searching', 'footagesearch')
            )
        );
    }

    public function form($instance) {
        if (isset($instance['title'])){
            $title = $instance['title'];
        }
        else {
            $title = __( 'Footage Search', 'footagesearch' );
        }
        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    public function widget($args, $instance) {
        global $footage_search;
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);



        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;

        echo $footage_search->get_footagesearch_search_form(false);
        echo $after_widget;
    }

}
add_action('widgets_init', create_function('', 'return register_widget("FootageSearchWidget");') );


class FootageSearchCategoriesWidget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'footagesearch_categories_widget', // Base ID
            'Footage Search Categories',
            array(
                'description' => __('Categories list', 'footagesearch')
            )
        );
    }

    public function form($instance) {
        if (isset($instance['title'])){
            $title = $instance['title'];
        }
        else {
            $title = __( 'Footage Search Categories', 'footagesearch' );
        }
        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    public function widget($args, $instance) {
        global $footage_search;
        /*global $wp_query;
        $post = $wp_query->get_queried_object();
        $pagename = $post->post_name;
        if($pagename == 'stockfootage'){
            return false;
        }*/
        extract($args);
        $title = apply_filters('widget_title', $instance['title']);

        $args = array(
            'post_type' => 'browse_page',
            'posts_per_page' => -1,
//            'meta_key' => 'browse_page_show_in_widget',
//            'meta_value' => 'yes',
            'meta_key' => 'browse_page_sort',
            'orderby' => 'meta_value_num',
            'order' => 'ASC',
            'meta_query' => array(
                array(
                    'key' => 'browse_page_show_in_widget',
                    'value' => 'yes',
                    'compare' => '=',
                )
            )

        );
        $browse_pages = new WP_Query($args);

        echo $before_widget;
        if ( ! empty( $title ) )
            echo $before_title . $title . $after_title;

        $i = 0;
        echo '<div class="footagesearch-widget-categories-list">';
        echo '<ul>';
        while ($browse_pages->have_posts()) : $browse_pages->the_post();
            $i++;
            $is_last = '';
            if(($i % 3) == 0 && $i >= 3){
                $is_last = ' last';
            }
            $thumb = get_post_meta(get_the_ID(), 'browse_page_thumbnail_url', true);
            echo '<li class="footagesearch-wirget-category' . $is_last . '">';
            echo '<a href="';
            the_permalink();
            echo '">';
            echo '<img src="' . $thumb . '" alt="' . get_the_title() . '" width="50" height="50">';
            echo '</a>';
            if(($i % 3) == 0 && $i >= 3){
                echo '<div class="clear"></div>';
            }
            echo '</li>';
        endwhile;
        echo '</ul>';
        echo '<div class="clear"></div>';
        echo '</div>';

        wp_reset_postdata();
        //echo $footage_search->get_footagesearch_categories_widget(false);
        echo $after_widget;
    }

}
add_action('widgets_init', create_function('', 'return register_widget("FootageSearchCategoriesWidget");') );


class FootageSearchFilterWidget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'footagesearch_filter_widget', // Base ID
            'Footage Search Filter', // Name
            array(
                'description' => __('Footage Search Filter', 'footagesearch') // Args
            )
        );
    }

    public function form($instance) {
        if (isset($instance['title'])){
            $title = $instance['title'];
        }
        else {
            $title = __('Footage Search Filter', 'footagesearch' );
        }
        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    public function widget($args, $instance) {
        wp_enqueue_script(
            'footagesearch-search-filters-script',
            plugins_url('footagesearch/js/search_filters.js'),
            array('jquery')
        );
        global $footage_search;
        $is_clips_holder = false;
        $clips_holder = $footage_search->get_clips_holder_page();
        if($clips_holder)
            $is_clips_holder = is_page($clips_holder['ID']);

        if($is_clips_holder){
            $search_action = $_SERVER['REQUEST_URI'];//get_permalink($clips_holder['ID']);
            extract( $args );
            $title = apply_filters( 'widget_title', $instance['title'] );
            $filters = $footage_search->get_search_filters();
            $template = $footage_search->fsec_get_template_file_path('footagesearch-filter.php');
            $output = '';
            if($template){
                ob_start();
                include_once($template);
                $output = ob_get_contents();
                ob_end_clean();
            }

            echo $before_widget;
            if ( ! empty( $title ) )
                echo $before_title . $title . $after_title;
            echo $output;
            echo $after_widget;
        }
    }

}
add_action('widgets_init', create_function('', 'return register_widget("FootageSearchFilterWidget");') );

class FootageSearchTopKeywordsWidget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'footagesearch_top_keywords_widget', // Base ID
            'Footage Top Keywords', // Name
            array(
                'description' => __('Footage Top Keywords', 'footagesearch') // Args
            )
        );
    }

    public function form($instance) {
        if (isset($instance['title'])){
            $title = $instance['title'];
        }
        else {
            $title = __('Footage Top Keywords', 'footagesearch' );
        }
        ?>
    <p>
        <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <p>
        <label for="<?php echo $this->get_field_id('top_keywords_limit'); ?>"><?php _e('Keywords limit:'); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id('top_keywords_limit'); ?>" name="<?php echo $this->get_field_name('top_keywords_limit'); ?>" type="text" value="<?php echo $instance['top_keywords_limit']; ?>" />
    </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['top_keywords_limit'] = strip_tags($new_instance['top_keywords_limit']);
        return $instance;
    }

    public function widget($args, $instance) {
        global $footage_search;
        $is_profile_page = false;
        $profile_page_id = $footage_search->settings['provider_profile_page'];
        if($profile_page_id)
            $is_profile_page = is_page($profile_page_id);

        if($is_profile_page){
            extract($args);
            $title = apply_filters( 'widget_title', $instance['title'] );
            $limit = $instance['top_keywords_limit'] ? $instance['top_keywords_limit'] : 20;
            $keywords = $footage_search->get_top_keywords($limit);
            if($keywords){
                $template = $footage_search->fsec_get_template_file_path('footagesearch-top-keywords.php');
                $output = '';
                if($template){
                    ob_start();
                    include_once($template);
                    $output = ob_get_contents();
                    ob_end_clean();
                }
                echo $before_widget;
                if ( ! empty( $title ) )
                    echo $before_title . $title . $after_title;
                echo $output;
                echo $after_widget;
            }
        }
    }

}
add_action('widgets_init', create_function('', 'return register_widget("FootageSearchTopKeywordsWidget");'));


class FootageSearchSharedPagesWidget extends WP_Widget
{
    public function __construct() {
        parent::__construct(
            'footagesearch_shared_pages_widget',
            'Shared Pages',
            array(
                'description' => __('Shared Pages', 'footagesearch') // Args
            )
        );
    }

    public function form($instance) {
        global $footage_search;
        $types = $footage_search->get_shared_pages_types();

        if (isset($instance['title'])){
            $title = $instance['title'];
        }
        else {
            $title = __('Shared Pages', 'footagesearch' );
        }
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e( 'Title:' ); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
        </p>
        <?php if($types) { ?>
        <p>
            <label for="pages_type">Pages type</label><br>
            <select name="<?php echo $this->get_field_name('pages_type'); ?>" id="<?php echo $this->get_field_id('pages_type'); ?>">
                <option value="">All</option>
                <?php foreach($types as $type) { ?>
                    <option value="<?php echo $type; ?>"<?php if($instance['pages_type'] == $type) { echo ' selected'; } ?>><?php echo $type; ?></option>
                <?php } ?>
            </select>
        </p>
        <?php } ?>

        <p>
            <label for="show_on_page">Show on page</label><br>
            <?php wp_dropdown_pages( array( 'name' => $this->get_field_name('show_on_page'), 'echo' => 1, 'show_option_none' => __( '&mdash; All pages &mdash;' ), 'option_none_value' => '0', 'selected' => $instance['show_on_page'] ) ) ?>
        </p>
    <?php
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['pages_type'] = strip_tags($new_instance['pages_type']);
        $instance['show_on_page'] = (int)$new_instance['show_on_page'];
        return $instance;
    }

    public function widget($args, $instance) {
        $show = true;
        if($instance['show_on_page'] && !is_page($instance['show_on_page']))
            $show = false;

        if($show){
//            global $footage_search;
//            extract($args);
//            $title = apply_filters('widget_title', $instance['title']);
//            $pages = $footage_search->get_shared_pages($instance['pages_type']);
//            if($pages){
//                $template = $footage_search->fsec_get_template_file_path('footagesearch-shared-pages.php');
//                $output = '';
//                if($template){
//                    ob_start();
//                    include($template);
//                    $output = ob_get_contents();
//                    ob_end_clean();
//                }
//                echo $before_widget;
//                if ( ! empty( $title ) )
//                    echo $before_title . $title . $after_title;
//                echo $output;
//                echo $after_widget;
//            }
        }
    }

}

add_action('widgets_init', create_function('', 'return register_widget("FootageSearchSharedPagesWidget");'));
function get_current_user_role() {
	global $wp_roles;
	$current_user = wp_get_current_user();
	$roles = $current_user->roles;
	$role = array_shift($roles);
	return isset($wp_roles->role_names[$role]) ? translate_user_role($wp_roles->role_names[$role] ) : false;
}