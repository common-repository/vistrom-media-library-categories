<?php
/**
 * @package Viström Media Library Categories
 * Plugin Name: Viström Media Library Categories
 * Description: Categorize your media library, and preform bulk updates for attachments.
 * Text Domain: vistrom-media
 * Domain Path: /resources/lang
 * Version: 1.2.0
 * Requires at least: 5.3
 * Requires PHP: 7.4
 * Author: Viström
 * Author URI: https://vistrom.se
 */
include_once vistrom_media_plugin_path('/includes/VistromMediaCommands.php');
include_once vistrom_media_plugin_path('/includes/VistromMedia.php');
VistromMedia::init();

/**
 * Initialize plugin.
 *
 * @return void
 */
function vistrom_media_init()
{
    $labels = [
        'name' => __('Categories', 'vistrom-media'),
        'singular_name' => __('Category', 'vistrom-media'),
        'search_items' => __('Search categories', 'vistrom-media'),
        'all_items' => __('All categories', 'vistrom-media'),
        'parent_item' => __('Parent category', 'vistrom-media'),
        'parent_item_colon' => __('Parent category:', 'vistrom-media'),
        'edit_item' => __('Edit category', 'vistrom-media'),
        'update_item' => __('Update category', 'vistrom-media'),
        'add_new_item' => __('Add new category', 'vistrom-media'),
        'new_item_name' => __('New category name', 'vistrom-media'),
        'menu_name' => __('Categories', 'vistrom-media'),
    ];

    $args = [
        'hierarchical' => true,
        'labels' => $labels,
        'show_ui' => true,
        'query_var' => true,
        'rewrite' => true,
        'update_count_callback' => '_update_generic_term_count',
        'show_admin_column' => true,
        'sort' => true,
        'default_term' => [
            'name' => __('Uncategorized', 'vistorm-media'),
            'slug' => 'uncategorized',
        ],
    ];

    $args = apply_filters('vistrom_media_register_media_category_args', $args);

    register_taxonomy(VistromMedia::TAXONOMY, 'attachment', $args);
}
add_action('init', 'vistrom_media_init');

/**
 * Load lang lines.
 *
 * @return void
 */
function vistrom_media_load_plugin_textdomain()
{
    load_plugin_textdomain(
        'vistrom-media',
        false,
        basename(dirname(__FILE__)) . '/resources/lang/'
    );
}
add_action('plugins_loaded', 'vistrom_media_load_plugin_textdomain');

/**
 * Declare HPOS compatibility with WooCommerce.
 *
 * @return void
 */
function vistrom_media_declare_hpos_compat()
{
    if (class_exists(\Automattic\WooCommerce\Utilities\FeaturesUtil::class)) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility('custom_order_tables', __FILE__, true);
    }
}
add_action('before_woocommerce_init', 'vistrom_media_declare_hpos_compat');

/**
 * Enqueue admin scripts
 *
 * @return void
 */
function vistrom_media_enqueue_admin_scripts()
{
    $jsVersion = filemtime(vistrom_media_plugin_path('/build/index.js'));

    wp_enqueue_script(
        'vistrom-media-js',
        vistrom_media_plugin_url('build/index.js'),
        [
            'jquery',
            'wp-i18n',
            'media-editor',
            'media-views',
        ],
        $jsVersion,
        true
    );

    $mediaTaxonomies = array_map(function ($tax) {
        $taxonomy = get_taxonomy($tax);
        $taxonomy->terms = vistrom_media_hierarchical_taxonomies($tax);

        return $taxonomy;
    }, vistrom_media_get_taxonomies_for_attachments());

    $settings = [
        'ajaxUrl' => admin_url('admin-ajax.php'),
        'mediaTaxonomies' => $mediaTaxonomies,
        'screen' => get_current_screen()->id,
        'nonces' => [
            'bulkUpdate' => wp_create_nonce('vistrom_media_bulk_update'),
        ],
    ];

    wp_set_script_translations(
        'vistrom-media-js',
        'vistrom-media',
        vistrom_media_plugin_path('/resources/lang/')
    );
    wp_localize_script('vistrom-media-js', 'vistromMedia', $settings);

    $styleVersion = filemtime(vistrom_media_plugin_path('/build/index.css'));

    wp_enqueue_style(
        'vistrom-media',
        vistrom_media_plugin_url('build/index.css'),
        [],
        $styleVersion
    );
}
add_action('admin_enqueue_scripts', 'vistrom_media_enqueue_admin_scripts');

/**
 * Get plugin path.
 *
 * @param string $path The path to append.
 *
 * @return string
 */
function vistrom_media_plugin_path($path = '')
{
    $basePath = untrailingslashit(plugin_dir_path(__FILE__));

    if ($path) {
        return $basePath . $path;
    }

    return $basePath;
}

/**
 * Get the plugin url.
 *
 * @param string $path The path to append.
 *
 * @return string
 */
function vistrom_media_plugin_url($path = '')
{
    $basePath = plugin_dir_url(__FILE__);

    if ($path) {
        return $basePath . $path;
    }

    return $basePath;
}

/**
 * Create hierarchical names for Taxonomy.
 *
 * @param  string $taxonomy    The taxonomy.
 * @param  array  $terms       List of terms.
 * @param  int    $parent      The parent to start from.
 * @param  int    $level       The level.
 *
 * @return array
 */
function vistrom_media_hierarchical_taxonomies($taxonomy, $terms = [], $parent = 0, $level = 0)
{
    $children = get_terms([
        'taxonomy' => $taxonomy,
        'hide_empty' => 0,
        'parent' => $parent
    ]);

    if (count($children) > 0) {
        foreach ($children as $term) {
            // Add spaces per level to indicate hierarchical structure.
            $term->name = esc_html(str_repeat('&nbsp;&nbsp;&nbsp;', $level) . ' ' . $term->name);
            $terms[] = $term;

            $terms = vistrom_media_hierarchical_taxonomies($taxonomy, $terms, $term->term_id, $level+1);
        }
    }

    return $terms;
}

/**
 * Get list of supported attachment taxonomies.
 *
 * @param string $output The output, supports 'names' or 'objects'
 *
 * @return array
 */
function vistrom_media_get_taxonomies_for_attachments($output = 'names')
{
    $taxonomies = get_taxonomies_for_attachments($output);

    $UnsupportedTaxonomies = apply_filters('vistrom_media_unsupported_taxonomy_names', []);

    if ($output === 'names') {
        $taxonomies = array_diff($taxonomies, $UnsupportedTaxonomies);
    } else {
        // Output is objects
        $taxonomies = array_filter($taxonomies, function ($taxonomy) use ($UnsupportedTaxonomies) {
            return !in_array($taxonomy->name, $UnsupportedTaxonomies);
        });
    }

    return apply_filters('vistrom_media_get_taxonomies_for_attachments', $taxonomies, $output);
}

