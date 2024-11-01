<?php

if (!(defined('WP_CLI') && WP_CLI)) {
    return;
}

class VistromMediaCommand {
    /**
     * Add default category to all media objects.
     *
     * @param array $args
     * @param array $assoc_args
     *
     * @return void
     */
    public function init($args, $assoc_args)
    {
        $args = [
            'post_type' => 'attachment',
            'numberposts' => -1,
        ];

        $attachments = get_posts($args);
        $defaultTerm = get_option('default_term_vistrom_media_category', null);

        if ($defaultTerm) {
            foreach ($attachments as $attachment) {
                $mediaCategories = wp_get_post_terms($attachment->ID, VistromMedia::TAXONOMY);

                if (empty($mediaCategories) && !is_wp_error($mediaCategories)) {
                    wp_set_post_terms($attachment->ID, $defaultTerm, VistromMedia::TAXONOMY, true);
                }
            }
        }
    }
}

WP_CLI::add_command('vistrom-media', 'VistromMediaCommand');
