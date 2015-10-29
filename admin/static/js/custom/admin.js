$(function() {
    if ($('.datetimepick').length > 0) {
        $('.datetimepick').datepicker({
            showOn: 'both',
            buttonImage: '/admin/static/img/calendar.png',
            buttonImageOnly: true,
            dateFormat: 'dd.mm.yy'
        });

        $('#ui-datepicker-div').hide();
    }

    $('a.delete').click(function() {
        if (!confirm('Удалить?')) return false;

        return true;
    });

    $('#publish_now').click(function() {
        if (!confirm('Вы уверены?')) return false;

        return true;
    });

    $('.tree_node_list[sortable="1"]').sortable({
        items : 'tr:not(.table_header)',
        handle: 'div.drag',
        stop: function(event,ui){ sortNodeList(); }
    });

    function sortNodeList()
    {
        var ids = [];
        var index_start = $('.tree_node_list').attr('index_start');
        $(".tree_node_list tr:not(.table_header)").each(function(ind){ ids[ids.length] = $(this).attr('item_id'); /*$('td:eq(1)', this).html(ind+1 + parseInt(index_start));*/ });

        $.ajax({
            type: 'POST',
            dataType: 'text',
            url: '/admin/',
            data: ({ ids: ids.join(), 'index_start': index_start, 'table': $('.tree_node_list').attr('table'), 'opcode': 'common', 'act' : 'tree_node_list_sort'})
        });
    }

    $('.selectAll').click(function () {
        var frm = $(this).closest('form');
        $('input[type="checkbox"].tree', frm).attr('checked', 'checked');
    });

    $('.deselectAll').click(function () {
        var frm = $(this).closest('form');
        $('input[type="checkbox"].tree', frm).each(function () { this.checked = false;} );
    });

    $('.edit_item_form').on('submit', function (){

    });

    $('#tw_text').on('keyup paste', function (){
        twitterCount($(this).val().length);
    });

    function twitterCount(l)
    {
        var tw = $('#tw_count');
        tw.text(l);
        var pic_l = parseInt($('#tw_characters_per_media').val());
        var url_l = parseInt($('#tw_short_url_length').val());
        if (l > (140-pic_l) && l <= 140)
        {
            if (!tw.hasClass('green')) tw.addClass('green')
            if (tw.hasClass('red')) tw.removeClass('red');
        } else if (l > 140) {
            if (!tw.hasClass('red')) tw.addClass('red')
            if (tw.hasClass('green')) tw.removeClass('green');
        } else {
            if (tw.hasClass('red')) tw.removeClass('red');
            if (tw.hasClass('green')) tw.removeClass('green');
        }
    }

    twitterCount($('#tw_text').val().length);

});