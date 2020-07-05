'use strict';
jQuery(document).ready(function ($) {
    const fbCbPlugin = {
        user_ref: '',
        init: function () {
            window.user_ref = this.user_ref = this.uniqueParam(32);
            if (!window.getCookie('wacv_fb_checkbox')) {
                let html = `<div class='fb-messenger-checkbox' origin='${Fbook.homeURL}' page_id='${Fbook.pageID}' messenger_app_id='${Fbook.appID}' user_ref='${this.user_ref}' allow_login='true' size='large' ref='wacv_ref_message'></div>`;
                $('.fb-messenger-checkbox-container').html(html);
            }
        },
        uniqueParam: function (length) {
            let chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghiklmnopqrstuvwxyz'.split('');
            if (!length) {
                length = Math.floor(Math.random() * chars.length);
            }
            let str = '';
            for (let i = 0; i < length; i++) {
                str += chars[Math.floor(Math.random() * chars.length)];
            }
            return str;
        },
    };

    fbCbPlugin.init();
    window.fbAsyncInit = function () {
        FB.init({
            appId: Fbook.appID,
            autoLogAppEvents: true,
            xfbml: true,
            version: "v3.3"
        });
        FB.Event.subscribe('messenger_checkbox', function (e) {
            // console.log("messenger_checkbox event", e);
            if (e.event === 'rendered') {
                // console.log("rendered");
            } else if (e.event === 'checkbox') {
                // console.log("Checkbox state: " + e.state);
                if (e.state === 'checked') {
                    window.cbStt = true;
                } else {
                    window.cbStt = false;
                }
            } else if (e.event === 'not_you') {
                // console.log("User clicked 'not you'");
            } else if (e.event === 'hidden') {
                window.fbHidden = true;
                // console.log("hidden");
            }
        });

        FB.getLoginStatus(function (response) {
            // console.log('login status:', response);
            if (response.status === 'connected') {
                //console.log(response);
            } else if (response.status === 'not_authorized') {
                //console.log('not connected to app');
            } else if (response.status === 'unknown') {
                window.cbRequire = false;
                //console.log('not logged in to fb');
            }
        });
    };
    window.connectFB = function () {
        (function (d, s, id) {  //connect fb to render checkbox plugin
            if (Fbook.appID && Fbook.userToken) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) {
                    return;
                }
                js = d.createElement(s);
                js.id = id;
                js.src = "//connect.facebook.net/" + Fbook.appLang + "/sdk.js"; // whole SDK
                fjs.parentNode.insertBefore(js, fjs);
            }
        }(document, 'script', 'facebook-jssdk'));
    };
    window.confirmOptin = {
        run: function () {
            FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
                'app_id': Fbook.appID,
                'page_id': Fbook.pageID,
                'ref': 'wacv_ref_message',
                'user_ref': window.user_ref
            });
        }
    };
});