<?php

class VistromMedia
{
    /**
     * The taxonomy.
     *
     * @var string
     */
    const TAXONOMY = 'vistrom_media_category';

    /**
     * Initialize plugin class.
     *
     * @return void
     */
    public static function init()
    {
        if (is_admin()) {
            self::includes();
            self::register_hooks();
        }
    }

    /**
     * Includes for the class.
     *
     * @return void
     */
    public static function includes()
    {
        include vistrom_media_plugin_path('/includes/VistromMediaAjaxController.php');
        include vistrom_media_plugin_path('/includes/VistromMediaHelpers.php');
    }

    /**
     * Register hooks.
     *
     * @return void
     */
    public static function register_hooks()
    {
        // Add media category taxonomy to attachment posts
        add_action('attachment_fields_to_edit', [__CLASS__, 'add_media_categories_to_modal'], 10, 2);
        add_filter('attachment_fields_to_save', [__CLASS__, 'save_media_categories_to_attachment'], 10, 2);

        // Maintain hierarchy of terms in edit page.
        add_filter('wp_terms_checklist_args', [__CLASS__, 'disable_checked_on_top_for_media_categories']);

        // Add filter on list view for attachments.
        add_filter('restrict_manage_posts', [__CLASS__, 'add_media_categories_as_filter_on_attachment_list']);

        // Bulk actions
        add_filter('bulk_actions-upload', [__CLASS__, 'add_media_bulk_actions'], 10, 1);
        add_filter('removable_query_args', [__CLASS__, 'remove_notice_query_args_from_url']);

        // Admin notices
        add_action('admin_notices', [__CLASS__, 'add_update_notice']);
    }

    /**
     * Remove query args related to notices from url.
     *
     * @param  array $queryArgs The query args to remove.
     *
     * @return array
     */
    public static function remove_notice_query_args_from_url($queryArgs)
    {
        $queryArgs[] = 'vistrom_media_updated';

        return $queryArgs;
    }

    /**
     * Add media category checkboxes to attachment modal.
     *
     * @return void
     */
    public static function add_media_categories_to_modal($fields, WP_Post $post)
    {
        foreach (get_attachment_taxonomies($post->ID) as $taxonomy) {
            if (in_array($taxonomy, vistrom_media_get_taxonomies_for_attachments())) {
                $terms = get_object_term_cache($post->ID, $taxonomy);
                $tax = (array) get_taxonomy($taxonomy);

                if (!$tax['public'] || !$tax['show_ui']) {
                    continue;
                }

                if (empty($tax['label'])) {
                    $tax['label'] = $taxonomy->name;
                }

                if (empty($tax['args'])) {
                    $tax['args'] = [];
                }

                if (false === $terms) {
                    $terms = wp_get_object_terms($post->ID, $taxonomy, $tax['args']);
                }

                $values = [];

                foreach ($terms as $term) {
                    $values[] = $term->slug;
                }

                $tax['value'] = join(', ', $values);
                $tax['show_in_edit'] = false;

                ob_start();

                $args = [
                    'taxonomy' => $taxonomy,
                    'checked_ontop' => false,
                ];

                wp_terms_checklist($post->ID, $args);
                $html = '<ul class="categorychecklist cat-checklist vistrom-media-categories">' . ob_get_contents() . '</ul>';
                ob_end_clean();

                $tax['input'] = 'html';
                $tax['html'] = $html;

                $fields[$taxonomy] = $tax;
            }
        }

        return $fields;
    }

    /**
     * Save media categories to attachment
     *
     * @param array $post           The post.
     * @param array $attachmentData The attachment data.
     *
     * @return array
     */
    public static function save_media_categories_to_attachment($post, $attachmentData)
    {
        // Check to see if we have any taxonomy input which matches the media category taxonomy
        if (isset($_POST['tax_input']) && !empty($_POST['tax_input'])) {
            foreach (vistrom_media_get_taxonomies_for_attachments() as $taxonomy) {
                $selectedTerms = [];
                $tax = get_taxonomy($taxonomy);

                if (isset($_POST['tax_input'][$taxonomy]) && !empty($_POST['tax_input'][$taxonomy])) {
                    $selectedTerms = VistromMediaHelpers::filter_valid_taxonomy_term_keys($_POST['tax_input'][$taxonomy], $tax);
                }

                wp_set_post_terms($post['ID'], $selectedTerms, $taxonomy);
            }
        }

        return $post;
    }

    /**
     * Disabled checked on top for taxonomy.
     *
     * @param array $args Arguments for wp_term_checklist walker.
     *
     * @return array
     */
    public static function disable_checked_on_top_for_media_categories($args)
    {
        if (!empty($args['taxonomy']) && $args['taxonomy'] === VistromMedia::TAXONOMY) {
            $args['checked_ontop'] = false;
        }

        return $args;
    }

    /**
     * Add media category filter to Attachment list.
     *
     * @return void
     */
    public static function add_media_categories_as_filter_on_attachment_list()
    {
        global $pagenow;

        if ('upload.php' === $pagenow) {
            foreach (vistrom_media_get_taxonomies_for_attachments() as $taxonomy) {
                $selected = sanitize_title(wp_unslash(isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : ''));
                $tax = get_taxonomy($taxonomy);

                $args = [
                    'taxonomy' => $taxonomy,
                    'name'=> $taxonomy,
                    'class'=> 'postform',
                    'show_option_all' => sprintf(__('All %s', 'vistrom-media'), $tax->label),
                    'hide_empty' => false,
                    'hierarchical' => $tax->hierarchical,
                    'selected' => $selected,
                    'show_count' => true,
                    'value_field' => 'slug',
                    'hide_if_empty' => true,
                ];

                wp_dropdown_categories($args);
            }
        }
    }

    /**
     * Add custom bulk actions.
     *
     * @param array $actions The actions.
     *
     * @return array
     */
    public static function add_media_bulk_actions($actions)
    {
        $actions['vistrom_media_edit'] = __('Edit', 'vistrom-media');

        return $actions;
    }

    /**
     * Add admin notice.
     *
     * @return void
     */
    public static function add_update_notice()
    {
        if (isset($_REQUEST['vistrom_media_updated']) && !empty($_REQUEST['vistrom_media_updated'])) {
            $numberOfChangedPosts = intval($_REQUEST['vistrom_media_updated']);

            ob_start();
            include vistrom_media_plugin_path('/views/admin/notices/media-updated.php');
            $html = ob_get_contents();
            ob_end_clean();

            echo wp_kses_post($html);
        }
    }
}
new VistromMedia();
