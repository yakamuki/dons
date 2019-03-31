<?php defined( 'ABSPATH' ) OR die( 'This script cannot be accessed directly.' );
/**
 * Comments walker. Show comments by crane theme style.
 *
 * @package crane
 */


if ( ! class_exists( 'Crane_Walker_Comment' ) ) {
	/** COMMENTS WALKER */
	class Crane_Walker_Comment extends Walker_Comment {

		// init classwide variables
		var $tree_type = 'comment';
		var $db_fields = array( 'parent' => 'comment_parent', 'id' => 'comment_ID' );

		/** CONSTRUCTOR
		 * You'll have to use this if you plan to get to the top of the comments list, as
		 * start_lvl() only goes as high as 1 deep nested comments */
		function __construct() {

			$comments_text = sprintf(
				_n( '%s Comment', '%s Comments', get_comments_number(), 'crane' ),
				number_format_i18n( get_comments_number() )
			);

			echo '<h4 class="comments-total">' . $comments_text . '</h4>';
		}

		/** START_LVL
		 * Starts the list before the CHILD elements are added.
		 */
		function start_lvl( &$output, $depth = 0, $args = array() ) {
			$GLOBALS['comment_depth'] = $depth + 1;
			echo '<div class="children">';
		}

		/** END_LVL
		 * Ends the children list of after the elements are added.
		 */
		function end_lvl( &$output, $depth = 0, $args = array() ) {
			$GLOBALS['comment_depth'] = $depth + 1;
			echo '</div><!-- /.children -->';
		}

		/** START_EL */
		public function start_el( &$output, $comment, $depth = 0, $args = array(), $id = 0 ) {
			$depth ++;
			$GLOBALS['comment_depth'] = $depth;
			$GLOBALS['comment']       = $comment;
			$is_approved              = $comment->comment_approved ? true : false;
			$not_approved_class       = $is_approved ? [ ] : [ 'crane-comment-not-approved' ];
			?>
			<div id="comment-<?php comment_ID() ?>" <?php comment_class( $not_approved_class ); ?>>
				<img src="<?php echo get_avatar_url( $comment->comment_author_email, [ 'size' => 64 ] ); ?>" alt="avatar" class="avatar">

				<div class="comment-body">
					<div class="comment-metadata">
						<span class="comment-author"><?php comment_author(); ?></span>
						<span class="comment-date"><?php comment_date(); ?> <?php comment_time(); ?></span>
					</div>
					<div class="comment-content">
						<?php comment_text(); ?>
					</div>
					<div class="comment-bottom">
						
						<div class="comment-button-group">
							<?php $reply_args = array(
								'depth'     => $depth,
								'max_depth' => $args['max_depth'],
							);

							if ( $is_approved ) {
								comment_reply_link( array_merge( $args, $reply_args ) );
							}

							edit_comment_link();

							?>
						</div>
						<?php if ( ! $is_approved ) : ?>
							<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'crane' ); ?></p>
						<?php endif; ?>
					</div>
				</div>
			</div>
			<?php
		}

		function end_el( &$output, $comment, $depth = 0, $args = array() ) {
			echo '<!-- /#comment-' . get_comment_ID() . ' -->';
		}

	}
}
