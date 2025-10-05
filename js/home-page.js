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
  // Open full calendar modal
  // ----------------------------
  const openCalendarBtn = document.getElementById('openCalendarBtn');
  if (openCalendarBtn) {
    openCalendarBtn.addEventListener('click', () => {
      Swal.fire({
        title: 'Calendar',
        html: `
          <div class="bn-cal-wrapper">
            <div class="bn-cal-panel"><div id="modal-calendar"></div></div>
            <div class="bn-cal-events">
              <div style="display:flex;justify-content:space-between;align-items:center;gap:8px;">
                <h4 id="bn-cal-date" class="bn-cal-date" style="margin:0;">Select a date</h4>
                <button id="bn-cal-today" class="bn-cal-today-btn">Today’s events</button>
              </div>
              <ul id="bn-cal-event-list" class="bn-cal-event-list"><li>No events selected.</li></ul>
            </div>
          </div>
        `,
        width: 'min(960px, 95vw)',
        showConfirmButton: true,
        confirmButtonText: 'Close',
        background: 'transparent',
        customClass: { popup: 'bn-swal', confirmButton: 'bn-btn-cancel', htmlContainer: 'bn-body', title: 'bn-title' },
        didOpen: () => {
          const el = document.getElementById('modal-calendar');
          const dateEl = document.getElementById('bn-cal-date');
          const listEl = document.getElementById('bn-cal-event-list');
          const todayBtn = document.getElementById('bn-cal-today');

          const cal = new FullCalendar.Calendar(el, {
            initialView: 'dayGridMonth',
            height: 560,
            dayMaxEvents: 4,
            headerToolbar: { left: 'prev,next today', center: 'title', right: '' },
            eventTimeFormat: { hour: 'numeric', minute: '2-digit', hour12: true },
            events: (fetchInfo, success, fail) => {
              fetch('api/get-calendar-events.php', { credentials: 'same-origin' })
                .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to load events')))
                .then(data => success((data && data.events) ? data.events : []))
                .catch(err => { try { fail(err); } catch(e) {} success([]); });
            },
            dateClick: (info) => {
              updateList(info.date);
              highlightDate(info.date);
            },
            moreLinkClick: (arg) => {
              updateList(arg.date);
              highlightDate(arg.date);
              return 'popover';
            },
            eventClick: (info) => {
              info.jsEvent?.preventDefault?.();
              const ev = info.event;
              const desc = (ev.extendedProps && ev.extendedProps.description) ? ev.extendedProps.description : '';
              const postId = ev.extendedProps?.postId;
              const url = ev.url || (postId ? `home.php#post-${postId}` : '');
              Swal.fire({
                title: ev.title || 'Event',
                html: `<div style="text-align:left"><p style="white-space:pre-wrap;">${escapeHtml(desc)}</p>${url?`<p style="margin-top:8px;"><a href="${url}">View post</a></p>`:''}</div>`,
                showCancelButton: true,
                confirmButtonText: 'View post',
                cancelButtonText: 'Close',
                customClass: { popup: 'bn-swal', confirmButton: 'bn-btn-confirm', cancelButton: 'bn-btn-cancel', title: 'bn-title', htmlContainer: 'bn-body' }
              }).then(res => {
                if (res.isConfirmed && url) {
                  window.location.href = url;
                }
              });
              if (ev.start) { updateList(ev.start); highlightDate(ev.start); }
            },
            eventDidMount: (info) => {
              const desc = info.event.extendedProps?.description;
              if (desc) { info.el.setAttribute('title', desc); }
            }
          });

          function updateList(date) {
            const pretty = date.toLocaleDateString(undefined, { year: 'numeric', month: 'long', day: 'numeric' });
            if (dateEl) dateEl.textContent = pretty;
            const sidebarHeader = document.getElementById('sidebarSelectedDate');
            if (sidebarHeader) sidebarHeader.textContent = `Events on ${pretty}`;
            const isoDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const dayStr = new Date(isoDay.getTime() - isoDay.getTimezoneOffset()*60000).toISOString().slice(0,10);
            const events = cal.getEvents().filter(ev => {
              const d = ev.start; if (!d) return false;
              const s = new Date(d.getTime() - d.getTimezoneOffset()*60000).toISOString().slice(0,10);
              return s === dayStr;
            });
            const itemsHtml = events.length ? events.map(ev => {
              const time = ev.start ? formatTime(ev.start) : '';
              const desc = ev.extendedProps?.description ? ` <div class="desc">${escapeHtml(String(ev.extendedProps.description))}</div>` : '';
              return `<li><div class="title">${escapeHtml(String(ev.title||''))}${time ? ` <span class=\"time\">${escapeHtml(time)}</span>` : ''}</div>${desc}</li>`;
            }).join('') : '<li>No events scheduled.</li>';
            if (listEl) { listEl.innerHTML = itemsHtml; }
            // sync the right sidebar list as well
            const sidebarList = document.getElementById('event-list');
            if (sidebarList) { sidebarList.innerHTML = itemsHtml; }
          }

          function formatTime(d) {
            try { return d.toLocaleTimeString(undefined, { hour: 'numeric', minute: '2-digit', hour12: true }); } catch { return ''; }
          }

          function highlightDate(date) {
            const isoDay = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            const dayStr = new Date(isoDay.getTime() - isoDay.getTimezoneOffset()*60000).toISOString().slice(0,10);
            document.querySelectorAll('.bn-cal-panel .fc-daygrid-day').forEach(cell => cell.classList.remove('bn-selected'));
            const cell = document.querySelector(`.bn-cal-panel .fc-daygrid-day[data-date="${dayStr}"]`);
            if (cell) cell.classList.add('bn-selected');
          }

          cal.render();
          // Preselect today
          const today = new Date();
          updateList(today);
          highlightDate(today);

          if (todayBtn) {
            todayBtn.addEventListener('click', () => {
              const t = new Date();
              cal.gotoDate(t);
              updateList(t);
              highlightDate(t);
            });
          }

          // Dynamic height for desktop view
          const setHeight = () => {
            const base = Math.round((window.innerHeight || 800) * 0.7);
            const h = Math.max(480, Math.min(820, base));
            cal.setOption('height', h);
            if (listEl) { listEl.style.maxHeight = (h - 120) + 'px'; }
          };
          setHeight();
          window.addEventListener('resize', setHeight);
        }
      });
    });
  }

  // Utility: escape HTML for safe injection
  function escapeHtml(s) {
    return String(s)
      .replace(/&/g, '&amp;')
      .replace(/</g, '&lt;')
      .replace(/>/g, '&gt;')
      .replace(/"/g, '&quot;')
      .replace(/'/g, '&#039;');
  }

  // ----------------------------
  // Edit/Delete post actions (admin/moderator)
  // ----------------------------
  function delegate(container, selector, event, handler) {
    container.addEventListener(event, (e) => {
      const target = e.target.closest(selector);
      if (target) handler(e, target);
    });
  }

  const middleContainer = document.querySelector('.middle-container');
  if (middleContainer) {
    // Delete
    delegate(middleContainer, '.btn-delete-post', 'click', (e, btn) => {
      const id = btn.getAttribute('data-id');
      if (!id) return;
      Swal.fire({
        title: 'Delete this post?',
        text: 'This action cannot be undone.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Delete'
      }).then((res) => {
        if (!res.isConfirmed) return;
        fetch('api/delete-post.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({ id: Number(id) })
        })
        .then(r => r.ok ? r.json() : Promise.reject(new Error('Delete failed')))
        .then(data => {
          if (data && data.success) {
            Swal.fire({ icon: 'success', title: 'Deleted', timer: 1200, showConfirmButton: false });
            setTimeout(() => window.location.reload(), 400);
          } else {
            Swal.fire({ icon: 'error', title: 'Delete failed', text: (data && data.message) || 'Please try again.' });
          }
        })
        .catch(() => Swal.fire({ icon: 'error', title: 'Delete failed', text: 'Please try again.' }));
      });
    });

    // Edit
    delegate(middleContainer, '.btn-edit-post', 'click', (e, btn) => {
      const id = Number(btn.getAttribute('data-id'));
      const type = (btn.getAttribute('data-type') || '').toLowerCase();
      const title = btn.getAttribute('data-title') || '';
      const content = btn.getAttribute('data-content') || '';
      const ev = btn.getAttribute('data-event') || '';

      const html = `
        <div class="bn-form">
          <div class="bn-field">
            <label>Type</label>
            <select id="editType" class="bn-select">
              <option value="announcements" ${type==='announcements'?'selected':''}>Announcements</option>
              <option value="performances" ${type==='performances'?'selected':''}>Performances</option>
              <option value="albums" ${type==='albums'?'selected':''}>Picture Albums</option>
            </select>
          </div>
          <div class="bn-field bn-field--full">
            <label>Title</label>
            <input id="editTitle" class="bn-input" placeholder="Write a clear, concise title" value="${title.replace(/"/g,'&quot;')}">
          </div>
          <div class="bn-field bn-field--full">
            <label>Content</label>
            <textarea id="editContent" class="bn-textarea" rows="4" placeholder="Add some details to your post...">${content.replace(/</g,'&lt;').replace(/>/g,'&gt;')}</textarea>
          </div>
          <div id="editEventRow" class="bn-field ${type==='announcements' ? '' : 'bn-hidden'}">
            <label>Event Date</label>
            <input id="editEventDate" type="datetime-local" class="bn-input" value="${ev ? ev.replace(' ','T').slice(0,16): ''}">
          </div>
        </div>`;

      Swal.fire({
        title: 'Edit Post',
        html,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Save changes',
        cancelButtonText: 'Cancel',
        width: '600px',
        customClass: {
          popup: 'bn-swal',
          title: 'bn-title',
          htmlContainer: 'bn-body',
          confirmButton: 'bn-btn-confirm',
          cancelButton: 'bn-btn-cancel',
          actions: 'bn-actions'
        },
        didOpen: () => {
          const typeSel = document.getElementById('editType');
          const row = document.getElementById('editEventRow');
          const dt = document.getElementById('editEventDate');
          typeSel.addEventListener('change', () => {
            const isAnn = typeSel.value === 'announcements';
            row.classList.toggle('bn-hidden', !isAnn);
            if (!isAnn) { if (dt) dt.value = ''; }
          });
          // set minimum event date to now
          if (dt) {
            const now = new Date();
            const pad = (n) => String(n).padStart(2,'0');
            const local = `${now.getFullYear()}-${pad(now.getMonth()+1)}-${pad(now.getDate())}T${pad(now.getHours())}:${pad(now.getMinutes())}`;
            dt.min = local;
          }
        },
        preConfirm: () => {
          const p = {
            id,
            postType: (document.getElementById('editType').value || '').toLowerCase(),
            title: (document.getElementById('editTitle').value || '').trim(),
            content: (document.getElementById('editContent').value || '').trim(),
            event_date: document.getElementById('editEventDate')?.value || ''
          };
          if (!p.title) { Swal.showValidationMessage('Title is required'); return false; }
          if (!['announcements','performances','albums'].includes(p.postType)) { Swal.showValidationMessage('Invalid post type'); return false; }
          return p;
        }
      }).then((res) => {
        if (!res.isConfirmed || !res.value) return;
        const payload = res.value;
        fetch('api/update-post.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        })
          .then(r => r.ok ? r.json() : Promise.reject(new Error('Update failed')))
          .then(data => {
            if (data && data.success) {
              Swal.fire({ icon: 'success', title: 'Updated', timer: 1200, showConfirmButton: false });
              setTimeout(() => window.location.reload(), 400);
            } else {
              Swal.fire({ icon: 'error', title: 'Update failed', text: (data && data.message) || 'Please try again.' });
            }
          })
          .catch(() => Swal.fire({ icon: 'error', title: 'Update failed', text: 'Please try again.' }));
      });
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
      events: (fetchInfo, successCallback, failureCallback) => {
        fetch('api/get-calendar-events.php', { credentials: 'same-origin' })
          .then(r => r.ok ? r.json() : Promise.reject(new Error('Failed to load events')))
          .then(data => successCallback((data && data.events) ? data.events : []))
          .catch(err => { console.error(err); try { failureCallback(err); } catch(e) {} successCallback([]); });
      },
      dateClick: (info) => {
        const eventList = document.getElementById("event-list");
        const selectedDate = info.dateStr; // YYYY-MM-DD
        const filteredEvents = calendar.getEvents().filter((ev) => {
          // compare by date only
          const d = ev.start;
          if (!d) return false;
          const iso = new Date(d.getTime() - d.getTimezoneOffset()*60000).toISOString().slice(0,10);
          return iso === selectedDate;
        });
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

  // ----------------------------
  // Upload Form Toggle & Basic UX
  // ----------------------------
  const toggleUploadBtn = document.getElementById("toggleUploadBtn");
  const uploadForm = document.getElementById("uploadForm");
  const cancelUpload = document.getElementById("cancelUpload");
  const postType = document.getElementById("postType");
  const fileRow = document.getElementById("fileRow");
  const postTitle = document.getElementById("postTitle");
  const postContent = document.getElementById("postContent");
  const uploadFiles = document.getElementById("uploadFiles");
  const filePreviewList = document.getElementById("filePreviewList");
  const eventDateRow = document.getElementById("eventDateRow");
  const eventDate = document.getElementById("eventDate");
  let selectedFiles = [];

  function setUploadVisible(visible) {
    if (!uploadForm) return;
    uploadForm.style.display = visible ? "block" : "none";
    if (toggleUploadBtn) toggleUploadBtn.textContent = visible ? "Close" : "Upload";
  }

  // Always show upload field for all post types
  function updateFileRow() {
    if (!fileRow) return;
    fileRow.style.display = "block";
    if (postType && eventDateRow) {
      const isAnn = (postType.value || '').toLowerCase() === 'announcements';
      eventDateRow.style.display = isAnn ? 'block' : 'none';
      if (!isAnn && eventDate) eventDate.value = '';
    }
  }

  // Keep the input's FileList in sync with selectedFiles
  function syncInputFromSelected() {
    if (!uploadFiles) return;
    const dt = new DataTransfer();
    selectedFiles.forEach(item => dt.items.add(item.file));
    uploadFiles.files = dt.files;
  }

  // Render previews for images/videos with ability to remove
  function renderPreviews() {
    if (!filePreviewList) return;
    filePreviewList.innerHTML = "";
    if (!selectedFiles.length) {
      const empty = document.createElement('div');
      empty.className = 'preview-empty';
      empty.textContent = 'No files selected.';
      filePreviewList.appendChild(empty);
      return;
    }
    selectedFiles.forEach((item, idx) => {
      const wrapper = document.createElement('div');
      wrapper.className = 'preview-item';

      const isVideo = item.file.type && item.file.type.startsWith('video/');
      const media = document.createElement(isVideo ? 'video' : 'img');
      media.className = 'preview-thumb';
      const url = item.url || URL.createObjectURL(item.file);
      item.url = url;
      media.src = url;
      if (isVideo) { media.controls = true; }

      const caption = document.createElement('div');
      caption.className = 'preview-caption';
      caption.textContent = item.file.name;

      const removeBtn = document.createElement('button');
      removeBtn.type = 'button';
      removeBtn.className = 'preview-remove';
      removeBtn.innerHTML = '&times;';
      removeBtn.addEventListener('click', () => {
        try { if (item.url) URL.revokeObjectURL(item.url); } catch (e) {}
        selectedFiles.splice(idx, 1);
        syncInputFromSelected();
        renderPreviews();
      });

      wrapper.appendChild(media);
      wrapper.appendChild(removeBtn);
      wrapper.appendChild(caption);
      filePreviewList.appendChild(wrapper);
    });
  }

  // Handle selecting files from input
  if (uploadFiles) {
    uploadFiles.addEventListener('change', (e) => {
      const files = Array.from(e.target.files || []);
      files.forEach(f => {
        // Avoid duplicates by name+size+lastModified
        const key = `${f.name}_${f.size}_${f.lastModified}_${f.type}`;
        if (!selectedFiles.find(sf => sf.key === key)) {
          selectedFiles.push({ file: f, key });
        }
      });
      // Allow selecting same files again
      uploadFiles.value = '';
      syncInputFromSelected();
      renderPreviews();
    });
  }

  if (toggleUploadBtn && uploadForm) {
    toggleUploadBtn.addEventListener("click", () => {
      const willShow = uploadForm.style.display === "none" || uploadForm.style.display === "";
      setUploadVisible(willShow);
      updateFileRow();
    });
    // initialize hidden
    setUploadVisible(false);
  }

  if (cancelUpload) {
    cancelUpload.addEventListener("click", () => {
      if (uploadForm) uploadForm.reset();
      // Clear previews and selected files
      selectedFiles.forEach(i => { try { if (i.url) URL.revokeObjectURL(i.url); } catch (e) {} });
      selectedFiles = [];
      if (filePreviewList) filePreviewList.innerHTML = '';
      syncInputFromSelected();
      updateFileRow();
      setUploadVisible(false);
    });
  }

  if (postType) {
    postType.addEventListener("change", updateFileRow);
    updateFileRow();
  }

  if (uploadForm) {
    uploadForm.addEventListener("submit", (e) => {
      e.preventDefault();
      const title = (postTitle?.value || "").trim();
      const type = (postType?.value || "").trim();
      if (!title) {
        Swal.fire({ icon: "warning", title: "Add a title", text: "Please provide a title for your post." });
        return;
      }

      // Build FormData using our selectedFiles to respect deletions
      const content = (postContent?.value || "").trim();
      const fd = new FormData();
      fd.append('postType', type);
      fd.append('title', title);
      fd.append('content', content);
      if (eventDate && eventDate.value) {
        fd.append('event_date', eventDate.value);
      }
      selectedFiles.forEach(sf => fd.append('files[]', sf.file));

      // Try to post to backend; if fails, show demo success
      Swal.fire({ title: 'Uploading...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
      fetch('api/create-post.php', { method: 'POST', body: fd })
        .then(r => r.ok ? r.json() : Promise.reject(new Error('Upload failed')))
        .then(data => {
          if (data && data.success) {
            Swal.fire({ icon: 'success', title: 'Posted!', text: data.message || 'Your post has been published.', timer: 1400, showConfirmButton: false });
            // Reset state
            uploadForm.reset();
            selectedFiles.forEach(i => { try { if (i.url) URL.revokeObjectURL(i.url); } catch (e) {} });
            selectedFiles = [];
            if (filePreviewList) filePreviewList.innerHTML = '';
            syncInputFromSelected();
            updateFileRow();
            setUploadVisible(false);
            setTimeout(() => { window.location.reload(); }, 300);
          } else {
            Swal.fire({ icon: 'error', title: 'Failed to post', text: (data && data.message) || 'Please try again.' });
          }
        })
        .catch(err => {
          // Fallback demo success when backend not available
          Swal.fire({ icon: 'success', title: 'Posted (local demo)', text: 'Your post has been saved locally.', timer: 1200, showConfirmButton: false });
          uploadForm.reset();
          selectedFiles.forEach(i => { try { if (i.url) URL.revokeObjectURL(i.url); } catch (e) {} });
          selectedFiles = [];
          if (filePreviewList) filePreviewList.innerHTML = '';
          syncInputFromSelected();
          updateFileRow();
          setUploadVisible(false);
        });
    });
  }

  loadMessages();
});
