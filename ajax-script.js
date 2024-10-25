jQuery(document).ready(function($) {
    $('#subscription_options').on('change', function() {
        var selectedValue = $(this).val();
        
        $.ajax({
            type: 'POST',
            url: ajax_object.ajax_url,
            data: {
                action: 'check_subscription_selection',
                selected_subscription: selectedValue,
                product_id: $('#product_id').val() 
            },
            success: function(response) {
                // console.log(response)
                if (response.success) {
                    updatePriceDisplay(response.data.new_price);
                } else {
                    alert(response.data);
                }
            },
            error: function() {
                alert('An error occurred while processing your request.');
            }
        });
    });

    function updatePriceDisplay(price) {
        $('#price_display').html(price.toFixed(2) + ' kr');
    }
});
