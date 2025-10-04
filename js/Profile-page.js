document.addEventListener("DOMContentLoaded", () => {
  const profilePic = document.getElementById("profile-pic");
  const dropdown = document.getElementById("dropdown");
  const userProfile = document.getElementById("user-profile");

  // Dropdown toggle
  profilePic.addEventListener("click", (e) => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
  });

  // Close dropdown on outside click
  window.addEventListener("click", (e) => {
    if (!userProfile.contains(e.target)) dropdown.style.display = "none";
  });

  // Logout
  window.logout = function () {
    Swal.fire({
      title: "Are you sure you want to log out?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, log me out",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        localStorage.clear();
        sessionStorage.clear();
        window.location.href = "login.php";
      }
    });
  };

  // Go to profile/settings
  window.goToProfile = () => (window.location.href = "profile.php");
  window.goToSettings = () => (window.location.href = "settings.php");

  // Edit profile popup
  const editBtn = document.getElementById("edit-profile-btn");
editBtn.addEventListener("click", () => {
  Swal.fire({
    title: "Edit Profile",
    html: `
      <input id="swal-name" class="swal2-input" placeholder="Full Name" value="${document.getElementById('profile-name').value}">
      <input id="swal-role" class="swal2-input" placeholder="Role" value="${document.getElementById('profile-role').value}">
      <input id="swal-section" class="swal2-input" placeholder="Section" value="${document.getElementById('profile-section').value}">
      <input id="swal-email" class="swal2-input" placeholder="Email" value="${document.getElementById('profile-email').value}">
    `,
    confirmButtonText: "Save",
    showCancelButton: true,
    background: "rgba(255, 255, 255, 0.1)", 
    color: "#fff",
    backdrop: `
      rgba(0,0,0,0.5) 
      blur(10px)
    `,
    customClass: {
      popup: 'glass-popup'
    },
    preConfirm: () => {
      document.getElementById("profile-name").value = document.getElementById("swal-name").value;
      document.getElementById("profile-role").value = document.getElementById("swal-role").value;
      document.getElementById("profile-section").value = document.getElementById("swal-section").value;
      document.getElementById("profile-email").value = document.getElementById("swal-email").value;
      Swal.fire("Saved!", "Your profile has been updated.", "success");
    },
  });
});


const backButton = document.getElementById("backButton");
if (backButton) {
  backButton.addEventListener("click", () => {
    window.location.href = "home.php";
  });
}

});
