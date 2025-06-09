function add_custom_form_handler() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $(document).on('submit', '.wpcf7-form', function(e) {
            e.preventDefault();
            
            const $form = $(this);
            const formData = new FormData(this);
            formData.append('action', 'process_cf7_form');
            
            $.ajax({
                type: 'POST',
                url: '<?php echo admin_url('admin-ajax.php'); ?>',
                data: formData,
                processData: false,
                contentType: false,
                beforeSend: function() {
                    $form.find('.wpcf7-spinner').addClass('is-active');
                },
                success: function(response) {
                    if(response.success) {
                        if(response.data.message) {
                            $form.find('.wpcf7-response-output')
                                 .attr('class', 'wpcf7-response-output wpcf7-mail-sent-ok')
                                 .html(response.data.message)
                                 .show();
                        }
                        $form[0].reset();
                    } else {
                        if(response.data.message) {
                            $form.find('.wpcf7-response-output')
                                 .attr('class', 'wpcf7-response-output wpcf7-validation-errors')
                                 .html(response.data.message)
                                 .show();
                        }
                    }
                },
                error: function() {
                    $form.find('.wpcf7-response-output')
                         .attr('class', 'wpcf7-response-output wpcf7-validation-errors')
                         .html(wpcf7.messages.fail)
                         .show();
                },
                complete: function() {
                    $form.find('.wpcf7-spinner').removeClass('is-active');
                }
            });
        });
    });
    </script>
    <?php
}
add_action('wp_footer', 'add_custom_form_handler');

// AJAX request handler
function process_cf7_form() {
    if(isset($_POST)) {
        $form_id = isset($_POST['_wpcf7']) ? intval($_POST['_wpcf7']) : 0;
        
        if($form_id) {
            $contact_form = wpcf7_contact_form($form_id);
            
            if($contact_form) {
                $result = $contact_form->submit();
                wp_send_json_success([
                    'message' => $result['message']
                ]);
            }
        }
    }
    
    wp_send_json_error([
        'message' => wpcf7_get_message('mail_sent_ng')
    ]);
}
add_action('wp_ajax_process_cf7_form', 'process_cf7_form');
add_action('wp_ajax_nopriv_process_cf7_form', 'process_cf7_form');