<?php

class VistromMediaAjaxController
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        // Regular list view.
        add_action('wp_ajax_admin_vistrom_media_render_bulk_edit', [$this, 'get_media_bulk_edit_view']);
        add_action('wp_ajax_admin_vistrom_media_bulk_update', [$this, 'media_bulk_edit_update']);

        // Grid view.
        add_action('wp_ajax_admin_vistrom_media_render_grid_bulk_edit', [$this, 'get_media_grid_bulk_edit_view']);
    }

    /**
     * Get media bulk edit view.
     *
     * @return void
     */
    public function get_media_bulk_edit_view()
    {
        if (isset($_POST['post_ids']) && !empty($_POST['post_ids'])) {
            $ids = wp_parse_id_list($_POST['post_ids']);

            $args = [
                'numberposts' => -1,
                'include'     => $ids,
                'post_type'   => 'attachment',
            ];
            $selectedAttachments = get_posts($args);

            ob_start();
            include vistrom_media_plugin_path('/views/admin/media/bulk-edit-list.php');
            $html = ob_get_contents();
            ob_end_clean();

            wp_send_json_success(['html' => $html]);
        }

        wp_send_json_error(['message' => __('No selected attachments', 'vistrom-media')], 400);
    }

    /**
     * Handle update of bulk edit view.
     *
     * @return void
     */
    public function media_bulk_edit_update()
    {
        check_ajax_referer('vistrom_media_bulk_update', 'security');

        if (!isset($_POST['media']) && empty($_POST['media'])) {
            wp_send_json_error(['message' => __('No media selected', 'vistrom-media')], 422);
        }

        $ids = wp_parse_id_list($_POST['media']);
        $replaceTaxonomies = false;

        if (!empty($_POST['vistrom_media_replace_existing_taxonomies'])) {
            $replaceTaxonomies = sanitize_text_field($_POST['vistrom_media_replace_existing_taxonomies']) === 'yes';
        }

        do_action('vistrom_media_before_bulk_update', $ids, $_POST);

        foreach (vistrom_media_get_taxonomies_for_attachments() as $taxonomy) {
            $selectedTerms = [];
            $tax = get_taxonomy($taxonomy);

            if (isset($_POST['tax_input'][$taxonomy]) && !empty($_POST['tax_input'][$taxonomy])) {
                $selectedTerms = VistromMediaHelpers::filter_valid_taxonomy_term_keys(
                    $_POST['tax_input'][$taxonomy],
                    $tax
                );
            }

            foreach ($ids as $postId) {
                wp_set_post_terms($postId, $selectedTerms, $taxonomy, !$replaceTaxonomies);
            }
        }

        do_action('vistrom_media_after_bulk_update', $ids, $_POST);

        wp_send_json_success(['message' => __('Media updated', 'vistrom-media')]);
    }

    /**
     * Get media bulk edit view.
     *
     * @return void
     */
    public function get_media_grid_bulk_edit_view()
    {
        if (!isset($_POST['post_ids'])) {
            wp_send_json_error(['message' => __('No selected attachments', 'vistrom-media')], 400);
        }

        $ids = wp_parse_id_list($_POST['post_ids']);

        $args = [
            'numberposts' => -1,
            'include'     => $ids,
            'post_type'   => 'attachment',
        ];
        $selectedAttachments = get_posts($args);

        ob_start();
        include vistrom_media_plugin_path('/views/admin/media/bulk-edit-grid.php');
        $html = ob_get_contents();
        ob_end_clean();

        wp_send_json_success(['html' => $html]);
    }
}
new VistromMediaAjaxController();
