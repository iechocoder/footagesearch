// ЛОКАЛЬНЫЙ СТОРАЖ ТЕСТИРОВАНИЕ РЕЙТИНГОВ
(function($) {
    $(document).ready(function () {
        rankClip.viewClip();
        // add clipbin
        $("form.footagesearch_clipbin_button_form.footagesearch_ajaxify_form").on('submit', function () {
            var clipId=$(this).closest('[id^="footagesearch-clip-"]').attr('id').match(/\d+/)[0];
            rankClip.clipbinAdd(clipId);
        });
        //del clipbin
        $('.footagesearch_clipbin_button_delete').on('click', function () {
            var clipId=$(this).closest('[id^="footagesearch-clip-"]').attr('id').match(/\d+/)[0];
            rankClip.clipbinSub(clipId);
        });
        // download preview
        $('a.preview_download').on('click', function () {
            var clipId=$(this).data('clip-id');
            rankClip.peviewDownload(clipId);
        });
        // save purchases clip in session
        $('input[name="confirm_order"]').on('click', function () {
            var order = '['
            $('.draggable-clip').each(function (i) {
                var clipId=$(this).attr('id').match(/\d+/)[0];
                order+=clipId+',';
            });
            order=order.slice(0, -1);
            order+=']';
            rankClip.saveOrderClips(order);

        });
        // purchases
        var urlParam=rankClip.getUrlParam();
        if(urlParam && urlParam['order_id'] !='' && urlParam['order_id'] !=undefined){rankClip.purchases();}
    });

    function dump(obj) {// Замена var_dump в php
        var out = '';
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }

        alert(out);
    }
    var rankClip = {
        saveOrderClips : function (idsArr) {
            window.sessionStorage.setItem('order',idsArr);
        },
        viewClip : function (){
            if($('[id^="footagesearch-preview-player"]').length !=0){
                var clipId=$('[id^="footagesearch-preview-player"]').attr('id').match(/\d+/)[0];
                var weight=5;
                //var id=window.sessionStorage.getItem(clipId);
                /*if(id == null){
                    window.sessionStorage.setItem(clipId);
                    this.saveRank(clipId,weight,'+');
                }*/
                this.verifiAction('view',clipId,weight,'+');
            }
        },
        purchases : function (){
            var that=this;
            var weight=50;
            var idsArr=window.sessionStorage.getItem('order');
            idsArr=JSON.parse(idsArr);
            idsArr.forEach(function (clipId){
                that.saveRank(clipId,weight,'+');
            });
        },
        peviewDownload : function (clipId){
            var weight=30;
            //this.saveRank(clipId,weight,'+');
            this.verifiAction('preview',clipId,weight,'+');
        },
        clipbinAdd : function(clipId){
            var weight=20;
            this.saveRank(clipId,weight,'+');
            //alert(this.getRank(clipId));
        },
        clipbinSub : function(clipId){
            var weight=20;
            this.saveRank(clipId,weight,'-');
            //alert(this.getRank(clipId));
        },
        saveRank : function (clipId,rank,action){
            $.post('/index.php?ajax=true', {
                    footagesearch_action: 'setRank',
                    clipId: clipId,
                    weight: rank,
                    action: action
                },
                function (data) {}
            );
        },
        getRank : function (clipId){
            var res=0;
            $.post('/index.php?ajax=true', {
                    footagesearch_action: 'getRank',
                    clipId: clipId
                },
                function (data) {res=data;}
            );
            return res;
        },
        getUrlParam : function () {
            var tmp = new Array();		// два вспомагательных
            var tmp2 = new Array();		// массива
            var param = new Array();
            var get = location.search;  // строка GET запроса
            if(get != '') {
                tmp = (get.substr(1)).split('&');   // разделяем переменные
                for(var i=0; i < tmp.length; i++) {
                    tmp2 = tmp[i].split('=');       // массив param будет содержать
                    param[tmp2[0]] = (typeof tmp2[1] == 'undefined')?'':tmp2[1];       // пары ключ(имя переменной)->значение
                }
                return param;
            }
            return false;
        },
        verifiAction : function (type,clipId,weight,action) {
            var id=window.sessionStorage.getItem(type+clipId);
            if(id == null){
                window.sessionStorage.setItem(type+clipId,1);
                this.saveRank(clipId,weight,action);
            }
        }
    };
})(jQuery);
