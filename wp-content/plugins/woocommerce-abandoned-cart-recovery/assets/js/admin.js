'use strict';
jQuery(document).ready(function ($) {

    $('.vi-ui.tabular.menu .item').tab({
        history: true,
        historyType: 'hash'
    });

    $('.vi-ui.accordion').accordion();

    var addRuleIndex = wacv_ls.wcCoupon;

    $('.wacv-order-stt, .wacv-sms-order-stt').select2({});

    $('.wacv-tracking-user-exclude').select2({
        width: '100%',
        placeholder: 'Select people who won\'t be tracked cart',
        ajax: {
            url: wacv_ls.ajax_url + '?action=wacv_search&param=user',
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            },
            cache: true,
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 2,
        allowClear: true,
    });


//Email rules
    addRules('email_rules');
    addRules('abd_orders');

    function addRules(slug) {
        $('.wacv-add-' + slug).on('click', function () {
            var row = '   <tr class="wacv-' + slug + '-row-target">' +
                '                            <td class="">' +
                '                                <input type="number" name="wacv_params[' + slug + '][time_to_send][]"' +
                '                                       class=""' +
                '                                       value="" min="1">' +
                '                            </td>' +
                '                            <td class="">' +
                '                                <select name="wacv_params[' + slug + '][unit][]"' +
                '                                        class="">' +
                '                                    <option value="minutes">minutes</option>' +
                '                                    <option value="hours">hours</option>' +
                '                                </select>' +
                '                            </td>' +
                '                            <td class="">' +
                '                                <select name="wacv_params[' + slug + '][template][]"' +
                '                                        class="wacv-select-email-template">' +
                list_cp.map(listCp) +
                '                                </select>' +
                '                            </td>' +
                '                            <td align="center" class="">' +
                '                                <button class="wacv-delete-' + slug + ' vi-ui small icon red button" type="button">' +
                '                                    <i class="trash icon"> </i>' +
                '                                </button>' +
                '                            </td>' +
                '                        </tr>';
            $('.wacv-' + slug + '-row-target').last().after(row);
            delete_rule('wacv-delete-' + slug);
        });
    }

    delete_rule('wacv-delete-email_rules');
    delete_rule('wacv-delete-abd_orders');

    function listCp(item) {
        return '<option value="' + item.id + '">' + item.value + '</option>';
    }

//wacv-delete-email-rule
    function delete_rule(target) {
        $('.' + target).on('click', function () {
            $(this).parents().eq(1).remove();
        });
    }


// View detail email history
    $('.wacv-get-logs').on('click', function () {
        let data = {action: 'wacv_get_email_history', id: $(this).attr('data-id')};
        $.ajax({
            url: wacv_ls.ajax_url,
            type: 'post',
            dataType: 'json',
            data: data,
            beforeSend: function () {
                $('.wacv-get-logs.' + data.id + ' .wacv-loading.icon').addClass('circle notch loading');
            },
            success: function (res) {
                let target = $('.wacv-email-reminder-popup.' + data.id);
                if (res.length === 0) {
                    let html = '<li>No history</li>';
                    target.html('<ul style="width: fit-content">' + html + '</ul>').css({
                        'background-color': 'white',
                        'border': '1px solid #eee'
                    });
                } else {
                    let html = res.map(display_email_history).join('');
                    if (html.length !== 0) {
                        target.html('<ul style="width:fit-content">' + html + '</ul>').css({
                            'background-color': 'white',
                            'border': '1px solid #eee'
                        });
                    }
                }
            },
            error: function (res) {
            }
        }).complete(function () {
            $('.wacv-get-logs.' + data.id + ' i').removeClass('circle notch loading');
        });
    });

    function display_email_history(item) {
        let display, sent_time, clicked, opened;

        if (item.type === 'messenger') {
            sent_time = item.sent_time ? `<li class="email-sent">Sent to messenger: ${item.sent_time}</li>` : '';
            opened = item.opened ? `<li class="email-opened">Opened: ${item.opened}</li>` : '';
            clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
        } else if (item.type === 'email') {
            sent_time = item.sent_time ? `<li class="email-sent">Sent to email: ${item.sent_time}</li>` : '';
            opened = item.opened ? `<li class="email-opened">Opened email: ${item.opened}</li>` : '';
            clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
        } else if (item.type === 'sms_cart') {
            sent_time = item.sent_time ? `<li class="email-sent">Sent to sms: ${item.sent_time}</li>` : '';
            opened = item.opened ? `<li class="email-opened">Opened sms: ${item.opened}</li>` : '';
            clicked = item.clicked ? `<li class="email-clicked">Clicked link: ${item.clicked}</li>` : '';
        }

        display = sent_time + opened + clicked;
        return (display);
    }

    //Messenger rules
    addMessageRules('sms_abd_cart');
    addMessageRules('sms_abd_order');
    addMessageRules('messenger_rules');

    delete_rule('wacv-delete-sms_abd_cart');
    delete_rule('wacv-delete-sms_abd_order');
    delete_rule('wacv-delete-messenger_rules');

    function addMessageRules(slug) {
        $('.wacv-add-' + slug).on('click', function () {
            var row = '   <tr class="wacv-' + slug + '-row-target" data-index="<?php echo $i ?>">' +
                '                            <td class=" wacv-messenger-time">' +
                '                                <input type="number" name="wacv_params[' + slug + '][time_to_send][]"' +
                '                                       class=""' +
                '                                       value="" min="1">' +
                '                            </td>' +
                '                            <td class="wacv-messenger-unit">' +
                '                                <select name="wacv_params[' + slug + '][unit][]"' +
                '                                        class="">' +
                '                                    <option value="minutes">minutes</option>' +
                '                                    <option value="hours">hours</option>' +
                '                                </select>' +
                '                            </td>' +
                '                            <td class="wacv-messenger-message">' +
                '                             <input type="text" value="" name="wacv_params[' + slug + '][message][]" class="wacv-message-content"> ' +
                '                            <span class="wacv-message-length"></span>' +
                '                            </td>' +
                '                            <td align="center" class="">' +
                '                                <button class="wacv-delete-' + slug + ' vi-ui small icon red button" type="button">' +
                '                                    <i class="trash icon"> </i>' +
                '                                </button>' +
                '                            </td>' +
                '                        </tr>';
            $('.wacv-' + slug + '-row-target').last().after(row);
            delete_rule('wacv-delete-' + slug);
        });
    }


    $('.wacv-log-out-fb').on('click', function () {
        $(this).addClass('loading');
        $.ajax({
            url: wacv_ls.ajax_url,
            type: 'post',
            data: {action: 'wacv_logout_fb'},
            success: function (res) {
                console.log(res);
                if (res.success) {
                    window.location.reload();
                }
            },
            error: function (res) {
                console.log(res);
            }
        });
    });

    $('.wacv-save-settings').on('click', function () {
        $(this).addClass('loading');
    });

    //Color picker

    $('.wacv-color-picker').wpColorPicker();

    //Load abandonded cart  detail

    $('.wacv-get-abd-cart-detail').on('click', function () {
        let id = $(this).attr('data-id');
        $.ajax({
            url: wacv_ls.ajax_url,
            type: 'post',
            data: {action: 'wacv_get_abd_cart_detail', id: id},
            beforeSend: function () {
                $('.wacv-get-abd-cart-detail.' + id + ' i').addClass('circle notch loading');
            },
            complete: function () {
                $('.wacv-get-abd-cart-detail.' + id + ' i').removeClass('circle notch loading');
            },
            success: function (res) {
                // console.log(res);
                if (res.length) {
                    let html = res.map(displayAbdCartDetail).join('');
                    let target = $('.wacv-get-abd-cart-detail.' + id);
                    target.after('<table class="wacv-abd-cart-detail">' + html + '</table>');
                }
            },
            error: function (res) {
                console.log(res);
            }
        });
    });

    function displayAbdCartDetail(item) {
        // console.log(item);
        var out = `<tr><td><img width="50" src="${item.img}"></td><td>${item.name} x ${item.quantity}</td><td class="last-col"> = ${item.amount}</td></tr>`;
        return out;
    }

    $('.wacv-change-token').on('click', function () {
        let newToken = randomString(32, '#aA');
        $('.wacv-change-token-input').val(newToken);
    });

    function randomString(length, chars) {
        var mask = '';
        if (chars.indexOf('a') > -1) mask += 'abcdefghijklmnopqrstuvwxyz';
        if (chars.indexOf('A') > -1) mask += 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        if (chars.indexOf('#') > -1) mask += '0123456789';
        var result = '';
        for (var i = length; i > 0; --i) result += mask[Math.floor(Math.random() * mask.length)];
        return result;
    }

    $('.wacv-readonly').on('click', function () {
        $(this).select();
        document.execCommand('copy', true);
    });
    $('.wacv-copy-icon').on('click', function () {
        $(this).parent().find('.wacv-readonly').select();
        document.execCommand('copy', true);
    });

    //Select popup template

    $('.wacv-select-popup-temp').on('click', function () {
        $('.wacv-select-popup-temp').removeClass('selected');
        $(this).addClass('selected');
    });

    //Send email abandoned manual
    $('.wacv-check-all').on('click', function () {
        $("input[type=checkbox]").prop('checked', $(this).prop('checked'));
    });

    $('.wacv-send-email-manual').on('click', function () {
        let temp = $('.wacv-template').val();
        var lists = [];
        $('.wacv-checkbox-bulk-action:checked').each(function (i) {
            let id = $(this).attr('data-id');
            let time = $(this).attr('data-time');
            lists[i] = {id, time};
        });

        if (lists.length > 0) {
            sendEmail_Manual(0, lists, temp);
        }
    });

    function sendEmail_Manual(index, lists, temp) {
        let progressBar = $('.wacv-send-email-manual-progress');
        if (index === 0) {
            progressBar.show(100);
            progressBar.val(0);
        }
        $.ajax({
            url: wacv_ls.ajax_url,
            type: 'POST',
            data: {
                action: 'send_email_abd_manual',
                id: lists[index].id,
                time: lists[index].time,
                temp: temp
            },
            success: function (res) {
                progressBar.val(((index + 1) / lists.length) * 100);

                if (res) {
                    let time = parseInt(lists[index].time) + 1;
                    $('.wacv-reminder-number.' + lists[index].id).text(time);
                    $('.wacv-checkbox-bulk-action.' + lists[index].id).attr({'data-time': time});
                }

                if (index + 1 < lists.length) {
                    sendEmail_Manual(index + 1, lists, temp);
                }
                if (index + 1 === lists.length) {
                    setTimeout(function () {
                        progressBar.hide(300);
                    }, 2000)
                }
            },
            error: function (res) {

            }
        });
    }

    $('.wacv-select-time-report').on('change', function () {

    });

    $('.wp-list-table.abandoneds').before('<div class="wacv-send-mail-progress"><progress class="wacv-send-email-manual-progress" value="0" max="100" ></progress></div>');

    //SMS config

    $('.wacv-sms-provider').on('change', function () {
        let provider = $(this).val();
        $('.wacv-providers').children().hide();
        $('.wacv-' + provider + '-config').show();
    });

    $('.wacv-send-test-sms').on('click', function () {
        let $provider = $('.wacv-sms-provider').val(), data = {},
            bitlyCheck = $('.wacv-shortlink-access-token').val(),
            noticeSpan = $('.wacv-send-test-sms-notice');

        noticeSpan.removeClass('red green').text('');

        if (!bitlyCheck) {
            noticeSpan.addClass('red').text('Bitly access token is required');
            return;
        }
        data.action = 'wacv_send_test_sms';
        data.provider = $provider;
        data.to = $('.wacv-to-phone-number').val();
        switch ($provider) {
            case 'twilio':
                data.id = $('.wacv-sms-app-id').val();
                data.secret = $('.wacv-sms-app-secret').val();
                data.number = $('.wacv-from-phone').val();
                break;
            case 'nexmo':
                data.id = $('.wacv-sms-app-id-nexmo').val();
                data.secret = $('.wacv-sms-app-secret-nexmo').val();
                data.number = $('.wacv-from-phone-nexmo').val();
                break;
            case 'plivo':
                data.id = $('.wacv-sms-app-id-plivo').val();
                data.secret = $('.wacv-sms-app-secret-plivo').val();
                data.number = $('.wacv-powerpack-uuid').val();
                break;
        }

        console.log(data);

        $.ajax({
            url: wacv_ls.ajax_url,
            type: 'post',
            data: data,
            success: function (res) {
                if (res.success) {
                    noticeSpan.addClass('green').text(res.data);
                } else {
                    noticeSpan.addClass('red').text(res.data);
                }
            },
            error: function (res) {
            }
        });
    });

    function smsCounter(text) {
        let smsLength = 0, isUnicode = false;

        for (var charPos = 0; charPos < text.length; charPos++) {
            switch (text[charPos]) {
                case "\n":
                case "[":
                case "]":
                case "\\":
                case "^":
                case "{":
                case "}":
                case "|":
                case "€":
                    smsLength += 2;
                    break;

                default:
                    smsLength += 1;
            }

            //!isUnicode && text.charCodeAt(charPos) > 127 && text[charPos] != "€" && (isUnicode = true)
            if (text.charCodeAt(charPos) > 127 && text[charPos] != "€")
                isUnicode = true;
        }

        let maxLength = isUnicode ? 63 : 160;
        return maxLength - text.length;
    }

    $('body').on('keyup', '.wacv-sms_abd_cart-table .wacv-message-content, .wacv-sms_abd_order-table .wacv-message-content', function () {
        let smsText = $(this).val();
        let characterLeft = smsCounter(smsText), cssColor = 'green';
        if (characterLeft <= 0) {
            cssColor = 'red';
        }
        $(this).closest('.wacv-messenger-message').find('.wacv-message-length').text(characterLeft).css('color', cssColor);
    });

    let messageInput = $('.wacv-sms_abd_cart-table .wacv-message-content, .wacv-sms_abd_order-table .wacv-message-content');
    messageInput.map(function (index, input) {
        let smsText = $(input).val();
        let characterLeft = smsCounter(smsText), cssColor = 'green';
        if (characterLeft <= 0) {
            cssColor = 'red';
        }
        $(input).closest('.wacv-messenger-message').find('.wacv-message-length').text(characterLeft).css('color', cssColor);
    });


    //Auto update
    jQuery('.villatheme-get-key-button').one('click', function (e) {
        let v_button = jQuery(this);
        v_button.addClass('loading');
        let data = v_button.data();
        let item_id = data.id;
        let app_url = data.href;
        let main_domain = window.location.hostname;
        main_domain = main_domain.toLowerCase();
        let popup_frame;
        e.preventDefault();
        let download_url = v_button.attr('data-download');
        popup_frame = window.open(app_url, "myWindow", "width=380,height=600");
        window.addEventListener('message', function (event) {
            /*Callback when data send from child popup*/
            let obj = jQuery.parseJSON(event.data);
            let update_key = '';
            let message = obj.message;
            let support_until = '';
            let check_key = '';
            if (obj['data'].length > 0) {
                for (let i = 0; i < obj['data'].length; i++) {
                    if (obj['data'][i].id == item_id && (obj['data'][i].domain == main_domain || obj['data'][i].domain == '' || obj['data'][i].domain == null)) {
                        if (update_key == '') {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        } else if (support_until < obj['data'][i].support_until) {
                            update_key = obj['data'][i].download_key;
                            support_until = obj['data'][i].support_until;
                        }
                        if (obj['data'][i].domain == main_domain) {
                            update_key = obj['data'][i].download_key;
                            break;
                        }
                    }
                }
                if (update_key) {
                    check_key = 1;
                    jQuery('.villatheme-autoupdate-key-field').val(update_key);
                }
            }
            v_button.removeClass('loading');
            if (check_key) {
                jQuery('<p><strong>' + message + '</strong></p>').insertAfter(".villatheme-autoupdate-key-field");
                jQuery(v_button).closest('form').submit();
            } else {
                jQuery('<p><strong> Your key is not found. Please contact support@villatheme.com </strong></p>').insertAfter(".villatheme-autoupdate-key-field");
            }
        });
    });


});