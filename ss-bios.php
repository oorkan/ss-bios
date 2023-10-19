<?php
/*
Plugin Name: SS Bios
Plugin URI: placeholder
Description: Create, read, update and delete bios
Version: 1.0.0
Author: oorkan
Author URI: https://oorkan.dev
License:
Text Domain: ss-bios
*/

defined( 'ABSPATH' ) || exit;

/**
 * This class contains all of the logic for the plugin.
 * All of the plugin's hooks are set up when an instance of the class is constructed.
 * Note: an instance of this class is created at the bottom of this file.
 * @todo This code could be cleaned up more
 */
class SSBio
{
    /**
     * Enables the plugin.
     * The plugin's hooks are automatically set up.
     * @uses init
     */
    public function __construct()
    {
        $this->init();
    }

    private $post_type = [
        'slug' => 'ss-bio',
        'singular' => 'Bio',
        'plural' => 'Bios'
    ];
    private $taxonomies = [
        [
            'slug' => 'ss-bio-category',
            'singular' => 'Category',
            'plural' => 'Categories'
        ]
    ];
    public $textdomain = 'ss-bios'; // Should match plugin's Text Domain

    private $admin_ui_opts = [
        'menu_icon'     => 'dashicons-businessman',
        'menu_position' => 6,
        'description'   => 'Create, read, update and delete bios',
    ];

    /**
     * Sets up all of the plugin's hooks.
     * This is called automatically by the constructor.
     */
    public function init()
    {
        add_action('init', array($this, 'ss_post_type_init'));
        add_action('init', array($this, 'ss_post_type_taxonomies'));
        add_action('wp_insert_post', array($this, 'ss_post_type_custom_fields'));
        add_shortcode($this->post_type['slug'], array($this, 'ss_post_type_shortcode'));
        add_filter('manage_'. $this->post_type['slug'] .'_posts_columns', array($this, 'ss_post_type_columns'), 10, 3);
        add_filter('manage_'. $this->post_type['slug'] .'_posts_custom_column', array($this, 'ss_post_type_custom_column'), 10, 3);
        add_filter('posts_orderby', array($this, 'ss_edit_posts_orderby'), 10, 3);
        add_action('init', array($this, 'ss_register_gutenberg_block'));
        wp_register_style('ss-bios', plugins_url('ss-bios.css', __FILE__));
    }

    /**
     * Filter hook for altering the ORDER BY clause of a query.
     * This allows bio posts to be sorted by menu order.
     * @param string $orderby The ORDER BY clause of the query
     */
    public function ss_edit_posts_orderby($orderby)
    {
        if (is_admin()) {
            if (isset($_GET['post_type']) && $_GET['post_type'] === $this->post_type['slug']) {
                if (isset($_GET['orderby']) && ($_GET['orderby'] === 'menu_order')) {
                    if (isset($_GET['order']) && ($_GET['order'] === 'asc' || $_GET['order'] === 'desc')) {
                        $orderby = 'menu_order ' . $_GET['order'];
                    }
                }
            }
        }

        return $orderby;
    }

    /**
     * Filter hook for drawing the columns displayed in the Posts list table.
     * This adds a column that allows bio posts to be sorted by menu order.
     * @param string[] $columns An associative array of column headings
     * @return string[] Updated column headings array
     */
    public function ss_post_type_columns($columns)
    {
        $order = 'desc';

        if (isset($_GET['orderby']) && ($_GET['orderby'] === 'menu_order')) {
            if (isset($_GET['order']) && $_GET['order'] === 'desc') {
                $order = 'asc';
            }
        }

        $html = "<a href='?post_type=". $this->post_type['slug'] ."&orderby=menu_order&order=". $order ."'>
            <span>Order</span>
            <span class='sorting-indicator'></span>
        </a>";

        $columns = $columns + [ 'order' => $html ];

        return $columns;
    }

    /**
     * Filter hook for drawing custom columns in the Posts list table.
     * This shows a bio post's menu order value.
     * @param string $column The name of the column
     * @param int $post_id The current post ID
     */
    public function ss_post_type_custom_column($column, $post_id)
    {
        if ($column == 'order') {
            $order = get_post_field('menu_order', $post_id);
            echo $order ? (string)$order : '0';
        }

        return $column;
    }

