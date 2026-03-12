/**
 * ============================================
 * CUSTOM SCRIPTS - Portal INSPIRE
 * File ini berisi semua JavaScript khusus proyek
 * ============================================
 */

// Jalankan setelah DOM siap
document.addEventListener('DOMContentLoaded', function() {
    
    // ============================================
    // INISIALISASI TOOLTIP
    // ============================================
    if (typeof $ !== 'undefined' && $.fn.tooltip) {
        $("[rel='tooltip']").tooltip();
    }
    
    // ============================================
    // AJAX LOADING INDICATOR
    // ============================================
    if (typeof $ !== 'undefined') {
        $.ajaxSetup({
            beforeSend: function() {
                $('#loading-container').show();
            },
            complete: function() {
                $('#loading-container').hide();
            }
        });
    }
    
    // ============================================
    // TOMBOL LOGOUT DI MODAL
    // ============================================
    var btnAppLogout = document.getElementById('btn-app-logout');
    if (btnAppLogout && typeof $ !== 'undefined') {
        btnAppLogout.addEventListener('click', function() {
            $("#modal-app-list").modal("hide");
        });
    }
    
    // ============================================
    // BACK TO TOP BUTTON
    // ============================================
    var backToTopBtn = document.getElementById('back-to-top');
    var scrollTimer;
    
    if (backToTopBtn) {
        // Tampilkan/sembunyikan tombol berdasarkan scroll
        window.addEventListener('scroll', function() {
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                if (window.scrollY > 100) {
                    backToTopBtn.style.display = 'block';
                } else {
                    backToTopBtn.style.display = 'none';
                }
            }, 50);
        });
        
        // Scroll ke atas saat diklik
        backToTopBtn.addEventListener('click', function(e) {
            e.preventDefault();
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
            return false;
        });
    }
    
    // ============================================
    // CUSTOM CONFIRM & ALERT DIALOGS
    // ============================================
    var confirmCallback = null;
    
    // Fungsi konfirmasi custom
    window.customConfirm = function(message, callback) {
        var confirmMsg = document.getElementById('confirm-message');
        if (confirmMsg) {
            confirmMsg.textContent = message;
        }
        confirmCallback = callback;
        if (typeof $ !== 'undefined') {
            $('#mdl-confirm').modal('show');
        }
    };
    
    // Handle tombol Ya di konfirmasi
    var confirmYesBtn = document.getElementById('confirm-yes-btn');
    if (confirmYesBtn && typeof $ !== 'undefined') {
        confirmYesBtn.addEventListener('click', function() {
            $('#mdl-confirm').modal('hide');
            if (confirmCallback && typeof confirmCallback === 'function') {
                confirmCallback(true);
            }
        });
        
        // Handle modal dismiss (Batal)
        $('#mdl-confirm').on('hidden.bs.modal', function() {
            confirmCallback = null;
        });
    }
    
    // Fungsi alert custom
    window.customAlert = function(message, type) {
        type = type || 'info';
        var header = document.getElementById('alert-header');
        var title = document.getElementById('alert-title');
        var alertMsg = document.getElementById('alert-message');
        
        if (!header || !title || !alertMsg) return;
        
        // Reset class
        header.className = 'modal-header py-2';
        
        // Set styling berdasarkan tipe
        switch(type) {
            case 'success':
                header.classList.add('bg-success');
                title.innerHTML = '<i class="fas fa-check-circle mr-2"></i>Berhasil';
                break;
            case 'warning':
                header.classList.add('bg-warning');
                title.innerHTML = '<i class="fas fa-exclamation-triangle mr-2"></i>Peringatan';
                break;
            case 'danger':
            case 'error':
                header.classList.add('bg-danger');
                title.innerHTML = '<i class="fas fa-times-circle mr-2"></i>Error';
                break;
            default:
                header.classList.add('bg-info');
                title.innerHTML = '<i class="fas fa-info-circle mr-2"></i>Info';
        }
        
        alertMsg.textContent = message;
        if (typeof $ !== 'undefined') {
            $('#mdl-alert').modal('show');
        }
    };
    
    // ============================================
    // FORM CONFIRM SUBMISSIONS
    // ============================================
    document.addEventListener('click', function(e) {
        var target = e.target.closest('[data-confirm]');
        if (!target) return;
        
        e.preventDefault();
        var message = target.getAttribute('data-confirm');
        var form = target.closest('form');
        
        customConfirm(message, function(result) {
            if (result && form) {
                // Hapus data-confirm sementara biar ga loop
                target.removeAttribute('data-confirm');
                
                // Cek halaman perencanaan - pakai AJAX biar ga scroll jump
                if (window.location.pathname.includes('perencanaan')) {
                    // Simpan posisi scroll
                    var scrollY = window.scrollY || window.pageYOffset;
                    sessionStorage.setItem('perencanaan_scroll', scrollY);
                    
                    // Simpan nilai prediksi dropdown
                    var prediksiValues = {};
                    document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
                        var tr = select.closest('tr');
                        if (tr) {
                            var kodeMkCell = tr.querySelector('td:nth-child(2)');
                            if (kodeMkCell && select.value) {
                                var kodeMk = kodeMkCell.textContent.trim();
                                prediksiValues[kodeMk] = select.value;
                            }
                        }
                    });
                    sessionStorage.setItem('prediksi_values', JSON.stringify(prediksiValues));
                    
                    // Submit via AJAX
                    var formData = new FormData(form);
                    
                    fetch('perencanaan.php', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(function(response) { return response.text(); })
                    .then(function(html) {
                        // Parse response HTML
                        var parser = new DOMParser();
                        var doc = parser.parseFromString(html, 'text/html');
                        
                        // Update hanya bagian content
                        var newContent = doc.querySelector('.content');
                        var oldContent = document.querySelector('.content');
                        
                        if (newContent && oldContent) {
                            oldContent.innerHTML = newContent.innerHTML;
                            
                            // Restore posisi scroll
                            var savedScroll = sessionStorage.getItem('perencanaan_scroll');
                            if (savedScroll) {
                                window.scrollTo(0, parseInt(savedScroll));
                                sessionStorage.removeItem('perencanaan_scroll');
                            }
                            
                            // Restore nilai prediksi
                            var savedPrediksi = sessionStorage.getItem('prediksi_values');
                            if (savedPrediksi) {
                                var prediksiData = JSON.parse(savedPrediksi);
                                document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
                                    var tr = select.closest('tr');
                                    if (tr) {
                                        var kodeMkCell = tr.querySelector('td:nth-child(2)');
                                        if (kodeMkCell) {
                                            var kodeMk = kodeMkCell.textContent.trim();
                                            if (prediksiData[kodeMk]) {
                                                select.value = prediksiData[kodeMk];
                                            }
                                        }
                                    }
                                });
                                sessionStorage.removeItem('prediksi_values');
                                
                                // Trigger kalkulasi setelah restore
                                if (typeof hitungSimulasi === 'function') {
                                    hitungSimulasi();
                                }
                            }
                            
                            // Tampilkan toast jika ada
                            var toast = document.getElementById('toast-notification');
                            if (toast) {
                                setTimeout(function() {
                                    toast.classList.remove('show');
                                    setTimeout(function() { toast.remove(); }, 150);
                                }, 5000);
                            }
                            
                            // Re-attach form listeners jika ada
                            if (typeof attachFormListeners === 'function') {
                                attachFormListeners();
                            }
                        }
                    })
                    .catch(function(error) {
                        console.error('Error:', error);
                        form.submit(); // Fallback ke submit biasa
                    });
                } else {
                    // Halaman lain - submit biasa
                    form.submit();
                }
            }
        });
    });
    
    // ============================================
    // AUTO-HIDE TOAST NOTIFICATION
    // ============================================
    var toast = document.getElementById('toast-notification');
    if (toast) {
        setTimeout(function() {
            toast.classList.remove('show');
            setTimeout(function() { toast.remove(); }, 150);
        }, 5000);
    }
    
    // ============================================
    // CLEAR PESAN DARI URL (HALAMAN LOGIN)
    // ============================================
    if (window.location.search.includes('pesan=')) {
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    
    // ============================================
    // CLICK TO LAUNCH ANIMATION (HALAMAN LOGIN)
    // ============================================
    var btnPulse = document.querySelector('.btn_pulse_1');
    if (btnPulse) {
        btnPulse.addEventListener('click', function() {
            var particles = document.getElementById('particles-js');
            var containerStart = document.getElementById('container_start');
            var btnPulseKet = document.querySelector('.btn_pulse_ket');
            var containerLogin = document.getElementById('container_login');
            var navbar = document.querySelector('.navbar');
            
            if (particles) particles.style.display = 'none';
            
            // Fade out dengan CSS transition
            if (containerStart) {
                containerStart.style.transition = 'opacity 1s';
                containerStart.style.opacity = '0';
                setTimeout(function() { containerStart.style.display = 'none'; }, 1000);
            }
            if (btnPulseKet) {
                btnPulseKet.style.transition = 'opacity 0.5s';
                btnPulseKet.style.opacity = '0';
                setTimeout(function() { btnPulseKet.style.display = 'none'; }, 500);
            }
            
            // Fade in
            if (containerLogin) {
                containerLogin.style.display = 'block';
                containerLogin.style.opacity = '0';
                containerLogin.style.transition = 'opacity 1s';
                setTimeout(function() { containerLogin.style.opacity = '1'; }, 10);
            }
            if (navbar) {
                navbar.style.display = 'block';
                navbar.style.opacity = '0';
                navbar.style.transition = 'opacity 1s';
                setTimeout(function() { navbar.style.opacity = '1'; }, 10);
            }
        });
    }
});

// ============================================
// FULLCALENDAR LAZY LOAD (HALAMAN DASHBOARD)
// ============================================
window.addEventListener('load', function() {
    setTimeout(function() {
        var calendarEl = document.getElementById('calendar');
        
        if (calendarEl && typeof FullCalendar !== 'undefined') {
            var calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    start: 'prev,next today',
                    center: 'title',
                    end: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                height: 'auto',
                events: []
            });
            calendar.render();
        }
    }, 100); // Delay kecil biar halaman settle dulu
});
