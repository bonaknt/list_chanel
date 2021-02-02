$(document).ready(function () {
    $('.deleteBtn').on('click', function () {
        console.log('aaaaa');
        let removeUrl = $(this).attr('data-remove-url');
        $('.remove_item').attr('data-remove-url', removeUrl);
        $('#confirmationModal').modal('toggle');
    });

    $(".remove_item").click(function () {
        let removeUrl = $(this).attr('data-remove-url');
        $.ajax({
            url: removeUrl,
            type: 'POST',
            data: {},
            contentType: 'text',
            success: function(data)
            {
                $('div.modal-content').html(data)
                $('#confirmationModal').modal('toggle');
                location.reload();
            },
            error: function(jqXHR){
                $('div.modal-content').html(jqXHR.responseText)
                $('#confirmationModal').modal('toggle');
                location.reload();
            }
        });
    });
});