    /**
     * Registers a new post type
     * @uses $wp_post_types Inserts new post type object into the list
     *
     * @param string  Post type key, must not exceed 20 characters
     * @param array|string  See optional args description above.
     * @return object|WP_Error the registered post type object, or an error object
     */
    public function ss_post_type_init()
    {
        $labels = array(
            'name'               => __($this->post_type['plural'], $this->textdomain),
            'singular_name'      => __($this->post_type['singular'], $this->textdomain),
            'add_new'            => _x('Add New '. $this->post_type['singular'], $this->textdomain, $this->textdomain),
            'add_new_item'       => __('Add New '. $this->post_type['singular'], $this->textdomain),
            'edit_item'          => __('Edit '. $this->post_type['singular'], $this->textdomain),
            'new_item'           => __('New '. $this->post_type['singular'], $this->textdomain),
            'view_item'          => __('View '. $this->post_type['singular'], $this->textdomain),
            'search_items'       => __('Search '. $this->post_type['plural'], $this->textdomain),
            'not_found'          => __('No '. $this->post_type['plural'] .' found', $this->textdomain),
            'not_found_in_trash' => __('No '. $this->post_type['plural'] .' found in Trash', $this->textdomain),
            'parent_item_colon'  => __('Parent '. $this->post_type['singular'] .':', $this->textdomain),
            'menu_name'          => __($this->post_type['plural'], $this->textdomain),
        );

        $args = array(
            'labels'              => $labels,
            'hierarchical'        => false,
            'description'         => $this->admin_ui_opts['description'],
            'public'              => false,
            'show_ui'             => true,
            'show_in_menu'        => true,
            'show_in_admin_bar'   => true,
            'menu_position'       => $this->admin_ui_opts['menu_position'],
            'menu_icon'           => $this->admin_ui_opts['menu_icon'],
            'show_in_nav_menus'   => false,
            'publicly_queryable'  => false,
            'exclude_from_search' => true,
            'has_archive'         => false,
            'can_export'          => true,
            'rewrite'             => false,
            'capability_type'     => [
                str_replace(' ', '_', strtolower($this->post_type['singular'])),
                str_replace(' ', '_', strtolower($this->post_type['plural'])),
            ],
            'map_meta_cap'        => true,
            'show_in_rest'        => true,
            'supports'            => array(
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'custom-fields',
                'revisions',
                'page-attributes',
            ),
        );

        if (!post_type_exists($this->post_type['slug'])) {
            register_post_type($this->post_type['slug'], $args);
            return true;
        }

        return false;
    }

    /**
     * Creates default custom fields for custom post type.
     * @param int $post_id Post ID
     * @return true
     */
    public function ss_post_type_custom_fields($post_id)
    {
        if ($_GET['post_type'] === $this->post_type['slug']) {
            add_post_meta($post_id, 'Title', '', true);
        }

        return true;
    }

