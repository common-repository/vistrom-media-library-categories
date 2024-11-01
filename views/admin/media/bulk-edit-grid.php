<div class="vistrom-media-modal" role="dialog">
    <form class="vistrom-media-modal-content js-vistrom-bulk-edit-form">
        <div class="vistrom-media-modal-header">
            <h1><?php _e('Bulk edit', 'vistrom-media') ?></h1>
            <button class="js-vistrom-media-modal-close vistrom-media-modal-close">
                &#10005;
            </button>
        </div>

        <div class="vistrom-media-modal-body">
            <?php do_action('vistrom_media_bulk_edit_grid_start'); ?>

            <div id="bulk-title-div">
                <div id="bulk-titles">
                    <ul id="bulk-titles-list">
                        <?php foreach ($selectedAttachments as $post): ?>
                            <li class="ntdelitem">
                                <button type="button" data-product_id="<?php echo esc_attr($post->ID) ?>" class="js-vistrom-media-remove-from-bulk-edit ntdelbutton button-link"></button>
                                <span class="ntdeltitle"><?php echo esc_html($post->post_title); ?></span>
                                <input type="hidden" name="media[]" value="<?php echo esc_attr($post->ID) ?>">
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>

            <label>
                <input type="checkbox" name="vistrom_media_replace_existing_taxonomies" value="yes">
                <?php echo __('Replace existing taxonomies', 'vistrom-media'); ?>
            </label>

            <ul class="vistrom-media-taxonomy-grid">
                <?php foreach (vistrom_media_get_taxonomies_for_attachments('objects') as $taxonomy): ?>
                    <?php if ($taxonomy->show_ui): ?>
                        <div class="categorydiv vistrom-media-taxonomy-item">
                            <span class="title inline-edit-categories-label"><?php echo esc_html($taxonomy->label) ?></span>
                            <ul class="cat-checklist">
                                <?php
                                $args = [
                                    'taxonomy' => $taxonomy->name,
                                    'checked_ontop' => false,
                                ];

                                wp_terms_checklist(0, $args);
                                ?>
                            </ul>
                        </div>
                    <?php endif; ?>
                <?php endforeach; ?>
            </ul>

            <?php do_action('vistrom_media_bulk_edit_grid_end'); ?>
        </div>

        <div class="submit inline-edit-save">
            <input type="hidden" name="screen" value="upload">
            <input type="submit" name="vistrom_media_bulk_edit" id="vistrom-media-bulk-edit" class="button button-primary" value="<?php _e('Update', 'vistrom-media') ?>"/>
        </div>
    </form>
</div>
