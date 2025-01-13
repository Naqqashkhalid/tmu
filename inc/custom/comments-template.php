<?php
/**
 * Comment structure.
 *
 * @package green_entertainment
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

if ( ! function_exists( 'green_comment' ) ) {
	/**
	 * Template for comments and pingbacks.
	 * Used as a callback by wp_list_comments() for displaying the comments.
	 *
	 * @param object $comment The comment object.
	 * @param array  $args The existing args.
	 * @param int    $depth The thread depth.
	 */
	function green_comment( $comment, $args, $depth ) {
		if (get_comment_author_link() === 'Anonymous') return;
		$args['avatar_size'] = apply_filters( 'generate_comment_avatar_size', 50 );

		if ( 'pingback' === $comment->comment_type || 'trackback' === $comment->comment_type ) : ?>

		<li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
			<div class="comment-body">
				<?php esc_html_e( 'Pingback:', 'green_entertainment' ); ?> <?php comment_author_link(); ?> <?php edit_comment_link( __( 'Edit', 'green_entertainment' ), '<span class="edit-link">', '</span>' ); ?>
			</div>

		<?php else : ?>

		<li id="comment-<?php comment_ID(); ?>" <?php comment_class( empty( $args['has_children'] ) ? '' : 'parent' ); ?>>
			<article class="comment-body" id="div-comment-<?php comment_ID(); ?>">
				<footer class="comment-meta" aria-label="Comment meta">
					<?php
					if ( 0 != $args['avatar_size'] ) { // phpcs:ignore
						echo get_avatar( $comment, $args['avatar_size'] );
					}
					?>
					<div class="comment-author-info">
						<div class="comment-author">
							<?php printf( '<cite class="fn">%s</cite>', get_comment_author_link() ); ?>
							<?= display_comment_rating(get_comment_ID()) ?>
						</div>

						<?php
						/**
						 * green_after_comment_author_name hook.
						 *
						 * @since 3.1.0
						 */
						do_action( 'generate_after_comment_author_name' );

						if ( apply_filters( 'generate_show_comment_entry_meta', true ) ) :
							$has_comment_date_link = apply_filters( 'generate_add_comment_date_link', true );

							?>
							<div class="entry-meta comment-metadata">
								<?php
								if ( $has_comment_date_link ) {
									printf(
										'<a href="%s">',
										esc_url( get_comment_link( $comment->comment_ID ) )
									);
								}
								?>
									<time datetime="<?php comment_time( 'c' ); ?>">
										<?php
											printf(
												/* translators: 1: date, 2: time */
												_x( '%1$s at %2$s', '1: date, 2: time', 'green_entertainment' ), // phpcs:ignore
												get_comment_date(), // phpcs:ignore
												get_comment_time() // phpcs:ignore
											);
										?>
									</time>
								<?php
								if ( $has_comment_date_link ) {
									echo '</a>';
								}

								edit_comment_link( __( 'Edit', 'green_entertainment' ), '<span class="edit-link">| ', '</span>' );
								?>
							</div>
							<?php
						endif;
						?>
					</div>

					<?php if ( '0' == $comment->comment_approved ) : // phpcs:ignore ?>
						<p class="comment-awaiting-moderation"><?php esc_html_e( 'Your comment is awaiting moderation.', 'green_entertainment' ); ?></p>
					<?php endif; ?>
				</footer>

				<div class="comment-content">
					<?php
					/**
					 * green_before_comment_content hook.
					 *
					 * @since 2.4
					 */
					do_action( 'generate_before_comment_text', $comment, $args, $depth );

					comment_text();

					/**
					 * green_after_comment_content hook.
					 *
					 * @since 2.4
					 */
					do_action( 'generate_after_comment_text', $comment, $args, $depth );
					?>
				</div>
			</article>
			<?php
		endif;
	}
}

