<?php
/**
 * Header builder element: search icon (opens the real search modal).
 *
 * @package Noorifa
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li class="d-none d-sm-block">
	<a href="#search" data-bs-toggle="modal" class="nav-icon-item link">
		<?php \Noorifa\Setup\Icons::render( 'MagnifyingGlass' ); ?>
	</a>
</li>
