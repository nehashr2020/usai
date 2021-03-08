<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * After setup theme hook
 */
function blossom_pinthis_theme_setup(){
    /*
     * Make chile theme available for translation.
     * Translations can be filed in the /languages/ directory.
     */
    load_child_theme_textdomain( 'blossom-pinthis', get_stylesheet_directory() . '/languages' );

    add_image_size( 'blossom-pinthis-slider', 1303, 650, true );
    add_image_size( 'blossom-pinthis-blog', 1200, 1500, true );

}
add_action( 'after_setup_theme', 'blossom_pinthis_theme_setup' );

function blossom_pinthis_styles() {
    $my_theme = wp_get_theme();
	$version = $my_theme['Version'];

	if( blossom_pin_is_woocommerce_activated() ){
        $dependencies = array( 'blossom-pin-woocommerce', 'owl-carousel', 'blossom-pin-google-fonts' );  
    }else{
        $dependencies = array( 'owl-carousel', 'blossom-pin-google-fonts' );
    }

    wp_enqueue_style( 'blossom-pinthis-parent-style', get_template_directory_uri() . '/style.css', $dependencies );

	wp_enqueue_script( 'blossom-pinthis', get_stylesheet_directory_uri() . '/js/custom.js', array('jquery', 'owl-carousel' ), $version, true );
    
    $array = array( 
        'rtl'       => is_rtl(),
    ); 
    wp_localize_script( 'blossom-pinthis', 'blossom_pinthis_data', $array );
    
}
add_action( 'wp_enqueue_scripts', 'blossom_pinthis_styles', 10 );

function blossom_pin_body_classes( $classes ) {
    global $wp_query;
    $blog_layout = get_theme_mod( 'blog_layout_option', 'home-two' );

    // Adds a class of hfeed to non-singular pages.
    if ( ! is_singular() ) {
        $classes[] = 'hfeed';
    }
    
    if ( $wp_query->found_posts == 0 ) {
        $classes[] = 'no-post';
    }

    // Adds a class of custom-background-image to sites with a custom background image.
    if ( get_background_image() ) {
        $classes[] = 'custom-background-image';
    }
    
    // Adds a class of custom-background-color to sites with a custom background color.
    if ( get_background_color() != 'ffffff' ) {
        $classes[] = 'custom-background-color';
    }

    if( is_home() && $blog_layout == 'home-two' ){
        $classes[] = 'layout-two-right-sidebar';
    }
    
    $classes[] = blossom_pin_sidebar( true );
    
    return $classes;
}

//Remove a function from the parent theme
function blossom_pinthis_remove_parent_filters(){ //Have to do it after theme setup, because child theme functions are loaded first
    remove_action( 'customize_register', 'blossom_pin_customizer_theme_info' );
    remove_action( 'customize_register', 'blossom_pin_customize_register_color' );
    remove_action( 'customize_register', 'blossom_pin_customize_register_appearance' );
}
add_action( 'init', 'blossom_pinthis_remove_parent_filters' );

