<?php
/**
 * Header builder element: search icon (opens the real search modal).
 *
 * @package Ecombon
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li class="d-none d-sm-block">
	<a href="#search" data-bs-toggle="modal" class="nav-icon-item link">
		<?php \Ecombon\Setup\Icons::render( 'MagnifyingGlass' ); ?>
	</a>
</li>
