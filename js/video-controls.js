(function ( $ ) {

    function playPlayer( playerID ) {
        var player = videojs( playerID );
        player.play();
    }

    function pausePlayer( playerID ) {
        var player = videojs( playerID );
        player.pause();
    }

    function forwardPlayer( playerID ) {
        var video = document.getElementById( playerID + '_html5_api' );
        if ( video ) {
            var speeds = [1.0, 2.0, 4.0];
            var currentSpeed = video.playbackRate;
            var speed = 1.0;
            for ( var i = 0; i < speeds.length; i++ ) {
                if ( speeds[i] == currentSpeed && speeds[i + 1] !== undefined ) {
                    speed = speeds[i + 1];
                    break;
                }
            }
            video.playbackRate = speed;
        }
    }

    // Новый обработчик для превью клипов

    $( document ).ready( function () {
        clipPreview.init();
    } );

    var clipPreview = {

        init : function () {
            this.initEnvironment();
            this.bindPreviewBox();
            this.bindPlayButtons();
            this.bindPauseButtons();
            this.bindSpeedButtons();
            this.bindInfoTrigger();
        },

        showPreviewTimeout : null,

        previewBoxName : '#footagesearch-clip-preview',
        previewBox : null,

        pricePopupClip : null,
        PopupClipData : null,

        clipsInnerBoxesName : '.footagesearch-clip-inner',
        clipsInnerThumbName : '.footagesearch-clip-thumb img',
        clipsPlayButtonsName : '.footagesearch-clip-play-btn',
        clipsPlayX2ButtonsName : '.footagesearch-clip-forward-btn',
        clipsPlayX3ButtonsName : '.footagesearch-clip-forward3x-btn',
        clipsPauseButtonsName : '.footagesearch-clip-pause-btn',
        clipsInfoButtonsName : '.info a',
        clipsInnerBoxes : null,
        clipsPlayButtons : null,
        clipsPlayX2Buttons : null,
        clipsPlayX3Buttons : null,
        clipsPauseButtons : null,
        clipsInfoButtons : null,

        previeBoxVideoIdPrefix : 'footagesearch-preview-player',
        previewBoxVideo : null,
        previewBoxTitle : null,
        previewBoxDescription : null,
        previewBoxSourceFormat : null,
		previewBoxMasterFormat : null,
		//previewBoxDeliveryOptions : null,

        activeClipBox : null,
        activeClipData : {},
        videoContainer : {},

        thumbWidth : 16,
        thumbHeight : 9,

        initEnvironment : function () {
            this.previewBox = $( this.previewBoxName );
            this.clipsInnerBoxes = $( this.clipsInnerBoxesName );
            this.clipsPlayButtons = $( this.clipsPlayButtonsName );
            this.clipsPlayX2Buttons = $( this.clipsPlayX2ButtonsName );
            this.clipsPlayX3Buttons = $( this.clipsPlayX3ButtonsName );
            this.clipsPauseButtons = $( this.clipsPauseButtonsName );
            this.clipsInfoButtons = $( this.clipsInfoButtonsName );
            this.previewBoxVideo = this.previewBox.find( 'video' );
            this.previewBoxTitle = this.previewBox.find( '.title' );
            this.previewBoxDescription = this.previewBox.find( '.description' );
            this.previewBoxSourceFormat = this.previewBox.find( '.source_format' );
			this.previewBoxMasterFormat = this.previewBox.find( '.master_format' );
			//this.previewBoxDeliveryOptions = this.previewBox.find( '.dilivery_options' );
        },

        bindPreviewBox : function () {
            var that = this;
            that.previewBox.dialog( {
                autoOpen : false,
                width : 468,
                show: { effect: 'fadeIn', duration: 100 },
                close : function () {},
                open : function () {
                    $( this ).siblings( '.ui-dialog-titlebar' ).remove();
                }
            } );
            that.clipsInnerBoxes.on( 'mouseenter', function ( event ) {
                that.showPreviewTimeout = setTimeout( function () {
                    if ( !that.isPreviewOpen() ) {
                        var thumb = $( event.currentTarget ).find(that.clipsInnerThumbName);
                        that.thumbWidth = thumb.width();
                        that.thumbHeight = thumb.height();
                        that.findActiveClipBox( event );
                        that.showPreviewBox();
                        that.hidePlayButton();
                        that.showPauseButton();
                        that.playPreview();
                    }
                }, 400 );
            } ).on( 'mouseleave', function ( event ) {
                clearTimeout( that.showPreviewTimeout );
                that.findActiveClipBox( event );
                that.hidePauseButton();
                that.showPlayButton();
                that.hidePreviewBox();
                that.pausePreview();
            } );
        },

        bindSpeedButtons : function () {
            var that = this;
            that.clipsPlayX2Buttons.on( 'click', function ( event ) {
                that.findActiveClipBox( event );
                if ( !that.isPreviewOpen() ) {
                    that.showPreviewBox();
                }
                that.hidePlayButton();
                that.showPauseButton();
                that.playPreview();
                that.setVideoSpeed( 2.0 );
            } );
            that.clipsPlayX3Buttons.on( 'click', function ( event ) {
                that.findActiveClipBox( event );
                if ( !that.isPreviewOpen() ) {
                    that.showPreviewBox();
                }
                that.hidePlayButton();
                that.showPauseButton();
                that.playPreview();
                that.setVideoSpeed( 3.0 );
            } );
        },

        bindPlayButtons : function () {
            var that = this;
            that.clipsPlayButtons.on( 'click', function ( event ) {
                that.findActiveClipBox( event );
                that.playPreview();
                that.hidePlayButton();
                that.showPauseButton();
                if ( !that.isPreviewOpen() ) {
                    that.showPreviewBox();
                }
            } );
        },

        bindPauseButtons : function () {
            var that = this;
            that.clipsPauseButtons.on( 'click', function ( event ) {
                that.findActiveClipBox( event );
                that.hidePauseButton();
                that.showPlayButton();
                that.pausePreview();
            } );
        },

        bindInfoTrigger : function () {
            var that = this;
            that.clipsInfoButtons.on( 'click', function ( event ) {
                that.findActiveClipBox( event );
                if ( that.isPreviewOpen() ) {
                    that.hidePauseButton();
                    that.showPlayButton();
                    that.pausePreview();
                }
            } );
        },

        findActiveClipBox : function ( event ) {
            this.activeClipBox = $( event.currentTarget ).closest( '.footagesearch-clip' );
            this.activeClipData = eval(
                "(" + this.activeClipBox.find( this.clipsPlayButtonsName ).attr( 'data-clip' ) + ")"
            );
        },

        isPreviewOpen : function () {
            return !! this.previewBox.dialog( 'isOpen' )
        },

        showPlayButton : function () {
            this.activeClipBox.find( this.clipsPlayButtonsName ).show();
        },

        hidePlayButton : function () {
            this.activeClipBox.find( this.clipsPlayButtonsName ).hide();
        },

        showPauseButton : function () {
            this.activeClipBox.find( this.clipsPauseButtonsName ).show();
        },

        hidePauseButton : function () {
            this.activeClipBox.find( this.clipsPauseButtonsName ).hide();
        },

        playPreview : function () {
            try {
                this.setVideoSpeed( 1 );
                this.videoContainer.play();
            } catch ( e ) {}
        },

        pausePreview : function () {
            try {
                this.videoContainer.pause();
            } catch ( e ) {}
        },
        priceClip : function (clipID,type){
            type=type.replace(/\s/g, "");
            var term_id = $(this.previewBoxName).data('term');
            var category = $(this.previewBoxName).data('category');
            var use = $(this.previewBoxName).data('use');
            var format = $(this.previewBoxName).data('format');
            return (type == 'RF')? this.getRFPrice(clipID,0,format) : this.getPrice(clipID,term_id,category,use,format);
        },
        getPrice : function (clipID,term_id,category,use,format){
            var that = this;
            jQuery.ajax({
                url:'/index.php?ajax=true',
                type: "POST",
                async:false,
                data:{
                    cart_action: "gets_clip_price",
                    clip_id: clipID,
                    /*term: term_id,
                    category: category,
                    use: use,*/
                    duration: 10
                    //format: format
                },
                success:function (data) {
                    that.pricePopupClip=(data.price_with_delivery)?data.price_with_delivery:data.price;
                    var use=(data.use_data)?data.use_data:null;
                    var term=(data.term_data)?data.term_data:null;
                    that.PopupClipData={use:use,category:data.category,term:term};
                }
            });
        },
        getRFPrice : function (clipID,term_id,format){
            var that = this;
            jQuery.ajax({
                url:'/index.php?ajax=true',
                type: 'POST',
                async:false,
                data:{
                    cart_action: "gets_rf_clip_price",
                    clip_id: clipID,
                    term: term_id,
                    format: ''
                },
                success:function (data) {that.pricePopupClip=data.price;}
            });
        },
        getPriceLevel : function (priceLevelID){
            switch (priceLevelID){
                case '1': return 'Budget'; break;
                case '2': return 'Standard'; break;
                case '3': return 'Premium'; break;
                case '4': return 'Exclusive'; break;
                default : return ''; break;
            }
        },
        getClipAllData : function (clipId){
			console.log($.parseJSON($('#footagesearch-clip-'+clipId+' .footagesearch-clip-thumb input').val()));
            return $.parseJSON($('#footagesearch-clip-'+clipId+' .footagesearch-clip-thumb input').val());
        },

		/*getClipDeliveryOptions : function (clipId){
			console.log($('#footagesearch-clip-'+clipId+' .footagesearch-clip-play-forward-actions input').val());
            return $.parseJSON($('#footagesearch-clip-'+clipId+' .footagesearch-clip-play-forward-actions input').val());
        },*/

        createVideoForHoverPreview : function () {
            this._prevPlayedClipId = this._prevPlayedClipId || 0;
            if (this._prevPlayedClipId == this.activeClipData.id) {
                // do nothing, show the same clip
                return;
            }
            // save previous clip id
            this._prevPlayedClipId = this.activeClipData.id;

            var id = this.previeBoxVideoIdPrefix + this.activeClipData.id;

            this.previewBoxVideo.attr( 'id',  id);

            // create source function
            var createSource = function (src, type) {
                var source = document.createElement('source');
                source.setAttribute('src', src);
                source.setAttribute('type', type);

                return source;
            };

            // remove prev added source elements
            this.previewBoxVideo.empty();

            // append both motion_thumb and preview video to be played preview in case motion_thumb is broken
            this.previewBoxVideo.append(createSource(this.activeClipData.motion_thumb, 'video/mp4'));
            this.previewBoxVideo.append(createSource(this.activeClipData.preview, 'video/mp4'));

            try {
                this.videoContainer = videojs( id );
                // call load function to update source files played
                this.videoContainer.load();
            } catch ( e ) {
                console.log(e);
            }
        },

        showPreviewBox : function () {
			var titlewid = this.activeClipData.title;
			var newtitle = titlewid.replace("Clip ID", "");
            this.previewBoxTitle.html( newtitle );
            this.priceClip(this.activeClipData.id,$('#footagesearch-clip-'+this.activeClipData.id+' .footagesearch-clip-license').text());

            var clipData = this.getClipAllData(this.activeClipData.id);
            var priceLevel = this.getPriceLevel(clipData.price_level);

			//var clipDataDelivery = this.getClipDeliveryOptions(this.activeClipData.id);

			//console.log(clipDataDelivery);
            //console.log(priceLevel,clipData.price_level);
            var price='';
            if(this.pricePopupClip != undefined || this.pricePopupClip != null){
                var price = ($('#footagesearch-clip-'+this.activeClipData.id+' .footagesearch-clip-license').text().replace(/\s/g, "") == 'RF')?'$'+this.pricePopupClip+' Royalty Free ('+priceLevel+')':'$'+this.pricePopupClip+' Rights Managed ('+priceLevel+')';
            }

            if(this.usePopupClip != undefined || (this.PopupClipData != null && this.PopupClipData.use !=null && this.PopupClipData.term !=null)){
                price+='<br><span class="license">'+this.PopupClipData.category+': '+this.PopupClipData.use.use+': '+this.PopupClipData.term.territory+' '+this.PopupClipData.term.term+'</span>';
            }

			//priceCat = '<p style="font-size:15px; padding-top:15px;">Pricing Category: '+priceLevel+'</p>';
            priceHtml = '<p style="font-size:15px; padding-bottom:5px;">'+price+'<br>Click <span class="info-icon-popup"></span> to View Pricing Calculator</p>';

            this.previewBoxDescription.html( '<strong>'+this.activeClipData.description+'</strong>'+priceHtml);
            
			if(this.activeClipData.delivery_methods != undefined)
			{
				this.previewBoxMasterFormat.html( 'Source Format:<br>'+this.activeClipData.delivery_methods[0].formats[0].description );
			}
			
			if(this.activeClipData.keywords != undefined)
			{
				this.previewBoxSourceFormat.html( 'Source Format:<br>'+this.activeClipData.source_format+' '+this.activeClipData.source_frame_rate );
			}
			else
			{
				this.previewBoxSourceFormat.html( 'Source Format:<br>'+this.activeClipData.source_format );	
			}

            this.createVideoForHoverPreview();

            this.previewBox.dialog( 'open' );

            this.setPreviewPosition();
        },

        hidePreviewBox : function () {
           this.previewBox.dialog( 'close' );
        },

        setPreviewPosition : function () {
            var that = this;
            //noinspection JSValidateTypes
            var windowScrollTop = $( window ).scrollTop();
            that.previewBox.dialog( 'option', 'position', {
                my : 'middle bottom-15',
                at : 'middle top',
                using : function ( pos ) {
                    var parentMiddle = that.activeClipBox.offset().top + ( that.activeClipBox.height() / 2 );
                    var parentRight = that.activeClipBox.offset().left + that.activeClipBox.width();
                    var parentLeft = that.activeClipBox.offset().left;
                    var height = $( this ).height();
                    var width = $( this ).width();

                    if ( pos.top + height - 15 > that.activeClipBox.offset().top ) {
                        pos.top = parentMiddle - (height / 2);
                        pos.left = parentRight + 1;
                    }
                    if ( pos.top < windowScrollTop ) {
                        pos.top = windowScrollTop;
                    }
                    if ( pos.left + width > $( window ).width() ) {
                        pos.left = parentLeft - width;
                    }

                    $( this ).css( pos );
                },
                of : that.activeClipBox
            } );
        },

        setVideoSpeed : function ( speed ) {
            var video = document.getElementById( this.previeBoxVideoIdPrefix + this.activeClipData.id );
            if ( video ) {
                if ( !speed ) {
                    speed = 1;
                }
                video.playbackRate = speed;
            }
        }

    };

})( jQuery );