    /**
     * Create a taxonomy
     *
     * @uses  Inserts new taxonomy object into the list
     * @uses  Adds query vars
     *
     * @param string  Name of taxonomy object
     * @param array|string  Name of the object type for the taxonomy object.
     * @param array|string  Taxonomy arguments
     * @return null|WP_Error WP_Error if errors, otherwise null.
     */
    public function ss_post_type_taxonomies()
    {
        foreach ($this->taxonomies as $taxonomy) {
            $labels = array(
                'name'                  => _x($taxonomy['plural'], 'Taxonomy plural name', $this->textdomain),
                'singular_name'         => _x($taxonomy['singular'], 'Taxonomy singular name', $this->textdomain),
                'search_items'          => __('Search '. $taxonomy['plural'], $this->textdomain),
                'popular_items'         => __('Popular '. $taxonomy['plural'], $this->textdomain),
                'all_items'             => __('All '. $taxonomy['plural'], $this->textdomain),
                'parent_item'           => __('Parent '. $taxonomy['singular'], $this->textdomain),
                'parent_item_colon'     => __('Parent '. $taxonomy['singular'], $this->textdomain),
                'edit_item'             => __('Edit '. $taxonomy['singular'], $this->textdomain),
                'update_item'           => __('Update '. $taxonomy['singular'], $this->textdomain),
                'add_new_item'          => __('Add New '. $taxonomy['singular'], $this->textdomain),
                'new_item_name'         => __('New '. $taxonomy['singular'] .' Name', $this->textdomain),
                'add_or_remove_items'   => __('Add or remove '. $taxonomy['plural'], $this->textdomain),
                'choose_from_most_used' => __('Choose from most used '. $taxonomy['plural'], $this->textdomain),
                'menu_name'             => __($taxonomy['plural'], $this->textdomain),
            );

            $args = array(
                'labels'            => $labels,
                'public'            => false,
                'show_in_nav_menus' => false,
                'show_admin_column' => false,
                'hierarchical'      => false,
                'show_tagcloud'     => false,
                'show_ui'           => true,
                'rewrite'           => false,
                'query_var'         => false,
                'show_in_rest'      => true,
                'capabilities'      => array(),
            );

            if (!taxonomy_exists($taxonomy['slug'])) {
                register_taxonomy($taxonomy['slug'], array( $this->post_type['slug'] ), $args);
            }
        }
    }

    /**
     * Returns the parsed shortcode.
     * @param array $atts Attributes of the shortcode.
     * @param string $content Shortcode content.
     * @return string HTML content to display the shortcode.
     * @uses ss_html_output
     */
    public function ss_post_type_shortcode($atts = array(), $content = '')
    {
        // If not called as add_shortcode callback
        if (!in_array($this->post_type['slug'], func_get_args(), true)) {
            return $this->post_type['slug'];
        }

        $atts = shortcode_atts([
            'names' => '',
            'categories' => '',
        ], $atts);

        return $this->ss_html_output($atts['names'], $atts['categories']);
    }

