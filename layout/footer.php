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