function blossom_pinthis_customizer_register( $wp_customize ) {
    
    $wp_customize->add_section( 'theme_info', array(
		'title'       => __( 'Demo & Documentation' , 'blossom-pinthis' ),
		'priority'    => 6,
	) );
    
    /** Important Links */
	$wp_customize->add_setting( 'theme_info_theme',
        array(
            'default' => '',
            'sanitize_callback' => 'wp_kses_post',
        )
    );
    
    $theme_info = '<p>';
	$theme_info .= sprintf( __( 'Demo Link: %1$sClick here.%2$s', 'blossom-pinthis' ),  '<a href="' . esc_url( 'https://blossomthemes.com/theme-demo/?theme=blossom-pinthis' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p><p>';
    $theme_info .= sprintf( __( 'Documentation Link: %1$sClick here.%2$s', 'blossom-pinthis' ),  '<a href="' . esc_url( 'https://docs.blossomthemes.com/docs/blossom-pinthis/' ) . '" target="_blank">', '</a>' );
    $theme_info .= '</p>';

	$wp_customize->add_control( new Blossom_Pin_Note_Control( $wp_customize,
        'theme_info_theme', 
            array(
                'section'     => 'theme_info',
                'description' => $theme_info
            )
        )
    );
    
    /** Primary Color*/
    $wp_customize->add_setting( 
        'primary_color', array(
            'default'           => '#e7475e',
            'sanitize_callback' => 'sanitize_hex_color'
        ) 
    );

    $wp_customize->add_control( 
        new WP_Customize_Color_Control( 
            $wp_customize, 
            'primary_color', 
            array(
                'label'       => __( 'Primary Color', 'blossom-pinthis' ),
                'description' => __( 'Primary color of the theme.', 'blossom-pinthis' ),
                'section'     => 'colors',
                'priority'    => 5,                
            )
        )
    );

    /** Appearance Settings */
    $wp_customize->add_panel( 
        'appearance_settings',
         array(
            'priority'    => 50,
            'capability'  => 'edit_theme_options',
            'title'       => __( 'Appearance Settings', 'blossom-pinthis' ),
            'description' => __( 'Customize Typography & Background Image', 'blossom-pinthis' ),
        ) 
    );
    
    /** Typography */
    $wp_customize->add_section(
        'typography_settings',
        array(
            'title'    => __( 'Typography', 'blossom-pinthis' ),
            'priority' => 15,
            'panel'    => 'appearance_settings',
        )
    );
    
    /** Primary Font */
    $wp_customize->add_setting(
        'primary_font',
        array(
            'default'           => 'Nunito Sans',
            'sanitize_callback' => 'blossom_pin_sanitize_select'
        )
    );

    $wp_customize->add_control(
        new Blossom_Pin_Select_Control(
            $wp_customize,
            'primary_font',
            array(
                'label'       => __( 'Primary Font', 'blossom-pinthis' ),
                'description' => __( 'Primary font of the site.', 'blossom-pinthis' ),
                'section'     => 'typography_settings',
                'choices'     => blossom_pin_get_all_fonts(),   
            )
        )
    );
    
    /** Secondary Font */
    $wp_customize->add_setting(
        'secondary_font',
        array(
            'default'           => 'Spectral',
            'sanitize_callback' => 'blossom_pin_sanitize_select'
        )
    );

    $wp_customize->add_control(
        new Blossom_Pin_Select_Control(
            $wp_customize,
            'secondary_font',
            array(
                'label'       => __( 'Secondary Font', 'blossom-pinthis' ),
                'description' => __( 'Secondary font of the site.', 'blossom-pinthis' ),
                'section'     => 'typography_settings',
                'choices'     => blossom_pin_get_all_fonts(),   
            )
        )
    );
    
    /** Font Size*/
    $wp_customize->add_setting( 
        'font_size', 
        array(
            'default'           => 18,
            'sanitize_callback' => 'blossom_pin_sanitize_number_absint'
        ) 
    );
    
    $wp_customize->add_control(
        new Blossom_Pin_Slider_Control( 
            $wp_customize,
            'font_size',
            array(
                'section'     => 'typography_settings',
                'label'       => __( 'Font Size', 'blossom-pinthis' ),
                'description' => __( 'Change the font size of your site.', 'blossom-pinthis' ),
                'choices'     => array(
                    'min'   => 10,
                    'max'   => 50,
                    'step'  => 1,
                )                 
            )
        )
    );
    
    /** Move Background Image section to appearance panel */
    $wp_customize->get_section( 'background_image' )->panel    = 'appearance_settings';
    $wp_customize->get_section( 'background_image' )->priority = 10;

    /** Blog Layout */
    $wp_customize->add_section(
        'header_layout',
        array(
            'title'    => __( 'Header Layout', 'blossom-pinthis' ),
            'panel'    => 'layout_settings',
            'priority' => 10,
        )
    );
    
    /** Blog Page layout */
    $wp_customize->add_setting( 
        'header_layout_option', 
        array(
            'default'           => 'two',
            'sanitize_callback' => 'esc_attr'
        ) 
    );
    
    $wp_customize->add_control(
        new Blossom_Pin_Radio_Image_Control(
            $wp_customize,
            'header_layout_option',
            array(
                'section'     => 'header_layout',
                'label'       => __( 'Header Layout', 'blossom-pinthis' ),
                'description' => __( 'This is the layout for header.', 'blossom-pinthis' ),
                'choices'     => array(                 
                    'one'   => get_stylesheet_directory_uri() . '/images/header/one.jpg',
                    'two'   => get_stylesheet_directory_uri() . '/images/header/two.jpg',
                )
            )
        )
    );
    
    /** Blog Layout */
    $wp_customize->add_section(
        'blog_layout',
        array(
            'title'    => __( 'Home Page Layout', 'blossom-pinthis' ),
            'panel'    => 'layout_settings',
            'priority' => 10,
        )
    );
    
    /** Blog Page layout */
    $wp_customize->add_setting( 
        'blog_layout_option', 
        array(
            'default'           => 'home-two',
            'sanitize_callback' => 'esc_attr'
        ) 
    );
    
    $wp_customize->add_control(
        new Blossom_Pin_Radio_Image_Control(
            $wp_customize,
            'blog_layout_option',
            array(
                'section'     => 'blog_layout',
                'label'       => __( 'Home Page Layout', 'blossom-pinthis' ),
                'description' => __( 'This is the layout for blog index page.', 'blossom-pinthis' ),
                'choices'     => array(                 
                    'home-one'   => get_stylesheet_directory_uri() . '/images/home/one-right.jpg',
                    'home-two'   => get_stylesheet_directory_uri() . '/images/home/two-right.jpg',
                )
            )
        )
    );
    
    /** Blog Layout */
    $wp_customize->add_section(
        'archive_layout',
        array(
            'title'    => __( 'Archive Layout', 'blossom-pinthis' ),
            'panel'    => 'layout_settings',
            'priority' => 15,
        )
    );

    /** Archive Page layout */
    $wp_customize->add_setting( 
        'archive_layout_option', 
        array(
            'default'           => 'archive-two',
            'sanitize_callback' => 'esc_attr'
        ) 
    );
    
    $wp_customize->add_control(
        new Blossom_Pin_Radio_Image_Control(
            $wp_customize,
            'archive_layout_option',
            array(
                'section'     => 'archive_layout',
                'label'       => __( 'Archive Page Layout', 'blossom-pinthis' ),
                'description' => __( 'This is the layout for archive and search page.', 'blossom-pinthis' ),
                'choices'     => array(                 
                    'archive-one'   => get_stylesheet_directory_uri() . '/images/archive/one-right.jpg',
                    'archive-two'   => get_stylesheet_directory_uri() . '/images/archive/two-right.jpg',
                )
            )
        )
    );

    /** Slider Layout Settings */
    $wp_customize->add_section(
        'slider_layout_settings',
        array(
            'title'    => __( 'Slider Layout', 'blossom-pinthis' ),
            'priority' => 20,
            'panel'    => 'layout_settings',
        )
    );
    
    /** Page Sidebar layout */
    $wp_customize->add_setting( 
        'slider_layout', 
        array(
            'default'           => 'two',
            'sanitize_callback' => 'esc_attr'
        ) 
    );
    
    $wp_customize->add_control(
        new Blossom_Pin_Radio_Image_Control(
            $wp_customize,
            'slider_layout',
            array(
                'section'     => 'slider_layout_settings',
                'label'       => __( 'Slider Layout', 'blossom-pinthis' ),
                'description' => __( 'Choose the layout of the slider for your site.', 'blossom-pinthis' ),
                'choices'     => array(
                    'one'   => get_stylesheet_directory_uri() . '/images/slider/one.jpg',
                    'two'   => get_stylesheet_directory_uri() . '/images/slider/two.jpg',
                )
            )
        )
    );
    
}
add_action( 'customize_register', 'blossom_pinthis_customizer_register', 40 );

function blossom_pin_header(){ 
    $header_layout = get_theme_mod( 'header_layout_option', 'two' ); ?>
    <header id="masthead" class="site-header header-layout-<?php echo esc_attr( $header_layout ); ?>" itemscope itemtype="http://schema.org/WPHeader">
        <?php if( $header_layout == 'two' ) : ?>
            <div class="container">
                <div class="header-t">
                    <?php 
                        if( blossom_pin_social_links( false, false ) ){
                            echo '<span class="separator"></span>';
                            blossom_pin_social_links( true, false );
                        }
        endif; ?>
        <?php blossom_pin_site_branding(); ?>
        <?php if( $header_layout == 'two' ) :
                    get_search_form(); ?>
                </div> <!-- header-t -->
            </div><!-- .container -->
            <div class="header-b">
                <div class="overlay"></div>
        <?php endif; ?>    
        <nav id="site-navigation" class="main-navigation" itemscope itemtype="http://schema.org/SiteNavigationElement">
            <?php
                wp_nav_menu( array(
                    'theme_location' => 'primary',
                    'menu_id'        => 'primary-menu',
                    'fallback_cb'    => 'blossom_pin_primary_menu_fallback',
                ) );
            ?>
        </nav><!-- #site-navigation -->
        <?php if( $header_layout == 'two' ) : ?>
            </div> <!-- .header-b -->                    
        <?php endif;
        if( $header_layout == 'header-one' ) : ?>                      
            <div class="tools">
                <div class="search-icon">
                    <svg class="open-icon" xmlns="http://www.w3.org/2000/svg" viewBox="-18214 -12091 18 18"><path id="Path_99" data-name="Path 99" d="M18,16.415l-3.736-3.736a7.751,7.751,0,0,0,1.585-4.755A7.876,7.876,0,0,0,7.925,0,7.876,7.876,0,0,0,0,7.925a7.876,7.876,0,0,0,7.925,7.925,7.751,7.751,0,0,0,4.755-1.585L16.415,18ZM2.264,7.925a5.605,5.605,0,0,1,5.66-5.66,5.605,5.605,0,0,1,5.66,5.66,5.605,5.605,0,0,1-5.66,5.66A5.605,5.605,0,0,1,2.264,7.925Z" transform="translate(-18214 -12091)"/></svg>
                    <svg class="close-icon" xmlns="http://www.w3.org/2000/svg" viewBox="10906 13031 18 18"><path id="Close" d="M23,6.813,21.187,5,14,12.187,6.813,5,5,6.813,12.187,14,5,21.187,6.813,23,14,15.813,21.187,23,23,21.187,15.813,14Z" transform="translate(10901 13026)"/></svg>
                </div>
                <?php 
                if( blossom_pin_social_links( false, false ) ){
                    echo '<span class="separator"></span>';
                    blossom_pin_social_links( true, false );
                } ?>
            </div>
        <?php endif; ?>
    </header>
    <?php 
}


function blossom_pin_banner(){
    $ed_banner      = get_theme_mod( 'ed_banner_section', 'slider_banner' );
    $slider_type    = get_theme_mod( 'slider_type', 'latest_posts' ); 
    $slider_cat     = get_theme_mod( 'slider_cat' );
    $posts_per_page = get_theme_mod( 'no_of_slides', 7 );
    $banner_title      = get_theme_mod( 'banner_title', __( 'Wondering how your peers are using social media?', 'blossom-pinthis' ) );
    $banner_subtitle   = get_theme_mod( 'banner_subtitle', __( 'Discover how people in the community create pins to get their attention?', 'blossom-pinthis' ) );
    $banner_label      = get_theme_mod( 'banner_label', __( 'Discover More', 'blossom-pinthis' ) );
    $banner_link       = get_theme_mod( 'banner_link', '#' );
    $slider_layout     = get_theme_mod( 'slider_layout', 'two' );
    $add_class  = ( $slider_layout == 'one' ) ? 'banner-slider ' : '';    
    $image_size = ( $slider_layout == 'one' ) ? 'blossom-pin-slider' : 'blossom-pinthis-slider';    
    
    if( is_front_page() || is_home() ){ 
        
        if( $ed_banner == 'static_banner' && has_custom_header() ){ ?>
            <div class="banner<?php if( has_header_video() ) echo esc_attr( ' video-banner' ); ?>">
                <?php the_custom_header_markup();
                if( $ed_banner == 'static_banner' && ( $banner_title || $banner_subtitle || ( $banner_label && $banner_link ) ) ){
                    echo '<div class="banner-caption"><div class="wrapper"><div class="banner-wrap">';
                    if( $banner_title ) echo '<h2 class="banner-title">' . esc_html( $banner_title ) . '</h2>';
                    if( $banner_subtitle ) echo '<div class="banner-content b-content">' . wpautop( wp_kses_post( $banner_subtitle ) ) . '</div>';
                    if( $banner_label && $banner_link ) echo '<a href="' . esc_url( $banner_link ) . '" class="banner-link">' . esc_html( $banner_label ) . '</a>';
                    echo '</div></div></div>';
                } ?>
            </div>
            <?php
        }elseif( $ed_banner == 'slider_banner' ){
            $args = array(
                'post_type'           => 'post',
                'post_status'         => 'publish',            
                'ignore_sticky_posts' => true
            );
            
            if( $slider_type === 'cat' && $slider_cat ){
                $args['cat']            = $slider_cat; 
                $args['posts_per_page'] = -1;  
            }else{
                $args['posts_per_page'] = $posts_per_page;
            }
                
            $qry = new WP_Query( $args );
            
            if( $qry->have_posts() ){ ?>
            <div class="banner">
                <div class="<?php echo esc_attr( $add_class ); ?>banner-layout-<?php echo esc_attr( $slider_layout ); ?> owl-carousel">
                    <?php while( $qry->have_posts() ){ $qry->the_post(); ?>
                    <div class="item">
                        <?php 
                        if( has_post_thumbnail() ){
                            the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );    
                        }
                        ?>                        
                        <div class="text-holder">
                            <?php
                                blossom_pin_category();
                                the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
                            ?>
                        </div>
                    </div>
                    <?php } ?>
                    
                </div>
            </div>
            <?php
            }
            wp_reset_postdata();
        } 
    }    
}

function blossom_pin_fonts_url(){
    $fonts_url = '';
    
    $primary_font       = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $ig_primary_font    = blossom_pin_is_google_font( $primary_font );    
    $secondary_font     = get_theme_mod( 'secondary_font', 'Spectral' );
    $ig_secondary_font  = blossom_pin_is_google_font( $secondary_font );    
    $site_title_font    = get_theme_mod( 'site_title_font', array( 'font-family'=>'Cormorant Garamond', 'variant'=>'regular' ) );
    $ig_site_title_font = blossom_pin_is_google_font( $site_title_font['font-family'] );
        
    /* Translators: If there are characters in your language that are not
    * supported by respective fonts, translate this to 'off'. Do not translate
    * into your own language.
    */
    $primary    = _x( 'on', 'Primary Font: on or off', 'blossom-pinthis' );
    $secondary  = _x( 'on', 'Secondary Font: on or off', 'blossom-pinthis' );
    $site_title = _x( 'on', 'Site Title Font: on or off', 'blossom-pinthis' );
    
    
    if ( 'off' !== $primary || 'off' !== $secondary || 'off' !== $site_title ) {
        
        $font_families = array();
     
        if ( 'off' !== $primary && $ig_primary_font ) {
            $primary_variant = blossom_pin_check_varient( $primary_font, 'regular', true );
            if( $primary_variant ){
                $primary_var = ':' . $primary_variant;
            }else{
                $primary_var = '';    
            }            
            $font_families[] = $primary_font . $primary_var;
        }
         
        if ( 'off' !== $secondary && $ig_secondary_font ) {
            $secondary_variant = blossom_pin_check_varient( $secondary_font, 'regular', true );
            if( $secondary_variant ){
                $secondary_var = ':' . $secondary_variant;    
            }else{
                $secondary_var = '';
            }
            $font_families[] = $secondary_font . $secondary_var;
        }
        
        if ( 'off' !== $site_title && $ig_site_title_font ) {
            
            if( ! empty( $site_title_font['variant'] ) ){
                $site_title_var = ':' . blossom_pin_check_varient( $site_title_font['font-family'], $site_title_font['variant'] );    
            }else{
                $site_title_var = '';
            }
            $font_families[] = $site_title_font['font-family'] . $site_title_var;
        }
        
        $font_families = array_diff( array_unique( $font_families ), array('') );
        
        $query_args = array(
            'family' => urlencode( implode( '|', $font_families ) ),            
        );
        
        $fonts_url = add_query_arg( $query_args, 'https://fonts.googleapis.com/css' );
    }
     
    return esc_url_raw( $fonts_url );
}


function blossom_pin_dynamic_css(){
    
    $primary_font    = get_theme_mod( 'primary_font', 'Nunito Sans' );
    $primary_fonts   = blossom_pin_get_fonts( $primary_font, 'regular' );
    $secondary_font  = get_theme_mod( 'secondary_font', 'Spectral' );
    $secondary_fonts = blossom_pin_get_fonts( $secondary_font, 'regular' );
    $font_size       = get_theme_mod( 'font_size', 18 );
    
    $site_title_font      = get_theme_mod( 'site_title_font', array( 'font-family'=>'Cormorant Garamond', 'variant'=>'regular' ) );
    $site_title_fonts     = blossom_pin_get_fonts( $site_title_font['font-family'], $site_title_font['variant'] );
    
    $primary_color = get_theme_mod( 'primary_color', '#e7475e' );
    
    $rgb = blossom_pin_hex2rgb( blossom_pin_sanitize_hex_color( $primary_color ) );
     
    $custom_css = '';
    $custom_css .= '
    
    /*Typography*/

    body,
    button,
    input,
    select,
    optgroup,
    textarea{
        font-family : ' . wp_kses_post( $primary_fonts['font'] ) . ';
        font-size   : ' . absint( $font_size ) . 'px;        
    }
    
    .site-header .site-branding .site-title,
    .single-header .site-branding .site-title{
        font-family : ' . wp_kses_post( $site_title_fonts['font'] ) . ';
        font-weight : ' . esc_html( $site_title_fonts['weight'] ) . ';
        font-style  : ' . esc_html( $site_title_fonts['style'] ) . ';
    }

    .blog #primary .format-quote .post-thumbnail blockquote cite, 
    .newsletter-section .blossomthemes-email-newsletter-wrapper .text-holder h3,
    .newsletter-section .blossomthemes-email-newsletter-wrapper.bg-img .text-holder h3, 
    #primary .post .entry-content blockquote cite,
    #primary .page .entry-content blockquote cite{
        font-family : ' . wp_kses_post( $primary_fonts['font'] ) . ';
    }
    
    /*Color Scheme*/
    a,
    .main-navigation ul li a:hover,
    .main-navigation ul .current-menu-item > a,
    .main-navigation ul li:hover > a,
    .banner-slider .item .text-holder .entry-title a:hover,
    .blog #primary .post .entry-header .entry-title a:hover,
    .widget_bttk_popular_post ul li .entry-header .entry-title a:hover,
    .widget_bttk_pro_recent_post ul li .entry-header .entry-title a:hover,
    .widget_bttk_popular_post ul li .entry-header .entry-meta a:hover,
    .widget_bttk_pro_recent_post ul li .entry-header .entry-meta a:hover,
    .widget_bttk_popular_post .style-two li .entry-header .cat-links a:hover,
    .widget_bttk_pro_recent_post .style-two li .entry-header .cat-links a:hover,
    .widget_bttk_popular_post .style-three li .entry-header .cat-links a:hover,
    .widget_bttk_pro_recent_post .style-three li .entry-header .cat-links a:hover,
    .widget_recent_entries ul li:before,
    .widget_recent_entries ul li a:hover,
    .widget_recent_comments ul li:before,
    .widget_bttk_posts_category_slider_widget .carousel-title .title a:hover,
    .widget_bttk_posts_category_slider_widget .carousel-title .cat-links a:hover,
    .site-footer .footer-b .footer-nav ul li a:hover,
    .single #primary .post .holder .meta-info .entry-meta a:hover,
    .recommended-post .post .entry-header .entry-title a:hover,
    .search #primary .search-post .entry-header .entry-title a:hover,
    .archive #primary .post .entry-header .entry-title a:hover,
    .instagram-section .profile-link:hover,
    .site-header .site-branding .site-title a:hover,
    .mobile-header .mobile-site-header .site-branding .site-title a:hover,
    .single-blossom-portfolio .post-navigation .nav-previous a:hover,
    .single-blossom-portfolio .post-navigation .nav-next a:hover,
    .single .navigation a:hover .post-title,
    .blog #primary .post .bottom .posted-on a:hover,
    .search #primary .search-post .entry-footer .posted-on a:hover,
    .archive #primary .post .entry-footer .posted-on a:hover, 
    .site-header .social-networks ul li a:hover, 
    .banner-layout-two .text-holder .entry-title a:hover, 
    .single-header .social-networks ul li a:hover, 
    .portfolio-item a:hover, 
    .error-wrapper .error-holder h3, 
    .mobile-menu .main-navigation ul ul li a:hover, 
    .mobile-menu .main-navigation ul ul li:hover > a, 
    .archive #primary .site-main .bottom .posted-on a:hover, 
    .search #primary .site-main .bottom .posted-on a:hover, 
    #crumbs a:hover, #crumbs .current a{
        color: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
    }

    .blog #primary .post .entry-header .category a,
    .widget .widget-title::after,
    .widget_bttk_custom_categories ul li a:hover .post-count,
    .widget_blossomtheme_companion_cta_widget .text-holder .button-wrap .btn-cta,
    .widget_blossomtheme_featured_page_widget .text-holder .btn-readmore:hover,
    .widget_bttk_icon_text_widget .text-holder .btn-readmore:hover,
    .widget_bttk_image_text_widget ul li .btn-readmore:hover,
    .newsletter-section,
    .single .post-entry-header .category a,
    .single #primary .post .holder .meta-info .entry-meta .byline:after,
    .recommended-post .post .entry-header .category a,
    .search #primary .search-post .entry-header .category a,
    .archive #primary .post .entry-header .category a,
    .banner-slider .item .text-holder .category a,
    .back-to-top, 
    .banner-layout-two .text-holder .category a, .banner-layout-two .text-holder .category span, 
    .banner-layout-two .item, 
    .single-header .progress-bar, 
    .widget_bttk_author_bio .readmore:hover{
        background: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
    }

    .blog #primary .post .entry-footer .read-more:hover, 
    .blog #primary .post .entry-footer .edit-link a:hover, 
    .archive #primary .site-main .top .read-more:hover, 
    .search #primary .site-main .top .read-more:hover{
        border-bottom-color: <?php echo blossom_pin_sanitize_hex_color( $primary_color ); ?>;
        color: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
    }

    button:hover,
    input[type="button"]:hover,
    input[type="reset"]:hover,
    input[type="submit"]:hover, 
    .error-wrapper .error-holder .btn-home a:hover{
        background: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
        border-color: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
    }

    @media screen and (max-width: 1024px) {
        .main-navigation ul ul li a:hover, 
        .main-navigation ul ul li:hover > a, 
        .main-navigation ul ul .current-menu-item > a, 
        .main-navigation ul ul .current-menu-ancestor > a, 
        .main-navigation ul ul .current_page_item > a, 
        .main-navigation ul ul .current_page_ancestor > a {
            color: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ' !important;
        }
    }

    /*Typography*/ 
    .banner-layout-two .text-holder .entry-title, 
    .banner-slider .item .text-holder .entry-title, 
    .banner .banner-caption .banner-title, 
    .blog #primary .post .entry-header .entry-title, 
    .blog #primary .format-quote .post-thumbnail .blockquote-holder, 
    .search #primary .search-post .entry-header .entry-title,
    .archive #primary .post .entry-header .entry-title, 
    .single .post-entry-header .entry-title, 
    #primary .post .entry-content blockquote,
    #primary .page .entry-content blockquote, 
    #primary .post .entry-content .pull-left,
    #primary .page .entry-content .pull-left, 
    #primary .post .entry-content .pull-right,
    #primary .page .entry-content .pull-right, 
    .single-header .site-branding .site-title, 
    .single-header .title-holder .post-title, 
    .recommended-post .post .entry-header .entry-title, 
    .widget_bttk_popular_post ul li .entry-header .entry-title,
    .widget_bttk_pro_recent_post ul li .entry-header .entry-title, 
    .blossomthemes-email-newsletter-wrapper.bg-img .text-holder h3, 
    .widget_recent_entries ul li a, 
    .widget_recent_comments ul li a, 
    .widget_bttk_posts_category_slider_widget .carousel-title .title, 
    .single .navigation .post-title, 
    .single-blossom-portfolio .post-navigation .nav-previous,
    .single-blossom-portfolio .post-navigation .nav-next{
        font-family : ' . wp_kses_post( $secondary_fonts['font'] ) . ';
    }';


    if( blossom_pin_is_woocommerce_activated() ) {
        $custom_css .='
        .woocommerce ul.products li.product .add_to_cart_button:hover,
        .woocommerce ul.products li.product .add_to_cart_button:focus,
        .woocommerce ul.products li.product .product_type_external:hover,
        .woocommerce ul.products li.product .product_type_external:focus,
        .woocommerce ul.products li.product .ajax_add_to_cart:hover,
        .woocommerce ul.products li.product .ajax_add_to_cart:focus,
        .woocommerce #secondary .widget_price_filter .ui-slider .ui-slider-range,
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:hover,
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:focus,
        .woocommerce div.product form.cart .single_add_to_cart_button:hover,
        .woocommerce div.product form.cart .single_add_to_cart_button:focus,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:hover,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:focus,
        .woocommerce .woocommerce-message .button:hover,
        .woocommerce .woocommerce-message .button:focus,
        .woocommerce #secondary .widget_shopping_cart .buttons .button:hover,
        .woocommerce #secondary .widget_shopping_cart .buttons .button:focus,
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:hover,
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:focus,
        .woocommerce-checkout .woocommerce form.woocommerce-form-login input.button:hover,
        .woocommerce-checkout .woocommerce form.woocommerce-form-login input.button:focus,
        .woocommerce-checkout .woocommerce form.checkout_coupon input.button:hover,
        .woocommerce-checkout .woocommerce form.checkout_coupon input.button:focus,
        .woocommerce form.lost_reset_password input.button:hover,
        .woocommerce form.lost_reset_password input.button:focus,
        .woocommerce .return-to-shop .button:hover,
        .woocommerce .return-to-shop .button:focus,
        .woocommerce #payment #place_order:hover,
        .woocommerce-page #payment #place_order:focus, 
        .woocommerce ul.products li.product .added_to_cart:hover,
        .woocommerce ul.products li.product .added_to_cart:focus, 
        .woocommerce ul.products li.product .add_to_cart_button:hover,
        .woocommerce ul.products li.product .add_to_cart_button:focus,
        .woocommerce ul.products li.product .product_type_external:hover,
        .woocommerce ul.products li.product .product_type_external:focus,
        .woocommerce ul.products li.product .ajax_add_to_cart:hover,
        .woocommerce ul.products li.product .ajax_add_to_cart:focus, 
        .woocommerce div.product .entry-summary .variations_form .single_variation_wrap .button:hover,
        .woocommerce div.product .entry-summary .variations_form .single_variation_wrap .button:focus, 
        .woocommerce div.product form.cart .single_add_to_cart_button:hover,
        .woocommerce div.product form.cart .single_add_to_cart_button:focus,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:hover,
        .woocommerce div.product .cart .single_add_to_cart_button.alt:focus, 
        .woocommerce .woocommerce-message .button:hover,
        .woocommerce .woocommerce-message .button:focus, 
        .woocommerce-cart #primary .page .entry-content table.shop_table td.actions .coupon input[type="submit"]:hover,
        .woocommerce-cart #primary .page .entry-content table.shop_table td.actions .coupon input[type="submit"]:focus, 
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:hover,
        .woocommerce-cart #primary .page .entry-content .cart_totals .checkout-button:focus, 
        .woocommerce-checkout .woocommerce form.woocommerce-form-login input.button:hover,
        .woocommerce-checkout .woocommerce form.woocommerce-form-login input.button:focus,
        .woocommerce-checkout .woocommerce form.checkout_coupon input.button:hover,
        .woocommerce-checkout .woocommerce form.checkout_coupon input.button:focus,
        .woocommerce form.lost_reset_password input.button:hover,
        .woocommerce form.lost_reset_password input.button:focus,
        .woocommerce .return-to-shop .button:hover,
        .woocommerce .return-to-shop .button:focus,
        .woocommerce #payment #place_order:hover,
        .woocommerce-page #payment #place_order:focus, 
        .woocommerce #secondary .widget_shopping_cart .buttons .button:hover,
        .woocommerce #secondary .widget_shopping_cart .buttons .button:focus, 
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:hover,
        .woocommerce #secondary .widget_price_filter .price_slider_amount .button:focus{
            background: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
        }

        .woocommerce #secondary .widget .product_list_widget li .product-title:hover,
        .woocommerce #secondary .widget .product_list_widget li .product-title:focus,
        .woocommerce div.product .entry-summary .product_meta .posted_in a:hover,
        .woocommerce div.product .entry-summary .product_meta .posted_in a:focus,
        .woocommerce div.product .entry-summary .product_meta .tagged_as a:hover,
        .woocommerce div.product .entry-summary .product_meta .tagged_as a:focus, 
        .woocommerce-cart #primary .page .entry-content table.shop_table td.product-name a:hover, .woocommerce-cart #primary .page .entry-content table.shop_table td.product-name a:focus{
            color: ' . blossom_pin_sanitize_hex_color( $primary_color ) . ';
        }';
    }
           
    wp_add_inline_style( 'blossom-pin', $custom_css );
}


