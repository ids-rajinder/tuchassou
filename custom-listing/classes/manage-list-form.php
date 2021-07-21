<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly



class manageListForm{  
  
  private $debug                = false;
  protected static $_instance   = null;
  public $formId                = 12; 

  
  public $fields = [
    'region'       => '146',
    'jurnyTitle'   => '123',
    'actionTitle'  => '37',
    'homyTitle'    => '151',
  ];

  public $viewId =  '4506';
  public $listPageId = '2704'; //'138';
  public $dashBoardPageId = '1615'; //'138';

  // Ile-de-France
  // 'Provence-Alpes-Côte d’Azur',

  public $regArray = [
    'Auvergne-Rhône-Alpes',
    'Bourgogne-Franche-Comté',
    'Bretagne',
    'Centre-Val de Loire',
    'Corse',
    'Grand Est',
    'Hauts-de-France',
    'Île-de-France',
    //'Ile-de-France',
    'Normandie',
    'Nouvelle-Aquitaine',
    'Occitanie',
    'Pays de la Loire',
    'Provence-Alpes-Côte d\'Azur',
    //'Provence-Alpes-Côte d’Azur',
  ];

  public $_args = array(
      'form_id' => false,
      'post_id' => false,
      'title'   => false,
      'content' => false,
      'author'  => false,
      'terms'   => array(),
      'meta'    => array(),
  );


  public $listFormPageId = '15';

  private $gfap = null;

  public function __construct(){

    $formId             = $this->formId;
    $this->plugin_url   = plugin_dir_url(  dirname(__FILE__) );
    $this->plugin_path  = plugin_dir_path( dirname(__FILE__) );
  
    if( $this->debug === true || !empty($_GET['pankaj-dev']) ){
      ini_set('display_errors', 1);
      ini_set('display_startup_errors', 1);
      error_reporting(E_ALL); 
    }

    add_action( "gform_enqueue_scripts_{$this->formId}", [$this, 'addedJsScript'], 10, 2 );
    add_action( "gform_advancedpostcreation_post_after_creation_{$this->formId}", [$this, 'apcSerializeCheckboxes'], 10, 4 );
    add_filter('acf/load_value/type=checkbox', [$this, "advancedCustomFieldCheck"], 10, 3);
  
    //add_filter( "gform_pre_render_{$this->formId}" , array( $this, 'pouplateTitleData' ));
    add_filter( "gform_pre_render_{$this->formId}" , array( $this, 'pouplateRegionDropDown' ));

    
    add_shortcode("list-edit-data", [$this, "listDataEdit"]);
    add_action( 'wp_enqueue_scripts', [$this, 'wpse_enqueue_datepicker'] );


    add_filter( "gform_validation_message_{$this->formId}", function( $message, $form ){
      if( $this->listPageId != get_queried_object_id() ){
        return $message;
      }
      return "<div class='gform_wrapper'>{$message}</div>";
    }, 10, 5 );

    add_filter( 'gravityview/edit_entry/success', [$this, 'customizeGvEditEntrySuccessMessage'], 10, 4 );

    add_action( 'gravityview/edit_entry/after_update', [$this, 'gravityviewTriggerGformAfterSubmissionForm'], 20, 3 );

    //add_action('plugins_loaded', [$this, 'GF_PostCreation_Bootstrap'] );

    //add_filter( 'gform_field_content_53',  [$this, 'allowShortCode'], 10, 5 );
    
    add_shortcode( 'licensee-to-hunt',  [$this, 'licenseeToHunt'], 10, 5 );
    add_shortcode( 'user-insurance',  [$this, 'userInsurance'], 10, 5 );
    add_shortcode( 'user-id-proof',  [$this, 'userIdProof'], 10, 5 );

    
    
    add_filter( 'dfi_thumbnail_id',  [$this,'dfi_no_page_post'], 10, 2 );
  }

  function dfi_no_page_post( $dfi_id, $post_id ) {
    $post = get_post( $post_id );
    if ( !in_array($post->post_type, array( 'listing')) ) {
      return 0; // Don't use DFI for this post or pages
    }
  
    return $dfi_id; // the original featured image id
  }


