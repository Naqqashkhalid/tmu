<?php
/**
 * The template for displaying Comments.
 *
 * The area of the page that contains both current comments
 * and the comment form. The actual display of comments is
 * handled by a callback to generate_comment() which is
 * located in the inc/template-tags.php file.
 *
 * @package GeneratePress
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/*
 * If the current post is protected by a password and
 * the visitor has not yet entered the password we will
 * return early without loading the comments.
 */
if ( post_password_required() ) {
	return;
}

/**
 * generate_before_comments hook.
 *
 * @since 0.1
 */
do_action( 'generate_before_comments' );
?>
<div id="comments">

	<?php
	comment_form();
	/**
	 * generate_inside_comments hook.
	 *
	 * @since 1.3.47
	 */
	do_action( 'generate_inside_comments' );
	
	if ( have_comments() ) :
        $comments_number = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*)
             FROM $wpdb->comments
             WHERE comment_post_ID = %d
               AND comment_approved = '1'
               AND comment_content != ''",
                get_the_ID()));
		$comments_title = sprintf( '<span class="title">'.(is_singular( 'movie' ) || is_singular( 'tv' ) || is_singular( 'episode' ) ? 'Reviews' : 'Comments').'</span><span class="count-comments">(%1$s)</span>', $comments_number );

		// phpcs:ignore -- Title escaped in output.
		echo apply_filters(
			'generate_comments_title_output',
			sprintf(
				'<h3 class="comments-title short-heading font-size-36 weight-700">%s</h3>',
				$comments_title
			),
			$comments_title,
			$comments_number
		);

		/**
		 * generate_below_comments_title hook.
		 *
		 * @since 0.1
		 */
		do_action( 'generate_below_comments_title' );

		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			?>
			<nav id="comment-nav-above" class="comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'generatepress' ); ?></h2>
				<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'generatepress' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'generatepress' ) ); ?></div>
			</nav><!-- #comment-nav-above -->
		<?php endif; ?>

		<ol class="comment-list">
			<?php
			/*
			 * Loop through and list the comments. Tell wp_list_comments()
			 * to use generate_comment() to format the comments.
			 * If you want to override this in a child theme, then you can
			 * define generate_comment() and that will be used instead.
			 * See generate_comment() in inc/template-tags.php for more.
			 */
			wp_list_comments(
				array(
					'callback' => 'green_comment',
				)
			);
			?>
		</ol><!-- .comment-list -->

		<?php
		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) :
			?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'generatepress' ); ?></h2>
				<div class="nav-previous"><?php previous_comments_link( __( '&larr; Older Comments', 'generatepress' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( __( 'Newer Comments &rarr;', 'generatepress' ) ); ?></div>
			</nav><!-- #comment-nav-below -->
			<?php
		endif;

	endif;

	// phpcs:ignore WordPress.PHP.StrictComparisons.LooseComparison
	if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) :
		?>
		<p class="no-comments"><?php esc_html_e( 'Comments are closed.', 'generatepress' ); ?></p>
		<?php
	endif;
	?>

</div><!-- #comments -->