function blossom_pin_post_thumbnail() {
    global $wp_query;
    $image_size  = 'thumbnail';
    $ed_featured = get_theme_mod( 'ed_featured_image', true );
    $home_layout = get_theme_mod( 'blog_layout_option', 'home-two' );
    $archive_layout = get_theme_mod( 'archive_layout_option', 'archive-two' );
    
    if( !is_singular() && ( $archive_layout == 'archive-two' )  ) : ?>
        <div class="holder">
            <div class="top">
    <?php endif;

    if( is_home() ){        
        if( has_post_thumbnail() ){
            $image_size = ( $home_layout == 'home-two' ) ? 'blossom-pinthis-blog' : 'full';                        
            echo '<a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';
            the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );    
            echo '</a>';
        }       
    }elseif( is_archive() || is_search() ){
        if( has_post_thumbnail() ){
            $image_size = ( $archive_layout == 'archive-two' ) ? 'full' : 'blossom-pin-archive';
            echo '<div class="post-thumbnail"><a href="' . esc_url( get_permalink() ) . '" class="post-thumbnail">';
            the_post_thumbnail( $image_size, array( 'itemprop' => 'image' ) );    
            echo '</a></div>';
        }
    }elseif( is_singular() ){
        if( is_single() ){
            if( $ed_featured ) {
                echo '<div class="post-thumbnail">';
                the_post_thumbnail( 'full', array( 'itemprop' => 'image' ) );
                echo '</div>';
            }
        }else{
            echo '<div class="post-thumbnail">';
            the_post_thumbnail( 'full', array( 'itemprop' => 'image' ) );
            echo '</div>';
        }
    }
}

