<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Template part for displaying author info block
 *
 * @package crane
 */


global $post;

if ( is_single() && isset( $post->post_author ) ) :

	$post_author          = $post->post_author;
	$user_first_name      = get_the_author_meta( 'first_name', $post_author );
	$user_last_name       = get_the_author_meta( 'last_name', $post_author );
	$user_description_esc = esc_textarea( get_the_author_meta( 'user_description', $post_author ) );
	$user_website_esc     = esc_url( get_the_author_meta( 'user_url', $post_author ) );

	// Collect user social media links
	$contact_methods = wp_get_user_contact_methods();
	foreach ( $contact_methods as $method => $data ) {
		$contact_methods[ $method ] = get_user_meta( $post_author, $method, true );
		if ( empty( $contact_methods[ $method ] ) ) {
			unset( $contact_methods[ $method ] );
		}
	}

	// Link to the author archive page (not in use)
	$display_name_esc = get_the_author_meta( 'display_name', $post_author ) ? get_the_author_meta( 'display_name', $post_author ) : get_the_author_meta( 'user_nicename', $post_author );
	$display_name_esc = esc_html( $display_name_esc );

	$display_author_box = false;
	if ( $user_description_esc || $user_website_esc || ! empty( $contact_methods ) ) {
		$display_author_box = true;
	}
	if ( $user_first_name || $user_last_name || ( $display_name_esc !== get_the_author_meta( 'user_nicename', $post_author ) ) ) {
		$display_author_box = true;
	}


	if ( $display_author_box ) :
		?>

		<div class="crane-author-info">
			<div class="crane-author-info__avatar">
				<?php echo get_avatar( get_the_author_meta( 'user_email', $post_author ), '80', '' ); ?>
			</div>
			<div class="crane-author-info__bio">
				<?php if ( $display_name_esc ) : ?>
					<div class="crane-author-info__name">
						<?php echo crane_clear_echo( $display_name_esc ); ?>
					</div>
				<?php endif; ?>
				<?php if ( $user_website_esc ) : ?>
					<a class="crane-author-info__website" href="<?php echo crane_clear_echo( $user_website_esc ); ?>" target="_blank"
					   rel="nofollow" title="<?php esc_attr_e( 'Visit poster&#39;s website', 'crane' ); ?>">
						<?php echo crane_clear_echo( $user_website_esc ); ?>
					</a>
				<?php endif; ?>
				<p class="crane-author__txt">
					<?php if ( $user_description_esc ) {
						echo crane_clear_echo( $user_description_esc );
					} ?>
				</p>
				<?php if ( ! empty( $contact_methods ) ) : ?>
					<div class="crane-author-info__socials">
						<?php foreach ( $contact_methods as $method => $data ) : ?>
							<a class="crane-author-info__social-link crane-sm-<?php echo esc_attr( $method ); ?>"
							   href="<?php echo esc_url( $data ); ?>"
							   target="_blank" rel="nofollow">
								<img class="crane-social-media-icon" height="32" width="32"
								     src="<?php echo get_template_directory_uri() . '/assets/images/socialmedia/' . esc_attr( $method ) . '.png'; ?>"
								     alt="<?php echo esc_attr( $method ); ?>">
							</a>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
		</div>

		<?php

	endif;
endif;
