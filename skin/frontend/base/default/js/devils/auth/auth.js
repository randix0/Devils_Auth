jQuery.noConflict();

Cookie = {
    get: function(name) {
        var cookie = " " + document.cookie;
        var search = " " + name + "=";
        var setStr = null;
        var offset = 0;
        var end = 0;
        if (cookie.length > 0) {
            offset = cookie.indexOf(search);
            if (offset != -1) {
                offset += search.length;
                end = cookie.indexOf(";", offset);
                if (end == -1) {
                    end = cookie.length;
                }
                setStr = unescape(cookie.substring(offset, end));
            }
        }
        return (setStr);
    },
    set: function(name, value, expires, path, domain, secure) {
        document.cookie = name + "=" + escape(value) + ((expires) ? "; expires=" + expires : "") + ((path) ? "; path=" + path : "") + ((domain) ? "; domain=" + domain : "") + ((secure) ? "; secure" : "");
    }
}

Auth = {
    vkontakte: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';

        var url='https://oauth.vk.com/authorize?client_id='+vk_app_id+'&display=popup&scope=friends,email&redirect_uri='+encodeURIComponent('http://'+location.host+'/auth/index/step1/handler/vkontakte/'+reason)+'&response_type=code';

        return Auth.popup(url, '/auth/index/step2/handler/vkontakte/'+reason);
    },
    facebook: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';

        var url='https://www.facebook.com/dialog/oauth?client_id='+fb_app_id+'&display=popup&scope=user_birthday,email,publish_actions&redirect_uri='+encodeURIComponent('http://'+location.host+'/auth/index/step1/handler/facebook/'+reason);

        return Auth.popup(url, '/auth/index/step2/handler/facebook/'+reason);

    },
    google: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';

        var url='https://accounts.google.com/o/oauth2/auth?response_type=code&approval_prompt=auto&access_type=offline&client_id='+gl_app_id+'&display=popup&scope=https://www.googleapis.com/auth/userinfo.profile+https://www.googleapis.com/auth/userinfo.email&redirect_uri='+encodeURIComponent('http://'+location.host+'/auth/index/step1/handler/google/'+reason);

        return Auth.popup(url, '/auth/index/step2/handler/google/'+reason);

    },
    soundcloud: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';

        var url='https://soundcloud.com/connect?state=' + auth_state +'&client_id='+sc_app_id+'&response_type=code&scope=non-expiring&display=popup;&redirect_uri='+encodeURIComponent('http://'+location.host+'/auth/index/step1/handler/soundcloud/'+reason);

        return Auth.popup(url, '/auth/index/step2/handler/soundcloud/'+reason);
    },
    linkedin: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';
        var url='https://www.linkedin.com/uas/oauth2/authorization?response_type=code&client_id='+ln_app_id+'&scope=20r_emailaddress&state=HsIHpOmLWpQUP9th8K7cFwZDZD&redirect_uri='+encodeURIComponent('http://'+location.host+'/auth/index/step1/handler/linkedin/'+reason);;

        return Auth.popup(url, '/auth/index/step2/handler/linkedin/'+reason);
    },
    twitter: function(reason) {
        if(typeof(reason) == 'undefined')
            reason = '';
        var url=BASE_URL+'twitter/auth/'+reason;
        return Auth.popup(url, '/auth/twitter_step2/'+reason);
    },
    popup: function(url, step2) {
        Cookie.set('sl','0','0','/');

        Window.popup(url);
        Window.popupOpened = true;

        var popupCheck = function() {
            if (!Window.popupOpened) return false;
            try {
                if(Window.activePopup.closed/* || !Window.activePopup.top*/) {
                    Window.popupOpened = false;
                    console.log('Auth.popup setTimeout...OK');
                    Auth.authCallback(step2);
                    return true;
                }
            } catch(e) {
                Window.popupOpened = false;
                Auth.authCallback(step2);
                return true;
            }
            setTimeout(popupCheck, 100);
            console.log('Auth.popup setTimeout...');
        }
        setTimeout(popupCheck, 100);

        return false;
    },
    authCallback: function(url) {
        var state = Cookie.get('sl');
        console.log('Auth.authCallback '+url+' ...');
        if(state != null && parseInt(state) > 0) {
            jQuery.ajax({
                url: url,
                dataType: 'json',
                beforeSend: function() {
                    if(Window.params.dimmer && !Window.dimmerLoaded) {
                        Window.placeDimmer();
                    }
                    console.log('Auth.authCallback ajax ...');
                    Window.placeLoadingIndicator();
                },
                success: function(data) {
                    console.log('Auth.authCallback ajax data.status='+data.status);
                    Window.removeLoadingIndicator();
                    if(data.status == 3)
                        Window.create({'header':data.header, 'body': data.body}, 'win-social-signup-msg');
                    else if(data.status == 2)
                        Window.create({'header':data.header, 'body': data.body}, 'win-signup-social');
                    else if(data.status == 1) {
                        if (Auth.forceStop){
                            Auth.forceStop = null;
                            Auth.successful = 1;
                            return true;
                        }
                        else {
                            if (typeof(data.url) != 'undefined' && data.url) {
                                window.location = data.url;
                            } else {
                                window.location = window.location;
                            }
                        }
                    }
                }
            });
        }
    },
    forceStop: null,
    successful: null
}