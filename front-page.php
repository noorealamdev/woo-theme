<?php
/**
 * The homepage template.
 *
 * Deliberately just a blank canvas: the front page's own content — built
 * with Core Plugin blocks in the editor — is rendered as-is, with no
 * title or wrapper chrome imposed by the theme.
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div id="primary" class="site-main">
	<?php
	while ( have_posts() ) :
		the_post();
		the_content();
	endwhile;
	?>
</div>

<?php
get_footer();
