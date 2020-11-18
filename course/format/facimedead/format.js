// Javascript functions for Weeks course format

M.course = M.course || {};

M.course.format = M.course.format || {};

/**
 * Get sections config for this format
 *
 * The section structure is:
 * <ul class="weeks">
 *  <li class="section">...</li>
 *  <li class="section">...</li>
 *   ...
 * </ul>
 *
 * @return {object} section list configuration
 */
M.course.format.get_config = function() {
    return {
        container_node: 'ul',
        container_class: 'facimedead',
        section_node: 'li',
        section_class: 'section'
    };
};

/**
 * Swap section
 *
 * @param {YUI} Y YUI3 instance
 * @param {string} node1 node to swap to
 * @param {string} node2 node to swap with
 * @return {NodeList} section list
 */
M.course.format.swap_sections = function(Y, node1, node2) {
    var CSS = {
        COURSECONTENT: 'course-content',
        SECTIONADDMENUS: 'section_add_menus'
    };

    var sectionlist = Y.Node.all('.' + CSS.COURSECONTENT + ' ' + M.course.format.get_section_selector(Y));
    // Swap menus.
    sectionlist.item(node1).one('.' + CSS.SECTIONADDMENUS).swap(sectionlist.item(node2).one('.' + CSS.SECTIONADDMENUS));
};

/**
 * Process sections after ajax response
 *
 * @param {YUI} Y YUI3 instance
 * @param {array} response ajax response
 * @param {string} sectionfrom first affected section
 * @param {string} sectionto last affected section
 * @return void
 */
M.course.format.process_sections = function(Y, sectionlist, response, sectionfrom, sectionto) {
    var CSS = {
        SECTIONNAME: 'sectionname'
    },
    SELECTORS = {
        SECTIONLEFTSIDE: '.left .section-handle .icon'
    };

    if (response.action == 'move') {
        // If moving up swap around 'sectionfrom' and 'sectionto' so the that loop operates.
        if (sectionfrom > sectionto) {
            var temp = sectionto;
            sectionto = sectionfrom;
            sectionfrom = temp;
        }

        // Update titles and move icons in all affected sections.
        var ele, str, stridx, newstr;

        for (var i = sectionfrom; i <= sectionto; i++) {
            // Update section title.
            var content = Y.Node.create('<span>' + response.sectiontitles[i] + '</span>');
            sectionlist.item(i).all('.' + CSS.SECTIONNAME).setHTML(content);

            // Update move icon.
            ele = sectionlist.item(i).one(SELECTORS.SECTIONLEFTSIDE);
            str = ele.getAttribute('alt');
            stridx = str.lastIndexOf(' ');
            newstr = str.substr(0, stridx + 1) + i;
            ele.setAttribute('alt', newstr);
            ele.setAttribute('title', newstr); // For FireFox as 'alt' is not refreshed.

            // Remove the current class as section has been moved.
            sectionlist.item(i).removeClass('current');
        }
        // If there is a current section, apply corresponding class in order to highlight it.
        if (response.current !== -1) {
            // Add current class to the required section.
            sectionlist.item(response.current).addClass('current');
        }
    }
};


// Accordions


