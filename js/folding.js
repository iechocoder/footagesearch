/*
folding-content.js
v2.0.0
by Samuel Palpant - http://samuel.palpant.com
MIT License
*/

jQuery.fn.foldingContent = function( Args ) {
  jQuery( document ).ready( function() {
    // build variables from on-page script init
    _args = Args;
    var menuSelector          = _args.menuSelector;
    var menuItemSelector      = _args.menuItemSelector;
    var menuItemLink          = menuItemSelector + ' > a';
    var contentSelector       = _args.contentSelector;
    var unfoldedContentBefore = _args.unfoldBeforeMarkup;
    var unfoldedContentAfter  = _args.unfoldAfterMarkup;
    // should be selector for element inside menuItemSelector, this is used to prevent open/close inline popup
    // on clicking by some buttons like cart, clipbin so on
    var onClickSelector = _args.onClickSelector || undefined;
    var closeButtonMarkup     = '';
    if ( _args.closeMarkup ) {
      closeButtonMarkup       = _args.closeMarkup;
    }
      var loadUrl = _args.url || '';
      var cacheRequest = _args.cacheRequest || false;

    // set up folding parent menu items and cache their content
    function setupMenu(){
      var $menuItems = jQuery( menuItemSelector, menuSelector );
      var $menu = jQuery( menuSelector );
      for ( var i = 0; i < $menuItems.length; i++ ) {
        var $item = $menuItems.eq(i);
        $item
          // add cache key to data attribute
          .data( 'data-fc-key', 'fcid-' + i )
          // void href of immediate child links
          .children( 'a' ).attr( 'href', 'javascript:' );
        if ( ! $item.hasClass( 'folding-parent' ) ) {
          $item.addClass( 'folding-parent' );
        }
      }
    }

    // label end of rows
    function labelRows(){
      jQuery( '.row-end' ).removeClass( 'row-end' );
      jQuery( '.row-begin' ).removeClass( 'row-begin' );

      var $menuItems = jQuery( menuItemSelector, menuSelector );
      var rowY = -1;
      var $prev = '';
      $menuItems.each( function(){
        $this = jQuery( this );
        thisY = $this.position().top;

        if ( thisY != rowY ) {
          $this.addClass( 'row-begin' );
          $prev = $this.prev();
          if ( $prev.hasClass( 'unfolded-content' ) ) {
            // the previous element is the unfolded content, so skip it and go next previous
            $prev = $prev.prev();
          }
          $prev.addClass( 'row-end' );
          rowY = thisY;
        }
      });
      $menuItems.last().addClass( 'row-end' );
    }

    // do initial menu setup
    setupMenu();
    labelRows();

    function cleanUpActiveFoldingMenu(){
      jQuery( '.active-item' )
        .css( 'height', '' )
        .removeClass( 'active-item' );
      jQuery( '.unfolded-content' ).fadeOut( 700, function() {
        jQuery( this ).remove();
      });
    }

      function closeUnfoldedContent(activeObject) {
          var container = activeObject.closest('.unfolded-content');
          var contentKey = container.data( 'data-fc-key' );
          var objectToCache = jQuery(container).find(contentSelector);
          setCachedContent(contentKey, objectToCache);
          cleanUpActiveFoldingMenu();
      }

    // find first and last item in $activeItem's row
    // returns an object with .begin and .end
    function activeItemRow( $activeItem ) {
      var $rowBegin = '';
      var $currentItem = $activeItem;
      for ( var i = 0; i < 100; i++ ) {
        if ( $currentItem.hasClass( 'row-begin' ) ) {
          $rowBegin = $currentItem;
          i += 200;
        } else {
          $currentItem = $currentItem.prev();
        }
      }

      var $rowEnd = '';
      $currentItem = $activeItem;
      for ( var j = 0; j < 100; j++ ) {
        if ( $currentItem.hasClass( 'row-end' ) ) {
          $rowEnd = $currentItem;
          j += 200;
        } else {
          $currentItem = $currentItem.next();
        }
      }

      return { begin: $rowBegin, end: $rowEnd };
    }

    // equalize height of active item with height of tallest item in row
    function equalizeItemHeight( $activeItem ) {
      var $rowBegin = activeItemRow( $activeItem ).begin;

      var isActiveRow = 0;
      var $currentItem = $rowBegin;
      var $activeRowItems = jQuery();
      // get object of all items in active row
      while ( 100 > isActiveRow ) {
        isActiveRow++;
        $activeRowItems = $activeRowItems.add( $currentItem );
        if ( $currentItem.hasClass( 'row-end' ) ) {
          isActiveRow += 200;
        } else {
          $currentItem = $currentItem.next();
        }
      }

      var maxHeight = 0;
      // find the height of the tallest item in the row
      $activeRowItems.each( function(){
        $this = jQuery( this );
        if ( maxHeight < $this.outerHeight() ) {
          maxHeight = $this.outerHeight();
        }
      });


      // set active item equal to tallest item
      if ( $activeItem.outerHeight() < maxHeight ) {
        // reset the height on .active-item
        $activeItem.css('height', '');
        // outerHeight() can only find the height
        // we care about outer height, but need to set the inner height with height()
        heightDifference = $activeItem.outerHeight() - $activeItem.height();
        var newHeight = maxHeight - heightDifference;
        // set the height
        $activeItem.height( newHeight );
      }
    }

    /**
    * append content and display
    *
    * @param parentObject
    * @param contentObject
    */
    function displayContent(parentObject, contentObject) {

        parentObject.addClass( 'active-item' );

        equalizeItemHeight( parentObject );

        // assemble content
        var wrapper = '<div class="close-unfolded-content">' + closeButtonMarkup + '</div>';
        wrapper = unfoldedContentBefore + wrapper + unfoldedContentAfter;
        // add content
        var $activeRowEnd = activeItemRow( parentObject ).end;
        jQuery( wrapper ).insertAfter( $activeRowEnd );
        $activeRowEnd.next().addClass( 'unfolded-content' );

        contentObject.appendTo('.unfolded-content');

        jQuery('.unfolded-content').slideDown(700, function () {
            try {
                var videos = contentObject.find('video');
                [].forEach.call(videos, function (video) {
                    if (!video.id) {
                        throw new Error('video must have an unique id');
                    }
                    videojs(video.id, {controls: true, autoplay : true});
                });
            } catch (e) {
                console.log(e);
            }
        });
    }

      /**
       * save object to cache
       *
       * @param string contentKey
       * @param object to cache dataObject
       */
    function setCachedContent(contentKey, dataObject) {
          if (!cacheRequest) {
              return;
          }

          jQuery(menuSelector).data(contentKey, dataObject)
    }


    /**
    * get already saved content from cache, if exists
    *
    * @param string contentKey
    * @return {*} | undefined
    */
    function getCachedContent(contentKey) {
        if (!cacheRequest) {
            return undefined;
        }
        return jQuery( menuSelector ).data( contentKey ) || undefined;
    }

    // open or close folding menu when parent clicked
    var __selector = onClickSelector ? '.folding-parent ' + onClickSelector : '.folding-parent';
    jQuery( menuSelector ).on( 'click', __selector, function(event) {
        // work with folding parent, selector used only to filter clicks on links, buttons
      var $this = jQuery( this.closest('.folding-parent') );

      if ( $this.hasClass( 'active-item' ) ) {
        // this menu is already open so close it and be done
        closeUnfoldedContent($this);
        return;
      }

      cleanUpActiveFoldingMenu();


      // get content for this item from cache and dispaly
        var contentKey = $this.data( 'data-fc-key' );

        var content = getCachedContent(contentKey);
        if (content != undefined) {
            displayContent($this, content);
        } else {
            // or get it from ajax and display
            jQuery.ajax({
                url: self.loadUrl,
                data: {footagesearch_inlinepopup : {clip_id : $this.data('clip-id')}},
                type: 'POST',
                success: function (data) {
                    if (data) {
                        var dataObject = jQuery(data.html);
                        setCachedContent(contentKey, dataObject);
                        displayContent($this, dataObject);
                    } else {
                        throw new Error('inline popup data is emppty');
                    }
                }
            })
        }
    });

    // close folding menu when X clicked
    // click() doesn't work on dynamically added elements
    jQuery( menuSelector ).on( 'click', '.close-unfolded-content', function() {
        closeUnfoldedContent(jQuery(this));
    });
  }); // document ready
}; // jQuery.fn