  function licenseeToHunt() {
    ob_start();
    $userID = get_current_user_id();
    $licUrl = get_user_meta( $userID, 'list_pro_licensee_to_hunt',  true );
    if(!empty($licUrl)){ ?>
      <div class="lc"><a href="<?php echo $licUrl; ?>" target="_blank">Voir le permis de chasse </a></div>
    <?php  }
    return ob_get_clean();
  }
  function userInsurance() {
    ob_start();
    $userID = get_current_user_id();
    $inUrl = get_user_meta( $userID, 'list_pro_insurance',  true );
    if(!empty($inUrl)){ ?>
      <div class="lc"><a href="<?php echo $inUrl; ?>" target="_blank">Voir les assurances</a></div>
    <?php  }
    return ob_get_clean();
  }
  function userIdProof() {
    ob_start();
    $userID = get_current_user_id();
    $listIdUrl = get_user_meta( $userID, 'list_post_id',  true );
    if(!empty($listIdUrl)){ ?>
      <div class="lc"><a href="<?php echo $listIdUrl; ?>" target="_blank">Voir la carte d'identité </a></div>
    <?php  }
    return ob_get_clean();
  }



  function allowShortCode( $field_content, $field ) {
    return do_shortcode($field_content);
  }

 

  public function init(){
    $this->gfap = GF_Advanced_Post_Creation::get_instance();
    // $this->gfapcSlug = $getInstance->get_slug();
  }


  public function gravityviewTriggerGformAfterSubmissionForm( $form, $entry_id, $object ) {

    
    gf_do_action( array( 'gform_after_submission', $form['id'] ), $object->entry, $form );


    if ( function_exists( 'gf_advancedpostcreation' ) ) {
      $entry = GFAPI::get_entry( $entry_id );
      gf_advancedpostcreation()->maybe_process_feed( $entry, $form );
    }
  }

  public function gravityviewTriggerGformAfterSubmissionFormOld( $form, $entry_id, $object ) {
    // Only trigger for Form ID #1 - update with your form ID
    if( $this->formId != $form['id'] ) {
      return;
    }
    $this->init();
    $slugName = $this->gfap->get_slug();
    $feeds    = $this->gfap->get_feeds_by_slug( $slugName );
    $created_posts = gform_get_meta( $entry_id, 'gravityformsadvancedpostcreation_post_id' );

    $post_id = "";
    foreach ( $created_posts as $post ){
      $post_id = $post['post_id'];
    }
    
    $entry = GFAPI::get_entry( $entry_id );

    //$fd = "";
    foreach($feeds as $feed){
      if( 2 != $feed['id']){
        continue;
      }
      $this->AllPostMetaUpdate($post_id, $fd, $entry, $form);
    }
    
    // $feed = array(
    //   'id'         => 2,
    //   'form_id'    => 12,
    //   'is_active'  => true,
    //   'meta'       => array(),
    //   'addon_slug' => 'gravityformsadvancedpostcreation',
    // );
    // gf_do_action( array( 'gform_after_submission', $form['id'] ), $object->entry, $form );
    // if( !class_exists('gf_advancedpostcreation') ){
    //   return;
    // }
  
    //$this->updatePostByEntry( $entry, $form );
    // if ( function_exists( 'gf_advancedpostcreation' ) ) {
    //   $entry = GFAPI::get_entry( $entry_id );
    //   gf_advancedpostcreation()->maybe_process_feed( $entry, $form );
    // }
  }

   # {Field:348} => 348
   function getFirstFieldIdFromMergeTag($tag){
    if(empty($tag)){
      return false;
    }
    $colonArray = explode(":", $tag);
    if(count($colonArray) < 2){
      return false;
    }
    $curlyArray = explode("}", $colonArray[1]);
    if(count($curlyArray) < 2){
      return false;
    }
    return $curlyArray[0];
  }


