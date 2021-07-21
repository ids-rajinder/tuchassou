<?php 

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

GFForms::include_feed_addon_framework();
//GFAddOn::register( 'GF_Advanced_Post_Creation' );

// if ( !function_exists( 'gf_advancedpostcreation' ) ) {
//  die('hhhh');
// }


if(class_exists('GF_Advanced_Post_Creation')){
  new overWriteGfPost;
}

#
# This shouldn't happen
if (!class_exists('manageListForm')) {
  echo 'Core plugin file not found';
  die();
}
require_once LIST_PATH.'classes/gf-post-creatipon.php';


class overWriteGfPost extends GF_Advanced_Post_Creation{


  protected static $_instance = null;

  private $userCache = []; 

  private $confirmation = "";

  public function __construct(){
    //$this->mngListObj  = manageListForm();
  }

  public function create_post( $feed, $entry, $form ) {

    echo "here";
    die();

	


		// Create base post object.

		//$post_id = wp_insert_post( $post, true );

		/* Here is my code for update Post Start*/
		
		$createdPosts = gform_get_meta( $entry['id'], $this->_slug . '_post_id' );
		if ( !empty($createdPosts) ) {
			foreach ( $createdPosts as $createdPost ) {
				$post_id =	$createdPost['post_id'];
      }
			//$existPost  = get_post($post_id);
			//$post['post_status'] = 	$existPost->post_status;
			$existPostStatus  = get_post_status($post_id);
			$post['post_status'] = 	$existPostStatus;
		}
		if(empty($post_id)){
			$post_id = wp_insert_post( $post, true );
		}
		echo "<pre>";
		print_r($entry);
		print_r($post);
		print_r($post_id);
		echo "</pre>";

		die();
		/* Here is my code for update Post End */

		



		// If post could not be created, exit.
		if ( is_wp_error( $post_id ) ) {

			// Log that post was not created.
			$this->add_feed_error( 'Could not create base post object: ' . $post_id->get_error_message(), $feed, $entry, $form );

			return $entry;

		} else {

			// Add post ID to post object.
			$post['ID'] = $post_id;

			// Add the form ID so it is available for GFFormsModel::copy_post_image().
			update_post_meta( $post['ID'], '_gform-form-id', $form['id'] );

		}

		// Added uploaded files to Media Library.
		$this->maybe_handle_post_media( $post['ID'], $feed, $entry );

		// Set standard post data.
		$post = $this->set_post_data( $post, $feed, $entry, $form );

		/**
		 * Modify the post object to be created.
		 *
		 * @since 1.0
		 *
		 * @param array $post  The post object to be created.
		 * @param array $feed  The current Feed object.
		 * @param array $entry The current Entry object.
		 * @param array $form  The current Form object.
		 */
		$post = gf_apply_filters( array( 'gform_advancedpostcreation_post', $form['id'] ), $post, $feed, $entry, $form );

		// Save full post object.
		$updated_post = wp_update_post( $post, true );

		// If post could not be created, exit.
		if ( is_wp_error( $updated_post ) ) {

			// Log that post was not created.
			$this->add_feed_error( 'Could not create post object: ' . $updated_post->get_error_message(), $feed, $entry, $form );

			return $entry;

		} else {

			// Log that post was created.
			$this->log_debug( __METHOD__ . '(): Post was created with an ID of ' . $post['ID'] . '.' );

			// Add entry and feed ID to post meta.
			update_post_meta( $post['ID'], '_' . $this->_slug . '_entry_id', $entry['id'] );
			update_post_meta( $post['ID'], '_' . $this->_slug . '_feed_id', $feed['id'] );

		}

		// Set post format.
		if ( rgars( $feed, 'meta/postFormat' ) ) {
			$this->log_debug( __METHOD__ . "(): Setting post format for post ID {$post['ID']} to: " . rgars( $feed, 'meta/postFormat' ) );
			set_post_format( $post['ID'], rgars( $feed, 'meta/postFormat' ) );
		}

		$this->maybe_set_post_thumbnail( $post['ID'], $feed, $entry, $form );
		$this->maybe_set_post_meta( $post['ID'], $feed, $entry, $form );
		$this->maybe_set_post_taxonomies( $post['ID'], $feed, $entry, $form );

		// Get entry post ID meta.
		$entry_post_ids = gform_get_meta( $entry['id'], $this->_slug . '_post_id' );

		// If entry post ID meta is not an array, set it to an array.
		if ( ! is_array( $entry_post_ids ) ) {
			$entry_post_ids = array();
		}

		// Add post ID to array.
		$entry_post_ids[] = array(
			'post_id' => $post['ID'],
			'feed_id' => $feed['id'],
			'media'   => $this->_current_media,
		);

		// Save entry meta.
		gform_update_meta( $entry['id'], $this->_slug . '_post_id', $entry_post_ids );

		/**
		 * Run action after post has been created.
		 *
		 * @since 1.0
		 *
		 * @param int   $post_id  New post ID.
		 * @param array $feed     The current Feed object.
		 * @param array $entry    The current Entry object.
		 * @param array $form     The current Form object.
		 */
		gf_do_action( array( 'gform_advancedpostcreation_post_after_creation', $form['id'] ), $post['ID'], $feed, $entry, $form );

		return $entry;

	}

  # We can't use this in common class
  public static function instance () {

    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()



}

function overWriteGfPost(){
  return overWriteGfPost::instance();
}
overWriteGfPost();

