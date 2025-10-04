document.addEventListener("DOMContentLoaded", () => {
  const aboutLink = document.getElementById("aboutLink");
  const contactLink = document.getElementById("contactLink");
  const logoLink = document.getElementById("logoLink");
  const formLink = document.getElementById("formLink");

  const overlay = document.getElementById("popupOverlay");
  
  // Debug: Check if form exists
  const auditionForm = document.getElementById("auditionForm");
  console.log("Audition form found:", auditionForm);
  
  if (!auditionForm) {
    console.error("ERROR: Audition form not found! Check if the form has id='auditionForm'");
    return;
  }

  // Dynamic fields: Student Level -> SHS Strand / College Course
  const studentLevel = document.getElementById('studentLevel');
  const shsStrandWrapper = document.getElementById('shsStrandWrapper');
  const collegeCourseWrapper = document.getElementById('collegeCourseWrapper');
  const shsStrand = document.getElementById('shsStrand');
  const collegeCourse = document.getElementById('collegeCourse');

  function updateLevelFields() {
    const level = (studentLevel && studentLevel.value) ? studentLevel.value : '';
    if (level === 'shs') {
      if (shsStrandWrapper) shsStrandWrapper.style.display = '';
      if (collegeCourseWrapper) collegeCourseWrapper.style.display = 'none';
      if (shsStrand) shsStrand.setAttribute('required', 'required');
      if (collegeCourse) collegeCourse.removeAttribute('required');
      if (collegeCourse) collegeCourse.value = '';
    } else if (level === 'college') {
      if (collegeCourseWrapper) collegeCourseWrapper.style.display = '';
      if (shsStrandWrapper) shsStrandWrapper.style.display = 'none';
      if (collegeCourse) collegeCourse.setAttribute('required', 'required');
      if (shsStrand) shsStrand.removeAttribute('required');
      if (shsStrand) shsStrand.value = '';
    } else {
      if (shsStrandWrapper) shsStrandWrapper.style.display = 'none';
      if (collegeCourseWrapper) collegeCourseWrapper.style.display = 'none';
      if (shsStrand) shsStrand.removeAttribute('required');
      if (collegeCourse) collegeCourse.removeAttribute('required');
      if (shsStrand) shsStrand.value = '';
      if (collegeCourse) collegeCourse.value = '';
    }
  }

  if (studentLevel) {
    studentLevel.addEventListener('change', updateLevelFields);
    // Initialize on load
    updateLevelFields();
  }

  const popups = {
    about: document.getElementById("aboutPopup"),
    contact: document.getElementById("contactPopup"),
    form: document.getElementById("formPopup"),
  };

  // Ensure everything starts hidden
  Object.values(popups).forEach((p) => p.classList.remove("show"));
  overlay.style.display = "none";

  function sizeScrollableArea(popup) {
    if (!popup) return;
    const header = popup.querySelector('.popup-header');
    const scrollable = popup.querySelector('.scrollable-text');
    if (!scrollable) return;
    // Force a max height on the popup, then compute available height for the scroll area
    popup.style.maxHeight = '90vh';
    const styles = getComputedStyle(popup);
    const paddingY = parseFloat(styles.paddingTop) + parseFloat(styles.paddingBottom);
    const headerH = header ? header.offsetHeight : 0;
    const available = Math.max(window.innerHeight * 0.90 - headerH - paddingY - 8, 120);
    scrollable.style.maxHeight = available + 'px';
    scrollable.style.overflowY = 'auto';
  }

  // Show popup
  function showPopup(popup) {
    Object.values(popups).forEach((p) => p.classList.remove("show"));
    if (popup) {
      popup.classList.add("show");
      overlay.style.display = "block";
      sizeScrollableArea(popup);
    }
  }

  // Hide all popups
  function hideAll() {
    Object.values(popups).forEach((p) => p.classList.remove("show"));
    overlay.style.display = "none";
  }

  // Event listeners
  aboutLink.addEventListener("click", (e) => { e.preventDefault(); showPopup(popups.about); });
  contactLink.addEventListener("click", (e) => { e.preventDefault(); showPopup(popups.contact); });
  logoLink.addEventListener("click", (e) => { e.preventDefault(); hideAll(); });
  formLink.addEventListener("click", (e) => { e.preventDefault(); showPopup(popups.form); });

  // Recompute sizes on window resize for the visible popup
  window.addEventListener('resize', () => {
    const visible = Object.values(popups).find(p => p.classList.contains('show'));
    if (visible) sizeScrollableArea(visible);
  });

  // Close buttons
  document.querySelectorAll(".close-btn").forEach((btn) => {
    btn.addEventListener("click", hideAll);
  });

  // Overlay click closes
  overlay.addEventListener("click", hideAll);


  // Email validation functionality
  const emailField = document.getElementById('email');
  let emailTimeout;
  let isEmailValid = false;
  
  // Real-time email validation
  function validateEmailRealTime(email) {
    if (!email || email.length < 5) {
      resetEmailValidation();
      return;
    }
    
    // Show loading state
    showEmailValidationState('loading', 'Checking email...');
    
    fetch('validate-email.php', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify({ email: email })
    })
    .then(response => response.json())
    .then(data => {
      if (data.valid) {
        showEmailValidationState('valid', '✓ Email verified and deliverable');
        isEmailValid = true;
      } else {
        showEmailValidationState('invalid', data.message || 'Email validation failed');
        isEmailValid = false;
      }
    })
    .catch(error => {
      console.error('Email validation error:', error);
      showEmailValidationState('warning', 'Could not verify email - will validate on submit');
      isEmailValid = null; // Allow submission but validate server-side
    });
  }
  
  function showEmailValidationState(state, message) {
    const emailField = document.getElementById('email');
    const feedbackDiv = document.getElementById('email-feedback') || createEmailFeedback();
    
    // Remove all validation classes
    emailField.classList.remove('is-valid', 'is-invalid', 'is-loading');
    feedbackDiv.classList.remove('valid-feedback', 'invalid-feedback', 'loading-feedback');
    
    // Add appropriate classes
    switch(state) {
      case 'valid':
        emailField.classList.add('is-valid');
        feedbackDiv.classList.add('valid-feedback');
        break;
      case 'invalid':
        emailField.classList.add('is-invalid');
        feedbackDiv.classList.add('invalid-feedback');
        break;
      case 'loading':
        emailField.classList.add('is-loading');
        feedbackDiv.classList.add('loading-feedback');
        break;
      case 'warning':
        feedbackDiv.classList.add('loading-feedback');
        break;
    }
    
    feedbackDiv.textContent = message;
    feedbackDiv.style.display = 'block';
  }
  
  function createEmailFeedback() {
    const emailField = document.getElementById('email');
    const feedbackDiv = document.createElement('div');
    feedbackDiv.id = 'email-feedback';
    feedbackDiv.className = 'feedback-message';
    feedbackDiv.style.fontSize = '0.875rem';
    feedbackDiv.style.marginTop = '0.25rem';
    feedbackDiv.style.display = 'none';
    emailField.parentNode.appendChild(feedbackDiv);
    return feedbackDiv;
  }
  
  function resetEmailValidation() {
    const emailField = document.getElementById('email');
    const feedbackDiv = document.getElementById('email-feedback');
    
    emailField.classList.remove('is-valid', 'is-invalid', 'is-loading');
    if (feedbackDiv) {
      feedbackDiv.style.display = 'none';
    }
    isEmailValid = false;
  }
  
  // Add email validation event listeners
  if (emailField) {
    emailField.addEventListener('input', function() {
      clearTimeout(emailTimeout);
      emailTimeout = setTimeout(() => {
        validateEmailRealTime(this.value.trim());
      }, 1000); // Wait 1 second after user stops typing
    });
    
    emailField.addEventListener('blur', function() {
      clearTimeout(emailTimeout);
      if (this.value.trim()) {
        validateEmailRealTime(this.value.trim());
      }
    });
  }

  // Debug: Confirm event listener is being attached
  console.log("Attaching submit event listener to audition form");
  
  auditionForm.addEventListener("submit", (e) => {
    e.preventDefault();
    console.log("🚀 Form submission triggered!");
    
    // Collect form data for confirmation
    const formData = new FormData(auditionForm);
    const firstName = formData.get('firstName') || '';
    const lastName = formData.get('lastName') || '';
    const email = formData.get('email') || '';
    const phone = formData.get('phone') || '';
    const category = formData.get('category') || '';
    const details = formData.get('details') || '';
    const birthDate = formData.get('birthDate') || '';
    const gender = formData.get('gender') || '';
    const level = formData.get('studentLevel') || '';
    const strand = formData.get('shsStrand') || '';
    const course = formData.get('collegeCourse') || '';
    
    // Format category for display
    const categoryDisplay = {
      'singer': '🎤 Singer',
      'dancer': '💃 Dancer', 
      'solo-musician': '🎸 Solo Musician',
      'band': '🎵 Band'
    }[category] || category;
    
    // Show confirmation dialog with all form data
    Swal.fire({
      title: "📋 Confirm Your Audition Details",
      html: `
        <div style="text-align: left; max-width: 400px; margin: 0 auto;">
          <div style="background: rgba(255, 255, 255, 0.1); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(255, 255, 255, 0.2);">
            <h6 style="color: #64b5f6; margin-bottom: 10px;">👤 Personal Information</h6>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Name:</strong> ${firstName} ${lastName}</p>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Email:</strong> ${email}</p>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Phone:</strong> ${phone}</p>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Birth Date:</strong> ${birthDate}</p>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Gender:</strong> ${gender}</p>
          </div>
          
          <div style="background: rgba(33, 150, 243, 0.2); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(33, 150, 243, 0.3);">
            <h6 style="color: #81c784; margin-bottom: 10px;">🎭 Audition Category</h6>
            <p style="margin: 5px 0; font-size: 16px; color: #ffffff;"><strong>${categoryDisplay}</strong></p>
          </div>

          <div style="background: rgba(0,0,0,0.2); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(255,255,255,0.2);">
            <h6 style="color: #ffcc02; margin-bottom: 10px;">🎓 Student Information</h6>
            <p style="margin: 5px 0; color: #ffffff;"><strong>Level:</strong> ${level || 'N/A'}</p>
            ${level === 'shs' ? `<p style="margin: 5px 0; color: #ffffff;"><strong>SHS Strand:</strong> ${strand}</p>` : ''}
            ${level === 'college' ? `<p style=\"margin: 5px 0; color: #ffffff;\"><strong>College Course:</strong> ${course}</p>` : ''}
          </div>
          
          ${details ? `
          <div style="background: rgba(255, 193, 7, 0.2); padding: 15px; border-radius: 8px; margin-bottom: 15px; border: 1px solid rgba(255, 193, 7, 0.3);">
            <h6 style="color: #ffb74d; margin-bottom: 10px;">📝 Additional Details</h6>
            <p style="margin: 5px 0; font-style: italic; color: #ffffff;">"${details}"</p>
          </div>
          ` : ''}
          
          <div style="margin-top: 20px; padding: 10px; background: rgba(255, 193, 7, 0.15); border-radius: 8px; border-left: 4px solid #ffc107;">
            <small style="color: #ffcc02;">
              ⚠️ Please double-check all information before submitting. 
              You cannot edit your application after submission.
            </small>
          </div>
        </div>
      `,
      icon: "question",
      showCancelButton: true,
      confirmButtonText: "✅ Submit Application",
      cancelButtonText: "✏️ Edit Information",
      confirmButtonColor: "#28a745",
      cancelButtonColor: "#6c757d",
      width: '600px',
      background: 'linear-gradient(135deg, #2c3e50 0%, #34495e 100%)',
      color: '#ffffff'
    }).then((result) => {
      if (result.isConfirmed) {
        // User confirmed, proceed with submission
        submitAuditionForm(formData);
      }
      // If cancelled, do nothing - user can edit the form
    });
  });

  // Separate function to handle the actual submission
  function submitAuditionForm(formData) {
    // Show loading state
    Swal.fire({
      title: "Submitting...",
      text: "Please wait while we process your audition application.",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });
    
    // Debug: Log form data
    console.log('Form data being submitted:');
    for (let [key, value] of formData.entries()) {
      console.log(key + ': ' + value);
    }
    
    // Submit form data via fetch API
    fetch('submit_audition.php', {
      method: 'POST',
      body: formData
    })
    .then(response => {
      console.log('Response status:', response.status);
      if (!response.ok) {
        throw new Error(`HTTP error! status: ${response.status}`);
      }
      return response.json();
    })
    .then(data => {
      console.log('Server response:', data);
      if (data.success) {
        Swal.fire({
          title: "Success! 🎉",
          text: data.message || "Your audition form has been submitted. Wait for the audition day. Good luck and thank you!",
          icon: "success",
          confirmButtonText: "OK",
        }).then(() => {
          auditionForm.reset();
          hideAll();
        });
      } else {
        Swal.fire({
          title: "Error!",
          text: data.message || "Failed to submit audition. Please try again.",
          icon: "error",
          confirmButtonText: "OK",
        });
      }
    })
    .catch(error => {
      console.error('Error submitting form:', error);
      Swal.fire({
        title: "Error!",
        text: "Network error occurred. Please check your connection and try again. Error: " + error.message,
        icon: "error",
        confirmButtonText: "OK",
      });
    });
  }
});
