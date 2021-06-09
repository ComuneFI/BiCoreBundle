/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you require will output into a single css file (app.css in this case)
require('../css/bicore.css');
// Need jQuery? Install it with "yarn add jquery", then uncomment to require it.
//const $ = require('jquery');

//console.log('Hello Webpack Encore! Edit me in assets/js/app.js');

//Gestione menu
$(window).resize(function () {
    ComponiMenu();
});

document.addEventListener('DOMContentLoaded', function () {
    ComponiMenu();
});

function ComponiMenu() {
    var menus = $('#menuhide').data("items");
    $("#navbar-menu").html('');
    var desktopmode = ($(window).width() >= 980) ? true : false;
    console.log($(window).width());
    $(menus).each(function (index) {
        if (menus[index].hasOwnProperty('sottolivello') && menus[index].sottolivello.length > 0) {
            var contatorevoci = 0;
            var numerocolonne = 1;
            submenuitems = menus[index].sottolivello;

            if (desktopmode) {
                //console.log(submenuitems);
                $(submenuitems).each(function (indexsubitem) {
                    contatorevoci = contatorevoci + 1;
                    if (contatorevoci >= 6) {
                        contatorevoci = 0;
                        numerocolonne = numerocolonne + 1;

                    }
                    if (submenuitems.hasOwnProperty('sottolivello') && submenuitems.sottolivello.length > 0) {
                        contatorevoci = contatorevoci + 1;
                        subsubmenuitems = submenuitems.sottolivello;
                        $(subsubmenuitems).each(function (indexsubsubitem) {
                            contatorevoci = contatorevoci + 1;
                            if (contatorevoci >= 6) {
                                contatorevoci = 0;
                                numerocolonne = numerocolonne + 1;

                            }
                        });
                    }
                });
                var valorelarghezza = (3 * numerocolonne);
            }

            var subsubclasse = submenuitems.hasOwnProperty('classe') ? 'submenuitems.classe' : '';
            var classe_sub_li;
            if (numerocolonne > 0 && desktopmode) {
                classe_sub_li = 'nav-item dropdown megamenu moved-megamenu' + ' ' + subsubclasse;
            } else {
                classe_sub_li = 'nav-item dropdown';
            }
            var new_sub_li = $('<li class="' + classe_sub_li + '"></li>').appendTo($("#navbar-menu"));
            $('<a class="nav-link dropdown-toggle bicore-menu-item-level-1" href="' + menus[index].percorso.percorso + '" data-toggle="dropdown" aria-expanded="false"><span>' + menus[index].nome + '</span></a>').appendTo(new_sub_li);
            var div_dropdown = $('<div class="mainmenu-dropdown-menu dropdown-menu col-' + parseInt(valorelarghezza) + '"></div>').appendTo(new_sub_li);
            var row_dropdown = $('<div class="row"></div>').appendTo(div_dropdown);

            //Ogni 6 link genera una nuova colonna (in desktop mode)
            var numerolinks = 0;
            var ul_link_list;
            var div_list_wrapper;
            var div_col_dropdown;
            $(submenuitems).each(function (indexsubitem) {
                if (numerolinks > 5 && desktopmode) {
                    numerolinks = 0;
                }
                if (numerolinks === 0) {
                    div_col_dropdown = $('<div class="col"></div>').appendTo(row_dropdown);
                    div_list_wrapper = $('<div class="link-list-wrapper"></div>').appendTo(div_col_dropdown);
                    ul_link_list = $('<ul class="link-list"></ul>').appendTo(div_list_wrapper);
                }
                numerolinks++;
                if (submenuitems[indexsubitem].hasOwnProperty('sottolivello') && submenuitems[indexsubitem].sottolivello.length > 0) {
                    //titolo
                    //$('<li><div class="dropdown-divider"></div></li>').appendTo(ul_link_list);
                    $('<li class="no_toc text-uppercase"><strong>' + submenuitems[indexsubitem].nome + '</strong></li>').appendTo(ul_link_list);
                    $('<li><div class="dropdown-divider"></div></li>').appendTo(ul_link_list);
                    numerolinks = numerolinks + 1;
                    var subsubmenuitems = submenuitems[indexsubitem].sottolivello;
                    $(subsubmenuitems).each(function (indexsubsubitem) {
                        //console.log(subsubmenuitems[indexsubsubitem]);
                        numerolinks = numerolinks + 1;
                        $('<li role="menuitem"><a class="list-item bicore-menu-item-level-2" href="' + subsubmenuitems[indexsubsubitem].percorso + '" target="' + subsubmenuitems[indexsubsubitem].target + '"><span>' + subsubmenuitems[indexsubsubitem].nome + '</span></a></li>').appendTo(ul_link_list);
                    });

                    $('<li><div class="dropdown-divider"></div></li>').appendTo(ul_link_list);
                } else {
                    //voce menu
                    $('<li><a class="list-item bicore-menu-item-level-2" href="' + submenuitems[indexsubitem].percorso + '" target="' + submenuitems[indexsubitem].target + '"><span>' + submenuitems[indexsubitem].nome + '</span></a></li>').appendTo(ul_link_list);
                }
            });
        } else {
            var subclasse = menus[index].hasOwnProperty('classe') ? 'nav-link ' + menus[index].classe : '';
            var classe_li;
            if (subclasse.length > 0) {
                classe_li = 'nav-item' + ' ' + subclasse;
            } else {
                classe_li = 'nav-item';
            }
            var new_li = $('<li class="' + classe_li + '"><a href="' + menus[index].percorso.percorso + '" class="nav-link bicore-menu-item-level-1"><span>' + menus[index].nome + '</span></a></li>').appendTo($("#navbar-menu"));
        }
    });
}

$(document).on("shown.bs.dropdown", '.moved-megamenu', function (e) {
    var mymegamenu = $(this).find('.mainmenu-dropdown-menu');
    var o = $(this).offset();
    var o2 = mymegamenu.offset();
    var newleft = o.left - parseInt((mymegamenu.width() / 2));
    if (newleft < 10) {
        newleft = 10;
    }
    while ((mymegamenu.width() + newleft) > $(window).width()) {
        newleft--;
    }
    mymegamenu.offset({top: o2.top, left: newleft});
});