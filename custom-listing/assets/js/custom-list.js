jQuery( document ).ready(function($){



  function listFieldDatepicker(formId, fieldId, column) {
    var listField = '#field_' + formId + '_' + fieldId,
    columnClass = '.gfield_list_' + fieldId + '_cell' + column + ' input';
    jQuery.datepicker.setDefaults({
        showOn: 'both',
        buttonImage: '/wp-content/plugins/gravityforms/images/calendar.png',
        buttonImageOnly: true,
        dateFormat: 'dd/mm/yy',
        firstDay: 1
    });
    jQuery(columnClass).css({'width': '80%', 'margin-right': '2px'}).datepicker({ dateFormat: 'd M yy' });
    jQuery(listField).on('click', '.add_list_item', function () {
        jQuery('.ui-datepicker-trigger').remove();
        jQuery(columnClass).removeClass('hasDatepicker').removeAttr('id').datepicker({ dateFormat: 'd M yy' });
    });
  }


var autocomplete;
var address1Field;
var address2Field;
var postalField;
var map;
var marker;
var infowindow;
var latlng;
function initialize() {
    // TODO: Change the following to jQuery reference
    address1Field = document.querySelector("#input_12_52");
    address2Field = document.querySelector("#input_12_53");
    postalField   = document.querySelector("#input_12_56");
    // Create the autocomplete object, restricting the search predictions to
    // addresses in the US and Canada.
    autocomplete = new google.maps.places.Autocomplete(address1Field, {
      componentRestrictions: { country: ["fr"] },
      fields: ["address_components", "geometry"],
      types: ["address"],
    });
    address1Field.focus();
    autocomplete.addListener("place_changed", fillInAddress);
    mapCodeRun();
    
}


function mapCodeRun(){

  mylat =  jQuery('#input_12_170').val();
  mylong = jQuery('#input_12_171').val();

  latlng = new google.maps.LatLng(mylat,mylong);
  map = new google.maps.Map(document.getElementById('map-tec'), {
    center: latlng,
    zoom: 13
  });
   marker = new google.maps.Marker({
    map: map,
    position: latlng,
    draggable: true,
    anchorPoint: new google.maps.Point(0, -29)
  });

  infowindow = new google.maps.InfoWindow();  

  google.maps.event.addListener(marker, 'click', function() {
    infowindow.close();
    marker.setVisible(false);
    var place = autocomplete.getPlace();
    if (!place.geometry) {
        window.alert("Autocomplete's returned place contains no geometry");
        return;
    }

    // If the place has a geometry, then present it on a map.
    if (place.geometry.viewport) {
        map.fitBounds(place.geometry.viewport);
    } else {
        map.setCenter(place.geometry.location);
        map.setZoom(17);
    }
    marker.setIcon(({
        url: place.icon,
        size: new google.maps.Size(71, 71),
        origin: new google.maps.Point(0, 0),
        anchor: new google.maps.Point(17, 34),
        scaledSize: new google.maps.Size(35, 35)
    }));
    marker.setPosition(place.geometry.location);
    marker.setVisible(true);

    var address = '';
    if (place.address_components) {
        address = [
          (place.address_components[0] && place.address_components[0].short_name || ''),
          (place.address_components[1] && place.address_components[1].short_name || ''),
          (place.address_components[2] && place.address_components[2].short_name || '')
        ].join(' ');
    }

  var iwContent = '<div id="iw_container">' +
    '<div class="iw_title"><b>Location</b> : '+address+'</div></div>';
    // including content to the infowindow
    infowindow.setContent(iwContent);
    // opening the infowindow in the current map and at the current marker location
    infowindow.open(map, marker);
});
 // this function will work on marker move event into map 
 google.maps.event.addListener(marker, 'dragend', function() {
    var lat = marker.getPosition().lat();
    var lng = marker.getPosition().lng();
    jQuery('#input_12_170').val(lat);
    jQuery('#input_12_171').val(lng);
    jQuery('#input_12_185').val(lat + ',' + lng).trigger("change");

 });
}

function fillInAddress() {
  // Get the place details from the autocomplete object.
  const place = autocomplete.getPlace();
  var address1 = "";
  var postcode = "";

      infowindow.close();
      marker.setVisible(false);
      // If the place has a geometry, then present it on a map.
      if (place.geometry.viewport) {
          map.fitBounds(place.geometry.viewport);
      } else {
          map.setCenter(place.geometry.location);
          map.setZoom(17);
      }
      marker.setIcon(({
          url: place.icon,
          size: new google.maps.Size(71, 71),
          origin: new google.maps.Point(0, 0),
          anchor: new google.maps.Point(17, 34),
          scaledSize: new google.maps.Size(35, 35)
      }));
      marker.setPosition(place.geometry.location);
      marker.setVisible(true);

      var address = '';
      if (place.address_components) {
          address = [
            (place.address_components[0] && place.address_components[0].short_name || ''),
            (place.address_components[1] && place.address_components[1].short_name || ''),
            (place.address_components[2] && place.address_components[2].short_name || '')
          ].join(' ');
      }

    var iwContent = '<div id="iw_container">' +
      '<div class="iw_title"><b>Location</b> : '+address+'</div></div>';
      // including content to the infowindow
      infowindow.setContent(iwContent);
      // opening the infowindow in the current map and at the current marker location
      infowindow.open(map, marker);

  var lat = place.geometry.location.lat(),
      lng = place.geometry.location.lng();
      jQuery('#input_12_170').val(lat).trigger("change");
      jQuery('#input_12_171').val(lng).trigger("change");
      jQuery('#input_12_185').val(lat + ',' + lng).trigger("change");
  // Get each component of the address from the place details,
  // and then fill-in the corresponding field on the form.
  // place.address_components are google.maps.GeocoderAddressComponent objects
  // which are documented at http://goo.gle/3l5i5Mr
  for (const component of place.address_components) {
    const componentType = component.types[0];
    //console.log(componentType);
    switch (componentType) {
      case "street_number": {
        address1 = `${component.long_name} ${address1}`;
        break;
      }
      case "route": {
        address1 += component.long_name;
        break;
      }
      case "postal_code": {
        postcode = `${component.long_name}${postcode}`;
        break;
      }
      case "postal_code_suffix": {
        postcode = `${postcode}-${component.long_name}`;
        break;
      }
      case "locality":
        //document.querySelector("#input_12_54").value = component.long_name;
        jQuery("#input_12_54").val(component.long_name);
        break;

      case "administrative_area_level_2": 
        //document.querySelector("#input_12_55").value = component.long_name;
        jQuery("#input_12_55").val(component.long_name);
        break;

      case "administrative_area_level_1": 
       // document.querySelector("#input_12_146").value = component.long_name;
       jQuery("#input_12_146").val(component.long_name);
        break;        
        
      case "country":
       // document.querySelector("#input_12_58").value = component.long_name;
       jQuery("#input_12_58").val(component.long_name);
        break;
    }
  }

  // input_52 = street_number + route | Adresse 
  // input_53 = X  | Complément d'adresse
  // input_54 = "locality", "political" | Ville
  // input_55 = "administrative_area_level_2", "political" | Département* - Dropdown

  // input_146 =  "administrative_area_level_1", "political"
  // input_58 = Country | Default value to be france 

  // address1Field = document.querySelector("#input_12_52");
  // address2Field = document.querySelector("#input_12_53");
  // postalField   = document.querySelector("#input_12_56");

  address1Field.value = address1;
  postalField.value = postcode;
  address2Field.focus();
}

function renderDatePicker(event, form_id, current_page){
  //google.maps.event.addDomListener(window, 'load', initialize);
  listFieldDatepicker(12, 147, 1);

  // Journee field
  listFieldDatepicker(12, 199, 1);

  // Bracelet Field
  listFieldDatepicker(12, 148, 1);
  listFieldDatepicker(12, 148, 2);
  initialize();
}

jQuery(document).on('gform_page_loaded', renderDatePicker);
jQuery(document).ready(renderDatePicker);

});






   