require(['jquery'], function($) {
    $.urlParam = function(name) {
        var results = new RegExp('[\?&]' + name + '=([^&#]*)').exec(window.location.href);
        if (results == null) {
            return null;
        }
        else {
            return decodeURI(results[1]) || 0;
        }
    };

    var editing = $('div#user_is_editing').length;

    $('.subsection').on('click', function() {
        $('.subsection').removeClass('active');
        $(this).addClass('active');
        // TODO aparecer a div com os itens desta subseção
        var idsecao = $(this).attr('sectionid');
        var idsubsecao = $(this).attr('subsectionid');
        // var contentdiv = $('.subsection-content[sectionid=' + idsecao + '][subsectionid='+ idsubsecao+']');
        var contentdiv = $('.subsection-content[parentsectionid=' + idsecao + '][sectionid=' + idsubsecao + ']');

        var panel = contentdiv.parent();
        var siblings = contentdiv.siblings('.subsection-content');

        siblings.css('maxHeight', '');
        contentdiv.css('maxHeight', contentdiv.prop('scrollHeight') + 'px');
        var maxheight = (Number)(panel.css('maxHeight').replace('px', ''));
        panel.css('maxHeight', maxheight + (Number)(contentdiv.prop('scrollHeight')) + 'px');
    });

    var mobilewidth = 761;
    // accordions com colapse jquery
    function mobMoveTop(key, mobilewidth) {

        if (window.innerWidth < mobilewidth) {
            $('body,html').animate({
                scrollTop: $($('.sectionname.notfirst')[key]).offset().top}, 'fast');
        }
    }
    // Previne o scroll da ancora quando abrir uma semana
    $('h3.sectionname.notfirst a').on('click',function(e) {
        e.preventDefault();
    });
    $('.sectionname.notfirst').each(function(key, value) {
        var icon = $('<i>').addClass('fa fa-angle-right');
        $(value).prepend(icon);
        $(value).click(function() {
            var estaFora = null;
            if($('.sectionname.notfirst.active').length){
                if($('.sectionname.notfirst.active').offset().top + 100 < $(window).scrollTop()){
                    estaFora = true;
                }else{
                    estaFora = false;
                }
            }
            // if(!editing) {
            //     $('.sectionname.notfirst').not(value).removeClass('active');
            //     $('.fac-section-panel').not(facSub).stop().slideUp('fast');
            // }
            var facSub = $(value).siblings('.fac-section-panel');
            facSub.stop().slideToggle('fast', function() {
                // if(!editing && $(value).hasClass( "active" ) && estaFora) {
                //     $('body,html').animate({
                //         scrollTop: $($('.sectionname.notfirst')[key]).offset().top}, 'fast');
                // }
            });
            $(value).toggleClass('active');
        });
    });

    $('.module-list').each(function(key, value) {

        var btn = $(value).find('.facimedead_subsectionfull');
        var tagContainer = $(value).find('.fac_tag_content');

        $(btn).find('h3').each(function() {
            if($(this).parents('li.label').length){
                var title = $('<span>').text($(this).text());
                if(title.text() !== '')
                    var icon = getIcon(title.text());
                $(this).addClass('fac-module-title');
                $(this).html(title);
                $(this).prepend(icon);
            }
        });

        $(btn).find('h3').click(function(scroll) {
            var button = $(this);
            var title = $(this).text();
            var panel = $(this).parents('.facimedead_subsectionfull').find('.facimedead_subsection');
            if (window.innerWidth < mobilewidth) {
                $(this).toggleClass('active');
                //$('.facimedead_subsection').not(panel).stop().slideUp('fast');
                panel.stop().slideToggle('fast', function() {
                    //coloca o botão clicado no topo da página quando em tela pequena
                    //$('body,html').animate({scrollTop: button.offset().top}, 'fast');
                });
            } else {
                $(value).find('h3').not(this).removeClass('active');
                $(this).addClass('active');
                tagContainer.html('<h3>'+title+'</h3>'+panel.html());
                //coloca o primeiro botão no topo da página quando em tela grande
                //$('body,html').animate({scrollTop: btn.parent().children().eq(1).offset().top}, 'fast');
            }
        });
    });

    $( document ).ready(function() {
        var hash = window.location.hash;
        if (hash != null && hash != '') {
            $(hash).find('h3.sectionname').click(); // Abrir a seção após inserção
        }else{
            $('li.current').find('h3.sectionname').each(function(){
                if(!editing) {
                    $('.sectionname.notfirst').not(this).removeClass('active');
                }
                $(this).toggleClass('active');
                var facSub = $(this).siblings('.fac-section-panel');
    
                if(!editing) {
                    $('.fac-section-panel').not(facSub).stop().slideUp('fast');
                }
                facSub.stop().slideToggle('fast');
            });
        }
    });

    $('body').on('updated', '[data-inplaceeditable]', function(e) {
        var ajaxreturn = e.ajaxreturn; // Everything that web service returned.
        var oldvalue = e.oldvalue; // Element value before editing (note, this is raw value and not display value).
        var newvalue = e.newvalue;
        var sectiontitle = $(this).parent().parent().attr('sectionid');
        $('div.subsection[subsectionid=' + sectiontitle + '] span').text(ajaxreturn.displayvalue);
    });

    function getIcon(text) {
        text = text.replaceAll(' ', '-');
        text = removeAcento(text);

        switch (true) {
            case text.includes('objetivos'):
                text = 'ÍCONE-04.png';
                break;
            case text.includes('pre-requisitos'):
                text = 'ÍCONE-05.png';
                break;
            case text.includes('atividade-avaliativa'):
                text = 'icons8-hand-with-pen-filled-50.png';
                break;
            case text.includes('forum'):
                text = 'ÍCONE-01.png';
                break;
            case text.includes('forum-avaliativo'):
                text = 'ÍCONE-01.png';
                break;
            case text.includes('atividade-de-fixacao'):
                text = 'icons8-choice-filled-50.png';
                break;
            case text.includes('material-complementar'):
                text = 'icons8-book-shelf-50.png';
                break;
            case text.includes('gabarito'):
                text = 'icons8-pass-fail-50.png';
                break;
            case text.includes('video-aula'):
                text = 'ÍCONE-06.png';
                break;
            case text.includes('atividade'):
                text = 'icons8-choice-filled-50.png';
                break;
            case text.includes('referencias'):
                text = 'icons8-books-filled-50.png';
                break;
            case text.includes('web-conferencia'):
                text = 'icons8-video-conference-50.png';
                break;
            default:
                text = 'icons8-study-filled-50.png';
        }
        
        var img = $('<img>').attr('src', `${this.M.cfg.wwwroot}/theme/lambda/pix/icons-facimed/` + text);
        $('<i>').addClass('fac-sub-section-icon fa fa-folder-o');
        return $('<div>').addClass('fac-ead-module-icon').append(img);
    }
    function removeAcento(text)
    {
        text = text.toLowerCase();
        text = text.replace(new RegExp('[ÁÀÂÃ]', 'gi'), 'a');
        text = text.replace(new RegExp('[ÉÈÊ]', 'gi'), 'e');
        text = text.replace(new RegExp('[ÍÌÎ]', 'gi'), 'i');
        text = text.replace(new RegExp('[ÓÒÔÕ]', 'gi'), 'o');
        text = text.replace(new RegExp('[ÚÙÛ]', 'gi'), 'u');
        text = text.replace(new RegExp('[Ç]', 'gi'), 'c');
        return text;
    }

});

String.prototype.replaceAll = function(de, para) {
    var str = this;
    var pos = str.indexOf(de);
    while (pos > -1) {
        str = str.replace(de, para);
        pos = str.indexOf(de);
    }
    return (str);
};


