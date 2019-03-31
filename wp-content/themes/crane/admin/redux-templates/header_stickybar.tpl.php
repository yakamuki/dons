<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for the header sticky bar.
 *
 * @author   Redux Framework
 * @author   Grooni
 * @package  Crane
 * @version: 3.5.7.8
 */
?>
<div id="redux-sticky">
	<div id="info_bar">

		<a href="javascript:void(0);"
		   class="expand_options<?php echo esc_attr( ( $this->parent->args['open_expanded'] ) ? ' expanded' : '' ); ?><?php echo ( $this->parent->args['hide_expand'] ? ' hidden' : '' ); ?>">
			<i class="fa fa-indent"></i>
		</a>

		<div class="redux-action_bar">
			<span class="spinner"></span>
			<?php if ( false === $this->parent->args['hide_save'] ) { ?>
				<?php submit_button( esc_attr__( 'Save Changes', 'crane' ), 'primary', 'redux_save', false ); ?>
			<?php } ?>

			<?php if ( false === $this->parent->args['hide_reset'] ) { ?>
				<?php submit_button( esc_attr__( 'Reset Section', 'crane' ), 'secondary', $this->parent->args['opt_name'] . '[defaults-section]', false, array( 'id' => 'redux-defaults-section' ) ); ?>
				<?php submit_button( esc_attr__( 'Reset All', 'crane' ), 'secondary', $this->parent->args['opt_name'] . '[defaults]', false, array( 'id' => 'redux-defaults' ) ); ?>
			<?php } ?>
		</div>
		<div class="redux-ajax-loading" alt="<?php esc_attr_e( 'Working...', 'crane' ) ?>">&nbsp;</div>
		<div class="clear"></div>
	</div>

	<!-- Notification bar -->
	<div id="redux_notification_bar">
		<?php $this->notification_bar(); ?>
	</div>

</div>
