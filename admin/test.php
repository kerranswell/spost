<script>
//alert(window.opener);
</script>

<?php
    $rid = ''.$_REQUEST['return_id'];
?>
<link rel="stylesheet" type="text/css" href="/admin/static/css/arteditor.css"/>
<script type="text/javascript" src="/static/js/jquery.js"></script>
<!--<script type="text/javascript" src="/admin/static/tiny_mce/tiny_mce.js"></script>-->
<script type="text/javascript" src="/admin/static/tinymce/tinymce.min.js"></script>
<script type="text/javascript" src="/admin/js/jquery.msword_html_filter.js"></script>
<body>
<script>
function htmlspecialchars_decode (string, quote_style) {
    // Convert special HTML entities back to characters  
    // 
    // version: 1109.2015
    // discuss at: http://phpjs.org/functions/htmlspecialchars_decode    // +   original by: Mirek Slugen
    // +   improved by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Mateusz "loonquawl" Zalega
    // +      input by: ReverseSyntax
    // +      input by: Slawomir Kaniecki    // +      input by: Scott Cariss
    // +      input by: Francois
    // +   bugfixed by: Onno Marsman
    // +    revised by: Kevin van Zonneveld (http://kevin.vanzonneveld.net)
    // +   bugfixed by: Brett Zamir (http://brett-zamir.me)    // +      input by: Ratheous
    // +      input by: Mailfaker (http://www.weedem.fr/)
    // +      reimplemented by: Brett Zamir (http://brett-zamir.me)
    // +    bugfixed by: Brett Zamir (http://brett-zamir.me)
    // *     example 1: htmlspecialchars_decode("<p>this -&gt; &quot;</p>", 'ENT_NOQUOTES');    // *     returns 1: '<p>this -> &quot;</p>'
    // *     example 2: htmlspecialchars_decode("&amp;quot;");
    // *     returns 2: '&quot;'
    var optTemp = 0,
        i = 0,        noquotes = false;
    if (typeof quote_style === 'undefined') {
        quote_style = 2;
    }
    //string = string.toString().replace(/&lt;/g, '<').replace(/&gt;/g, '>');
    string = string.toString();
	var OPTS = {
        'ENT_NOQUOTES': 0,
        'ENT_HTML_QUOTE_SINGLE': 1,
        'ENT_HTML_QUOTE_DOUBLE': 2,
        'ENT_COMPAT': 2,
        'ENT_QUOTES': 3,
        'ENT_IGNORE': 4
    };
    if (quote_style === 0) {
        noquotes = true;    }
    if (typeof quote_style !== 'number') { // Allow for a single string or an array of string flags
        quote_style = [].concat(quote_style);
        for (i = 0; i < quote_style.length; i++) {
            // Resolve string input to bitwise e.g. 'PATHINFO_EXTENSION' becomes 4
			if (OPTS[quote_style[i]] === 0) {
                noquotes = true;
            } else if (OPTS[quote_style[i]]) {
                optTemp = optTemp | OPTS[quote_style[i]];
            }        }
        quote_style = optTemp;
    }
    if (quote_style & OPTS.ENT_HTML_QUOTE_SINGLE) {
        string = string.replace(/&#0*39;/g, "'"); // PHP doesn't currently escape if more than one 0, but it should        // string = string.replace(/&apos;|&#x0*27;/g, "'"); // This would also be useful here, but not a part of PHP
    }
    if (!noquotes) {
        string = string.replace(/&quot;/g, '"');
    }    // Put this in last place to avoid escape being double-decoded
    string = string.replace(/&amp;/g, '&');
    string = string.replace(/&lt;/g, '<');
    string = string.replace(/&gt;/g, '>');
 
    return string;
}


$(document).ready(function(){

        tinymce.init({
            selector: "textarea",

            <? if ( !empty($_GET['type']) && ($_GET['type'] == 'bulleted_list' || $_GET['type'] == 'bulleted_list_recept')): ?>
                toolbar: "bullist",
            <? elseif ( !empty($_GET['type']) && ( $_GET['type'] == 'num_list' || $_GET['type'] == 'recipe_num_list' ) ): ?>
                toolbar: "numlist",
            <? elseif ( !empty($_GET['type']) && $_GET['type'] == 'text' ): ?>
                toolbar: "link",
            <? else: ?>
                toolbar: "undo redo | " +
                    "bold italic underline strikethrough superscript subscript | " +
                    "link | " +
                    "charmap code | " +
                    "bullist numlist | " +
                    "<?php echo ( ( !empty($_GET['type']) && $_GET['type'] == 'styles' ) ? ' styleselect':'' )?>" +
                    "<?php echo ( ( !empty($_GET['type']) && $_GET['type'] == 'quote' ) ? ' quote':'' )?>",
            <? endif; ?>

            plugins: "link charmap code wordcount lists textcolor",
            menubar: false,
            height: 400,
            setup : function(editor) {
                editor.on('Init', function(e) {
                    var url = window.location.href;
                    var content = $(window.opener.document).find('body #<?php echo mysql_escape_string( $rid )?>').html();

                    if ( url.indexOf('&type=num_list') != -1 )
                    {
                        content = content.replace(/<span>/g,"<p>");
                        content = content.replace(/<\/span>/g,"<\/p>");
                        content = content.replace(/<\/br>/g,"");
                        content = content.replace(/<br>/g,"");
                    }

                    content = htmlspecialchars_decode( content );

                    content = content.replace(/rel="nofollow"/g,"");
                    content = content.replace(/<!--noindex-->/g,"");
                    content = content.replace(/<!--\/noindex-->/g,"");

                    editor.focus(false);
                    editor.setContent( content );
                });
                editor.addButton('quote', {
                    title : 'Цитата',
                    image : './static/tinymce/skins/lightgray/img/ico27.png',
                    onclick : function() {
                        editor.focus();
                        editor.selection.setContent('{цитата}');
                    }
                });
                editor.on('PostProcess', function(ed) {
                    ed.content = ed.content.replace(/(<p>&nbsp;<\/p>)/gi,'<br />');
                });
                console.log(tinymce);
            },
            element_format : "xhtml",
            relative_urls : false,

            style_formats: [
                {title: 'Заголовок H2', block: 'h2'}
            ],

            <? if (isset($_GET['extended']) && $_GET['extended']): ?>
                invalid_elements  : "font",
                extended_valid_elements : 'span[*]'
            <? else: ?>
                invalid_elements  : "span,font,div,iframe"
            <? endif; ?>
        });

});

function word_filter(editor){
            var content = editor.html();

            // Word comments like conditional comments etc
            content = content.replace(/<!--[\s\S]+?-->/gi, '');

            // Remove comments, scripts (e.g., msoShowComment), XML tag, VML content,
            // MS Office namespaced tags, and a few other tags
            content = content.replace(/<(!|script[^>]*>.*?<\/script(?=[>\s])|\/?(\?xml(:\w+)?|img|meta|link|style|\w:\w+)(?=[\s\/>]))[^>]*>/gi, '');

            // Convert <s> into <strike> for line-though
            content = content.replace(/<(\/?)s>/gi, "<$1strike>");

            // Replace nbsp entites to char since it's easier to handle
            //content = content.replace(/ /gi, "\u00a0");
            content = content.replace(/ /gi, ' ');

            // Convert <span style="mso-spacerun:yes">___</span> to string of alternating
            // breaking/non-breaking spaces of same length
            content = content.replace(/<span\s+style\s*=\s*"\s*mso-spacerun\s*:\s*yes\s*;?\s*"\s*>([\s\u00a0]*)<\/span>/gi, function(str, spaces) {
                return (spaces.length > 0) ? spaces.replace(/./, " ").slice(Math.floor(spaces.length/2)).split("").join("\u00a0") : '';
            });

            editor.html(content);

            // Parse out list indent level for lists
            $('p', editor).each(function(){
                var str = $(this).attr('style');
                var matches = /mso-list:\w+ \w+([0-9]+)/.exec(str);
                if (matches) {
                    $(this).data('_listLevel',  parseInt(matches[1], 10));
                }
            });

            // Parse Lists
            var last_level=0;
            var pnt = null;
            $('p', editor).each(function(){
                var cur_level = $(this).data('_listLevel');
                if(cur_level != undefined){
                    var txt = $(this).text();
                    var list_tag = '<ul></ul>';
                    if (/^\s*\w+\./.test(txt)) {
                        var matches = /([0-9])\./.exec(txt);
                        if (matches) {
                            var start = parseInt(matches[1], 10);
                            list_tag = start>1 ? '<ol start="' + start + '"></ol>' : '<ol></ol>';
                        }else{
                            list_tag = '<ol></ol>';
                        }
                    }

                    if(cur_level>last_level){
                        if(last_level==0){
                            $(this).before(list_tag);
                            pnt = $(this).prev();
                        }else{
                            pnt = $(list_tag).appendTo(pnt);
                        }
                    }
                    if(cur_level<last_level){
                        for(var i=0; i<last_level-cur_level; i++){
                            pnt = pnt.parent();
                        }
                    }
                    $('span:first', this).remove();
                    pnt.append('<li>' + $(this).html() + '</li>')
                    $(this).remove();
                    last_level = cur_level;
                }else{
                    last_level = 0;
                }
            })

            $('[style]', editor).removeAttr('style');
            $('[align]', editor).removeAttr('align');
            $('span', editor).replaceWith(function() {return $(this).contents();});
            $('span:empty', editor).remove();
            $("[class^='Mso']", editor).removeAttr('class');
            $('p:empty', editor).remove();
            $('a[name]', editor).remove();
        }

function go(){
    var url = window.location.href;
    var content = tinyMCE.activeEditor.getContent();
    var re = /<a href="http:\/\/([w\.]*)elle\.ru([^"]+)"([^>]*)>/ig;
    content = content.replace( re, '<a href="$2"$3>' );

    re = /<p><span[^>]*>{цитата}<\/span><\/p>/ig;
    content = content.replace( re, '<p>{цитата}</p>' );

    if ( $('#noindex:checked').length == 0 )
    {
        var re = /<a href="([^"]+)"([^>]*)>([^<]+)<\/a>/ig;
        content = content.replace( re, function(a,b,c,d){
            var str = '';
            if ( b.indexOf('elle.ru') !== -1 || b.charAt(0) == '/' )
            {
                str = a;
            }
            else
            {
                str = '<!--noindex--><a href="'+b+'"'+c+' rel="nofollow">'+d+'</a><!--/noindex-->';
            }

            return str;
        } );
    }

    if ( url.indexOf('&type=num_list') != -1 )
    {
        content = content.replace(/<p>/g,"<span>");
        content = content.replace(/<\/p>/g," <\/span>");
    }

    if ( url.indexOf('&type=bulleted_list_recept') != -1 )
    {
        content = content.replace(/<li>/g,'<li class="ingredient">');
    }

    if ( url.indexOf('&type=recipe_num_list') != -1 )
    {
        content = content.replace(/<li>/g,'<li class="instruction">');
        content = content.replace(/<p>/g,'<p class="instruction">');
    }

	$(window.opener.document).find('body #<?php echo mysql_escape_string( $rid )?>').html( content );
    word_filter($(window.opener.document).find('body #<?php echo mysql_escape_string( $rid )?>'));

    var short_elem = $(window.opener.document).find('body #<?php echo mysql_escape_string( $rid )?>-m');

    if ( short_elem.length == 1 )
    {
        var short = content.replace(/<\/?[^>]+>/gi, '').replace(/[\r\n]/gi, ' ');
        if ( short.length > 100 ) short = short.substr(0,100)+'...';
        short_elem.html( short );
    }

	window.close();
}

</script>
<textarea></textarea>
<br />
<input type="checkbox" id="noindex" /> <label for="noindex">не закрывать ссылки от индексации</label>
<br />
<br />
<input type="button" class="tynimce btn btn-success" value="Готово" onClick="go()" />

</body>