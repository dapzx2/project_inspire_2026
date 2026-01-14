/**
 *   file javascript berisi kelas-kelas javascript yang diperlukan TIK UNSRAT 
 * 
 */

/**
 *   kelas untuk mengatur input hanya angka
 *
 */
$('.tik_num').on('input', function () {
    var id = $(this).attr('id');

    // jika ada input yang bukan angka
    if ($(this).val().match(/^\d+$/) == null) {
        $(this).val('');
        alert_js('gagal', 'Input hanya terdiri dari angka');
    }
});

/**
 *   kelas untuk mengatur rupiah pada input
 *
 */
$('.tik_rupiah').keyup(function () {
    var val = $(this).val();

    // mengatur agar inputan hanya bisa angka dan string "Rp"
    if (val.indexOf("Rp.") == -1) {
        $(this).val('');
        alert_js('gagal', 'Masukan hanya berupa angka');
    }

    var split = val.replace(/[^A-za-z0-9]/g, '').split('').reverse().join('');
    var match = split.match(/\d{1,3}/g);
    var rupiah = match.join(",").split('').reverse().join('');
    $(this).val('Rp. ' + rupiah);
});

/**
 * Kelas untuk mengubah input semua karakter menjadi huruf besar
 * 
 */
$('.tik_uppercase').on('input', function () {
    var val = $(this).val().toUpperCase();

    $(this).val(val);
});

/** ======================= OLD CODE ====================- */
// /**
// *   kelas untuk mengatur input hanya angka
// *
// */
// $('.tik_num').on('input', function(){
//     var id = $(this).attr('id');

//     // jika ada input yang bukan angka
//     if ($(this).val().match(/^\d+$/) == null) {
//         $(this).val('');
//         alert_js('gagal', 'Input hanya terdiri dari angka');
//     }
// });   

// /**
// *   kelas untuk mengatur rupiah pada input
// *
// */
// $('.tik_rupiah').keyup(function(){
//     var val    = $(this).val();

//     // mengatur agar inputan hanya bisa angka dan string "Rp"
//     if(val.indexOf("Rp.") == -1) {
//         $(this).val('');
//         alert_js('gagal', 'Masukan hanya berupa angka');
//     }

//     var split  = val.replace(/[^A-za-z0-9]/g, '').split('').reverse().join('');
//     var match  = split.match(/\d{1,3}/g);
//     var rupiah = match.join(",").split('').reverse().join('');
//     $(this).val('Rp. '+rupiah);
// });