// add_action( 'generate_after_comment_text', 'green_do_comment_reply_link', 10, 3 );
// /**
//  * Add our comment reply link after the comment text.
//  *
//  * @since 2.4
//  * @param object $comment The comment object.
//  * @param array  $args The existing args.
//  * @param int    $depth The thread depth.
//  */
// function green_do_comment_reply_link( $comment, $args, $depth ) {
// 	comment_reply_link(
// 		array_merge(
// 			$args,
// 			array(
// 				'add_below' => 'div-comment',
// 				'depth'     => $depth,
// 				'max_depth' => $args['max_depth'],
// 				'before'    => '<span class="reply">',
// 				'after'     => '</span>',
// 			)
// 		)
// 	);
// }

add_filter( 'comment_form_defaults', 'green_set_comment_form_defaults' );
/**
 * Set the default settings for our comments.
 *
 * @since 2.3
 *
 * @param array $defaults The existing defaults.
 * @return array
 */
function green_set_comment_form_defaults( $defaults ) {
	$defaults['comment_field'] = sprintf(
		'<p class="comment-form-comment"><label for="comment" class="screen-reader-text">%1$s</label><textarea id="comment" name="comment" cols="45" rows="8" required></textarea></p>',
		esc_html__( 'Comment', 'green_entertainment' )
	);

	$defaults['comment_notes_before'] = '';
	$defaults['comment_notes_after']  = '';
	$defaults['id_form']              = 'commentform';
	$defaults['id_submit']            = 'submit';
	$defaults['title_reply']          = apply_filters( 'green_leave_comment', __( 'Leave a Comment', 'green_entertainment' ) );
	$defaults['label_submit']         = apply_filters( 'green_post_comment', __( 'Post Comment', 'green_entertainment' ) );

	return $defaults;
}

add_filter( 'comment_form_default_fields', 'green_filter_comment_fields' );
/**
 * Customizes the existing comment fields.
 *
 * @since 2.1.2
 * @param array $fields The existing fields.
 * @return array
 */
function green_filter_comment_fields( $fields ) {
	$commenter = wp_get_current_commenter();
	$required = get_option( 'require_name_email' );

	$fields['author'] = sprintf(
		'<label for="author" class="screen-reader-text">%1$s</label><input placeholder="%1$s%3$s" id="author" name="author" type="text" value="%2$s" size="30"%4$s />',
		esc_html__( 'Name', 'green_entertainment' ),
		esc_attr( $commenter['comment_author'] ),
		$required ? ' *' : '',
		$required ? ' required' : ''
	);

	$fields['email'] = sprintf(
		'<label for="email" class="screen-reader-text">%1$s</label><input placeholder="%1$s%3$s" id="email" name="email" type="email" value="%2$s" size="30"%4$s />',
		esc_html__( 'Email', 'green_entertainment' ),
		esc_attr( $commenter['comment_author_email'] ),
		$required ? ' *' : '',
		$required ? ' required' : ''
	);

	$fields['url'] = sprintf(
		'<label for="url" class="screen-reader-text">%1$s</label><input placeholder="%1$s" id="url" name="url" type="url" value="%2$s" size="30" />',
		esc_html__( 'Website', 'green_entertainment' ),
		esc_attr( $commenter['comment_author_url'] )
	);

	return $fields;
}

add_action( 'green_after_do_template_part', 'green_do_comments_template', 15 );
/**
 * Add the comments template to pages and single posts.
 *
 * @since 3.0.0
 * @param string $template The template we're targeting.
 */
function green_do_comments_template( $template ) {
	if ( 'single' === $template || 'page' === $template ) {
		// If comments are open or we have at least one comment, load up the comment template.
		// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison -- Intentionally loose.
		if ( comments_open() || '0' != get_comments_number() ) :
			/**
			 * green_before_comments_container hook.
			 *
			 * @since 2.1
			 */
			do_action( 'generate_before_comments_container' );
			?>

			<div class="comments-area">
				<?php comments_template(); ?>
			</div>

			<?php
		endif;
	}
}


function filter_comments_by_author_name( $comments ) {
    $filtered_comments = array();

    foreach ( $comments as $comment ) {
        if ( ! empty( $comment->comment_author ) ) {
            $filtered_comments[] = $comment;
        }
    }

    return $filtered_comments;
}
add_filter( 'comments_array', 'filter_comments_by_author_name' );
