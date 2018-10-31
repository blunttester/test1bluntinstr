<?php
/**
 *
 * Template name: Homepage with searchbar
 * The template for displaying homepage.
 *
 */
get_header();
?>

<?php get_template_part( 'template-part', get_theme_mod( 'header-style', 'head' ) ); ?>

<!-- start content container -->
<div class="row">
	<?php if ( has_post_thumbnail() && class_exists( 'WooCommerce' ) ) { ?>
		<div class="top-area no-gutter searchbar-template" style="background-image: url(<?php the_post_thumbnail_url( 'maxstore-slider' ); ?>)">
				<h1 class="header-search-text col-xs-8 col-xs-push-2">
					<?php the_title(); ?>
				</h1>
			<div class="header-search-form col-xs-8 col-xs-push-2">
				<form role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
					<input type="hidden" name="post_type" value="product" />
					<input class="col-xs-12" name="s" type="text" placeholder="<?php esc_attr_e( 'Search for products', 'webstore' ); ?>"/>
					<button type="submit">
            <i class="fa fa-search" aria-hidden="true"></i>
          </button>
				</form>
			</div>
		</div>
	<?php } ?>   
	<div class="rsrc-home" >        
		<?php if ( have_posts() ) : while ( have_posts() ) : the_post(); ?>                                
				<div <?php post_class( 'rsrc-post-content' ); ?>>                                                      
					<div class="entry-content">                       
						<?php the_content(); ?>                            
					</div>                                                       
				</div>        
			<?php endwhile; ?>        
		<?php else: ?>            
			<?php get_404_template(); ?>        
		<?php endif; ?>    
	</div>
</div>
<!-- end content container -->
<?php get_footer(); ?>