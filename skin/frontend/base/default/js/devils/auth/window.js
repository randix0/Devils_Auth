function toint(i) {
    i = parseInt(i);
    if(isNaN(i))
        i = 0;
    return i;
}


Window = {
    subscribers: {
        'onResize': []
    },
    deferred: [],
    params: {'indicator': '/resources/img/misc/ajax-loader.gif', 'width': 500, 'dimmer': true, 'zindex': 10000},
    instances: [],
    wCounter: 0,
    mwWidth: 0,
    mwHeight: 0,
    topMin: 25,
    calcInited: false,
    dimmerLoaded: false,
    _sbWidth: null,
    sbWidth: function() {
        if (this._sbWidth === null) {
            var el = jQuery('<div><div style="height: 75px;">1<br>1</div></div>').appendTo('body').css({overflowY: 'scroll',position: 'absolute',width: '50px',height: '50px'});
            var t = el.get(0);
            this._sbWidth = Math.max(0, t.offsetWidth - t.firstChild.offsetWidth - 1);
            el.remove();
        }
        return this._sbWidth;
    },
    calcParams: function() {
        var w = window, de = document.documentElement, htmlNode = document.getElementsByTagName('html')[0],bodyNode = document.getElementsByTagName('body')[0];
        var dwidth = Math.max(toint(w.innerWidth), toint(de.clientWidth));
        var dheight = Math.max(toint(w.innerHeight), toint(de.clientHeight));
        var sbw = this.sbWidth(); var changed = false;

        /*if ((/iphone|ipad|ipod|android|blackberry|mini|windows\sce|palm/i.test(navigator.userAgent.toLowerCase()))) {
         dwidth = Math.max(dwidth, parseInt(bodyNode.scrollWidth));
         dheight = Math.max(dheight, parseInt(bodyNode.scrollHeight));
         } else*/
        /*
         if (jQuery.browser.msie) {
         var ver = parseInt(jQuery.browser.version, 10);
         if(ver === 7) {
         if (htmlNode.scrollHeight > htmlNode.offsetHeight && !jQuery('#dimmer').is(':visible')) {
         dwidth += sbw + 1;
         }
         } else if(ver === 8) {
         if (htmlNode.scrollHeight + 3 > htmlNode.offsetHeight && !jQuery('#dimmer').is(':visible')) {
         dwidth += sbw + 1;
         }
         }
         }
         */

        Window.mwWidth = dwidth;
        Window.mwHeight = dheight;

        Window.lastInnerWidth = (Window.mwWidth - sbw - 1);

        /*
        jQuery('#dimmer').height(Window.mwHeight);
        jQuery('#modal_layer_wrap').css({width:Window.mwWidth,height:Window.mwHeight});
        jQuery('#modal_layer').width(Window.lastInnerWidth);
        */

        if (!this.calcInited)
        {
            this.calcInited = true;
            for(var k in Window.deferred) {
                Window.create.apply(this, this.deferred[k]);
            }
        }
    },
    onBodyResize: function() {
        Window.calcParams();

        var height, width, top, left;
        for(w in Window.instances) {


            win = jQuery('#' + Window.instances[w][0]);
            if(!Window.instances[w][2]){
                height = parseInt(win.height());
                top = parseInt((Window.mwHeight-height)/2);

                if(top <= Window.topMin) {
                    //height = Window.mwHeight;
                    top = Window.topMin;
                }
            } else {
                top = Window.topMin;
            }


            win.css('padding-top', top);
        }

        for(var k in Window.subscribers['onResize']) {
            executeFunctionByName(Window.subscribers['onResize'][k], window);
        }
    },
    init: function() {
        jQuery(window).resize(function() {
            Window.onBodyResize();
        });
    },
    body: function(c) {
        if(typeof(c) != 'undefined')
            return '.modal-window.' + c + ' .modal-window-body';
        else
            return false;
    },
    isOpened: function(c) {
        if(!Window.checkDuplicates(c))
            return true;
        else
            return false;
    },
    load: function(url, wClass, header, callback, autoheight) {
        if(typeof(url) != 'undefined') {
            if(typeof(wClass) == 'undefined')
                var wClass = '';
            else if(!Window.checkDuplicates(wClass))
                return false;

            if(typeof(callback) == 'undefined')
                callback = '';

            if(typeof(autoheight) == 'undefined')
                autoheight = '';

            var dataType;
            if(typeof(header) == 'undefined')
                dataType = 'json';
            else
                dataType = 'html';

            jQuery.ajax({
                url: url,
                dataType: dataType,
                beforeSend: function() {
                    if(Window.params.dimmer && !Window.dimmerLoaded) {
                        Window.placeDimmer();
                    }

                    Window.placeLoadingIndicator();
                },
                success: function(data) {
                    Window.removeLoadingIndicator();
                    if(dataType == 'html')
                        Window.create({'header':header, 'body': data}, wClass, callback, autoheight);
                    else
                        Window.create(data, wClass, callback, autoheight);
                }
            });
        }
    },
    create: function(d, wClass, callback, nonMiddled) {
        if(typeof(d) == 'undefined')
            return false;
        if (!this.calcInited)
        {
            this.deferred.push(arguments);
            return false;
        }

        //Window.closeAll();

        //Place dimmer
        if(!Window.dimmerLoaded) {
            Window.placeDimmer();
        }


        if(typeof(wClass) == 'undefined')
            var wClass = '';


        var winID = 'win_' + Window.wCounter;
        var content = '<div id="' + winID + '" class="modal-window-container"><div class="modal-window ' + ((wClass)?' ' + wClass:'') +'"><div class="modal-window-header"><div class="desc"></div><a onclick="Window.close(this)" class="modal-window-close"></a></div><div class="modal-window-body"></div></div></div>';



        jQuery('#modal_layer').append(content);
        jQuery('#' + winID).css({'z-index': Window.wCounter + 1 + Window.params.zindex});

        var width, height;
        var win = jQuery('#' + winID +' .modal-window');

        if(!wClass)
            width = Window.params.width;
        else {
            width = win.width();
            if(width <= 0) {
                width = Window.params.width;
            }
        }


        if(d.header) {
            jQuery('.modal-window-header .desc', win).html(d.header);
        }
        jQuery('.modal-window-body', win).html(d.body);


        var ptop;
        if(!nonMiddled) {
            height = win.height();
            ptop = parseInt((Window.mwHeight-height)/2);

            if(ptop <= Window.topMin) {
                //height = Window.mwHeight;
                ptop = Window.topMin;
            }
        } else {
            ptop = Window.topMin;
        }

        jQuery('#' + winID).css('padding-top', ptop);



        Window.instances.push([winID, wClass, nonMiddled]);
        Window.wCounter++;

        if(typeof(callback) != 'undefined' && callback)
            callback();
    },
    close: function(p) {
        if(typeof(p) == 'object') {
            var winID = jQuery(p).parents('.modal-window-container').attr('id');
        }
        else {
            var winID = jQuery('.modal-window.' + p).parent('.modal-window-container').attr('id');
        }


        if(winID == 'undefined') {
            return false;
        }

        jQuery('#' + winID).remove();


        for (i in Window.instances){
            if(winID == Window.instances[i][0]) {
                Window.instances.splice(i, 1);
            }
        }


        //Remove dimmer if set
        if(Window.instances.length <= 0 && Window.params.dimmer && Window.dimmerLoaded) {
            Window.removeDimmer();
        }

        return false;
    },
    closeAll: function() {
        if(Window.instances.length <= 0) {
            return false;
        }

        jQuery.each(Window.instances, function(i, item) {
            var winID = Window.instances[i][0];
            jQuery('#' + winID).remove();

            Window.instances.splice(i, 1);
        });
    },
    checkDuplicates: function(c) {
        if(typeof(c) != 'undefined' && Window.instances.length) {
            if(jQuery('.modal-window.' + c).length)
                return false
        }
        return true;
    },
    placeDimmer: function() {
        //jQuery('body').prepend('<div class="dimmer"></div>');
        jQuery('#dimmer').show();
        jQuery('#modal_layer_wrap').show();

        Window.params.zindex = parseInt(jQuery('#modal_layer_wrap').css('z-index'));
        if(isNaN(Window.params.zindex))
            Window.params.zindex = 0;

        jQuery('body').css('overflow', 'hidden');
        Window.dimmerLoaded = true;
    },
    removeDimmer: function() {
        jQuery('#dimmer').hide();
        jQuery('#modal_layer_wrap').hide();

        jQuery('body').css('overflow', 'auto');
        Window.dimmerLoaded = false;
    },
    placeLoadingIndicator: function() {
        jQuery('body').prepend('<div class="window-loading-indicator"><table width="100%" height="100%"><tr><td align="center" class="vMiddle"><img src="' + Window.params.indicator + '" /></td></tr></table></div>');
        var indicator = jQuery('body .window-loading-indicator');

        var top = toint((Window.mwHeight-indicator.height())/2);
        var left = toint((Window.mwWidth-indicator.width())/2);

        indicator.css({'top': top, 'left': left, 'z-index': Window.wCounter + 1 + Window.params.zindex});
    },
    removeLoadingIndicator: function() {
        jQuery('body .window-loading-indicator').remove();
    },
    popup: function(url) {
        var popupName = '_blank';
        var width = 554;
        var height = 349;
        var left = (Window.mwWidth - width) / 2;
        var top = (Window.mwHeight - height) / 2;
        var popupParams = 'scrollbars=0, resizable=1, menubar=0, left=' + left + ', top=' + top + ', width=' + width + ', height=' + height + ', toolbar=0, status=0';
        Window.activePopup = window.open(url, popupName, popupParams);
        Window.activePopup.blur();
        Window.activePopup.focus();
    },
    popupCheck: function(callback) {
        if (!Window.popupOpened) return false;
        try {
            if(!Window.activePopup['top']) {
                Window.popupOpened = false;
                callback();
                return true;
            }
        } catch(e) {
            Window.popupOpened = false;
            callback();
            return true;
        }
        setTimeout(Window.popupCheck(callback), 100);
    }
}

Window.init();

jQuery(document).ready(function(){
    Window.calcParams();
});