  public function AllPostMetaUpdate($post_id, $feed, $entry, $form){

      $post = get_post($post_id);

      $postMetaFields         = rgars( $feed, "meta/postMetaFields", false );
      $postTitle              = rgars( $feed, "meta/postTitle", false );
      $postContent            = rgars( $feed, "meta/postContent", false );
      $postTaxonomyTagList    = rgars( $feed, "meta/postTaxonomy_listing_type", false );
      $postTaxonomyTagRoom    = rgars( $feed, "meta/postTaxonomy_room_type", false );
      $postTaxonomyTagAmenity = rgars( $feed, "meta/postTaxonomy_listing_amenity", false );
      $postTaxonomyTagCountry = rgars( $feed, "meta/postTaxonomy_listing_country", false );
      $postTaxonomyTagState   = rgars( $feed, "meta/postTaxonomy_listing_state", false );
      $postTaxonomyTagCity    = rgars( $feed, "meta/postTaxonomy_listing_city", false );
      $postTaxonomyTagArea    = rgars( $feed, "meta/postTaxonomy_listing_area", false );
      
      if( $postTitle !== false ){
        $gfFieldId = $this->getFirstFieldIdFromMergeTag( $postTitle );
        if( $gfFieldId !== false){
          $post->post_title  = $entry[ $gfFieldId ];
        }
      }
      if( $postContent !== false ){
        $gfFieldId = $this->getFirstFieldIdFromMergeTag( $postContent );
        if( $gfFieldId !== false){
          $post->post_content = $entry[ $gfFieldId ];
        }
      }
      if( $postTaxonomyTagList !== false ){
        $gfFieldIdS = "";
        foreach( $postTaxonomyTagList as $taxnomyInfo){
          $gfFieldId =  rgar( $taxnomyInfo, 'value');
          if( $gfFieldId !== false){
            $gfFieldIdS = $gfFieldId;
          }
        }
        $terms = [];
        for($i=0; $i < 8; $i++){
          $fieldId =  $gfFieldId . "." . $i;
          if(!empty($entry[ $fieldId  ])){
            $terms[] = $entry[ $fieldId  ];
          }
         }
         wp_set_object_terms( $post->ID, $terms, 'listing_type' );
      }

      if( $postTaxonomyTagRoom !== false ){
        $gfFieldIdS = "";
        foreach( $postTaxonomyTagRoom as $taxnomyInfo){
          $gfFieldId =  rgar( $taxnomyInfo, 'value');
          if( $gfFieldId !== false){
            $gfFieldIdS = $gfFieldId;
          }
        }
        $terms = [];
        for($i=0; $i < 4; $i++){
          $fieldId =  $gfFieldId . "." . $i;
          if(!empty($entry[ $fieldId  ])){
            $terms[] = $entry[ $fieldId  ];
          }
         }
         wp_set_object_terms( $post->ID, $terms, 'room_type' );
      }
      if( $postTaxonomyTagArea !== false ){
        foreach( $postTaxonomyTagArea as $taxnomyInfo){
          $gfFieldId =  rgar( $taxnomyInfo, 'value');
          if( $gfFieldId !== false){
           wp_set_object_terms( $post->ID, [$entry[ $gfFieldId  ]], 'listing_area' );
          }
        }
      }
      if(!empty( $entry['id'])){
        update_post_meta( $post_id, 'list_gf_entry_id', $entry['id'] ); 
      }
      if( $postTitle !== false ){
        foreach( $postMetaFields as $dataKey => $dataValue){
          $feeddata[$dataValue['key']] = $dataValue['value'] ;
          $field = GFAPI::get_field( $form,  $dataValue['value'] );
          if ( $field->type == 'checkbox' ) {
              // Get a comma separated list of checkboxes checked
              $checked = $field->get_value_export( $entry );
              // Convert to array.
              $values = explode( ', ', $checked );
              // Replace my_custom_field_key with your custom field meta key.
              update_post_meta( $post_id, $dataValue['key'], $values );
          }
          if ( $field->type == 'fileupload' ) {
            // Get a comma separated list of checkboxes checked
            $checked = $field->get_value_export( $entry );
            // Convert to array.
            $values = explode( ', ', $checked );
            //$imageIds = [];
            $loop = 1;
            foreach($values as $imagew){
              $imageIds = $this->get_attachment_id( $imagew );
              if( $loop == 1){
                set_post_thumbnail( $post_id, $imageIds );
              }
              wp_update_post( $post_id, $dataValue['key'], $imageIds  );
              $loop ++;
            }
          }
          else{
            add_post_meta( $post_id, $dataValue['key'],   $entry[$dataValue['value']]  );
          }
        }
      }
      wp_update_post( $post );
    }

  public function updatePostByEntry( $entry, $form ) {

		// Get the post and, if the current user has capabilities, update post with new content.
		$post = get_post( rgar( $entry, $this->_args['post_id'] ) );
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return;
		}

		if ( $this->_args['title'] ) {
			$post->post_title = rgar( $entry, $this->_args['title'] );
		}

		if ( $this->_args['content'] ) {
			$post->post_content = rgar( $entry, $this->_args['content'] );
		}

		if ( $this->_args['author'] ) {
			$post->post_author = (int) rgar( $entry, $this->_args['author'] );
		}