    /**
     * Draws the HTML content for a bio post.
     * @param string|array $names [optional] One or more names to draw the bios for
     * @param string|array $categories [optional] One or more categories to draw bios for
     * @return HTML content
     * @uses sanitize
     */
    private function ss_html_output($names = '', $categories = '')
    {
        global $wpdb;
        $html = "";

        $query_opts = [
            'table'         => $wpdb->posts,
            'post_type'     => $this->post_type['slug'],
            'post_status'   => 'publish',
            'orderby'       => 'menu_order',
            'groupby'       => 'ID',
            'order'         => 'DESC',
            'names'         => $this->sanitize($names),
            'categories'    => $this->sanitize($categories, 'category')
        ];

        $query_arr = [
            "select" => "SELECT * FROM ".$wpdb->posts."",
            "where" => $wpdb->prepare(
                "WHERE `post_type`=%s AND `post_status`=%s",
                $query_opts["post_type"], $query_opts["post_status"]
            ),
            "groupby" => "GROUP BY ". $wpdb->posts .".`". $query_opts["groupby"] ."`",
            "orderby" => "ORDER BY ". $wpdb->posts .".`". $query_opts["orderby"] ."` ". $query_opts["order"] .""
        ];

        // filter by category
        $categories = $query_opts['categories'];
        if ($categories) {
            $query_arr["select"] .=
                " LEFT JOIN ". $wpdb->term_relationships ." ON
                ( ". $wpdb->posts .".`ID`=". $wpdb->term_relationships .".`object_id` )
                LEFT JOIN ". $wpdb->term_taxonomy ." ON
                ( ". $wpdb->term_relationships .".`term_taxonomy_id`=". $wpdb->term_taxonomy .".`term_taxonomy_id` )
                LEFT JOIN ". $wpdb->terms ." ON
                ( ". $wpdb->term_taxonomy .".`term_id`=". $wpdb->terms .".`term_id` )";

            if (is_string($categories)) {
                $query_arr["where"] .= $wpdb->prepare(
                    " AND ". $wpdb->terms .".`slug`=%s",
                    $categories
                );
            } elseif ($categories[0] !== ':exclude:') {
                $query_arr["where"] .= " AND (". $wpdb->terms .".`slug`=";

                for ($i = 0; $i < count($categories); $i++) {
                    $query_arr["where"] .= $wpdb->prepare(
                        "%s" . (($i == count($categories) - 1) ? "" : " OR ". $wpdb->terms .".`slug`="),
                        $categories[$i]
                    );
                }
                $query_arr["where"] .= ")";
            } else {
                $query_arr["where"] .= " AND ( (". $wpdb->terms .".`slug` IS NULL) OR ( (". $wpdb->terms .".`slug` IS NOT NULL) AND (". $wpdb->terms .".`slug`!=";

                for ($i = 0; $i < count($categories[1]); $i++) {
                    $query_arr["where"] .= $wpdb->prepare(
                        "%s) " . (($i == count($categories[1]) - 1) ? "" : " AND (". $wpdb->terms .".`slug` != "),
                        $categories[1][$i]);
                }
                $query_arr["where"] .= ") )";
            }
        }

        // filter by title
        $names = $query_opts['names'];
        if ($names) {
            if (is_string($names)) {
                $query_arr["where"] .= $wpdb->prepare(
                    " AND `post_title` LIKE %s",
                    "%".$atts['names']."%"
                );
            } elseif ($names[0] !== ':exclude:') {
                $query_arr["where"] .= " AND (`post_title` LIKE ";

                for ($i = 0; $i < count($names); $i++) {
                    $query_arr["where"] .= $wpdb->prepare(
                        "%s" . (($i == count($names) - 1) ? "" : " OR `post_title` LIKE "),
                        "%".$names[$i]."%"
                    );
                }
                $query_arr["where"] .= ")";
            } else {
                $query_arr["where"] .= " AND (`post_title` NOT LIKE ";

                for ($i = 0; $i < count($names[1]); $i++) {
                    $query_arr["where"] .= $wpdb->prepare(
                        "%s" . (($i == count($names[1]) - 1) ? "" : " AND `post_title` NOT LIKE "),
                        "%".$names[1][$i]."%"
                    );
                }
                $query_arr["where"] .= ")";
            }
        }

        $query = join(" ", array_values($query_arr));
        // Final prepare
        $query = $wpdb->prepare($query, "");

        $bios = $wpdb->get_results($query, OBJECT);
        if ($bios && !empty($bios)) {
            wp_enqueue_style("ss-bios");

            $listStyle = "team";
            if (count($bios) > 2) {
                // we need more space if there is enough to fill the grid
                $listStyle .= " ss-full-width";
            }
            $html .= "<ul class='{$listStyle}'>";

            foreach ($bios as $bio) {
                $nameAttr = esc_attr($bio->post_title);
                $nameHtml = esc_html($bio->post_title);
                $title = get_post_meta($bio->ID, $key = 'Title', $single = true);
                if ($title) {
                    $title = "<h4>".esc_html($title)."</h4>";
                }
                $content = $bio->post_content;
                $bioImage = get_the_post_thumbnail_url($bio->ID, $size = 'full');
                if ($bioImage) {
                    $bioImage = esc_url($bioImage);
                }

                $html .= "<li class='team-member'>
                    <a class='team-member-photo' data-popup-id='{$bio->ID}'>
                        <img src='{$bioImage}' alt='{$nameAttr}' title='{$nameAttr}' loading='lazy'/>
                        <h4>{$nameHtml}</h4>
                        {$title}
                    </a>
                    <div class='ss-hidden'>
                        <div class='team-member-bio' id='bio-popup-{$bio->ID}'>
                            <div class='bio-popup-container'>
                                <div class='bio-popup-photo'>
                                    <img src='{$bioImage}' alt='{$nameAttr}' title='{$nameAttr}' loading='lazy'/>
                                    <h4>{$nameHtml}</h4>
                                    {$title}
                                </div>
                                <div class='bio-popup-text'>
                                    {$content}
                                </div>
                            </div>
                        </div>
                    </div>
                </li>";
            }

            $html .= "</ul>";
            // set up pop-up links for each bio
            $html .= "<script>
                jQuery(function() {
                    jQuery('.team-member-photo').each(function(i,e) {
                        jQuery(e).colorbox({inline:true, initialWidth:'0px', maxWidth:'100%', initialHeight:'0px', href: '#bio-popup-'+jQuery(e).data('popup-id')});
                    });
                });
            </script>";
        }

        return $html;
    }

