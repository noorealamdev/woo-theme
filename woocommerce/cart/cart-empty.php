<?php
/**
 * Empty cart page.
 *
 * @package Noorifa
 * @version 7.0.1
 */

defined( 'ABSPATH' ) || exit;

get_template_part( 'template-parts/cart/page-title' );
?>
<section class="flat-spacing-2 pt-0">
	<div class="container">
		<?php get_template_part( 'template-parts/cart/empty-content' ); ?>
	</div>
</section>
