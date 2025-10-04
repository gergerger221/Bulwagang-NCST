document.addEventListener("DOMContentLoaded", () => {
  // ----------------------------
  // Topnav Dropdown
  // ----------------------------
  const profilePic = document.getElementById("profile-pic");
  const dropdown = document.getElementById("dropdown");
  const menuToggle = document.getElementById("menu-toggle");
  const userProfile = document.getElementById("user-profile");

  if (profilePic && dropdown && menuToggle && userProfile) {
    profilePic.addEventListener("click", (e) => {
      e.stopPropagation();
      dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
    });

    menuToggle.addEventListener("click", () => {
      dropdown.style.display = dropdown.style.display === "flex" ? "none" : "flex";
    });

    window.addEventListener("click", (e) => {
      if (!userProfile.contains(e.target)) dropdown.style.display = "none";
    });
  }

  // ----------------------------
  // Logout
  // ----------------------------
  window.logout = function () {
    Swal.fire({
      title: "Are you sure you want to log out?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: "Yes, log me out",
      cancelButtonText: "Cancel",
    }).then((result) => {
      if (result.isConfirmed) {
        localStorage.removeItem("user");
        sessionStorage.clear();
        window.location.href = "login.php";
      }
    });
  };

  // ----------------------------
  // Digital Clock & Date
  // ----------------------------
  function updateDateTime() {
    const now = new Date();
    const date = now.toLocaleDateString(undefined, { year: "numeric", month: "long", day: "numeric" });
    let hours = now.getHours();
    const minutes = String(now.getMinutes()).padStart(2, "0");
    const seconds = String(now.getSeconds()).padStart(2, "0");
    const ampm = hours >= 12 ? "PM" : "AM";
    hours = hours % 12 || 12;

    const datetimeEl = document.getElementById("datetime");
    if (datetimeEl) {
      datetimeEl.innerHTML = `
        <div class="digital-date">${date}</div>
        <div class="digital-time">${hours}:${minutes}:${seconds} ${ampm}</div>
      `;
    }
  }
  setInterval(updateDateTime, 1000);
  updateDateTime();

  // ----------------------------
  // FullCalendar
  // ----------------------------
  const calendarEl = document.getElementById("calendar-gui");
  if (calendarEl) {
    const calendar = new FullCalendar.Calendar(calendarEl, {
      initialView: "dayGridMonth",
      height: 400,
      selectable: true,
      headerToolbar: { left: "prev,next today", center: "title", right: "" },
      events: [
        { title: "1st Day of Exam", start: "2025-09-03" },
        { title: "2nd Day of Exam", start: "2025-09-04" },
        { title: "3rd Day of Exam", start: "2025-09-05" },
        { title: "4th Day of Exam", start: "2025-09-06" },
        { title: "Audition Day", start: "2025-09-12" },
        { title: "BN Performance", start: "2025-09-29" },
      ],
      dateClick: (info) => {
        const eventList = document.getElementById("event-list");
        const selectedDate = info.dateStr;
        const filteredEvents = calendar.getEvents().filter((ev) => ev.startStr === selectedDate);
        if (eventList) {
          eventList.innerHTML = filteredEvents.length
            ? filteredEvents.map((ev) => `<li>${ev.title}</li>`).join("")
            : "<li>No events scheduled.</li>";
        }
      },
    });
    calendar.render();
  }

  // ----------------------------
  // Image Viewer (Posts)
  // ----------------------------
  document.querySelectorAll(".post-img").forEach((img) => {
    img.addEventListener("click", () => {
      Swal.fire({
        imageUrl: img.src,
        imageAlt: img.alt,
        background: "rgba(0,0,0,0.95)",
        showConfirmButton: true,
        confirmButtonText: "Close",
        width: "80%",
        imageWidth: "100%",
        imageHeight: "auto",
      });
    });
  });

  // ----------------------------
  // Members, Performances, Albums
  // ----------------------------
  const membersData = {
    bands: [
      { img: "asset/img/ca1.jpg", name: "Sevi", role: "Guitarist" },
      { img: "asset/img/cat2.jpg", name: "Shagi", role: "Drummer" },
      { img: "asset/img/cat3.jpg", name: "Smokey", role: "Keyboard" },
    ],
    singers: [
      { img: "asset/img/cat1-singer.jpg", name: "Mikasa", role: "Vocalist" },
      { img: "asset/img/cat2-singer.jpg", name: "Maisy", role: "Soloist" },
      { img: "asset/img/cat3-singer.jpg", name: "Cloud", role: "Rap" },
    ],
    dancers: [
      { img: "asset/img/cat1-dancer.jpg", name: "Rigby", role: "Lead Dancer" },
      { img: "asset/img/cat2-dancer.jpg", name: "Levi", role: "Hip-Hop" },
      { img: "asset/img/cat3-dancer.jpg", name: "Sevi Sigma", role: "Hip-Hop" },
    ],
  };

  const createMemberCard = (img, name, role) => `
    <div class="member-card" data-name="${name.toLowerCase()}" style="width:150px;backdrop-filter:blur(10px);background:rgba(255,255,255,0.15);border-radius:16px;overflow:hidden;text-align:center;transition:transform 0.3s;cursor:pointer;border:1px solid rgba(255,255,255,0.3);">
      <img src="${img}" style="width:100%;height:120px;object-fit:cover;border-bottom:1px solid rgba(255,255,255,0.3);">
      <div style="padding:10px;color:#fff;text-shadow:0 1px 3px rgba(0,0,0,0.7);">
        <b>${name}</b><br><small style="color:#eee;text-shadow:0 1px 2px rgba(0,0,0,0.5);">${role}</small>
      </div>
    </div>
  `;

  document.querySelectorAll(".sidebar a").forEach((link) => {
    link.addEventListener("click", (e) => {
      const text = e.target.textContent.trim().toLowerCase();
      
      // Allow Admin link to work normally (don't prevent default)
      if (text.includes("admin") || e.target.id === "adminLink" || e.target.closest("#adminLink")) {
        return; // Let the link navigate normally
      }
      
      e.preventDefault();

      // Members Popup
      if (text.includes("members")) {
        Swal.fire({
          title: "Members",
          html: `
            <div style="color:#fff">
              <div style="display:flex;gap:15px;justify-content:center;flex-wrap:wrap;margin-bottom:15px;">
                <button class="member-category" data-type="bands">🎸 Bands</button>
                <button class="member-category" data-type="singers">🎤 Singers</button>
                <button class="member-category" data-type="dancers">💃 Dancers</button>
              </div>
              <input type="text" id="member-search" placeholder="🔎 Search members..." style="width:95%;padding:8px 12px;margin-bottom:10px;border-radius:12px;border:1px solid rgba(255,255,255,0.3);background:rgba(255,255,255,0.2);color:#fff;">
              <div id="members-container" style="max-height:400px;overflow-y:auto;display:flex;flex-wrap:wrap;gap:15px;justify-content:center;"></div>
            </div>
          `,
          showConfirmButton: false,
          background: "rgba(0,0,0,0.85)",
          color: "#fff",
          didOpen: () => {
            const searchInput = document.getElementById("member-search");
            const membersContainer = document.getElementById("members-container");

            const renderMembers = (type) => {
              membersContainer.innerHTML = membersData[type].map((m) => createMemberCard(m.img, m.name, m.role)).join("");
              membersContainer.querySelectorAll(".member-card").forEach((card) => {
                card.addEventListener("mouseover", () => (card.style.transform = "scale(1.05)"));
                card.addEventListener("mouseout", () => (card.style.transform = "scale(1)"));
                card.addEventListener("click", () => {
                  Swal.fire({
                    title: card.querySelector("b").innerText,
                    html: `<p style="color:#fff;">Role: ${card.querySelector("small").innerText}</p>`,
                    imageUrl: card.querySelector("img").src,
                    background: "rgba(0,0,0,0.9)",
                    confirmButtonText: "Close",
                    color: "#fff",
                  });
                });
              });
            };

            document.querySelectorAll(".member-category").forEach((btn) => {
              btn.addEventListener("click", () => renderMembers(btn.dataset.type));
            });

            searchInput.addEventListener("input", () => {
              const query = searchInput.value.toLowerCase();
              membersContainer.querySelectorAll(".member-card").forEach((card) => {
                card.style.display = card.dataset.name.includes(query) ? "block" : "none";
              });
            });

            renderMembers("bands");
          },
        });
      }

      // Performances Popup
      else if (text.includes("performances")) {
        Swal.fire({
          title: "🎤 Performances",
          html: `<div style="color:#fff;text-align:center;">Upcoming and past performances will be listed here!</div>`,
          showCloseButton: true,
          background: "rgba(0,0,0,0.85)",
          color: "#fff",
        });
      }

      // Albums Popup
      else if (text.includes("albums")) {
        const albumsData = [
          { title: "Album 1", images: ["asset/img/audition-15.jpg", "asset/img/cat1-dancer.jpg", "asset/img/cat2-dancer.jpg"] },
          { title: "Album 2", images: ["asset/img/audition-copy(2).jpg", "asset/img/cat1-singer.jpg", "asset/img/cat3-singer.jpg"] },
          { title: "Album 3", images: ["asset/img/aud.jpg", "asset/img/cat2.jpg", "asset/img/cat2-singer.jpg"] },
        ];

        Swal.fire({
          title: "Albums",
          html: `
            <div style="color:#fff">
              <p style="text-align:center;">Click album to view all images</p>
              <div id="album-gallery" style="display:flex;gap:15px;flex-wrap:wrap;justify-content:center;max-height:400px;overflow-y:auto;">
                ${albumsData.map((album) => `
                  <div class="album-card" style="background:rgba(255,255,255,0.15);border-radius:16px;overflow:hidden;cursor:pointer;text-align:center;color:#fff;">
                    <img src="${album.images[0]}" style="width:150px;height:150px;object-fit:cover;">
                    <div style="padding:8px;">${album.title}</div>
                  </div>
                `).join("")}
              </div>
            </div>
          `,
          showConfirmButton: false,
          background: "rgba(0,0,0,0.85)",
          didOpen: () => {
            const albumCards = document.querySelectorAll(".album-card");
            albumCards.forEach((card, albumIndex) => {
              card.addEventListener("mouseover", () => (card.style.transform = "scale(1.05)"));
              card.addEventListener("mouseout", () => (card.style.transform = "scale(1)"));
              card.addEventListener("click", () => {
                Swal.fire({
                  title: albumsData[albumIndex].title,
                  html: albumsData[albumIndex].images.map((img) => `<img src="${img}" style="width:100px;height:100px;object-fit:cover;margin:5px;border-radius:8px;">`).join(""),
                  showConfirmButton: true,
                  background: "rgba(0,0,0,0.9)",
                  color: "#fff",
                });
              });
            });
          },
        });
      }
    });
  });

  // ----------------------------
  // Help Desk Popup
  // ----------------------------
  const helpDeskLink = document.getElementById("helpDeskLink");
  const helpDeskPopup = document.getElementById("helpDeskPopup");
  const closeHelpDesk = document.getElementById("closeHelpDesk");
  const sendBtn = document.getElementById("helpDeskSend");
  const messageInput = document.getElementById("helpDeskMessage");
  const messagesContainer = document.querySelector("#helpDeskPopup .messages");

  function loadMessages() {
    const saved = JSON.parse(localStorage.getItem("helpDeskMessages")) || [];
    messagesContainer.innerHTML = "";
    saved.forEach((msg) => addMessage(msg.text, msg.sender, false));
  }

  function saveMessages() {
    const allMessages = [...messagesContainer.querySelectorAll(".message")].map(
      (m) => ({ text: m.textContent, sender: m.classList.contains("user") ? "user" : "admin" })
    );
    localStorage.setItem("helpDeskMessages", JSON.stringify(allMessages));
  }

  function addMessage(text, sender, save = true) {
    const msgDiv = document.createElement("div");
    msgDiv.classList.add("message", sender);
    msgDiv.textContent = text;
    messagesContainer.appendChild(msgDiv);
    messagesContainer.scrollTop = messagesContainer.scrollHeight;
    if (save) saveMessages();
  }

  if (helpDeskLink && helpDeskPopup) {
    helpDeskLink.addEventListener("click", (e) => {
      e.preventDefault();
      helpDeskPopup.style.display = "flex";
      loadMessages();
    });
  }

  if (closeHelpDesk && helpDeskPopup) {
    closeHelpDesk.addEventListener("click", () => {
      helpDeskPopup.style.display = "none"; 
    });
  }

  if (sendBtn && messageInput) {
    sendBtn.addEventListener("click", () => {
      const message = messageInput.value.trim();
      if (!message) { Swal.fire("Empty!", "Please type a message.", "warning"); return; }
      addMessage(message, "user");
      setTimeout(() => { addMessage("Got it! Thanks for messaging us. 😊", "admin"); }, 1000);
      messageInput.value = "";
    });

    messageInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter" && !e.shiftKey) { e.preventDefault(); sendBtn.click(); }
    });
  }

  loadMessages();
});
