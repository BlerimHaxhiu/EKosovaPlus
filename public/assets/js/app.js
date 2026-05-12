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

function showOptionalSection(targetId, button) {
  const target = document.getElementById(targetId);
  if (!target) return;
  target.classList.remove('is-hidden');
  if (button) button.classList.add('is-hidden');
}

document.addEventListener('click', (event) => {
  const button = event.target.closest('.optional-section-toggle');
  if (!button) return;
  event.preventDefault();
  showOptionalSection(button.dataset.target, button);
});

document.addEventListener('click', (event) => {
  const button = event.target.closest('.add-study-entry');
  if (!button) return;
  event.preventDefault();

  const template = document.getElementById(button.dataset.template);
  const target = document.getElementById(button.dataset.target);
  if (!template || !target) return;

  const index = `${Date.now()}${target.children.length}`;
  target.insertAdjacentHTML('beforeend', template.innerHTML.replaceAll('__INDEX__', index));
});

document.addEventListener('click', (event) => {
  const button = event.target.closest('.remove-study-entry');
  if (!button) return;
  event.preventDefault();

  const entry = button.closest('.study-entry');
  if (entry) entry.remove();
});

document.addEventListener('submit', (event) => {
  const form = event.target.closest('form');
  if (!form) return;

  const startInputs = form.querySelectorAll('input[name$="[start_year]"]');
  for (const startInput of startInputs) {
    const endName = startInput.name.replace(/\[start_year\]$/, '[end_year]');
    const endInput = Array.from(form.querySelectorAll('input[name$="[end_year]"]'))
      .find((input) => input.name === endName);
    startInput.setCustomValidity('');
    if (endInput) endInput.setCustomValidity('');

    if (!endInput || startInput.value === '' || endInput.value === '') continue;

    if (Number(startInput.value) > Number(endInput.value)) {
      const message = document.body.dataset.invalidYearRange || 'invalid_year_range';
      startInput.setCustomValidity(message);
      endInput.setCustomValidity(message);
      event.preventDefault();
      startInput.reportValidity();
      break;
    }
  }
});

document.querySelectorAll('.card-number-input').forEach((input) => {
  input.addEventListener('input', () => {
    const digits = input.value.replace(/\D/g, '').slice(0, 16);
    input.value = digits.replace(/(.{4})/g, '$1 ').trim();
  });
});

document.querySelectorAll('.card-expiry-input').forEach((input) => {
  input.addEventListener('input', () => {
    const digits = input.value.replace(/\D/g, '').slice(0, 4);
    input.value = digits.length > 2 ? `${digits.slice(0, 2)}/${digits.slice(2)}` : digits;
  });
});

document.querySelectorAll('.cvv-input').forEach((input) => {
  input.addEventListener('input', () => {
    input.value = input.value.replace(/\D/g, '').slice(0, 3);
  });
});

const helpFileInput = document.getElementById('helpFileInput');
const helpFileName = document.getElementById('helpFileName');

if (helpFileInput && helpFileName) {
  helpFileInput.addEventListener('change', () => {
    helpFileName.textContent = helpFileInput.files[0]?.name || helpFileName.dataset.empty || 'not_filled';
  });
}

const studyFieldOptions = {
  'Gjimnaz': ['Shkenca Natyrore', 'Shkenca Shoqerore', 'Matematikor', 'Gjuhesor'],
  'Shkolla e mesme teknike': ['Automekanike', 'Elektromekanike', 'Hidromekanike', 'MolerGipser'],
  'Shkolla e mesme profesionale': ['Komunikacion', 'Kriminalistike'],
};

function syncStudyField(select) {
  const target = document.getElementById(select.dataset.studyTarget);
  if (!target) return;

  const fieldSelect = target.querySelector('.study-field-select');
  const options = studyFieldOptions[select.value] || [];
  const current = fieldSelect.dataset.current || fieldSelect.value;

  fieldSelect.innerHTML = `<option value="">${fieldSelect.dataset.placeholder || 'choose_field'}</option>`;
  options.forEach((option) => {
    const item = document.createElement('option');
    item.value = option;
    item.textContent = option;
    item.selected = option === current;
    fieldSelect.appendChild(item);
  });

  target.classList.toggle('is-hidden', options.length === 0);
  if (options.length === 0) fieldSelect.value = '';
}

document.querySelectorAll('.secondary-school-kind').forEach((select) => {
  select.addEventListener('change', () => {
    const target = document.getElementById(select.dataset.studyTarget);
    const fieldSelect = target ? target.querySelector('.study-field-select') : null;
    if (fieldSelect) fieldSelect.dataset.current = '';
    syncStudyField(select);
  });
  syncStudyField(select);
});

document.querySelectorAll('.section-edit-button').forEach((button) => {
  button.addEventListener('click', () => {
    const section = button.closest('.profile-collapsible-section');
    if (!section) return;
    section.classList.add('is-editing');
  });
});

document.querySelectorAll('.cancel-section-edit').forEach((button) => {
  button.addEventListener('click', (event) => {
    const section = button.closest('.profile-collapsible-section');
    if (!section) return;
    event.preventDefault();
    section.classList.remove('is-editing');
  });
});
