/**
 Workaround for iOS 6 setTimeout bug using requestAnimationFrame to simulate timers during Touch/Gesture-based events
 Author: Jack Pattishall (jpattishall@gmail.com)
 This code is free to use anywhere (MIT, etc.)

 Usage: Pass TRUE as the final argument for setTimeout or setInterval.

 Ex:
 setTimeout(func, 1000) // uses native code
 setTimeout(func, 1000, true) // uses workaround

 Demos:
 http://jsfiddle.net/xKh5m/ - uses native setTimeout
 http://jsfiddle.net/ujxE3/ - uses workaround timers
 */
(function () {
// Only apply settimeout workaround for iOS 6 - for all others, we map to native Timers
    if (!navigator.userAgent.match(/OS 6(_\d)+/i))
        return;

// Prevent multiple applications
    if (window.getTimeouts !== undefined)
        return;

    var TIMERID = 'rafTimer',
        touchTimeouts = {},
        touchIntervals = {},
    /* Reference to original timers */
        _st = window.setTimeout,
        _si = window.setInterval,
        _ct = window.clearTimeout,
        _ci = window.clearInterval,
    /* Request animation timers */
        _clearTouchTimer = function (uid, isInterval) {
            var interval = isInterval || false,
                timer = interval ? touchIntervals : touchTimeouts;
            if (timer[uid]) {
                timer[uid].callback = undefined;
                timer[uid].loop = false;
                return true;
            } else {
                return false;
            }
        },
        _touchTimer = function (callback, wait, isInterval) {
            var uid,
                name = callback.name || TIMERID + Math.floor(Math.random() * 1000),
                delta = new Date().getTime() + wait,
                interval = isInterval || false,
                timer = interval ? touchIntervals : touchTimeouts;
            uid = name + "" + delta;
            timer[uid] = {};
            timer[uid].loop = true;
            timer[uid].callback = callback;
            function _loop() {
                var now = new Date().getTime();
                if (timer[uid].loop !== false) {
                    timer[uid].requestededFrame = webkitRequestAnimationFrame(_loop);
                    timer[uid].loop = now <= delta;
                } else {
                    if (timer[uid].callback)
                        timer[uid].callback();
                    if (interval) {
                        delta = new Date().getTime() + wait;
                        timer[uid].loop = now <= delta;
                        timer[uid].requestedFrame = webkitRequestAnimationFrame(_loop);
                    } else {
                        delete timer[uid];
                    }
                }
            }
            ;
            _loop();
            return uid;
        },
        _timer = function (callback, wait, touch, isInterval) {
            if (touch) {
                return _touchTimer(callback, wait, isInterval);
            } else {
                return isInterval ? _si(callback, wait) : _st(callback, wait);
            }
        },
        _clear = function (uid, isInterval) {
            if (uid.indexOf && uid.indexOf(TIMERID) > -1) {
                return _clearTouchTimer(uid, isInterval);
            } else {
                return isInterval ? _ci(uid) : _ct(uid);
            }
        };
    /* Returns raf-based timers; For debugging purposes */
    window.getTimeouts = function () {
        return {timeouts: touchTimeouts, intervals: touchIntervals}
    };

    /* Exposed globally */
    window.setTimeout = function (callback, wait, touch) {
        return _timer(callback, wait, touch);
    };
    window.setInterval = function (callback, wait, touch) {
        return _timer(callback, wait, touch, true);
    };
    window.clearTimeout = function (uid) {
        return _clear(uid);
    };
    window.clearInterval = function (uid) {
        return _clear(uid, true);
    };
})();

//footagesearch namespace
var fs = {
    toggleSelection: function (clipID) {
        var clip = jQuery('#footagesearch-clip-' + clipID);
        if (clip.length > 0) {
            var clipInput = clip.find('.footagesearch-clip-input').first();
            clip.toggleClass('selected');
            if (clipInput.val() == 1) {
                clipInput.val(0);
                //clip.draggable('disable');
            }
            else {
                clipInput.val(1);
                clip.draggable('enable');
            }
        }
    },
    is_touch_device: function () {
        return 'ontouchstart' in window // works on most browsers
            || 'onmsgesturechange' in window; // works on ie10
    },
    is_mobile: function () {
        if (/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent)) {
            return true;
        }
        return false;
    }
};

