<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * The template for displaying comments.
 *
 * This is the template that displays the area of the page that contains both the current comments
 * and the comment form.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package crane
 */

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

$commenter = wp_get_current_commenter();
$req       = get_option( 'require_name_email' );
$aria_req  = ( $req ? " aria-required='true'" : '' );
$html_req  = ( $req ? " required='required'" : '' );
$html5     = 'html5' === current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
$consent   = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';

$fields = array(
	'author' => '<p class="comment-form-author"><input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" maxlength="245" ' . $aria_req . $html_req . ' placeholder="' . esc_html__( 'Name*', 'crane' ) . '"/> </p>',
	'email'  => '<p class="comment-form-email"><input id="email" name="email" '
              . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" maxlength="100" aria-describedby="email-notes" ' . $aria_req . $html_req . ' placeholder="' . esc_html__( 'Email*', 'crane' ) . '"/></p>',
	'url'    => '<p class="comment-form-url"><input id="url" name="url" ' . ( $html5 ? 'type="url"' : 'type="text"' ) . ' value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" maxlength="200" placeholder="' . esc_html__( 'Website', 'crane' ) . '"/></p>',
);

global $wp_version;
if ( version_compare( $wp_version, '4.9.6', '>=' ) ) {
	$fields['cookies'] = '<p class="comment-form-cookies-consent"><input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' .
	                     '<label for="wp-comment-cookies-consent">' . esc_html( 'Save my name, email, and website in this browser for the next time I comment.', 'crane' ) . '</label></p>';

}

$comments_args = array(
	'label_submit'         => esc_html__( 'Submit', 'crane' ),
	'title_reply'          => esc_html__( 'Leave a Reply', 'crane' ),
	'fields'               => apply_filters( 'comment_form_default_fields', $fields ),
	'comment_field'        => '<p class="comment-form-comment"><textarea id="comment" name="comment" cols="45" rows="8" maxlength="65525" aria-required="true" required="required" placeholder="' . esc_attr_x( 'Comment*', 'noun', 'crane' ) . '"></textarea></p><div class="crane-comment-fields">',
	'submit_field' => '</div><p class="form-submit">%1$s %2$s</p>',
);


if ( ( get_comments_number() || comments_open() ) && in_array( crane_page_type(), [ 'front', 'page' ] ) ) {
	echo '<section class="crane-section"><div class="crane-container">';
}


if ( get_comments_number() ) {
	?>
    <div id="comments">
		<?php wp_list_comments( array(
			'walker'       => new Crane_Walker_Comment,
			'style'        => '',
			'callback'     => null,
			'end-callback' => null,
			'type'         => 'all',
			'page'         => null,
		) ); ?>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
            <div class="crane-comments-pagination">
				<?php previous_comments_link() ?>
				<?php next_comments_link() ?>
            </div>
		<?php endif; ?>
    </div>
	<?php
}

if ( comments_open() ) {
	comment_form( $comments_args );
}


if ( ( get_comments_number() || comments_open() ) && in_array( crane_page_type(), [ 'front', 'page' ] ) ) {
	echo '</div></section>';
}

