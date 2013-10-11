WP Plugin Status - Beta
==============

A wordpress plugin that deactivates all wp plugin styles and scripts per page to increase load time.



**Author:** *John Burns - Pierce Communications*

**Email:** *me@johnburns87.com*

**Website** *www.piercecommunications.co.uk*

Please email me any bugs and issues. Thanks!




Installation
--------------

- Add folder to wp-content/plugins/ directory
- Login to wp-admin
- Go to plugins
- Activate plugin
- Put the following code into your themes header and footer.
- All plugin scripts and stylesheets will be disabled by default
- When editing a page, a new widget will appear below the MCE editor where you can enable / disable scripts.

header.php 
--------------

	<?php
	global $post;
	$styles_query = get_post_meta( $post->ID, '_active_styles', true );
	$styles_array = unserialize($styles_query);
	?>

	<?php if (!empty($styles_array[0])) { foreach($styles_array as $style): ?>
	<link rel="stylesheet" href="<?php echo $style; ?>">
	<?php endforeach; } ?>

footer.php
--------------

	<?php
	global $post;
	$scripts_query = get_post_meta( $post->ID, '_active_scripts', true );
	$scripts_array = unserialize($scripts_query);
	?>

	<?php if (!empty($scripts_array[0])) { foreach($scripts_array as $script): ?>
	<script src="<?php echo $script; ?>"></script>
	<?php endforeach; } ?>

Issues
--------------

- Cant delete custom styles and scripts. Later release