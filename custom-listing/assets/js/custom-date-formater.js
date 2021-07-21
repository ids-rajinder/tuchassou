jQuery( document ).ready(function($) {

  var nowDate = new Date();
  var today = new Date(nowDate.getFullYear(), nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);
  var maxLimitDate = new Date(nowDate.getFullYear() + 1, nowDate.getMonth(), nowDate.getDate(), 0, 0, 0, 0);

 // jQuery('.search-banner-desktop input[name="arrive"]', ).daterangepicker({
  jQuery('.search-date-range-arrive input[name="arrive"]', ).daterangepicker({
    
    singleDatePicker: true,
    showDropdowns: true,
    //timePicker: true,
    minDate: today,
    autoUpdateInput: false,
    locale: {
      format: 'DD-MM-YYYY'
    }
  }, function(start, end, label) {
    var end =  end.add(1, 'day').startOf('day')
    var eDate =  end.format('DD-MM-YYYY');
    var sDate = start.format('DD-MM-YYYY');

    //console.log(sDate);
    //console.log(eDate);

    if (sDate !== undefined || sDate !== null) {
      jQuery('input[name="arrive"]').val(sDate);
      jQuery('input[name="depart"]').val(eDate);
    }else{
      jQuery('input[name="arrive"]').val('');
      jQuery('input[name="depart"]').val('');
    }
  });
  jQuery('input[name="arrive"]').on('apply.daterangepicker', function(ev, picker) {
    $(this).val(picker.startDate.format('DD-MM-YYYY'));
  });

  jQuery('input[name="arrive"]').on('cancel.daterangepicker', function(ev, picker) {
    jQuery(this).val('');
    jQuery('input[name="depart"]').val('');
  });
});


