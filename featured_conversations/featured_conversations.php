<?php
/**
 * Plugin Name: Buddypress Featured Conversation Widget
 * Description: A widget that displays a selected BuddyPress activity conversation, based off Justin Tadlock's example widget.
 * Version: 0.1
 * Author: Collin Anderson
 * Author URI: http://partoschool.org
 * License: Example: GNU General Public License 2.0 (GPL) http://www.gnu.org/licenses/gpl.html
 *
 */

add_action( 'widgets_init', 'Featured_Conversations_load_widgets' );
add_action( 'wp_print_styles', 'Featured_Conversations_load_style');


function Featured_Conversations_load_widgets() {
	register_widget( 'Featured_Conversations' );
}
function Featured_Conversations_load_style() {
	wp_enqueue_style( 'featured_conversations', plugins_url('css/style.css', __FILE__)) ;
}

function Featured_Conversations_load_textdomain(){
	$plugin_path = plugin_basename( dirname( __FILE__ ) .'/language' );
	load_plugin_textdomain( 'featured_conversations', '', $plugin_path );
}

class Featured_Conversations extends WP_Widget {

	/**
	 * Widget setup.
	 */
	function Featured_Conversations() {
		/* Widget settings. */
		$widget_ops = array( 'classname' => 'featured_conversations', 'description' => __('Feature conversations of the sidebar.', 'featured_conversations') );

		/* Widget control settings. */
		$control_ops = array( 'width' => 300, 'height' => 350, 'id_base' => 'featured_conversations' );

		/* Create the widget. */
		$this->WP_Widget( 'featured_conversations', __('Featured Conversations', 'featured_conversations'), $widget_ops, $control_ops );
	}

	function widget( $args, $instance ) {
		extract( $args );

		/* Our variables from the widget settings. */
		$title = apply_filters('widget_title', $instance['title'] );
		$cid = $instance['cid'];
		$num_replies = $instance['num_replies'];

		/* Before widget (defined by themes). */
		echo $before_widget;

		/* Display the widget title if one was input (before and after defined by themes). */
		if ( $title )
			echo $before_title . $title . $after_title;

		echo '<div class="container">';
		echo Featured_Conversations::op_output($cid);

		echo '<a href="' . apply_filters( 'bp_get_activity_thread_permalink', bp_activity_get_permalink($cid)) . '" class="view" title="' . __( 'View', 'featured_conversations' ) .'">' . __( 'View', 'featured_conversations' ) .'</a>';
		echo Featured_Conversations::responses_output($cid, $num_replies);
		echo '
			</div>';

		/* After widget (defined by themes). */
		echo $after_widget;
	}

	/**
	 * Update the widget settings.
	 */
	function update( $new_instance, $old_instance ) {
		$instance = $old_instance;

		/* Strip tags for title and name to remove HTML (important for text inputs). */
		$instance['title'] = strip_tags( __("Featured Conversations","featured_conversations") );		
		$instance['cid'] = strip_tags( $new_instance['cid'] );
		$instance['num_replies'] = strip_tags( $new_instance['num_replies'] );

		return $instance;
	}

	/**
	 * Displays the widget settings controls on the widget panel.
	 * Make use of the get_field_id() and get_field_name() function
	 * when creating your form elements. This handles the confusing stuff.
	 */
	function form( $instance ) {

		/* Set up some default widget settings. */
		$defaults = array( 'cid' => __('', 'featured_conversations'), 'num_replies' => __('', 'featured_conversations'));
		$instance = wp_parse_args( (array) $instance, $defaults ); ?>

		<p>
			<label for="<?php echo $this->get_field_id( 'cid' ); ?>"><?php _e('Conversation ID:', 'featured_conversations'); ?></label>
			<input id="<?php echo $this->get_field_id( 'cid' ); ?>" name="<?php echo $this->get_field_name( 'cid' ); ?>" value="<?php echo $instance['cid']; ?>" style="width:100%;" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id( 'num_replies' ); ?>"><?php _e('Number of Replies:', 'featured_conversations'); ?></label>
			<input id="<?php echo $this->get_field_id( 'num_replies' ); ?>" name="<?php echo $this->get_field_name( 'num_replies' ); ?>" value="<?php echo $instance['num_replies']; ?>" style="width:100%;" />
		</p>

		<?php
	}
	
	function op_output($cid) {
		
		global $wpdb;
		$thread = $wpdb->get_results("SELECT id, user_id, content, item_id FROM wp_bp_activity WHERE id = " . $cid . " LIMIT 1", OBJECT);
	
		$comment_author = get_userdata($thread[0]->user_id);
	
		echo '
				<div class="op">
					<a href="' . apply_filters( 'bp_get_member_permalink', bp_core_get_user_domain( $comment_author->ID, $comment_author->user_nicename, $comment_author->user_login ) ) . '">'. get_avatar( $thread[0]->user_id, 32 ) . '</a>
					<div class="content-container">' . bp_create_excerpt($thread[0]->content, 20) . '</div>
				</div>
				<div class="clear"></div>
				';		
	}
	
	function responses_output($cid, $num_replies) {
		
		global $wpdb;
		$thread = $wpdb->get_results( "select user_id, content, item_id from wp_bp_activity where type = 'activity_comment' AND item_id = " . $cid . " order by date_recorded asc", OBJECT);

		if ($num_replies > sizeof($thread))
			$num_replies = sizeof($thread);

		printf('<div class="median">' .  __("(Showing %s of %s)", 'featured_conversations') . '</div>', $num_replies, sizeof($thread));

		$thread = array_slice($thread, -$num_replies, $num_replies);

		foreach ($thread as $post) {
			$comment_author = get_userdata($post->user_id);
			echo '
					<div class="reply">
						<a href="' . apply_filters( 'bp_get_member_permalink', bp_core_get_user_domain( $comment_author->ID, $comment_author->user_nicename, $comment_author->user_login ) ) . '">'. get_avatar( $post->user_id, 32 ) . '</a>
						<div class="content-container">' . bp_create_excerpt($post->content, 20) . '</div>
					</div>
					<div class="clear"></div>
				';
		}
	}

}

?>