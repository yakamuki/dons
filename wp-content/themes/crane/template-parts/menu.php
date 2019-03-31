<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying menu area.
 *
 * @package crane
 */

$nav_menu = apply_filters( 'crane_primary_nav_menu', '' );

?>
    <header class="crane-navbar">
        <div class="crane-container">

            <div class="crane-logo"><?php echo crane_get_logo_html(); ?></div>

			<?php

			$args = array(
				'theme_location'  => 'primary',
				'before'          => '', // before the menu
				'after'           => '', // after the menu
				'link_before'     => '', // before each link
				'link_after'      => '', // after each link
				'fallback_cb'     => '', // fallback function (if there is one)
				'container_class' => 'crane-nav',
				'menu_class'      => 'crane-menu',
				'items_wrap'      => '<ul id="%1$s" class="%2$s">%3$s</ul>',
				'item_spacing'    => 'discard',
				'echo'            => false
			);

			if ( $nav_menu ) {
			    $args['menu'] = $nav_menu;
            }

			$wp_nav_menu = wp_nav_menu( $args );

			if ( has_nav_menu( 'primary' ) || ! empty( $wp_nav_menu ) ) {
				echo crane_clear_echo( $wp_nav_menu );
				?><span class="crane-menu-btn"></span><?php
			}
			?>

        </div>
    </header>
<?php