    /**
     * Hook for registering the plugin's JavaScript file with Gutenberg.
     * @see ss_gutenberg_render_cb
     */
    public function ss_register_gutenberg_block()
    {
        if (!function_exists('register_block_type')) {
            // Gutenberg is not active.
            return;
        }

        wp_register_script(
            'ss-gutenberg-script',
            plugins_url('block.js', __FILE__),
            array('wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor', 'underscore'),
            filemtime(plugin_dir_path( __FILE__ ) . 'block.js')
        );

        register_block_type($this->textdomain.'/'.$this->textdomain, [
            'editor_script' => 'ss-gutenberg-script',
            'render_callback' => array($this, 'ss_gutenberg_render_cb'),
        ]);
    }

    /**
     * Callback function for rendering a block with Gutenberg.
     * @param array $attributes Block attributes
     * @param string $content Block contents (unused)
     * @return string HTML content
     * @uses transform_html
     * @uses ss_html_output
     */
    public function ss_gutenberg_render_cb($attributes, $content)
    {
        $names = ''; $categories = '';
        $delimiter_names = $attributes['names_exclude'] ? '!' : '+';
        $delimiter_categories = $attributes['categories_exclude'] ? '!' : '+';

        if ($attributes['names']) {
            $names = $this->transform_html($attributes['names'], 'li', $delimiter_names);
        }

        if ($attributes['categories']) {
            $categories = $this->transform_html($attributes['categories'], 'li', $delimiter_categories);
        }

        return $this->ss_html_output($names, $categories);
    }

    /**
     * Parses and sanitizes input values.
     * This allows delimited lists of names or categories to be passed when drawing the bio section.
     * @param string $input Raw input value
     * @param string $type [optional] Value type: "name" or "category"
     * @return string|array Santized value(s)
     */
    public function sanitize(string $input = '', string $type = 'name')
    {
        if (!$input) {
            return false;
        }

        $regex = ( $type === 'category' ? '/^([a-z\-]|(?!.*\!)\+(?!.*\!)|(?!.*\+)\!(?!.*\+))+$/' : '/^([a-zA-Z\s\.]|(?!.*\!)\+(?!.*\!)|(?!.*\+)\!(?!.*\+))+$/' );
        if (!preg_match($regex, $input)) {
            return false;
        }

        $delimiter = false;
        if (is_int(strpos($input, '+'))) {
            $delimiter = '+';
        }
        if (is_int(strpos($input, '!'))) {
            $delimiter = '!';
        }

        if ($delimiter) {
            $input = array_filter(explode($delimiter, $input));
            $input = array_values($input);

            if (empty($input)) {
                return false;
            }

            $input = ($delimiter === '!') ? [':exclude:', $input] : $input;
        }

        return $input;
    }

    /**
     * Modifies the given HTML to replace the specified tag with a delimeter.
     * @param string $html Input HTML
     * @param string $tag HTML tag to remove
     * @param string $delimiter Delimeter to use instead of the tag
     * @param bool $strip [optional] If set to FALSE, other HTML tags will not be removed (defaults to TRUE)
     * @return string Updated HTML
     */
    public function transform_html($html = '', $tag = '', $delimiter = '', $strip = true)
    {
        if (!$html || !$tag) {
            return false;
        }

        $starttag = '<'. $tag .'>';
        $endtag = '</'. $tag .'>';

        $html = str_replace($starttag, $delimiter, $html);
        $html = str_replace($endtag, '', $html);

        if ($strip) {
            $html = wp_strip_all_tags($html);
        }

        return $html;
    }
}

$ssbio = new SSBio();