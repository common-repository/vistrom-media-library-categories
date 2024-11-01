=== Viström Media Library Categories ===
Contributors: vistromdigital
Tags: media, library, bulk-edit, category, categories
Requires at least: 5.3
Tested up to: 6.3.1
Stable tag: 1.2.0
Requires PHP: 7.4
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Categorize and filter your media library by categories, added support for bulk editing in both list-view and the grid-view.

== Description ==

Vistrom Media Library Categories provides the ability to categorize and filter your files in the WordPress media library. You can use it in the grid view, list view and in the media modal. The plugin also provides bulk actions to add or remove categories for multiple attachments at once.

=== Features ===
* Use any taxonomy for attachments (built-in or custom)
* Assign the terms in media library or the regular list view.
* Filter attachments by terms in media library and admin-list.
* Bulk edit attachments in either the media library or admin-list.

== Frequently Asked Questions ==

= I have custom taxonomies for attachments in my theme, how do i hide them from being rendered via the plugin? =

Using the filter below, you can prevent taxonomies from being rendered as a result of this plugin.

`
add_filter('vistrom_media_unsupported_taxonomy_names', function () {
    return [
        'names-to-blacklist' // Your taxonomy name
    ];
});
`

= How can i add custom fields to the bulk edit views? =

Using the actions below it's possible to add your own custom fields to each respective bulk edit view. The views are located in the following folder */views/admin/media/*

`
// Grid modal
add_action('vistrom_media_bulk_edit_grid_start', 'render_my_field');
add_action('vistrom_media_bulk_edit_grid_end', 'render_my_field');

// List view
add_action('vistrom_media_bulk_edit_list_start', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_left_column_start', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_left_column_end', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_center_column_start', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_center_column_end', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_right_column_start', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_right_column_end', 'render_my_field');
add_action('vistrom_media_bulk_edit_list_end', 'render_my_field');

function render_my_field()
{
    echo "<input type='text' name='custom_field' value='' placeholder='My field'>";
}
`

= How can i save my custom added fields? =

To save the fields added to the bulk edit views, you can use the following actions. All supported taxonomies are saved automatically.

`
add_action('vistrom_media_before_bulk_update', 'save_my_field', 10, 2);
add_action('vistrom_media_after_bulk_update', 'save_my_field', 10, 2);

function save_my_field($postIds, $postData)
{
    foreach ($postIds as $id) {
        update_post_meta($id, 'custom_field', $postData['custom_field']);
    }
}
`

== Installation ==

* Download and install using the built in WordPress plugin installer.
* Activate in the "Plugins" area of your admin by clicking the "Activate" link.
* No further setup or configuration is necessary.

== Screenshots ==

1. Filter by taxonomies in media library list-view.
2. Filter by taxonomies in media library grid.
3. Bulk-edit attachments in list-view.
4. Bulk-edit attachments in library grid.
5. Filter by taxonomies when selecting media.

== Changelog ==

= 1.2.0 =
* Test up to WordPress 6.3.1.
* Declare HPOS compatibility with WooCommerce.

= 1.1.1 =
* Add FAQ section to readme.
* Improve the UI for the bulk edit forms.
* Allow the bulk edit modal to be closed using the escape button.
* Fix: Multiple categories with the same label will now be rendered in the library grid.
* Fix: The page can now be scrolled after closing the bulk edit modal in library grid.

= 1.1.0 =
* Add a checkbox to determine if taxonomies should be replaced or added via bulk edit.
* Align the post-title with the delete button when listing the chosen attachments in bulk edit.

= 1.0.1 =
* The bulk-edit button in library should only be visible while grid is active.
* Update readme
