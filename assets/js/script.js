
jQuery(document).ready(function($){
  $('#ccf-form').on('submit', function(e){
    e.preventDefault();
    var formData = {
      action: 'ccf_submit_form',
      nonce: ccf_ajax.nonce,
      name: $('#ccf-name').val(),
      email: $('#ccf-email').val(),
      message: $('#ccf-message').val()
    };
    $.post(ccf_ajax.ajax_url, formData, function(response){
      if (response.success) {
        $('#ccf-response').html('<span style="color:green;">'+response.data+'</span>');
        $('#ccf-form')[0].reset();
      } else {
        $('#ccf-response').html('<span style="color:red;">'+response.data+'</span>');
      }
    });
  });
});
