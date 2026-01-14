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
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">Konfirmasi Logout</h4>
				<button type="button" class="close" data-dismiss="modal" aria-label="Close">
					<span aria-hidden="true">&times;</span>
				</button>
			</div>
			<div class="modal-body">
				<p>Apakah Anda yakin ingin keluar dari sistem?</p>
			</div>
			<div class="modal-footer justify-content-between">
				<button type="button" class="btn btn-default" data-dismiss="modal">Batal</button>
				<a href="logout.php" class="btn btn-danger">Ya, Keluar</a>
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
</script>

</body></html>
