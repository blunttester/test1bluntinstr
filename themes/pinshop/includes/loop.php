<?php if(!is_single()){ global $more; $more = 0; } //enable more link ?>

<?php
/** Themify Default Variables
 *  @var object */
global $themify; ?>

<?php themify_post_before(); //hook ?>
<article id="post-<?php the_id(); ?>" <?php post_class("post clearfix " . $themify->get_categories_as_classes(get_the_id())); ?>>

	<div class="post-inner">
		<?php themify_post_start(); //hook ?>

		<div class="post-content">

			<?php if($themify->hide_title != 'yes'): ?>
				<?php themify_post_title(); ?>
			<?php endif; //post title ?>

			<?php if($themify->hide_date != 'yes'): ?>
				<p class="post-date-wrap">
					<time datetime="<?php the_time('o-m-d') ?>" class="post-date entry-date updated">
						<?php if ( 'list-post' == $themify->post_layout ) : ?>
							<span class="day"><?php the_time('j'); ?></span>
							<span class="month"><?php the_time('M'); ?></span>
							<span class="year"><?php the_time('Y'); ?></span>
						<?php else : ?>
							<?php echo get_the_date( apply_filters( 'themify_loop_date', '' ) ) ?>
						<?php endif; ?>
					</time>
				</p>
			<?php endif; //post date ?>

			<?php if($themify->hide_meta != 'yes'): ?>
				<p class="post-meta entry-meta">
					<span class="post-author"><?php echo themify_get_author_link() ?> <span class="separator">/<span></span>
                                        <?php $terms = get_the_term_list( get_the_id(), 'category', '<span class="post-category">', ', ', ' <span class="separator">/<span></span>' );
                                            if(!is_wp_error($terms)){
                                                    echo $terms;
                                            }
                                        ?>
					<?php the_tags(' <span class="post-tag">', ', ', ' <span class="separator">/<span></span>'); ?>
					<?php  if( !themify_get('setting-comments_posts') && comments_open() ) : ?>
						<span class="post-comment"><?php comments_popup_link( __( '0 Comments', 'themify' ), __( '1 Comment', 'themify' ), __( '% Comments', 'themify' ) ); ?></span>
					<?php endif; //post comment ?>
				</p>
			<?php endif; //post meta ?>

			<?php if ( $themify->hide_image != 'yes' ) : ?>
				<?php themify_before_post_image(); // Hook ?>

				<?php if ( themify_has_post_video() ) : ?>

					<?php echo themify_post_video(); ?>

				<?php elseif( $post_image = themify_get_image($themify->auto_featured_image . $themify->image_setting . "w=".$themify->width."&h=".$themify->height ) ) : ?>

					<figure class="post-image <?php echo $themify->image_align; ?>">
						<?php if( 'yes' == $themify->unlink_image): ?>
							<?php echo $post_image; ?>
						<?php else: ?>
							<a href="<?php echo themify_get_featured_image_link(); ?>"><?php echo $post_image; ?><?php themify_zoom_icon(); ?></a>
						<?php endif; // unlink image ?>
					</figure>

				<?php endif; // video else image ?>

				<?php themify_after_post_image(); // Hook ?>
			<?php endif; // hide image ?>

			<div class="entry-content">

			<?php if ( 'excerpt' == $themify->display_content && ! is_attachment() ) : ?>

				<?php the_excerpt(); ?>

			<?php if( themify_check('setting-excerpt_more') ) : ?>
				<p><a href="<?php the_permalink(); ?>" class="more-link"><?php echo themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify') ?></a></p>
			<?php endif; ?>

			<?php elseif ( 'none' == $themify->display_content && ! is_attachment() ) : ?>

			<?php else: ?>

				<?php the_content(themify_check('setting-default_more_text')? themify_get('setting-default_more_text') : __('More &rarr;', 'themify')); ?>

			<?php endif; //display content ?>

			</div><!-- /.entry-content -->

			<?php edit_post_link(__('Edit', 'themify'), '<span class="edit-button">[', ']</span>'); ?>

		</div>
		<!-- /.post-content -->
		<?php themify_post_end(); //hook ?>

	</div>

</article>
<!-- /.post -->
<?php themify_post_after(); //hook ?>
