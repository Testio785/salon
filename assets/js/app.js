const user = window.__USER__;
const servicesGrid = document.getElementById('servicesGrid');
const mastersGrid = document.getElementById('mastersGrid');
const serviceSelect = document.getElementById('serviceSelect');
const masterSelect = document.getElementById('masterSelect');
const datetimeInput = document.getElementById('datetimeInput');
const bookingForm = document.getElementById('bookingForm');
const bookingMessage = document.getElementById('bookingMessage');
const appointmentsList = document.getElementById('appointmentsList');
const bookBtn = document.getElementById('bookBtn');
const logoutBtn = document.getElementById('logoutBtn');
const openLogin = document.getElementById('openLogin');
const openRegister = document.getElementById('openRegister');
const authModal = document.getElementById('authModal');
const closeModal = document.getElementById('closeModal');
const tabs = document.querySelectorAll('.tab');
const panels = document.querySelectorAll('.tab-panel');
const loginForm = document.getElementById('loginForm');
const registerForm = document.getElementById('registerForm');
const loginMessage = document.getElementById('loginMessage');
const registerMessage = document.getElementById('registerMessage');
const slotHint = document.getElementById('slotHint');

async function loadServices() {
  const res = await fetch('/api/services.php');
  const data = await res.json();
  serviceSelect.innerHTML = data.services.map(s => `<option value="${s.id}">${s.name} — ${s.price} ₽ (${s.duration_minutes} мин)</option>`).join('');
  servicesGrid.innerHTML = data.services.map(s => `
    <div class="card service-card">
      <img src="${s.photo_url}" alt="${s.name}">
      <h3>${s.name}</h3>
      <p class="muted">${s.description}</p>
      <div class="service-meta"><span>${s.duration_minutes} мин</span><strong>${s.price} ₽</strong></div>
    </div>`).join('');
}

async function loadMasters() {
  const res = await fetch('/api/masters.php');
  const data = await res.json();
  masterSelect.innerHTML = data.masters.map(m => `<option value="${m.id}">${m.name} — ${m.specialty}</option>`).join('');
  mastersGrid.innerHTML = data.masters.map(m => `
    <div class="card master-card">
      <img src="${m.photo_url}" alt="${m.name}">
      <h3>${m.name}</h3>
      <div class="role">${m.specialty}</div>
      <p class="bio">${m.bio}</p>
    </div>`).join('');
}

async function loadAppointments() {
  if (!appointmentsList) return;
  const res = await fetch('/api/appointments.php');
  if (res.status === 401) {
    appointmentsList.innerHTML = '<div class="muted">Авторизуйтесь, чтобы видеть записи.</div>';
    return;
  }
  const data = await res.json();
  if (!data.appointments || data.appointments.length === 0) {
    appointmentsList.innerHTML = '<div class="muted">Записей пока нет.</div>';
    return;
  }
  appointmentsList.innerHTML = data.appointments.map(a => `
    <div class="item">
      <div>
        <div><strong>${a.service_name || ''}</strong> → ${a.master_name || ''}</div>
        <div class="muted">${new Date(a.appointment_date).toLocaleString('ru-RU')}</div>
      </div>
      <span class="badge ${a.status === 'booked' ? 'success' : a.status === 'cancelled' ? 'danger' : 'warning'}">${a.status}</span>
    </div>`).join('');
}

async function checkAvailability() {
  const masterId = masterSelect.value;
  const dateStr = datetimeInput.value.split('T')[0];
  if (!masterId || !dateStr) return;
  const res = await fetch(`/api/appointments.php?master_id=${masterId}&date=${dateStr}`);
  const data = await res.json();
  if (!Array.isArray(data.booked)) return;
  slotHint.textContent = data.booked.length ? `Занятые слоты: ${data.booked.join(', ')}` : 'Все слоты свободны';
}

bookingForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  if (!user) {
    openAuthModal('register');
    bookingMessage.textContent = 'Авторизуйтесь, чтобы записаться';
    bookingMessage.style.color = '#ff8686';
    return;
  }
  bookBtn.disabled = true;
  bookingMessage.textContent = 'Создаем запись...';
  const formData = new FormData(bookingForm);
  const res = await fetch('/api/appointments.php', { method: 'POST', body: formData });
  const data = await res.json();
  bookBtn.disabled = false;
  if (res.ok) {
    bookingMessage.textContent = 'Запись подтверждена';
    bookingMessage.style.color = '#6ec9f5';
    bookingForm.reset();
    loadAppointments();
  } else {
    bookingMessage.textContent = data.error || 'Ошибка';
    bookingMessage.style.color = '#ff8686';
  }
});

datetimeInput?.addEventListener('change', checkAvailability);
masterSelect?.addEventListener('change', checkAvailability);

// Modal logic
function openAuthModal(tab = 'login') {
  authModal.classList.add('open');
  tabs.forEach(btn => btn.classList.toggle('active', btn.dataset.tab === tab));
  panels.forEach(panel => panel.classList.toggle('hidden', panel.dataset.panel !== tab));
}
function closeAuthModal() { authModal.classList.remove('open'); }
openLogin?.addEventListener('click', () => openAuthModal('login'));
openRegister?.addEventListener('click', () => openAuthModal('register'));
closeModal?.addEventListener('click', closeAuthModal);
tabs.forEach(btn => btn.addEventListener('click', () => openAuthModal(btn.dataset.tab)));

authModal?.addEventListener('click', (e) => {
  if (e.target === authModal) closeAuthModal();
});

loginForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await fetch('/api/login.php', { method: 'POST', body: new FormData(loginForm) });
  const data = await res.json();
  if (res.ok) {
    loginMessage.textContent = 'Успешный вход';
    location.reload();
  } else {
    loginMessage.textContent = data.error || 'Ошибка';
  }
});

registerForm?.addEventListener('submit', async (e) => {
  e.preventDefault();
  const res = await fetch('/api/register.php', { method: 'POST', body: new FormData(registerForm) });
  const data = await res.json();
  if (res.ok) {
    registerMessage.textContent = 'Аккаунт создан';
    location.reload();
  } else {
    registerMessage.textContent = data.error || 'Ошибка';
  }
});

logoutBtn?.addEventListener('click', async () => {
  await fetch('/api/logout.php');
  location.reload();
});

loadServices();
loadMasters();
loadAppointments();
