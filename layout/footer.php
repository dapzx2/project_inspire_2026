			<!-- END MAIN CONTENT WRAPPER -->
		</div>
		
		<!-- Main Footer -->
		<footer class="main-footer text-sm">
			<strong>Copyright © 2026 UPT-TIK UNSRAT</strong> * All rights reserved.
			<div class="float-right d-none d-sm-inline-block">
				<b>Version</b> 1.70.7
			</div>
		</footer>
	</span>

<!-- Modal Logout -->
<div class="modal fade" id="mdl-logout">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-body">
				<h5>Yakin untuk keluar?</h5>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<a href="logout.php">
					<button type="button" class="btn bg-black"><i class="fas fa-sign-out-alt mr-2"></i>KELUAR</button>
				</a>
			</div>
		</div>
	</div>
</div>

<!-- Modal Custom Confirm -->
<div class="modal fade" id="mdl-confirm" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header bg-warning py-2">
				<h5 class="modal-title"><i class="fas fa-question-circle mr-2"></i>Konfirmasi</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center py-4">
				<p id="confirm-message" class="mb-0">Apakah Anda yakin?</p>
			</div>
			<div class="modal-footer justify-content-center border-top-0 pt-0">
				<button type="button" class="btn btn-secondary" data-dismiss="modal">
					<i class="fas fa-times mr-1"></i>Batal
				</button>
				<button type="button" class="btn btn-primary" id="confirm-yes-btn">
					<i class="fas fa-check mr-1"></i>Ya
				</button>
			</div>
		</div>
	</div>
</div>

<!-- Modal Custom Alert -->
<div class="modal fade" id="mdl-alert" tabindex="-1" role="dialog">
	<div class="modal-dialog modal-dialog-centered modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header py-2" id="alert-header">
				<h5 class="modal-title" id="alert-title"><i class="fas fa-info-circle mr-2"></i>Info</h5>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body text-center py-4">
				<p id="alert-message" class="mb-0">Pesan</p>
			</div>
			<div class="modal-footer justify-content-center border-top-0 pt-0">
				<button type="button" class="btn btn-primary" data-dismiss="modal">OK</button>
			</div>
		</div>
	</div>
</div>

<script src="assets/js/dashboard.bundle.js"></script>

<script>
	// button tooltip
	$("[rel='tooltip']").tooltip();

	$.ajaxSetup({
		beforeSend: function() {
			$('#loading-container').show();
		},
		complete: function() {
			$('#loading-container').hide();
		}
	});

	$("#btn-app-logout").on("click", function() {
		$("#modal-app-list").modal("hide");
	});

	// ============================================
	// CUSTOM CONFIRM & ALERT DIALOGS
	// ============================================
	
	let confirmCallback = null;

	// Custom Confirm Function
	function customConfirm(message, callback) {
		$('#confirm-message').text(message);
		confirmCallback = callback;
		$('#mdl-confirm').modal('show');
	}

	// Handle confirm yes button
	$('#confirm-yes-btn').on('click', function() {
		$('#mdl-confirm').modal('hide');
		if (confirmCallback && typeof confirmCallback === 'function') {
			confirmCallback(true);
		}
	});

	// Handle modal dismiss (No/Cancel)
	$('#mdl-confirm').on('hidden.bs.modal', function() {
		// Only call callback with false if it wasn't already handled by yes button
		confirmCallback = null;
	});

	// Custom Alert Function
	function customAlert(message, type = 'info') {
		const header = $('#alert-header');
		const title = $('#alert-title');
		
		// Reset classes
		header.removeClass('bg-info bg-success bg-warning bg-danger');
		
		// Set type-specific styling
		switch(type) {
			case 'success':
				header.addClass('bg-success');
				title.html('<i class="fas fa-check-circle mr-2"></i>Berhasil');
				break;
			case 'warning':
				header.addClass('bg-warning');
				title.html('<i class="fas fa-exclamation-triangle mr-2"></i>Peringatan');
				break;
			case 'danger':
			case 'error':
				header.addClass('bg-danger');
				title.html('<i class="fas fa-times-circle mr-2"></i>Error');
				break;
			default:
				header.addClass('bg-info');
				title.html('<i class="fas fa-info-circle mr-2"></i>Info');
		}
		
		$('#alert-message').text(message);
		$('#mdl-alert').modal('show');
	}

	// Override form confirm submissions
	$(document).on('click', '[data-confirm]', function(e) {
		e.preventDefault();
		const message = $(this).data('confirm');
		const form = $(this).closest('form');
		const btn = $(this);
		
		customConfirm(message, function(result) {
			if (result) {
				// If it's a form submit button, submit the form
				if (form.length) {
					// Remove the data-confirm temporarily to avoid loop
					btn.removeAttr('data-confirm');
					
					// Check if on perencanaan page - use AJAX to prevent scroll jump
					if (window.location.pathname.includes('perencanaan')) {
						// Save scroll position
						const scrollY = window.scrollY || window.pageYOffset;
						sessionStorage.setItem('perencanaan_scroll', scrollY);
						
						// Save prediksi nilai dropdown values before update
						const prediksiValues = {};
						document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
							const kodeMk = select.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
							if (select.value) {
								prediksiValues[kodeMk] = select.value;
							}
						});
						sessionStorage.setItem('prediksi_values', JSON.stringify(prediksiValues));
						
						// Submit via AJAX
						const formData = new FormData(form[0]);
						
						fetch('perencanaan.php', {
							method: 'POST',
							body: formData,
							headers: {
								'X-Requested-With': 'XMLHttpRequest'
							}
						})
						.then(response => response.text())
						.then(html => {
							// Parse the response HTML
							const parser = new DOMParser();
							const doc = parser.parseFromString(html, 'text/html');
							
							// Update only the content section
							const newContent = doc.querySelector('.content');
							const oldContent = document.querySelector('.content');
							
							if (newContent && oldContent) {
								oldContent.innerHTML = newContent.innerHTML;
								
								// Restore scroll position
								const savedScroll = sessionStorage.getItem('perencanaan_scroll');
								if (savedScroll) {
									window.scrollTo(0, parseInt(savedScroll));
									sessionStorage.removeItem('perencanaan_scroll');
								}
								
								// Restore prediksi nilai values
								const savedPrediksi = sessionStorage.getItem('prediksi_values');
								if (savedPrediksi) {
									const prediksiData = JSON.parse(savedPrediksi);
									document.querySelectorAll('.prediksi-nilai').forEach(function(select) {
										const kodeMk = select.closest('tr').querySelector('td:nth-child(2)').textContent.trim();
										if (prediksiData[kodeMk]) {
											select.value = prediksiData[kodeMk];
										}
									});
									sessionStorage.removeItem('prediksi_values');
									
									// Trigger calculation after restoring values
									if (typeof hitungSimulasi === 'function') {
										hitungSimulasi();
									}
								}
								
								// Show toast if exists
								const toast = document.getElementById('toast-notification');
								if (toast) {
									setTimeout(function() {
										toast.classList.remove('show');
										setTimeout(function() { toast.remove(); }, 150);
									}, 5000);
								}
								
								// Re-attach form listeners if function exists
								if (typeof attachFormListeners === 'function') {
									attachFormListeners();
								}
							}
						})
						.catch(error => {
							console.error('Error:', error);
							form.submit(); // Fallback to normal submit
						});
					} else {
						// Other pages - normal submit
						form.submit();
					}
				}
			}
		});
	});
</script>

</body></html>
