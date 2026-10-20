import jQuery from 'jquery';
import 'select2/select2';
import 'select2/select2.css';

$(function () {
    $('input[name^=\'sylius_admin_user[roles]\']').change(function () {
        if (this.checked) {
            checkedId = $(this).prop('id');
            $('input[name^=\'sylius_admin_user[roles]\']').each(function () {
                id = $(this).prop('id');
                if (id != checkedId) {
                    $(this).prop('checked', false);
                }
            });
        }
    });
});

$(document).ready(function(){
    $('.select2').each(function(){
        $(this).select2({
            width : "100%"
        });
    });

    if ($('#confirmation-button').length) {
        $('#confirmation-button').click(function(){
            if ($(this).attr('href') && $(this).attr('href') !== '') {
                window.location.href = $(this).attr('href');
            }
        });
    }
});
