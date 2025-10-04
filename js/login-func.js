//Back button
const buttonBack = document.getElementById('buttonBack');
buttonBack.addEventListener('click', () => {
  window.history.back();
});

//Get elements
const loginForm = document.getElementById('loginForm');
const forgotPasswordLink = document.getElementById('forgotPassword');
const forgotPasswordForm = document.getElementById('forgotPasswordForm');
const forgotPasswordModal = new bootstrap.Modal(document.getElementById('forgotPasswordModal'));

// Handle login form submission
loginForm.addEventListener('submit', function(e) {
  e.preventDefault();
  
  const formData = new FormData(loginForm);
  const email = formData.get('email');
  const password = formData.get('password');
  
  // Validate input
  if (!email || !password) {
    Swal.fire({
      icon: 'error',
      title: 'Missing Information',
      text: 'Please enter both email and password',
      timer: 3000,
      showConfirmButton: false
    });
    return;
  }
  
  // Show loading state
  const submitBtn = loginForm.querySelector('button[type="submit"]');
  const originalText = submitBtn.textContent;
  submitBtn.textContent = 'Logging in...';
  submitBtn.disabled = true;
  
  // Send authentication request
  fetch('api/authenticate.php', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password })
  })
  .then(async (response) => {
    // Gracefully handle non-JSON or non-200 responses
    const text = await response.text();
    let data;
    try { data = JSON.parse(text); } catch (e) { data = null; }
    if (!response.ok) {
      throw new Error((data && data.message) || text || `HTTP ${response.status}`);
    }
    if (!data) throw new Error('Unexpected server response.');
    return data;
  })
  .then((data) => {
    if (data.success) {
      // Show success and ensure redirect even if alert is auto-closed
      const redirectTo = data.redirect || 'home.php';
      let redirected = false;
      const go = () => { if (!redirected) { redirected = true; window.location.assign(redirectTo); } };
      Swal.fire({
        icon: 'success',
        title: 'Login Successful!',
        text: `Welcome back, ${data.user.name}!`,
        timer: 1500,
        showConfirmButton: false,
        willClose: go
      }).then(go);
      // Fallback in case the modal library behaves unexpectedly
      setTimeout(go, 2000);
    } else {
      Swal.fire({
        icon: 'error',
        title: 'Login Failed',
        text: data.message || 'Invalid credentials',
        timer: 3500,
        showConfirmButton: false
      });
    }
  })
  .catch((error) => {
    console.error('Login error:', error);
    Swal.fire({
      icon: 'error',
      title: 'Connection Error',
      text: (error && error.message) ? error.message : 'Unable to connect to the server. Please try again.',
      timer: 4000,
      showConfirmButton: false
    });
  })
  .finally(() => {
    // Restore button
    submitBtn.textContent = originalText;
    submitBtn.disabled = false;
  });
});

// Forgot Password functionality
forgotPasswordLink.addEventListener('click', () => {
  forgotPasswordModal.show();
});

// Handle forgot password form submission via SMTP API
forgotPasswordForm.addEventListener('submit', function(e) {
  e.preventDefault();

  const email = document.getElementById('resetEmail').value.trim();
  if (!email) {
    Swal.fire({ icon: 'error', title: 'Missing Email', text: 'Please enter your email.' });
    return;
  }

  Swal.fire({
    title: 'Sending reset email...',
    text: 'Please wait while we process your request.',
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading()
  });

  fetch('api/forgot_password.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify({ email })
  })
  .then(async (res) => {
    let data = null; try { data = await res.json(); } catch(e) {}
    return data || { success: true };
  })
  .then(() => {
    Swal.fire({
      icon: 'success',
      title: 'Check your email',
      text: 'If the email exists, a reset email has been sent.',
      confirmButtonText: 'OK'
    }).then(() => {
      forgotPasswordForm.reset();
      forgotPasswordModal.hide();
    });
  })
  .catch((err) => {
    console.error('Forgot password error:', err);
    Swal.fire({
      icon: 'success',
      title: 'Check your email',
      text: 'If the email exists, a reset email has been sent.'
    }).then(() => {
      forgotPasswordForm.reset();
      forgotPasswordModal.hide();
    });
  });
});

// Removed legacy localStorage login handler to avoid duplicate submission
