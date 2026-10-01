{{--
    Account deletion.

    The form is only shown to the account owner; the controller also requires the
    current password and revokes the session on success.
--}}
<x-page-card icon="trash" title="Delete account">
    <p class="small text-muted">
        Deleting your account removes your profile and the records tied to it. Complaints,
        announcements and audit entries are retained where they are part of the
        organisation's record. Download anything you need before continuing.
    </p>

    <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal"
            data-bs-target="#confirm-user-deletion">
        <i class="bi bi-trash me-1"></i>Delete account
    </button>

    <div class="modal fade" id="confirm-user-deletion" tabindex="-1"
         aria-labelledby="confirm-user-deletion-label" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')

                    <div class="modal-header">
                        <h2 class="modal-title h5" id="confirm-user-deletion-label">
                            Delete your account?
                        </h2>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body">
                        <p class="small">
                            This cannot be undone. Enter your password to confirm.
                        </p>

                        <label for="user-deletion-password" class="form-label small fw-semibold">Password</label>
                        <input type="password" id="user-deletion-password" name="password" required
                               class="form-control @error('userDeletion.password', 'userDeletion') is-invalid @enderror"
                               placeholder="Password">
                        @error('userDeletion.password', 'userDeletion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-danger">
                            Delete account
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-page-card>