<?php
/*
Plugin Name: Pro Related Post Widget
Plugin URI: http://wordpress.org/extend/plugins/pro-related-post-widget/
Description: Pro Related Post Widget plugin.dynamically show  related  post according to post.
Version: 1.0
Author: Shambhu Prasad Patnaik
Author URI:http://socialcms.wordpress.com/
*/
class Pro_Related_Post_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'pro_related_post', // Base ID
			'Pro Related Posts', // Name
			array( 'description' => __( 'The most related posts on your site', 'text_domain' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
	 extract( $args );
	 $title = apply_filters( 'widget_title', $instance['title'] );
	 if ( empty( $instance['number'] ) || ! $number = absint( $instance['number'] ) )
 	 $number = 5;
	 $show_date = isset( $instance['show_date'] ) ? $instance['show_date'] : false;
	 $hide_no_post = isset( $instance['hide_no_post'] ) ? $instance['hide_no_post']:false;
	 $catArray=array();
	 $arg = array( 'posts_per_page' => $number, 'no_found_rows' => true, 'post_status' => 'publish', 'ignore_sticky_posts' => true);
	 if(is_single())
	 {
      $postid = get_the_ID(); 
      $post_cat = get_the_category($postid); 
	  $arg['post__not_in'] = (array) $postid;
	  foreach($post_cat as $category)
	  {
	   array_push($catArray,$category->cat_ID);
	  }
	 }
	 else
     {
	  return ;
	 }
	 $post_category=implode(',',$catArray);
     $arg['cat'] = $post_category;

     $r = new WP_Query( apply_filters( 'widget_posts_args', $arg) );
	 if($r->post_count==0 && !$hide_no_post):
   	 $arg['cat'] = '';
     $r = new WP_Query( apply_filters( 'widget_posts_args', $arg) );
     endif;
	 //echo "<pre>";print_r($arg);echo "</pre>";die();
	 if ($r->have_posts()) :
     ?>
	 <?php echo $before_widget; ?>
	 <?php if ( $title ) echo $before_title . $title . $after_title; ?>
	 <ul>
	 <?php while ( $r->have_posts() ) : $r->the_post(); ?>
		<li>
		<a href="<?php the_permalink() ?>" title="<?php echo esc_attr( get_the_title() ? get_the_title() : get_the_ID() ); ?>"><?php if ( get_the_title() ) the_title(); else the_ID(); ?></a>
		<?php if ( $show_date ) : ?>
		<span class="post-date"><?php echo get_the_date(); ?></span>
		<?php endif; ?>
		</li>
	 <?php endwhile; ?>
	 </ul>
	 <?php echo $after_widget; ?>
     <?php
	 // Reset the global $the_post as this query will have stomped on it
	 endif;
	}
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['number'] = (int) $new_instance['number'];
		$instance['show_date'] = (bool) $new_instance['show_date'];
		$instance['hide_no_post'] = (bool)$new_instance['hide_no_post'];
		return $instance;
	}

	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
	 if ( isset( $instance[ 'title' ] ) ) {
	  $title = $instance[ 'title' ];
	 }
	 else {
	  $title = __( 'Related Posts', 'text_domain' );
 	 }
	 $number    = isset( $instance['number'] ) ? absint( $instance['number'] ) : 5;
	 $show_date = isset( $instance['show_date'] ) ? (bool) $instance['show_date'] : false;
	 $hide_no_post    = isset( $instance['hide_no_post'] ) ? (bool)$instance['hide_no_post'] : true;
	?>
	 <p>
	  <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
	   <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
	 </p>
	 <p>
	  <label for="<?php echo $this->get_field_id( 'number' ); ?>"><?php _e( 'Number of posts to show:' ); ?></label>
	  <input id="<?php echo $this->get_field_id( 'number' ); ?>" name="<?php echo $this->get_field_name( 'number' ); ?>" type="text" value="<?php echo $number; ?>" size="3" />
	 </p>
	 <p>
	  <input class="checkbox" type="checkbox" <?php checked( $hide_no_post ); ?> id="<?php echo $this->get_field_id( 'hide_no_post' ); ?>" name="<?php echo $this->get_field_name( 'hide_no_post' ); ?>" />
	  <label for="<?php echo $this->get_field_id( 'hide_no_post' ); ?>"><?php _e( 'Hide if no posts to show:?' ); ?></label>
	 </p>
	 <p>
	  <input class="checkbox" type="checkbox" <?php checked( $show_date ); ?> id="<?php echo $this->get_field_id( 'show_date' ); ?>" name="<?php echo $this->get_field_name( 'show_date' ); ?>" />
	  <label for="<?php echo $this->get_field_id( 'show_date' ); ?>"><?php _e( 'Display post date?' ); ?></label>
	 </p>
	 <?php 
	}
} // class pro_related_post

// register Pro_Related_Post_Widget widget
add_action( 'widgets_init', create_function( '', 'register_widget( "Pro_Related_Post_Widget" );' ) );
register_deactivation_hook(__FILE__, 'pro_related_post_plugin_deactivate');

function pro_related_post_plugin_deactivate ()
{
 unregister_widget('Pro_Related_Post_Widget');
}
?>