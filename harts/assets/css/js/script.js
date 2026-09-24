$(document).ready(function($){
    
    // 1. Logika Active Menu (Otomatis deteksi halaman)
    var path = window.location.pathname.split("/").pop();

    // Jika di root (kosong), arahkan ke index.php
    if (path == '') {
        path = 'index.php';
    }

    // Cari link yang href-nya cocok dengan nama file sekarang
    var target = $('#accordian ul li a[href="'+path+'"]');
    
    // Tambahkan class active ke element <li> (parent dari <a>)
    target.parent().addClass('active');


    // 2. Logika Toggle Sidebar (Buka-Tutup)
    $('#toggleSidebar').click(function(){
        // Tambahkan atau hapus class .sidebar-closed
        $('#accordian').toggleClass('sidebar-closed');
        
        // Animasi ganti icon panah
        if($('#accordian').hasClass('sidebar-closed')){
            $('#toggleIcon').text('▶');
            $(this).addClass('bg-indigo-600/20 text-indigo-800'); // Sedikit variasi warna saat ditutup
        } else {
            $('#toggleIcon').text('◀');
            $(this).removeClass('bg-indigo-600/20 text-indigo-800');
        }
    });

});