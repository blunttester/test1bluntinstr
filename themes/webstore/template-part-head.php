<div class="container rsrc-container" role="main">
	<?php
	if ( is_front_page() || is_home() || is_404() ) {
		$heading = 'h1';
		$desc	 = 'h2';
	} else {
		$heading = 'h2';
		$desc	 = 'h3';
	}
	?> 
	<?php if ( get_theme_mod( 'infobox-text-right', '' ) != '' || get_theme_mod( 'infobox-text-left', '' ) != '' ) : ?>
		<div class="top-section row"> 
			<div class="top-infobox text-left col-xs-6">
				<?php if ( get_theme_mod( 'infobox-text-left', '' ) != '' ) {
					echo wp_kses_post( get_theme_mod( 'infobox-text-left' ) );
				} ?> 
			</div> 
			<div class="top-infobox text-right col-xs-6">
			<?php if ( get_theme_mod( 'infobox-text-right', '' ) != '' ) {
				echo wp_kses_post( get_theme_mod( 'infobox-text-right' ) );
			} ?> 
			</div>               
		</div>
	<?php endif; ?>
	<div class="header-section row" >
		<?php // Site title/logo ?>
		<header id="site-header" class="col-md-12 text-center rsrc-header text-center" role="banner"> 
			<?php if ( get_theme_mod( 'header-logo', '' ) != '' ) : ?>
	        <div class="rsrc-header-img" itemprop="headline">
	            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><img src="<?php echo esc_url( get_theme_mod( 'header-logo' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" /></a>
	        </div>
			<?php else : ?>
	        <div class="rsrc-header-text">
	            <<?php echo esc_html( $heading ) ?> class="site-title" ><a itemprop="url" href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a></<?php echo esc_html( $heading ) ?>>
	            <<?php echo esc_html( $desc ) ?> class="site-desc" ><?php esc_attr( bloginfo( 'description' ) ); ?></<?php echo esc_html( $desc ) ?>>
	        </div>
			<?php endif; ?>   
		</header> 
	</div>
  <div class="rsrc-top-menu row" >
        <nav id="site-navigation" class="navbar navbar-inverse" role="navigation">
                <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-1-collapse">
                        <span class="sr-only"><?php esc_html_e('Toggle navigation','webstore'); ?></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </button>
                <header class="visible-xs-block responsive-title" role="banner"> 
                      <?php if (function_exists( 'has_custom_logo' ) && has_custom_logo( ) ) : ?>
          							<div class="rsrc-header-img menu-img text-left">
          								<?php	the_custom_logo( ); ?>
          							</div>
          						<?php else : ?>
                        <div class="rsrc-header-text menu-text">
                            <<?php echo esc_html( $heading ) ?> class="site-title"><a href="<?php echo esc_url( home_url( '/' ) ); ?>" title="<?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?>" rel="home"><?php echo esc_attr( get_bloginfo( 'name', 'display' ) ); ?></a></<?php echo esc_html( $heading ) ?>>
                        </div>
                    <?php endif; ?>   
                </header>
                </div>
                <?php
                wp_nav_menu( array(
                        'theme_location'    => 'main_menu',
                        'depth'             => 3,
                        'container'         => 'div',
                        'container_class'   => 'collapse navbar-collapse navbar-1-collapse',
                        'menu_class'        => 'nav navbar-nav menu-center',
                        'fallback_cb'       => 'wp_bootstrap_navwalker::fallback',
                        'walker'            => new wp_bootstrap_navwalker())
                );
                ?>
        </nav>
    </div>
  <?php if( get_theme_mod( 'search-bar-check', 1 ) == 1 && class_exists( 'WooCommerce' )) : ?> 
    <?php get_template_part('template-part', 'searchbar'); ?>
  <?php endif; ?>
  