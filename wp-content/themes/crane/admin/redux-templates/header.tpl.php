<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for the panel header area.
 *
 * @author   Redux Framework
 * @author   Grooni
 * @package  Crane
 * @version: 3.5.4.18
 */


$tip_title = esc_html__( 'Developer Mode Enabled', 'crane' );

if ( $this->parent->dev_mode_forced ) {
	$is_debug     = false;
	$is_localhost = false;

	$debug_bit = '';
	if ( Redux_Helpers::isWpDebug() ) {
		$is_debug  = true;
		$debug_bit = esc_html__( 'WP_DEBUG is enabled', 'crane' );
	}

	$localhost_bit = '';
	if ( Redux_Helpers::isLocalHost() ) {
		$is_localhost  = true;
		$localhost_bit = esc_html__( 'you are working in a localhost environment', 'crane' );
	}

	$conjunction_bit = '';
	if ( $is_localhost && $is_debug ) {
		$conjunction_bit = ' ' . esc_html__( 'and', 'crane' ) . ' ';
	}

	$tip_msg = esc_html__( 'This has been automatically enabled because', 'crane' ) . ' ' . $debug_bit . $conjunction_bit . $localhost_bit . '.';
} else {
	$tip_msg = esc_html__( 'If you are not a developer, your theme/plugin author shipped with developer mode enabled. Contact them directly to fix it.', 'crane' );
}

$theme         = wp_get_theme();
$theme_version = $theme->get( 'Version' );
if ( CRANE_THEME_VERSION !== $theme_version ) {
	$theme_version = CRANE_THEME_VERSION . ' [' . esc_html__( 'child version', 'crane' ) . ': ' . $theme_version . ']';
}

?>
<div id="redux-header">
	<?php if ( ! empty( $this->parent->args['display_name'] ) ) { ?>
		<div class="display_header">

			<?php if ( isset( $this->parent->args['dev_mode'] ) && $this->parent->args['dev_mode'] ) { ?>
				<div class="redux-dev-mode-notice-container redux-dev-qtip"
				     qtip-title="<?php echo esc_attr( $tip_title ); ?>"
				     qtip-content="<?php echo esc_attr( $tip_msg ); ?>">
					<span class="redux-dev-mode-notice"><?php esc_html_e( 'Developer Mode Enabled', 'crane' ); ?></span>
				</div>
			<?php } elseif ( isset( $this->parent->args['forced_dev_mode_off'] ) && $this->parent->args['forced_dev_mode_off'] == true ) { ?>
				<?php $tip_title = esc_html__( 'The "forced_dev_mode_off" argument has been set to true.', 'crane' ); ?>
				<?php $tip_msg = esc_html__( 'Support options are not available while this argument is enabled.  You will also need to switch this argument to false before deploying your project.  If you are a user of this product and you are seeing this message, please contact the author of this theme/plugin.', 'crane' ); ?>
				<div class="redux-dev-mode-notice-container redux-dev-qtip"
				     qtip-title="<?php echo esc_attr( $tip_title ); ?>"
				     qtip-content="<?php echo esc_attr( $tip_msg ); ?>">
					<span class="redux-dev-mode-notice"><?php esc_html_e( 'FORCED DEV MODE OFF ENABLED', 'crane' ); ?></span>
				</div>

			<?php } ?>

			<h2><?php echo wp_kses_post( $this->parent->args['display_name'] ); ?></h2>
			<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/crane-green-white.svg" alt="crane logo" class="redux-theme-logo">
			<?php if ( $theme_version ) { ?>
				<span class="crane-theme-version"><?php echo wp_kses_post( $theme_version ); ?></span>
			<?php } ?>
			<a href="http://grooni.com/" class="redux-header-link">
				<img src="<?php echo get_template_directory_uri(); ?>/assets/images/wp/theme-by-grooni.svg" alt="grooni logo">
			</a>

		</div>
	<?php } ?>

	<div class="clear"></div>
</div>
