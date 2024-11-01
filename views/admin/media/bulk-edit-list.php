<?php
if (!defined( 'ABSPATH')) {
    exit; // Exit if accessed directly
}
?>
<tr id="vistrom-media-bulk-edit-row" class="inline-edit-row inline-edit-row-post bulk-edit-row bulk-edit-row-post bulk-edit-attachment inline-editor">
    <td class="colspanchange">
        <div class="inline-edit-wrapper">
            <div class="vistorm-media-bulk-edit">
                <?php do_action('vistrom_media_bulk_edit_list_start'); ?>
                <fieldset class="inline-edit-col-left">
                    <legend class="inline-edit-legend">
                        <?php _e('Bulk edit', 'vistrom-media') ?>
                    </legend>
                    <div class="inline-edit-col">
                        <?php do_action('vistrom_media_bulk_edit_list_left_column_start'); ?>
                        <div id="bulk-title-div">
                            <div id="bulk-titles">
                                <ul id="bulk-titles-list">
                                    <?php foreach ($selectedAttachments as $post): ?>
                                        <li class="ntdelitem">
                                            <button type="button" data-product_id="<?php echo esc_attr($post->ID) ?>" class="js-vistrom-media-remove-from-bulk-edit ntdelbutton button-link"></button>
                                            <span class="ntdeltitle"><?php echo esc_html($post->post_title); ?></span>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        </div>
                        <label>
                            <input type="checkbox" name="vistrom_media_replace_existing_taxonomies" value="yes">
                            <?php echo __('Replace existing taxonomies', 'vistrom-media'); ?>
                        </label>
                        <?php do_action('vistrom_media_bulk_edit_list_left_column_end'); ?>
                    </div>
                </fieldset>
                <fieldset class="inline-edit-col-center">
                    <div class="inline-edit-col">
                        <?php do_action('vistrom_media_bulk_edit_list_center_column_start'); ?>
                        <?php do_action('vistrom_media_bulk_edit_list_center_column_end'); ?>
                    </div>
                </fieldset>
                <fieldset class="inline-edit-col-right">
                    <div class="inline-edit-col">
                        <div class="vistrom-media-taxonomy-grid">
                            <?php do_action('vistrom_media_bulk_edit_list_right_column_start'); ?>
                            <?php foreach (vistrom_media_get_taxonomies_for_attachments('objects') as $taxonomy): ?>
                                <?php if ($taxonomy->show_ui): ?>
                                    <div class="categorydiv">
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
                            <?php do_action('vistrom_media_bulk_edit_list_right_column_end'); ?>
                        </div>
                    </div>
                </fieldset>
                <?php do_action('vistrom_media_bulk_edit_list_end'); ?>
            </div>

            <div class="submit inline-edit-save">
                <input type="hidden" name="screen" value="upload">
                <button class="button action js-vistrom-media-cancel-bulk-edit" type="button"><?php _e('Cancel', 'vistrom-media') ?></button>
                <input type="submit" name="vistrom_media_bulk_edit" id="vistrom-media-bulk-edit" class="button button-primary" value="<?php _e('Update', 'vistrom-media') ?>"/>
            </div>
        </div>
    </td>
</tr>