function blossom_pin_entry_header(){ 
    $archive_layout = get_theme_mod( 'archive_layout_option', 'archive-two' ); ?>
    <?php if( ( is_archive() || is_search() ) && ( $archive_layout != 'archive-two' ) ){
        echo '<div class="text-holder">';
    } ?>
    <header class="entry-header">
        <?php 
            blossom_pin_category();

            if( is_singular() ) :
                the_title( '<h1 class="entry-title">', '</h1>' );
            else :
                the_title( '<h2 class="entry-title"><a href="' . esc_url( get_permalink() ) . '" rel="bookmark">', '</a></h2>' );
            endif;       
        ?>
    </header>    
<?php
}

function blossom_pin_entry_footer(){ 
    $readmore = get_theme_mod( 'read_more_text', __( 'Read more', 'blossom-pinthis' ) );
    $archive_layout = get_theme_mod( 'archive_layout_option', 'archive-two' ); ?>

    <footer class="entry-footer">
        <?php
            if( is_single() ){
                blossom_pin_tag();
            }
            
            if( ( is_front_page() || is_home() ) || ( is_archive() || is_search() ) && ( $archive_layout == 'archive-two' ) ){
                echo '<a href="' . esc_url( get_the_permalink() ) . '" class="read-more">' . esc_html( $readmore ) . '</a>';    
            }            
            
            if( ( is_archive() || is_search() ) && ( $archive_layout != 'archive-two' ) ) blossom_pin_posted_on();
            
            if( get_edit_post_link() ){
                edit_post_link(
                    sprintf(
                        wp_kses(
                            /* translators: %s: Name of current post. Only visible to screen readers */
                            __( 'Edit <span class="screen-reader-text">%s</span>', 'blossom-pinthis' ),
                            array(
                                'span' => array(
                                    'class' => array(),
                                ),
                            )
                        ),
                        get_the_title()
                    ),
                    '<span class="edit-link">',
                    '</span>'
                );
            }
        ?>
    </footer><!-- .entry-footer -->

    <?php if( ( is_archive() || is_search() ) && ( $archive_layout != 'archive-two' ) ){
        echo '</div><!-- .text-holder -->';
    }
    
    if( ! is_singular() && ( $archive_layout == 'archive-two' ) ) : ?>
        </div><!-- .top -->
        <div class="bottom">
            <?php blossom_pin_posted_on(); ?>
        </div><!-- .bottom -->
    </div> <!-- .holder -->
    <?php endif;
}

function blossom_pin_footer_bottom(){ ?>
    <div class="footer-b">
        <div class="container">
            <div class="site-info">            
            <?php
                blossom_pin_get_footer_copyright();
                esc_html_e( ' Blossom PinThis | Developed By ', 'blossom-pinthis' );
                echo '<a href="' . esc_url( 'https://blossomthemes.com/' ) .'" rel="nofollow" target="_blank">'. esc_html__( 'Blossom Themes', 'blossom-pinthis' ) . '</a>.';
                
                printf( esc_html__( ' Powered by %s', 'blossom-pinthis' ), '<a href="'. esc_url( __( 'https://wordpress.org/', 'blossom-pinthis' ) ) .'" target="_blank">WordPress</a>. ' );
                if ( function_exists( 'the_privacy_policy_link' ) ) {
                    the_privacy_policy_link();
                }
            ?>               
            </div>
            <?php blossom_pin_secondary_navigation(); ?>
        </div>
    </div>
    <?php
}