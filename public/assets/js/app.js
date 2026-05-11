const roleSelect = document.getElementById('roleSelect');

function syncRegisterFields() {
  if (!roleSelect) return;
  const isProvider = roleSelect.value === 'provider';
  document.querySelectorAll('.provider-field').forEach((el) => el.style.display = isProvider ? 'block' : 'none');
  document.querySelectorAll('.student-fields').forEach((el) => el.style.display = isProvider ? 'none' : 'grid');
}

if (roleSelect) {
  roleSelect.addEventListener('change', syncRegisterFields);
  syncRegisterFields();
}

const userMenuButton = document.getElementById('userMenuButton');
const userDropdown = document.getElementById('userDropdown');

if (userMenuButton && userDropdown) {
  userMenuButton.addEventListener('click', () => {
    const isOpen = userDropdown.classList.toggle('open');
    userMenuButton.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
  });

  document.addEventListener('click', (event) => {
    if (!userMenuButton.contains(event.target) && !userDropdown.contains(event.target)) {
      userDropdown.classList.remove('open');
      userMenuButton.setAttribute('aria-expanded', 'false');
    }
  });
}

document.querySelectorAll('.placeholder').forEach((link) => {
  link.addEventListener('click', (event) => {
    const message = link.dataset.placeholder;
    if (!message) return;
    event.preventDefault();
    alert(message);
  });
});
