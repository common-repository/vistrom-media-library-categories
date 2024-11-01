<?php
if (!defined( 'ABSPATH')) {
    exit; // Exit if accessed directly
}
?>

<div id="vistrom-media-updated" class="updated notice is-dismissible">
    <p><?php echo esc_html(sprintf(__('%s attachments updated.', 'vistrom-media'), $numberOfChangedPosts)) ?></p>
</div>
