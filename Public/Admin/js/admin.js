;$(function(){

    /**
    $('.logo').click(function(){

        layer.open({
            type: 1,
            skin: 'layui-layer-rim', //加上边框
            area: ['80%', '80%'], //宽高
            content: "<div id='calendar'></div>"
        });


        $('#calendar').fullCalendar({

            editable: true,
            eventLimit: true, // allow "more" link when too many events
            events: [
                {
                    title: '99元',
                    start: '2017-1-16'
                }
            ]
        });

        return false;
    });

**/

    /*操作 扩展功能*/
    /*弹出操作层*/
    $('.layer').click(function(){

        var href = $(this).attr('href');

        layer.open({
            type: 2,
            skin: 'layui-layer-rim', //加上边框
            area: ['80%', '80%'], //宽高
            content: href
        });

        return false;

    });
    /*自定义打开方式 */
    $('.target_blank').click(function(){

        var href = $(this).attr('href');
        window.open(href);
        return false;

    });


})