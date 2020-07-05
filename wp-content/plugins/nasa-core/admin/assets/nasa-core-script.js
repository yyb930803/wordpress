jQuery(document).ready(function ($) {
    'use strict';
    $('body').on('click', '.nasa-clear-variations-cache', function() {
        var _this = $(this);
        var _ok = $(_this).attr('data-ok');
        var _miss = $(_this).attr('data-miss');
        var _fail = $(_this).attr('data-fail');
        if(!$(_this).hasClass('nasa-disable')) {
            $(_this).addClass('nasa-disable');
            $.ajax({
                url: ajax_admin_nasa_core,
                type: 'get',
                dataType: 'html',
                data: {
                    action: 'nasa_clear_all_cache'
                },
                beforeSend: function() {
                    if($('.nasa-admin-loader').length) {
                        $('.nasa-admin-loader').show();
                    }
                },
                success: function(res){
                    $(_this).removeClass('nasa-disable');
                    if($('.nasa-admin-loader').length) {
                        $('.nasa-admin-loader').hide();
                    }
                    
                    if(res === 'ok') {
                        alert(_ok);
                    } else {
                        alert(_miss);
                    }
                },
                error: function () {
                    $(_this).removeClass('nasa-disable');
                    if($('.nasa-admin-loader').length) {
                        $('.nasa-admin-loader').hide();
                    }
                    
                    alert(_fail);
                }
            });
        }
    });
    
    if($('.term-parent-wrap select[name="parent"]').val() === '-1') {
        $('.nasa-term-root').show();
        if ($('.nasa-term-root select').length) {
            $('.nasa-term-root select').each(function () {
                var _val = $(this).val();
                var _name = $(this).attr('name');
                $('.nasa-term-root-child.' + _name).hide();
                if (_val) {
                    $('.nasa-term-root-child.nasa-term-' + _name + '-' + _val).show();
                }
            });
        }
    } else {
        $('.nasa-term-root, .nasa-term-root-child').hide();
    }

    $('body').on('change', '.term-parent-wrap select[name="parent"]', function() {
        var _val = $(this).val();
        if(_val === '-1') {
            $('.nasa-term-root').show();
            
            if ($('.nasa-term-root select').length) {
                $('.nasa-term-root select').each(function () {
                    var _val = $(this).val();
                    var _name = $(this).attr('name');
                    
                    $('.nasa-term-root-child.' + _name).hide();
                    if (_val) {
                        $('.nasa-term-root-child.nasa-term-' + _name + '-' + _val).show();
                    }
                });
            }
        } else {
            $('.nasa-term-root, .nasa-term-root-child').hide();
        }
    });
    
    $('body').on('change', '.nasa-term-root select', function() {
        var _val = $(this).val();
        var _name = $(this).attr('name');

        $('.nasa-term-root-child.' + _name).hide();
        if (_val) {
            $('.nasa-term-root-child.nasa-term-' + _name + '-' + _val).show();
        }
    });
});