		if ( $this->_args['terms'] ) {
			// Assign custom taxonomies.
			$term_fields = $this->_args['terms'];
			foreach ( $term_fields as $field ) {
				$term_field = GFAPI::get_field( $form, $field );
				$terms      = array_map( 'intval', explode( ',', is_object( $term_field ) ? $term_field->get_value_export( $entry ) : '' ) );
				$taxonomy   = is_object( $term_field ) ? $term_field['choices'][0]['object']->taxonomy : '';

				wp_set_post_terms( $post->ID, $terms, $taxonomy );
			}
		}

		if ( $this->_args['meta'] ) {
			// Assign custom fields.
			foreach ( $this->_args['meta'] as $key => $value ) {
				$meta_input[ "$key" ] = rgar( $entry, $value );
			}

			$post->meta_input = $meta_input;

		}
		wp_update_post( $post );
	}

  function customizeGvEditEntrySuccessMessage( $entry_updated_message, $view_id, $entry, $back_link ) {
    $viewId =  $this->viewId;
    if ( $viewId === $view_id ) {
      $returnPageUrl = esc_url( get_permalink( $this->dashBoardPageId ) ); // Return to a different page
      return '<div class="gform-edit-wrapper-error alert alert-success">Entry Updated. <a href="'.$returnPageUrl.'">Return to entries details page.</a></div>';
    }
    return $entry_updated_message;
  }

  

  function wpse_enqueue_datepicker() {
    $url     = $this->plugin_url;
    $path    = $this->plugin_path;
    wp_enqueue_script( 'jquery-custom-formater', $url . 'assets/js/custom-date-formater.js' , array('jquery'), time() );
    wp_register_style( 'custom-listing-map', $url . 'assets/css/listing.css' , false, filemtime( $path . 'assets/css/listing.css') );
    wp_enqueue_style ( 'custom-listing-map' );
  }

  public function listDataEdit($attr){
    $id = !empty($attr['eid'])  ? $attr['eid'] : "";
    $viewId =  $this->listPageId;
    $listPageId = $this->listPageId;
    echo $editUrl = do_shortcode('[gv_entry_link action="edit" entry_id="'.$id.'" view_id="4506" post_id="'.$listPageId.'"]Edit List[/gv_entry_link]');
  }

  public function getAllText(){
    //$category = get_term_by('listing_area', 'listing');
    $term_query = new WP_Term_Query( array( 
      'taxonomy'               => 'listing_area', // <-- Custom Taxonomy name..
      'orderby'                => 'name',
      'order'                  => 'ASC',
      'child_of'               => 0,
      'parent'                 => 0,
      'fields'                 => 'all',
      'hide_empty'             => false,
    ) );


    if ( empty( $term_query->terms ) ) {
       return [];
    }
    $response = [];
    foreach ( $term_query->terms as $term ) {

      if(!in_array($term->name, $this->regArray)){
        continue;
      }

      $response[] = array( 'text' => $term->name, 'value' =>  $term->name );
      /*echo wp_kses_post( $term->name ) . ", ";
      echo esc_html( $term->term_id ) . ", ";
      echo esc_html( $term->slug ) . ", ";
      echo "<br>";*/
    }

    return $response;

    // echo "<pre>";
    // print_r($response);
    // echo "</pre>";

   // die();
  }

  
  public function pouplateTitleData($form){
    $fields = $this->fields; 
    if( empty($_POST)){
      return $form;
    }
    $input  = 'input_';
    $postTile  = !empty($_POST[ $input . $fields['actionTitle'] ]) ? $_POST[ $input . $fields['actionTitle'] ] : $_POST[ $input . $fields['jurnyTitle'] ];
    $_POST[ $input . $fields['homyTitle'] ] =  $postTile;
    return $form;
  }

  public function pouplateRegionDropDown($form){
    $fields = $this->fields; 
    foreach ( $form['fields'] as &$field ) {
      if ( $field->type == 'select' && $field->id ==  $fields['region'] ) {
        $choices = array();
        $textArea = $this->getAllText();
        foreach ( $textArea as $area ) {
          $choices[] = array( 'text' => $area['text'], 'value' => $area['value'] );
        }
        $field->placeholder = 'Select a Post';
        $field->choices =   $choices;
      }
    }
    return $form;
  }


  public function advancedCustomFieldCheck($value, $post_id, $field){
    if(empty($value)){
      return $value;
    }
    if(is_string($value)) {
      $value = explode( ', ', $value );
      update_post_meta($post_id, $field['name'], $value);
    }
    return $value;
  }
  
  public function apcSerializeCheckboxes( $post_id, $feed, $entry, $form ) {
      if(!empty( $entry['id'])){
        update_post_meta( $post_id, 'list_gf_entry_id', $entry['id'] ); 
      }
      foreach($feed['meta']['postMetaFields'] as $dataKey => $dataValue){
        //$feeddata[$dataValue['key']] = $dataValue['value'] ;
        $field = GFAPI::get_field( $form,  $dataValue['value'] );
        switch ( $field->type) {
          case "checkbox":
            $checked = $field->get_value_export( $entry );
            // Convert to array.
            $values = explode( ', ', $checked );
            // Replace my_custom_field_key with your custom field meta key.
            update_post_meta( $post_id, $dataValue['key'], $values );
            break;
          case "fileupload":
            // Get a comma separated list of checkboxes checked
            $checked = $field->get_value_export( $entry );
            // Convert to array.
            $values = explode( ', ', $checked );
            //$imageIds = [];
            $loop = 1;
            foreach($values as $imagew){
              $imageIds = $this->get_attachment_id( $imagew );
              if( $loop == 1){
                set_post_thumbnail( $post_id, $imageIds );
              }
              //$imageIds[] = attachment_url_to_postid($imagew);
              add_post_meta( $post_id, $dataValue['key'], $imageIds  );
              $loop ++;
            }
            break;
          default:
          update_post_meta( $post_id, $dataValue['key'], $entry[$dataValue['value']] );
        }
      }
  }

  // function kd_get_attachment_id($image_url) {
  //   global $wpdb;
  //   $prefix = $wpdb->prefix;
  //   $attachment = $wpdb->get_col($wpdb->prepare("SELECT ID FROM " . $prefix . "posts" . " WHERE guid='" . $image_url . "';"));
  //   return $attachment[0];
  // }


  function get_attachment_id( $url ) {
    $attachment_id = 0;
    $dir = wp_upload_dir();
    if ( false !== strpos( $url, $dir['baseurl'] . '/' ) ) { // Is URL in uploads directory?
      $file = basename( $url );
  
      $query_args = array(
        'post_type'   => 'attachment',
        'post_status' => 'inherit',
        'fields'      => 'ids',
        'meta_query'  => array(
          array(
            'value'   => $file,
            'compare' => 'LIKE',
            'key'     => '_wp_attachment_metadata',
          ),
        )
      );
      $posts = get_posts($query_args);
      return $posts[0];
      $query = new WP_Query( $query_args );
      if ( $query->have_posts() ) {
        foreach ( $query->posts as $post_id ) {
          $meta = wp_get_attachment_metadata( $post_id );
          $original_file       = basename( $meta['file'] );
          $cropped_image_files = wp_list_pluck( $meta['sizes'], 'file' );
          if ( $original_file === $file || in_array( $file, $cropped_image_files ) ) {
            $attachment_id = $post_id;
            break;
          }
        }
      }
    }
    return $attachment_id;
  }

  public function addedJsScript(){
    $url     = $this->plugin_url;
    $path    = $this->plugin_path;
    $cssFile = 'assets/css/style.css';
    $cssVersion = date("ymd-Gis", filemtime( $path . $cssFile )); 
    wp_enqueue_script( 'jquery-custom-list', $url . 'assets/js/custom-list.js' , array('jquery'), time() );
    wp_register_style( 'style-custom', $url . $cssFile , false, filemtime( $path . $cssFile ) );
    wp_enqueue_style ( 'style-custom' );
    wp_register_style( 'custom-spinner', $url . 'assets/css/custom-spinner.css' , false, filemtime( $path . $cssFile ) );
    wp_enqueue_style ( 'custom-spinner' );

    $ajaxObj = [
      'ajax_url'      => admin_url( 'admin-ajax.php' ),
      'field_ids'     => $this->fields,
      'form_id'       => $this->formId
    ];
    wp_localize_script( 'jquery-custom-list', 'listObj', $ajaxObj );
  }
  public function debug($param){
    echo "<pre>";
    print_r($param);
    echo "</pre>";
  }
  # We can't use this in common class
  public static function instance () {
    if ( is_null( self::$_instance ) ){
      self::$_instance = new self();       
    }
    return self::$_instance;
  } // End instance()

}
function manageListForm(){
  return manageListForm::instance();
}

manageListForm();