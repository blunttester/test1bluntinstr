<?php if( is_active_sidebar( 'maxstore-footer-area' ) ) { ?>  				
  <div id="content-footer-section" class="row clearfix">    				
    <?php
      // Calling the header sidebar if it exists.
      dynamic_sidebar( 'maxstore-footer-area' );
    ?>  				
  </div>		
<?php } ?>         
<footer id="colophon" class="rsrc-footer" role="contentinfo">                
  <div class="row rsrc-author-credits">                    
    <p class="footer-credits-text text-center">
			<?php 
      /* translators: %s WordPress string and URL */
      printf( esc_html__( 'Proudly powered by %s', 'webstore' ), '<a href="' . esc_url( __( 'https://wordpress.org/', 'webstore' ) ) . '">WordPress</a>' ); 
      ?>
			<span class="sep"> | </span>
			<?php 
      /* translators: %1$s Theme name string and URL */
      printf( esc_html__( 'Theme: %1$s', 'webstore' ), '<a href="http://themes4wp.com/theme/webstore/">WebStore</a>' ); 
      ?>
		</p>                
  </div>    
</footer>
<div id="back-top">  
  <a href="#top">
    <span></span>
  </a>
</div>
<?php if ( function_exists( 'maxstore_header_cart' ) && class_exists( 'WooCommerce' ) ) { ?> 
	<div class="float-cart header-cart text-center">
		<?php maxstore_header_cart(); ?>
		<?php if ( get_theme_mod( 'my-account-link', 1 ) == 1 ) { ?>
			<a class="float-account" href="<?php echo esc_url( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) ); ?>" title="<?php esc_attr_e( 'My Account', 'webstore' ); ?>" data-toggle="tooltip" data-placement="top">
        <i class="fa fa-user" aria-hidden="true"></i>
      </a>
		<?php } ?>
	</div>
<?php } ?>
</div>
<!-- end main container -->
<?php wp_footer(); ?>
</body>
</html>