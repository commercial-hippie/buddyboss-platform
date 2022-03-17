<?php
/**
 * Template for displaying the search results of the courses ajax
 *
 * This template can be overridden by copying it to yourtheme/buddypress/search/loop/sfwd-courses-ajax.php.
 *
 * @package BuddyBoss\Core
 * @since   BuddyBoss 1.0.0
 * @version 1.0.0
 */

$total     = bp_search_get_total_lessons_count( get_the_ID() );
$course_id = get_the_ID();  ?>
<div class="bp-search-ajax-item bp-search-ajax-item_sfwd-courses">
	<a href="<?php echo esc_url( add_query_arg( array( 'no_frame' => '1' ), get_permalink() ) ); ?>">
		<div class="item-avatar">
			<img
				src="<?php echo get_the_post_thumbnail_url() ?: bp_search_get_post_thumbnail_default( get_post_type() ); ?>"
				class="attachment-post-thumbnail size-post-thumbnail wp-post-image"
				alt="<?php the_title(); ?>"
			/>
		</div>

		<div class="item">
			<div class="item-title"><?php the_title(); ?></div>
			<div class="item-desc">
				<?php
				if ( get_the_excerpt( $course_id ) ) {
					echo mb_strimwidth( get_the_excerpt( $course_id ), 0, 100 ) . '...';
				}
				?>

			</div>
			<div class="entry-meta">

				<?php if ( ! empty( learndash_course_status( $course_id ) ) ) : ?>
					<span class="course-status">
						<?php echo learndash_course_status( $course_id, null, false ); ?>
					</span>
				<?php endif; ?>
				
				<span class="middot">&middot;</span>

				<?php
				// @todo remove %d?
				printf( _n( '%d lesson', '%d lessons', $total, 'buddyboss' ), $total );
				?>
			</div>

		</div>
	</a>
</div>