(function ($) {

    function selectAllClips() {
        var clipInputsFound = $('#select_found_set_values');
        clipInputsFound.val('');
        var clipInputs = $('.footagesearch-clip-input');
        clipInputs.val(1);
        clipInputs.parents('.footagesearch-clip').addClass('selected');
        return false;
    }

    function selectFoundSet() {
        deselectAllClips();
        var clipInputs = $('#select_found_set_values');
        clipInputs.val('found_set');
        return clipInputs.val();
    }

    function selectAllLog() {
        deselectAllClips();
        var clipInputs = $('#select_all_log_values');
        clipInputs.val('all_log');
        var search_all_log = $('#select_all_log_values').val();
        if (search_all_log == 'all_log') {
            fs.ct.addItems(search_all_log);
            deselectAllClips();
        }
        return clipInputs.val();
    }

    function deselectAllClips() {
        var clipInputs = $('.footagesearch-clip-input');
        clipInputs.parents('.footagesearch-clip').removeClass('selected');
        clipInputs.val(0);
        return false;
    }

    function getSelectedClips() {
        var selected = $('.footagesearch-clip.selected');
        var selectedIDs = [];
        if (selected.length > 0) {
            selected.each(function (index) {
                var idArr = $(this).attr('id').split('-');
                if (idArr[2] !== undefined)
                    selectedIDs.push(idArr[2]);
            });
        }
        return selectedIDs;
    }

    function addItemsToCart(clipsIDs) {
        if (clipsIDs) {
            $.post('/index.php?ajax=true', {
                    cart_action: 'add_items',
                    items_ids: clipsIDs
                },
                function (data) {
                    if (data.success) {
                        if (data.items_count || data.items_count == 0) {
                            $('.footagesearch_cart_count_value').text(data.items_count);
                        }
                        if (data.delete_buttons) {
                            fs.ct.setAfterAddButtons(data);
                        }
                        if (data.droppable_area) {
                            fs.ct.refreshDroppableArea(data.droppable_area);
                        }
                    }
                }
            );
        }
    }

    function deleteClips(clipsIDs) {
        if (clipsIDs) {
            if (window.location.href.indexOf('?') >= 0) {
                window.location += '&delete=' + clipsIDs;
            }
            else {
                window.location += '?delete=' + clipsIDs;
            }

        }
    }

    function addSelectedToCart() {
        var selected = getSelectedClips();
        //var search_selected = selectFoundSet();
        var search_selected = $('#select_found_set_values').val();
        if (search_selected == 'found_set') {
            fs.ct.addItems(search_selected);
            deselectAllClips();
        }
        if (selected.length > 0) {
            fs.ct.addItems(selected);
            deselectAllClips();
        }
    }

    function delSelectedToCart() {
        var selected = getSelectedClips();
        if (selected.length > 0) {
            $.each(selected, function (k, v) {
                fs.ct.removeItem(v);
            });
            deselectAllClips();
        }
    }

    function addSelectedToClipbin() {
        var selected = getSelectedClips();

        //var search_selected = selectFoundSet();
        var search_selected = $('#select_found_set_values').val();
        if (search_selected == 'found_set') {
            fs.cb.addItemsToBin("0", search_selected);
            deselectAllClips();
        }
        if (selected.length > 0) {
            fs.cb.addItemsToBin("0", selected);
            deselectAllClips();
        }
    }

    function delSelectedToClipbin() {
        var selected = getSelectedClips();
        if (selected.length > 0) {
            $.each(selected, function (k, v) {
                fs.cb.removeItem(v);
            });
            deselectAllClips();
        }
    }

    function deleteSelected() {
        var selected = getSelectedClips();
        if (selected.length > 0) {
            deleteClips(selected);
        }
    }

    function previewCartDownload() {
        var ids = [];
        $('.footagesearch-cart-droppablearea .preview-wrapper').each(function (k, v) {
            ids[k] = $(this).data('clip-id');
        });
        if (ids)
            $.post('/index.php?ajax=true', {cart_action: 'preview_cart_download', ids: ids},
                function (data) {
                    console.log(data);
                    if (data.success)
                        modalWaitArchive();
                    else
                        modalCartLimitArchive();
                }
            );
    }

    function previewBinDownload() {
        var bin = getUrlParam('bin');
        if (!bin) {
            bin = parseInt($('.footagesearch-clipbin-bins-list.current-bin a').attr('href').replace(/\D+/g, ""));
        }
        $.post('/index.php?ajax=true', {clipbin_action: 'preview_bin_download', bin_id: bin},
            function (data) {
                console.log(data);
                if (data.success)
                    modalWaitArchive();
                else
                    modalBinLimitArchive();
            }
        );
    }

    function getUrlParam(name) {
        name = name.replace(/[\[]/, "\\[").replace(/[\]]/, "\\]");
        var regex = new RegExp("[\\?&]" + name + "=([^&#]*)"),
            results = regex.exec(location.search);
        return results === null ? "" : decodeURIComponent(results[1].replace(/\+/g, " "));
    }

    function modalWaitArchive() {
        if (!$('body').is("#archive-preview"))
            $('body').append('<div id="archive-preview">Your preview clips are being processed. You will receive an email when the preview clips are ready for download. <br><br> You can download the clips from the View Order page. </div>');
        $("#archive-preview").dialog({
            modal: true, buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            }
        });
    }

    function modalBinLimitArchive() {
        if (!$('body').is("#archive-preview"))
            $('body').append('<div id="archive-preview">You have already archived that clipbin today. </div>');
        $("#archive-preview").dialog({
            modal: true, buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            }
        });
    }

    function modalCartLimitArchive() {
        if (!$('body').is("#archive-preview"))
            $('body').append('<div id="archive-preview">You have exceeded the limit of archived cart for today. </div>');
        $("#archive-preview").dialog({
            modal: true, buttons: {
                Ok: function () {
                    $(this).dialog("close");
                }
            }
        });
    }


    function setCurrentBin(binID) {
        if (binID)
            $.post('/index.php?ajax=true', {
                    clipbin_action: 'set_current_bin',
                    bin_id: binID
                },
                function (data) {
                    if (data.success) {
                        window.location.href = data.clipbin_link;
                    }
                }
            );
    }

    function playPlayer(playerID) {
        try {
            var player = videojs(playerID);
            player.play();
        } catch (e) {
            console.log(e.name)
        }
    }

    function pausePlayer(playerID) {
        var player = videojs(playerID);
        player.pause();
    }

    function forwardPlayer(playerID, speed) {
        var video = document.getElementById(playerID + '_html5_api');
        if (video) {
            if (!speed) {
                var speeds = [1.0, 2.0, 4.0];
                var currentSpeed = video.playbackRate;
                var speed = 1.0;
                for (var i = 0; i < speeds.length; i++) {
                    if (speeds[i] == currentSpeed && speeds[i + 1] !== undefined) {
                        speed = speeds[i + 1];
                        break;
                    }
                }
            }
            video.playbackRate = speed;
        }
    }

    $(document).ready(function () {
        $('.footagesearch-clips-list-actions').on('change', function () {
            var action = $(this).val();
            if (action) {
                switch (action) {
                    case 'select_all':
                        selectAllClips();
                        break
                    case 'deselect_all':
                        deselectAllClips();
                        break
                    case 'select_found_set':
                        selectFoundSet();
                        break
                    case 'select_all_log':
                        selectAllLog();
                        break
                    case 'add_selected_to_cart':
                        addSelectedToCart();
                        break
                    case 'del_selected_to_cart':
                        delSelectedToCart();
                        break
                    case 'delete_selected':
                        deleteSelected();
                        break
                    case 'add_selected_to_admin':
                        editSelectedAllLog();
                        break
                    case 'add_selected_to_clipbin':
                        addSelectedToClipbin();
                        break
                    case 'del_selected_to_clipbin':
                        delSelectedToClipbin();
                        break
                    case 'preview_cart_download':
                        previewCartDownload();
                        break
                    case 'preview_bin_download':
                        previewBinDownload();
                        break
                    case 'move_items':
                        moveItemsTo();
                        break
                    case 'copy_items':
                        copyItemsTo();
                        break
                }
            }
            $(this).val('');
        });

        $('.footagesearch-clips-list-current-clipbin').on('change', function () {
            var binID = $(this).val();
            if (binID) {
                setCurrentBin(binID);
            }

        });

        $('.footagesearch-clips-list-sort').on('change', function () {
            window.location.href = $(this).val();
        });

        $('.footagesearch-clip .check').on('click', function (event) {
            event.stopPropagation();
            var $clip_block = $(this).parents('.footagesearch-clip');
            var idArr = $clip_block.attr('id').split('-');
            var clipID = idArr[2];
            if (clipID) {
                fs.toggleSelection(clipID);
            }
        });

        $('.footagesearch-clips-toggle-list-view').on('click', function () {
            var listViewForm = $('.footagesearc-list-view-form').first();
            var listViewInput = listViewForm.find('input').first();
            listViewInput.val('list');
            listViewForm.submit();
        });

        $('.footagesearch-clips-toggle-grid-view').on('click', function () {
            var listViewForm = $('.footagesearc-list-view-form').first();
            var listViewInput = listViewForm.find('input').first();
            listViewInput.val('grid');
            listViewForm.submit();
        });

        $('.footagesearch-clips-list-perpage').on('change', function () {
            var filter = ($('.footagesearch-clips-list-toggle-view-cont div.active').hasClass('footagesearch-clips-toggle-grid-view')) ? 'grid' : 'list';
            var itemsCount = $('.footagesearch-clips-list-perpage').val();

            $.ajax({
                type: "POST",
                url: "/index.php?ajax=true",
                async: true,
                data: {
                    footagesearch_action: "savePrepageUser",
                    preparefilter: filter,
                    prepageitems: itemsCount
                },
                success: function (msg) {
                }
            });
        });

        /***
         *
         * @return commented out this lines, so .footagesearch-clip-inner touch should show inline-popup, not href to clip page
         */

        //mobile devices event handlers
        // if (fs.is_mobile()) {
        //     $('.footagesearch-clip').on('touchend', '.footagesearch-clip-inner', function () {
        //         window.location.href = $('.info a', $(this)).attr('href');
        //     });
        // }

        /***
         * ************************************************************************************************************
         */

        function moveItemsTo() {
            var selectedIDs = getSelectedClips(),
                binID = 0;
            if (selectedIDs.length <= 0) {
                $('<p>No clips selected!</p>').dialog({
                    buttons: {
                        //modal: true,
                        "Ok": function () {
                            $(this).dialog("close");
                        }
                    },
                    create: function () {
                        jQuery('.ui-dialog-titlebar', jQuery(this).parents('.ui-dialog')).css({'background': 'none'});
                        jQuery('.ui-dialog-titlebar .ui-dialog-titlebar-close', jQuery(this).parents('.ui-dialog')).css({'border': 'none'});
                    }
                });
                return false;
            }
            binID = $('.bin-list-popup').clone().dialog({
                modal: true,
                create: function (event, ui) {
                    var that = this;
                    $('select').on('change', $(this), function () {
                        $(that).dialog('option', 'buttons')['OK'].click();
                    });
                    jQuery('.ui-dialog-titlebar', jQuery(this).parents('.ui-dialog')).css({'background': 'none'});
                    jQuery('.ui-dialog-titlebar .ui-dialog-titlebar-close', jQuery(this).parents('.ui-dialog')).css({'border': 'none'});
                },
                buttons: {
                    "Ok": function () {
                        binID = $('.bin-list-popup select').val();
                        if (binID) {
                            moveItems(binID);
                            return true;
                        }
                        else {
                            alert('Choose clipbin!');
                        }
                    }
                }
            });
        }

        function moveItems(binID) {
            var selectedIDs = getSelectedClips();
            if (binID && selectedIDs) {
                $.post('/index.php?ajax=true', {
                        clipbin_action: "move_items",
                        items_ids: selectedIDs,
                        bin_id: binID
                    },
                    function (data) {
                        if (data.success && data.clipbin_content) {
                            window.location.reload();
                        }
                    }
                );
            }
        }

        function copyItems(binID) {
            var selectedIDs = getSelectedClips();
            if (binID && selectedIDs) {
                $.post('/index.php?ajax=true', {
                        clipbin_action: "copy_items",
                        items_ids: selectedIDs,
                        bin_id: binID
                    },
                    function (data) {
                        if (data.success) {
                            alert('Copied');
                        }
                    }
                );
            }
        }

        function showClipPreview(elem) {
            $('.footagesearch-clip-pause-btn').hide();
            $('.footagesearch-clip-play-btn').show();

            var element = $(elem).parent().find('.footagesearch-clip-play-btn').first();
            if (element.is('[data-clip]')) {
                var clipInfo = eval("(" + element.attr('data-clip') + ")");
                var clipPreview = '<div class="footagesearch-clip-preview">';
                clipPreview += '<h1 class="footagesearch-clip-preview-title">' + clipInfo.code + '</h1>';
                clipPreview += '<video id="footagesearch-preview-player" class="video-js vjs-default-skin" preload="auto" autoplay muted width="432" height="240" data-setup="{}">';
                clipPreview += '<source src="' + clipInfo.motion_thumb + '" type="video/mp4" />';
                clipPreview += '</video>';

                var description = '';
                if (clipInfo.description)
                    description += '<p class="footagesearch-clip-preview-description">' + clipInfo.description + '</p>';
                clipPreview += description;
                clipPreview += '</div>';
                $('#footagesearch-clip-preview-dialog').html(clipPreview);
                $('#footagesearch-clip-preview-dialog').dialog('option', 'position', {
                    my: "left top+15",
                    at: "right bottom",
                    collision: "flipfit",
                    of: $(elem)
                }).dialog('open').siblings('.ui-dialog-titlebar').remove();
            }
        }

        var preview_opens = 0;

        function clickPlayBtn(element) {
            var isOpen = $('#footagesearch-clip-preview-dialog').dialog('isOpen');
            if (isOpen)
                $('#footagesearch-clip-preview-dialog').dialog('close');
            else {
                if (element.is('[data-clip]')) {
                    preview_opens++;
                    var clipInfo = eval("(" + element.attr('data-clip') + ")"),
                        clipPreview = '<div class="footagesearch-clip-preview">',
                        description = '',
                        parent = element.parents('.footagesearch-clip');
                    clipPreview += '<h1 class="footagesearch-clip-preview-title">' + clipInfo.title + '</h1>';
                    clipPreview += '<video id="footagesearch-preview-player' + clipInfo.id + '_' + preview_opens + '" class="video-js vjs-default-skin" preload="auto" muted width="432" height="240" data-setup="{}">';
                    clipPreview += '<source src="' + clipInfo.motion_thumb + '" type="video/mp4" />';
                    clipPreview += '</video>';
                    if (clipInfo.description)
                        description += '<p class="footagesearch-clip-preview-description">' + clipInfo.description + '</p>';
                    clipPreview += description;
                    clipPreview += '</div>';
                    $('#footagesearch-clip-preview-dialog').html(clipPreview);
                    $('#footagesearch-clip-preview-dialog').dialog('option', 'position', {
                        my: "middle bottom - 15",
                        at: "middle top",
                        using: function (pos, ui) {
                            var parentMiddle = parent.offset().top + (parent.height() / 2),
                                parentRight = parent.offset().left + parent.width(),
                                parentLeft = parent.offset().left,
                                height = $(this).height(),
                                width = $(this).width();

                            if (pos.top + height - 15 > parent.offset().top) {
                                pos.top = parentMiddle - (height / 2);
                                pos.left = parentRight + 1;
                            }
                            if (pos.top < $(window).scrollTop()) {
                                pos.top = $(window).scrollTop();
                            }
                            if (pos.left + width > $(window).width()) {
                                pos.left = parentLeft - width;
                            }
                            $(this).css(pos);
                        },
                        of: element.parents('.footagesearch-clip')
                    }).dialog('open').siblings('.ui-dialog-titlebar').remove();
                    playPlayer('footagesearch-preview-player' + clipInfo.id + '_' + preview_opens);
                }
                element.hide();
                var pauseBtn = element.parent().find('.footagesearch-clip-pause-btn');
                pauseBtn.show();
            }
        }

        function clickPauseBtn($element) {
            var isOpen = $('#footagesearch-clip-preview-dialog').dialog('isOpen');
            if (isOpen)
                $('#footagesearch-clip-preview-dialog').dialog('close');
            $element.hide();
            var playBtn = $element.parent().find('.footagesearch-clip-play-btn');
            playBtn.show();
        }

        $('.footagesearch-clip-preview-play-btn').on('click', function (e) {
            e.stopPropagation();
            $(this).hide();
            var pauseBtn = $(this).parent().find('.footagesearch-clip-preview-pause-btn');
            pauseBtn.show();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            playPlayer(id);
        });

        $('.footagesearch-clip-preview-pause-btn').on('click', function (e) {
            e.stopPropagation();
            $(this).hide();
            var playBtn = $(this).parent().find('.footagesearch-clip-preview-play-btn');
            playBtn.show();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            pausePlayer(id);
        });

        $('.footagesearch-clip-preview-forward-btn').on('click', function (e) {
            e.stopPropagation();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            forwardPlayer(id, 2.0);
        });

        $('.footagesearch-clip-preview-forward3x-btn').on('click', function (e) {
            e.stopPropagation();
            var idArr = $(this).attr('id').split('_');
            var id = 'footagesearch-preview-player' + idArr[1];
            forwardPlayer(id, 3.0);
        });

        /*$('.footagesearch-clip .info a').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var binID = $(this).data('bin-id');
            var href = $(this).attr('href');// + '?modal=1&bin=' + binID;
            /*if ($(this).attr('id')) {
             var clip_offset_arr = $(this).attr('id').split('-');
             if (clip_offset_arr[3] !== undefined)
             href += '&position=' + clip_offset_arr[3];
             }
            var windowWith = 900;//720;
            var windowHeight = 800;
            var leftPosition = (screen.width - windowWith) / 2;
            var topPosition = (screen.height - windowHeight) / 2;
            var clipWindow = window.open(
                href,
                'ClipInfo',
                'width=' + windowWith + ',height=' + windowHeight + ',top=' + topPosition + ',left=' + leftPosition + ',resizable=yes,scrollbars=yes,status=yes'
            )
            // Hide hover popup
            $('#footagesearch-clip-preview').parent().hide();
            clipWindow.focus();
        });*/

        if (fs.is_touch_device()) {
            //TODO IMPLEMENT
        }
        else {
            $('.draggable-clip').draggable({
                revert: 'invalid',
                cursor: 'move',
                cursorAt: {bottom: -10, left: -10},
                start: function (event, ui) {
                    ui.originalPosition.top -= $('body').scrollTop();
                },
                drag: function (event, ui) {
                    if ($.browser.msie || $.browser.mozilla) {
                        //ui.position.top -= $('html').scrollTop();
                    }
                    else {
                        ui.position.top -= $('body').scrollTop();
                    }
                },
                stop: function () {
                    $('.draggable-clip.selected').each(function () {
                        $(this).removeClass('selected');
                    });
                },
                helper: function () {
                    var selected = $('.draggable-clip.selected'),
                        length = selected.length;
                    if (length === 0) {
                        selected = $(this);
                        length = selected.length;
                        selected.addClass('selected');
                        selected.addClass('temp');
                    }
                    var container = $('<div/>').attr('id', 'draggingContainer').css({'z-index': '111'});
                    if (length == 1) {
                        container.append('<div class="mousetail">' + length + ' clip selected</div></br>');
                    }
                    else {
                        container.append('<div class="mousetail">' + length + ' clips selected</div></br>');
                    }
                    container.append(selected.clone());
                    return container;
                },
                cursorAt: {
                    left: -10,
                    top: -10
                }

            });
            $('.draggable-clip.selected').draggable('enable');
        }

        $('#filter-form').submit(function () {
            $(this).find(':input[value=""]').attr('disabled', 'disabled');
            $(this).find(':input[value="Search within results"]').attr('disabled', 'disabled');
            return true;
        });


        $('.preview_download').click(function () {
            var that = $(this);
            var clipid = that.attr('data-clip-id');
            var status = that.attr('data-status');
            var url = '/index.php?ajax=true';
            /*alert(status);*/
            if (status) {
               /* alert('in status');*/
                $.ajax({
                    url: url,
                    data: {clipbin_action: 'preview_download_rating', clipid: clipid, status: status},
                    type: "POST",
                    success: function (data) {

                    }
                });
            }
        });
    });

})(jQuery);

