(function($) {
    $(document).ready(function () {

        $('li.collapsed_expanded').each(function() {

            var storageKey = $(this)
                .children('p:first')
                .prop('className') + '-' + $(this).index();

            if (sessionStorage.hasOwnProperty(storageKey)) {

                if (sessionStorage.getItem(storageKey) == 'visible') {

                    $(this).removeClass('collapsed');
                    $(this).addClass('expanded');
                } else if (sessionStorage.getItem(storageKey) == 'hidden') {

                    $(this).removeClass('expanded');
                    $(this).addClass('collapsed');
                }
            }
        });

        if($('section#footagesearch_filter_widget-2').hasClass('widget_footagesearch_filter_widget')){
            //$('aside').prepend('<div id="hide_search_widget_head"></div>');
        }
        $('.widget_footagesearch_filter_widget .collapsed_expanded .filter_label').on('click', function(){
            var parentLi  = $(this).parent('.collapsed_expanded'),
                parentIndex = $(this).parent('.collapsed_expanded').index();

            if(parentLi.length > 0){
                if(parentLi.hasClass('expanded')){
                    parentLi.removeClass('expanded');
                    parentLi.addClass('collapsed');

                    sessionStorage.setItem(
                        $(this).prop('className') + '-' + parentIndex,
                        'hidden'
                    );
                }
                else if(parentLi.hasClass('collapsed')){
                    parentLi.removeClass('collapsed');
                    parentLi.addClass('expanded');

                    sessionStorage.setItem(
                        $(this).prop('className') + '-' + parentIndex,
                        'visible'
                    );
                }
            }
        });
        $('.widget_footagesearch_filter_widget form').on('submit', function(){
            var keywordInput = $(this).find('input[name="fs"]').first();
            if(keywordInput.val() == 'Search within results')
                keywordInput.val('');
        });
    });
})(jQuery);