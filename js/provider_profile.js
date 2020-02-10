(function($) {
    $(document).ready(function () {
        $('.footagesearch-view-all-galleries').on('click', function(e){
            e.preventDefault();
            $.post('/index.php?ajax=true', {
                    footagesearch_action: "all_galleries"
                },
                function(data){
                    if(data.success && data.galleries_list){
                        $('.footagesearch-provider-galleries').replaceWith(data.galleries_list);
                    }
                }
            );
        });
        $('.footagesearch-view-all-categories').on('click', function(e){
            e.preventDefault();
            $.post('/index.php?ajax=true', {
                    footagesearch_action: "all_categories"
                },
                function(data){
                    if(data.success && data.categories_list){
                        $('.footagesearch-provider-categories').replaceWith(data.categories_list);
                    }
                }
            );
        });
        $('.footagesearch-provider-search-form').on('submit', function(){
            var keywordInput = $(this).find('input[name="fs"]').first();
            if(keywordInput.val() == 'Clip search')
                keywordInput.val('');
        });

        $('.footagesearch-provider-follow-btn').on('click', function(){
            $.post('/index.php?ajax=true', {
                    footagesearch_action: "add_follower"
                },
                function(data){
                    if(data.success && data.followers_count !== undefined){
                        $('.footagesearch-provider-followers-count').text(data.followers_count);
                    }
                }
            );
        });

        var contributor = {
            init : function () {
                var that = this;
                that.contributor_readmore();
                that.view_galleries();
                that.view_cloud_tags();
            },
            contributor_readmore : function () {
                $('.footagesearch-browse-page-banner-text-rf .readmore' ).on('click', function () {
                    $('.footagesearch-browse-page-banner-text-rf .biography').toggleClass('full');
                    var readmore=$('.footagesearch-browse-page-banner-text-rf .readmore span').text();
                    var text=(readmore == 'Read More')? 'Read Less' : 'Read More';
                    $('.footagesearch-browse-page-banner-text-rf .readmore span').text(text);
                });
            },
            view_galleries : function () {
                var that = this;
                $('.footagesearch-provider-page #view-galleris' ).on('click', function () {
                    var view_galleries = ($(this).hasClass('featured')) ? 'Featured' : 'All';
                    var featured = ($(this).hasClass('featured')) ? '' : 'featured';
                    $('.footagesearch-provider-page .primary_container.galleries .footagesearch-categories-list' ).html('<div class="ajax-load"></div>')
                    $.ajax({
                        type: "POST",
                        url: "/index.php?ajax=true",
                        async:true,
                        data: {
                            footagesearch_action : "display_contributor_galleries",
                            view_galleries : view_galleries,
                            provider : $('.footagesearch-provider-page' ).data('provider')
                        },
                        success: function(msg){
                            var data=JSON.parse(msg);
                            var html='<div class="separatorDiv"><h2 class="footagesearch-browse-page-list-title">'+data.title+' Galleries</h2><div class="readmore minimenu">';
                                html+='<a href="#sharethis">share this</a><span id="view-galleris" class="'+featured+'">view '+view_galleries.toLowerCase()+' galleries</span></div></div>';
                                html+=data.galleries;
                            $('.footagesearch-provider-page .primary_container.galleries' ).html(html);
                        }
                    });
                });
            },
            view_cloud_tags : function () {
                var that=this;
                $('.footagesearch-provider-page #view-tags').on('click', function () {
                    if($(this).hasClass('limittags')){
                        var limitCount=100;
                    }else{
                        var limitCount=10000;
                    }
                    $(this).toggleClass('limittags');
                    var count=0;
                    $('.footagesearch-provider-page .keyword').each(function () {
                        if(count<limitCount){
                            $(this).css('display','inline-block');
                        }else{
                            $(this).css('display','none');
                        }
                        count++;
                    });
                });

            }

        };
        contributor.init();


    });
})(jQuery);