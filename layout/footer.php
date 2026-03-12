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

<!-- Back to Top Button -->
<a id="back-to-top" href="#" class="btn btn-danger back-to-top" role="button">
	<i class="fas fa-chevron-up"></i>
</a>

<script src="assets/js/dashboard.bundle.js"></script>
<script src="assets/js/script.js"></script>

</body></html>

