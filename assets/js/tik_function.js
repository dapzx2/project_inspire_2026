/**
*   file javascript berisi fungsi-fungsi umum yang diperlukan TIK UNSRAT 
* 
*/
$(document).ready(function() {
    // auto managing navbar
    var url = window.location.href;

    $('.nav-sidebar li').each(function() {
        if ($(this).children('a').attr('href') == url) {
            $(this).children('a').addClass('active');
            $(this).parents('li.nav-item').addClass('menu-open');
        }
    });

    // auto managing breadcrumb
    var i = 0;
    var menus = [];

    $('.nav-sidebar li a.active').each(function() {
        menus[i] = $.trim($(this).text());
    });

    $('.content-header .container-fluid').html('<h1>'+menus[menus.length-1]+'</h1>');
    
    $(window).scroll(function () {
        if ($(this).scrollTop() > 100) {
            $('#back-to-top').fadeIn();
        } else {
            $('#back-to-top').fadeOut();
        }
    });
    // scroll body to 0px on click
    $('#back-to-top').click(function () {
        $('body,html').animate({
            scrollTop: 0
        }, 400);
        return false;
    });
});


/**
*   mengatur navigasi/menu menjadi 'active' secara manual yang tidak terdeteksi otomatis
*
*   @param nav_name       nama navigasi/menu
*
*/
function navbar_dynamic(nav_name) {
    // managing navbar
    var menu_name = '';
    $('.nav-sidebar li').each(function() {
        var menu_link = $.trim($(this).children('a').text());

        if (menu_link == nav_name) {
            menu_name = $.trim($(this).children('a').text());
            $(this).children('a').addClass('active');
            $(this).parents('li.nav-item').addClass('menu-open');
        }
    });

    $('.content-header .container-fluid').html('<h1>'+menu_name+'</h1>');
}


/**
*   fungsi untuk membuat style alert
*
*   @param status       status error sesuai dengan switch
*   @param txt          pesan error  
*
*/
function alert_js(status, txt) {
    var style = '', icon = '';

    switch(status) {
        case 'berhasil'     :  style = 'alert-success alert_js'; icon = 'fa-check'; break;
        case 'peringatan'   :  style = 'alert-warning alert_js'; icon = 'fa-exclamation-triangle'; break;
        case 'gagal'        :  style = 'alert-danger alert_js'; icon = 'fa-ban'; break;
    }

    $('.alert_js--text').remove();
    $('#alert_js').append("<div class='alert alert-dismissible'><h5><i class='icon fa'></i></h5></div>");
    $('#alert_js .alert').addClass(style);
    $('#alert_js .icon').addClass(icon);
    $('#alert_js h5').append('<span class="alert_js--text">'+txt+'</span>');
    $('.alert_js').fadeOut(8000);
}

/**
*   fungsi untuk menghilangkan karakter selain angka pada input
*
*   @param input   string | masukan terdiri dari angka dan selain angka
* 
*   @return        string | value dengan isi yang hanya berupa angka
*/
function tik_alpha_numeric(input) 
{
    input = input.replace(/[^0-9]/g, '');

    return input;
}

/**
 *   fungsi untuk mengecek length dari input
 * 
 *   @param max_length  maksimal length yang diharapkan
 *   @param elem_id     id element yang diinput
 * 
 *   @return            pesan error
 */
function tik_check_length(max_length = 0, elem_id)
{   
    $(`#${elem_id}`).on('input', function() {
        if ($(this).val().length > max_length) {
            $(this).val('');
            alert_js('gagal', `Input melebihi batas (batas: ${max_length})`);
            form_error(id, true);
        }
    });
}