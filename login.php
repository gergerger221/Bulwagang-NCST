<?php
// Set page-specific variables
$current_page = 'login';

// Include head component
include 'includes/head.php';
?>

<div class="btn-back">
    <a href="view.php">
        <button id="buttonBack">&larr; Back</button>
    </a>
</div>

<div class="wrapper">
    <div class="container-fluid">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-12 col-md-10 col-lg-8 col-xl-6">
                <div class="card login-card shadow-lg">
                    <div class="row g-0">
                        <div class="col-md-6 d-flex align-items-center justify-content-center side-img">
                            <img src="<?php echo $site_config['logo']; ?>" alt="Logo" class="img-fluid">
                        </div>

                        <div class="col-md-6 right">
                            <div class="card-body p-4 p-md-5">
                                <div class="input-box">
                                    <form id="loginForm">
                                        <header class="text-center mb-4">Log In</header>

                                        <div class="mb-4 input-field">
                                            <input type="email" class="form-control input" name="email" required
                                                autocomplete="email" placeholder=" ">
                                            <label class="form-label">Email</label>
                                        </div>

                                        <div class="mb-3 input-field">
                                            <input type="password" class="form-control input" name="password" required
                                                placeholder=" ">
                                            <label class="form-label">Password</label>
                                        </div>

                                        <div class="d-grid mb-3">
                                            <button type="submit" class="btn btn-primary submit">Log In</button>
                                        </div>

                                        <div class="text-center">
                                            <span id="forgotPassword" class="text-decoration-underline text-primary"
                                                style="cursor: pointer">
                                                Forgot Password?
                                            </span>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Forgot Password Modal -->
<div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="forgotPasswordModalLabel">Forgot Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="forgotPasswordForm">
                    <div class="mb-3">
                        <label for="resetEmail" class="form-label">Enter your email:</label>
                        <input type="email" class="form-control" id="resetEmail" name="email" required
                            autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" form="forgotPasswordForm" class="btn btn-primary">Send Password</button>
            </div>
        </div>
    </div>
</div>

<?php
// Include footer component
include 'includes/footer.php';
?>