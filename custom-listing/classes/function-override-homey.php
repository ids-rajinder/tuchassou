<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

#
# Modifying email to send address of listing along with the email
#
if (!function_exists('homey_email_composer')) {
  function homey_email_composer( $email, $email_type, $args ) {
    if( $email_type === 'booked_reservation' || $email_type === 'admin_booked_reservation' ){
      $args['listing_address'] = "";
      $args['host_phone_number'] = "";
      $args['host_address'] = "";

      if(!empty($args['reservation_detail_url'])){    
        $queryParam = [];
        $queryString = parse_url(  $args['reservation_detail_url'], PHP_URL_QUERY);
        parse_str($queryString, $queryParam);

        if( !empty( $queryParam['reservation_detail'])){
          $listingID  = get_post_meta( $queryParam['reservation_detail'], 'reservation_listing_id', true);
          $listingOwner  = get_post_meta( $queryParam['reservation_detail'], 'listing_owner', true);

          $args['host_phone_number'] = get_user_meta($listingOwner, 'billing_phone', true);

          $addressLineOne = [];
          $addressLineTwo = [];
          $addressLineThree = [];
          $addressFull = [];

          # First Address Line
          $addressLineOne[] = get_user_meta($listingOwner, 'homey_street_address', true);
          $addressLineOne[] = get_user_meta($listingOwner, 'homey_apt_suit', true);
          # Second Address Line
          $addressLineTwo[] = get_user_homey_email_composer($listingOwner, 'homey_city', true);
          $addressLineTwo[] = get_user_homey_email_composer($listingOwner, 'homey_state', true);
          $addressLineTwo[] = get_user_meta($listingOwner, 'homey_zipcode', true);
          # Third Address Line
          $addressLineThree[] = get_user_meta($listingOwner, 'homey_neighborhood', true);
          $addressLineThree[] = get_user_meta($listingOwner, 'homey_country', true);
          # Remove Empty Value
          $addressLineOne = array_filter($addressLineOne);
          $addressLineTwo = array_filter($addressLineTwo);
          $addressLineThree = array_filter($addressLineThree);
          # Joinging address
          $addressFull[] = implode(", ", $addressLineOne);
          $addressFull[] = implode(", ", $addressLineTwo);
          $addressFull[] = implode(", ", $addressLineThree);
          $addressFull = array_filter($addressFull);
          $args['host_address'] = implode('<br/>', $addressFull);

          if(!empty($listingID)){
                        
            $addressLineOne = [];
            $addressLineTwo = [];
            $addressLineThree = [];
            $addressFull = [];
        
            # First Address Line
            $addressLineOne[] = get_post_meta($listingID, 'custom_homey_address', true);
            $addressLineOne[] = get_post_meta($listingID, 'list_complement', true);
            $addressLineOne[] = get_post_meta($listingID, 'list_ville', true);
            # Second Address Line
            $addressLineTwo[] = get_post_meta($listingID, 'list_departement', true);
            $addressLineTwo[] = get_post_meta($listingID, 'list_code_postal', true);
            $addressLineTwo[] = get_post_meta($listingID, 'list_region', true);
            # Third Address Line
            $addressLineThree[] = get_post_meta($listingID, 'list_pays', true);
            # Remove Empty Value
            $addressLineOne = array_filter($addressLineOne);
            $addressLineTwo = array_filter($addressLineTwo);
            $addressLineThree = array_filter($addressLineThree);
            # Joinging address
            $addressFull[] = implode(", ", $addressLineOne);
            $addressFull[] = implode(", ", $addressLineTwo);
            $addressFull[] = implode(", ", $addressLineThree);
            $addressFull = array_filter($addressFull);
            $args['listing_address'] = implode('<br/>', $addressFull);

          }
        }
      }
    }

    $value_message = homey_option('homey_' . $email_type);
    $value_subject = homey_option('homey_subject_' . $email_type);
    $value_subject = homey_option('homey_subject_' . $email_type);

    do_action( 'wpml_register_single_string', 'homey', 'homey_email_' . $value_message, $value_message );
    do_action( 'wpml_register_single_string', 'homey', 'homey_email_subject_' . $value_subject, $value_subject );

    $filters = homey_emails_filter_replace( $email, $value_message, $value_subject, $args);
    return $filters;
  }
}