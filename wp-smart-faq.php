<?php
/**
 *Plugin Name: WP Smart FAQ
 *Plugin URI: http://webkindreds.com/
 *Description: Custom accordion based Smart WordPress FAQ Plugin
 *Version: 1.0
 *Author: Rahul Kumar Singh
 *Author URI: http://webkindreds.com/
 *License: GPL2
**/

//Custom FAQ Post Type 


function wp_smart_faq_enqueue_scripts(){
     if(!is_admin()){
        wp_register_style('wp-samart-faq-style',plugins_url('/faq-accordion.css', __FILE__ ));
        wp_enqueue_style('wp-samart-faq-style');
        wp_enqueue_script('jquery');
        wp_enqueue_script('wp-samart-script', plugins_url('/faq-accordion.js', __FILE__ ), array(), '1.0.0');
    }   
}

add_action( 'init', 'wp_smart_faq_enqueue_scripts' );
function wp_smart_faq_post_type() {
    $labels = array(
        'name'               => _x( 'FAQ', 'post type general name' ),
        'singular_name'      => _x( 'FAQ', 'post type singular name' ),
        'add_new'            => _x( 'Add New', 'FAQ' ),
        'add_new_item'       => __( 'Add New FAQ' ),
        'edit_item'          => __( 'Edit FAQ' ),
        'new_item'           => __( 'New FAQ Items' ),
        'all_items'          => __( 'All FAQs' ),
        'view_item'          => __( 'View FAQ' ),
        'search_items'       => __( 'Search FAQ' ),
        'not_found'          => __( 'No FAQ Items found' ),
        'not_found_in_trash' => __( 'No FAQ Items found in the Trash' ), 
        'parent_item_colon'  => '',
        'menu_name'          => 'FAQ'
    );
    $args = array(
        'labels'        => $labels,
        'description'   => 'Holds FAQ specific data',
        'public'        => true,
        'show_ui'       => true,
        'show_in_menu'  => true,
        'query_var'     => true,
        'rewrite'       => array('slug' => 'faq-item'),
        'capability_type'=> 'post',
        'has_archive'   => true,
        'hierarchical'  => false,
        'menu_position' => 5,
        'supports'      => array( 'title', 'editor','page-attributes'),
        'menu_icon' => 'dashicons-welcome-write-blog'
    );

    register_post_type( 'ws_faq', $args ); 

        // Add new taxonomy, make it hierarchical (like categories)
        $labels = array(
            'name'              => _x( 'FAQ Categories', 'taxonomy general name' ),
            'singular_name'     => _x( 'FAQ Category', 'taxonomy singular name' ),
            'search_items'      =>  __( 'Search FAQ Categories' ),
            'all_items'         => __( 'All FAQ Category' ),
            'parent_item'       => __( 'Parent FAQ Category' ),
            'parent_item_colon' => __( 'Parent FAQ Category:' ),
            'edit_item'         => __( 'Edit FAQ Category' ),
            'update_item'       => __( 'Update FAQ Category' ),
            'add_new_item'      => __( 'Add New FAQ Category' ),
            'new_item_name'     => __( 'New FAQ Category Name' ),
            'menu_name'         => __( 'FAQ Category' ),
        );
    
        register_taxonomy('faq-tax',array('ws_faq'), array(
            'hierarchical' => true,
            'labels'       => $labels,
            'show_ui'      => true,
            'query_var'    => true,
            'rewrite'      => array( 'slug' => 'faq_tax' ),
        ));
}

add_action( 'init', 'wp_smart_faq_post_type' );

function wp_smart_faq_shortcode($atts, $content= null) { 
    
    extract( shortcode_atts(
        array(
           'id' => '',
		   'open' =>''
            ), $atts )
    );
	
    if( $id == '' ){
        $args = array (
				'post_type'		=> 'ws_faq',
				'posts_per_page'=> -1,
				'post_status'	=> 'publish',
				'orderby'		=> 'menu_order',
				'order' 		=> 'DESC'
				);
    }else{
        $args = array (
            	'post_type'		=> 'ws_faq',
				'posts_per_page'=> -1,
				'post_status'	=> 'publish',
            	'tax_query' 	=> array(
							array(
								'taxonomy' => 'faq-tax',
								'field'    => 'term_id',
								'terms'    => array( $id ),
							),
					),
				'orderby'		=> 'menu_order',
            	'order' 		=> 'DESC'
            	);
    }
    $wpsloop = new WP_Query( $args );
	
    ob_start();
    ?>
       <div class="accordion">
	   		<?php $i = 1; ?>
            <?php if( $wpsloop->have_posts() ) : while ( $wpsloop->have_posts() ) : $wpsloop->the_post(); ?>
			<?php if($i == $open){
			    $item_class = 'active-tab';
				$item_data_class = 'active';
			}else{
				$item_class = '';
				$item_data_class = '';
			}
			if($wpsloop->menu_order > 0){
				$qusno = $wpsloop->menu_order;
			}else{
				$qusno = $i;
			}
			?>
			
			<span><?php echo $qusno.".  "; ?> <?php the_title();?></span>
			<div><?php the_content(); ?></div>
                <?php 
				$i++;
				endwhile; //end while
            else :
                echo "<p>No FAQ Items. Please add some Items</p>";
              endif; ?>
   		</div>
    <?php
        //Reset the query
    wp_reset_query();
    wp_reset_postdata();
        $output = ob_get_contents(); // end output buffering
        ob_end_clean(); // grab the buffer contents and empty the buffer
        return $output;
}
add_shortcode('faq', 'wp_smart_faq_shortcode');



add_filter("manage_faq-tax_custom_column", 'jeweltheme_wp_awesome_faq_tax_columns', 10, 3);
add_filter("manage_edit-faq-tax_columns", 'jeweltheme_wp_awesome_faq_tax_manage_columns'); 
 
function jeweltheme_wp_awesome_faq_tax_manage_columns($theme_columns) {
    $new_columns = array(
            'cb' => '<input type="checkbox" />',
            'name' => __('Name'),
            'faq_category_shortcode' => __( 'Category Shortcode'),
            'slug' => __('Slug'),
            'posts' => __('Posts')
        );
    return $new_columns;

}


function jeweltheme_wp_awesome_faq_tax_columns($out, $column_name, $theme_id) {
    $theme = get_term($theme_id, 'faq-tax');
    switch ($column_name) {
        
        case 'title':
            echo get_the_title();
        break;

        case 'faq_category_shortcode':             
             echo '[faq id="' . $theme_id. '"]';
        break;
 
        default:
            break;
    }
    return $out;    
}
