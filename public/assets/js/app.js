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

const popupFlash = document.querySelector('[data-popup-message]');
if (popupFlash) {
  alert(popupFlash.dataset.popupMessage);
}

document.querySelectorAll('.delayed-application-result').forEach((card) => {
  setTimeout(() => {
    card.classList.add('is-visible');
    card.scrollIntoView({ behavior: 'smooth', block: 'center' });
  }, 3000);
});

document.querySelectorAll('.scholarship-apply-form').forEach((form) => {
  form.addEventListener('submit', (event) => {
    if (form.dataset.readyToSubmit === '1') return;
    event.preventDefault();

    const modal = document.getElementById('applicationModal');
    const title = document.getElementById('applicationModalTitle');
    const text = document.getElementById('applicationModalText');
    const button = form.querySelector('button');

    if (button) button.disabled = true;
    if (modal) {
      modal.classList.add('is-visible');
      modal.setAttribute('aria-hidden', 'false');
    }
    if (title) title.textContent = 'Duke verifikuar tÃ« dhÃ«nat e profilit tuaj...';
    if (text) text.textContent = form.dataset.scholarshipTitle || 'Ju lutemi prisni pak.';

    setTimeout(() => {
      if (title) title.textContent = 'Urime! Ju keni fituar bursÃ«n.';
      if (text) text.textContent = 'Raporti i aplikimit po pÃ«rgatitet.';
      setTimeout(() => {
        form.dataset.readyToSubmit = '1';
        form.submit();
      }, 900);
    }, 3000);
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

const adminScholarshipForm = document.getElementById('adminScholarshipForm');

if (adminScholarshipForm) {
  const templates = JSON.parse(adminScholarshipForm.dataset.templates || '[]');
  const categorySelect = document.getElementById('scholarshipCategory');
  const templateProviderSelect = document.getElementById('templateProviderSelect');
  const templateProviderWrap = document.getElementById('templateProviderWrap');
  const ojqProviderWrap = document.getElementById('ojqProviderWrap');
  const providerSelect = document.getElementById('providerSelect');
  const templateProviderIdSelect = document.getElementById('templateProviderIdSelect');
  const templateProviderIdWrap = document.getElementById('templateProviderIdWrap');
  const providerNameInput = document.getElementById('scholarshipProviderName');
  const templateIdInput = document.getElementById('scholarshipTemplateId');
  const loadTemplateButton = document.getElementById('loadScholarshipTemplate');
  const providerAliases = {
    'Universiteti i Prishtines': 'Universiteti Hasan Prishtina',
  };

  function syncProviderMode() {
    const isOjQ = categorySelect.value === 'Burse humanitare nga OJQ';
    ojqProviderWrap.classList.toggle('is-hidden', !isOjQ);
    templateProviderWrap.classList.toggle('is-hidden', isOjQ);
    templateProviderIdWrap.classList.add('is-hidden');
    providerSelect.disabled = !isOjQ;
    templateProviderIdSelect.disabled = isOjQ;

    Array.from(providerSelect.options).forEach((option) => {
      option.hidden = isOjQ && option.dataset.providerType !== 'OJQ';
    });
    if (isOjQ) {
      const firstOjQ = Array.from(providerSelect.options).find((option) => option.dataset.providerType === 'OJQ');
      if (firstOjQ) providerSelect.value = firstOjQ.value;
      templateIdInput.value = '';
    }
  }

  function refreshTemplateProviders() {
    const category = categorySelect.value;
    templateProviderSelect.innerHTML = '<option value="">Zgjedh ofruesin</option>';
    templates
      .filter((template) => template.category === category)
      .forEach((template) => {
        const option = document.createElement('option');
        option.value = template.id;
        option.textContent = template.provider_name;
        templateProviderSelect.appendChild(option);
      });
    syncProviderMode();
  }

  function setProviderAccount(providerName) {
    const targetName = providerAliases[providerName] || providerName;
    const match = Array.from(templateProviderIdSelect.options)
      .find((option) => option.dataset.providerName === targetName || option.dataset.providerName === providerName);
    if (match) templateProviderIdSelect.value = match.value;
    providerNameInput.value = providerName;
  }

  function inputCell(name, value, type = 'text') {
    return `<input name="${name}" type="${type}" value="${escapeHtml(value ?? '')}">`;
  }

  function hiddenInput(name, value) {
    return `<input type="hidden" name="${name}" value="${escapeHtml(value ?? '')}">`;
  }

  function ruleDisplayText(rule) {
    const info = rule.display_info || {};
    const base = rule.description || info.human_description || info.label || rule.rule_key;
    const value = String(rule.rule_value || '').trim();
    const op = String(rule.operator || '').trim();
    if (rule.rule_key === 'residence_municipality' && value) return `Studenti duhet tÃ« jetÃ« banor i KomunÃ«s sÃ« ${value}.`;
    if (rule.rule_key === 'full_time') return 'Studenti duhet tÃ« jetÃ« student i rregullt.';
    if (rule.rule_key === 'student_active') return 'Studenti duhet tÃ« jetÃ« student aktiv.';
    if (rule.rule_key === 'repeating_year' && value === 'jo') return 'Studenti nuk duhet tÃ« jetÃ« pÃ«rsÃ«ritÃ«s i vitit.';
    if (rule.rule_key === 'study_level' && op === '!=') return `Niveli i studimeve nuk duhet tÃ« jetÃ« ${value}.`;
    if (rule.rule_key === 'average_grade' && op === 'between') return `Nota mesatare ${value.replace('-', ' deri ')}.`;
    if (rule.rule_key === 'average_grade' && op === '>=') return `Nota mesatare duhet tÃ« jetÃ« sÃ« paku ${value}.`;
    if (rule.rule_key === 'first_year_university') return `PÃ«r vitin e parÃ«, universiteti duhet tÃ« jetÃ« ${value}.`;
    if (rule.rule_key === 'public_university_after_first_year') return 'PÃ«r vitin e dytÃ« e tutje, universiteti duhet tÃ« jetÃ« publik.';
    if (rule.rule_key === 'family_students_count') return 'Dy ose mÃ« shumÃ« studentÃ« nÃ« familje.';
    if (['receives_social_assistance', 'social_assistance'].includes(rule.rule_key)) return 'Studenti Ã«shtÃ« pÃ«rfitues i ndihmÃ«s sociale.';
    if (['two_parents_missing', 'missing_both_parents'].includes(rule.rule_key)) return 'Studenti Ã«shtÃ« pa dy prindÃ«r.';
    if (['one_parent_missing', 'missing_one_parent'].includes(rule.rule_key)) return 'Studenti Ã«shtÃ« pa njÃ«rin prind.';
    if (['war_category', 'is_veteran_child', 'veteran_child', 'martyr_child'].includes(rule.rule_key)) return base.replace(/\.$/, '') + '.';
    return base.replace(/\.$/, '') + '.';
  }

  function renderRuleRows(rules) {
    const requiredBody = document.getElementById('requiredRulesBody');
    const optionalBody = document.getElementById('optionalRulesBody');
    requiredBody.innerHTML = '';
    optionalBody.innerHTML = '';
    const groups = new Map();

    rules.forEach((rule, index) => {
      const isRequired = Number(rule.is_required) === 1;
      const info = rule.display_info || {};
      const section = info.document_section || info.label || 'Profili i studentit';
      if (!groups.has(section)) {
        groups.set(section, { section, required: [], optional: [] });
      }
      groups.get(section)[isRequired ? 'required' : 'optional'].push({ rule, index });
    });

    groups.forEach((group) => {
      const card = document.createElement('article');
      const hasRequired = group.required.length > 0;
      const hasOptional = group.optional.length > 0;
      const totalOptionalPoints = group.optional.reduce((sum, item) => sum + Number(item.rule.points || 0), 0);
      const groupItems = [...group.required, ...group.optional];
      const statusValue = hasRequired ? 'required' : (totalOptionalPoints > 0 ? 'bonus' : 'optional');
      const previewId = `ruleStatusPreview${groupItems[0]?.index ?? 0}`;
      const ruleIndexes = groupItems.map(({ index }) => index).join(',');
      card.className = 'template-rule-card';
      card.innerHTML = `
        <div class="template-rule-card-main">
          <h4>${escapeHtml(group.section)}</h4>
          <div class="template-rule-badges">
            <span class="badge ${ruleStatusClass(statusValue)}" id="${previewId}">${ruleStatusLabel(statusValue)}</span>
            <span class="badge muted">${totalOptionalPoints > 0 ? `Deri nÃ« ${totalOptionalPoints} pikÃ«` : 'PikÃ«t: nuk aplikohet'}</span>
          </div>
          <label class="document-status-control">Ndrysho statusin e ketij dokumenti
            <select class="rule-status-select" data-rule-indexes="${escapeHtml(ruleIndexes)}" data-preview-target="${previewId}">
              <option value="required" ${statusValue === 'required' ? 'selected' : ''}>Obligativ</option>
              <option value="bonus" ${statusValue === 'bonus' ? 'selected' : ''}>Opsional / Pikezues</option>
              <option value="optional" ${statusValue === 'optional' ? 'selected' : ''}>Opsional</option>
            </select>
          </label>
        </div>
        ${hasRequired ? `
          <div class="template-rule-section">
            <strong>Kriteret obligative</strong>
            <ul>${group.required.map(({ rule }) => `<li>${escapeHtml(ruleDisplayText(rule))}</li>`).join('')}</ul>
          </div>` : ''}
        ${hasOptional ? `
          <div class="template-rule-section">
            <strong>Kriteret opsionale / pikÃ«zuese</strong>
            <ul>${group.optional.map(({ rule }) => `<li>${escapeHtml(ruleDisplayText(rule))}${Number(rule.points || 0) > 0 ? ` = ${Number(rule.points || 0)} pikÃ«` : ''}</li>`).join('')}</ul>
          </div>` : ''}
        ${[...group.required, ...group.optional].map(({ rule, index }) => `
          ${hiddenInput(`rules[${index}][rule_key]`, rule.rule_key)}
          ${hiddenInput(`rules[${index}][operator]`, rule.operator)}
          ${hiddenInput(`rules[${index}][rule_value]`, rule.rule_value)}
          <input type="hidden" id="rulePoints${index}" name="rules[${index}][points]" value="${escapeHtml(rule.points ?? 0)}" data-original-points="${escapeHtml(rule.points ?? 0)}">
          <input type="hidden" id="ruleRequired${index}" name="rules[${index}][is_required]" value="${Number(rule.is_required) === 1 ? '1' : '0'}">
          ${hiddenInput(`rules[${index}][description]`, rule.description)}
        `).join('')}
        <details class="technical-details">
          <summary>Shfaq detajet teknike</summary>
          ${[...group.required, ...group.optional].map(({ rule }) => `
            <dl>
              <dt>rule_key</dt><dd>${escapeHtml(rule.rule_key || '')}</dd>
              <dt>operator</dt><dd>${escapeHtml(rule.operator || '')}</dd>
              <dt>value</dt><dd>${escapeHtml(rule.rule_value || '')}</dd>
            </dl>
          `).join('')}
        </details>`;
      (hasRequired ? requiredBody : optionalBody).appendChild(card);
    });

    document.querySelectorAll('.rule-status-select').forEach((select) => {
      select.addEventListener('change', () => syncRuleGroupStatus(select));
      syncRuleGroupStatus(select);
    });
  }

  function renderDocumentRows(documents, rules = []) {
    const body = document.getElementById('templateDocumentsBody');
    body.innerHTML = '';
    const normalizedDocuments = [...documents];
    const existingSections = new Set(normalizedDocuments.map((item) => item.document_section_name));

    rules.forEach((rule) => {
      const section = rule.display_info?.document_section;
      if (!section || existingSections.has(section)) return;
      existingSections.add(section);
      normalizedDocuments.push({
        document_section_name: section,
        is_required: Number(rule.is_required) === 1 ? 1 : 0,
        is_optional_bonus: Number(rule.is_required) === 1 ? 0 : 1,
        description: documentUseText(section),
      });
    });

    normalizedDocuments.forEach((documentItem, index) => {
      const sectionName = documentItem.document_section_name || '';
      const isRequired = Number(documentItem.is_required) === 1;
      const isBonus = Number(documentItem.is_optional_bonus) === 1;
      const statusValue = isRequired ? 'required' : (isBonus ? 'bonus' : 'optional');
      const card = document.createElement('article');
      card.className = 'template-document-card';
      card.innerHTML = `
        <h4>${escapeHtml(sectionName || 'Seksioni i profilit')}</h4>
        <label class="document-status-control">Statusi
          <select class="document-status-select" data-required-target="documentRequired${index}" data-bonus-target="documentBonus${index}">
            <option value="required" ${statusValue === 'required' ? 'selected' : ''}>Obligativ</option>
            <option value="bonus" ${statusValue === 'bonus' ? 'selected' : ''}>Opsional / PikÃ«zues</option>
            <option value="optional" ${statusValue === 'optional' ? 'selected' : ''}>Opsional</option>
          </select>
        </label>
        <p>${escapeHtml(documentItem.description || documentUseText(sectionName))}</p>
        ${hiddenInput(`documents[${index}][document_section_name]`, sectionName)}
        <input type="hidden" id="documentRequired${index}" name="documents[${index}][is_required]" value="${isRequired ? '1' : '0'}">
        <input type="hidden" id="documentBonus${index}" name="documents[${index}][is_optional_bonus]" value="${!isRequired && isBonus ? '1' : '0'}">
        ${hiddenInput(`documents[${index}][description]`, documentItem.description)}`;
      body.appendChild(card);
    });

    body.querySelectorAll('.document-status-select').forEach((select) => {
      select.addEventListener('change', () => syncDocumentStatus(select));
      syncDocumentStatus(select);
    });
  }

  function syncDocumentStatus(select) {
    const requiredInput = document.getElementById(select.dataset.requiredTarget);
    const bonusInput = document.getElementById(select.dataset.bonusTarget);
    if (!requiredInput || !bonusInput) return;
    requiredInput.value = select.value === 'required' ? '1' : '0';
    bonusInput.value = select.value === 'bonus' ? '1' : '0';
  }

  function syncRuleGroupStatus(select) {
    const indexes = String(select.dataset.ruleIndexes || '').split(',').filter(Boolean);
    indexes.forEach((index) => {
      const requiredInput = document.getElementById(`ruleRequired${index}`);
      const pointsInput = document.getElementById(`rulePoints${index}`);
      if (requiredInput) requiredInput.value = select.value === 'required' ? '1' : '0';
      if (pointsInput) {
        pointsInput.value = select.value === 'optional' ? '0' : (pointsInput.dataset.originalPoints || pointsInput.value || '0');
      }
    });

    const preview = document.getElementById(select.dataset.previewTarget);
    if (!preview) return;
    preview.textContent = ruleStatusLabel(select.value);
    preview.className = `badge ${ruleStatusClass(select.value)}`;
  }

  function ruleStatusLabel(value) {
    if (value === 'required') return 'Obligativ';
    if (value === 'bonus') return 'Opsional / Pikezues';
    return 'Opsional';
  }

  function ruleStatusClass(value) {
    return value === 'required' ? 'ok' : 'muted';
  }
  function documentUseText(section) {
    const map = {
      'ID / Leternjoftimi': 'PÃ«rdoret pÃ«r identifikimin e studentit.',
      'ID / LetÃ«rnjoftimi': 'PÃ«rdoret pÃ«r identifikimin e studentit.',
      'Vertetimi i Studentit Aktiv': 'PÃ«rdoret pÃ«r universitetin, nivelin, vitin e studimit dhe statusin aktiv.',
      'VÃ«rtetimi i Studentit Aktiv': 'PÃ«rdoret pÃ«r universitetin, nivelin, vitin e studimit dhe statusin aktiv.',
      'Certifikata e Notave': 'PÃ«rdoret pÃ«r notÃ«n mesatare dhe provimet.',
      'Certifikata e Vendbanimit': 'PÃ«rdoret pÃ«r komunÃ«n e vendbanimit.',
      'Konfirmimi Bankar': 'PÃ«rdoret pÃ«r pagesÃ«n e bursÃ«s.',
      'Vertetimi per Kategori te Luftes': 'PÃ«rdoret pÃ«r pikÃ« shtesÃ«.',
      'VÃ«rtetimi pÃ«r Kategori tÃ« LuftÃ«s': 'PÃ«rdoret pÃ«r pikÃ« shtesÃ«.',
    };
    return map[section] || 'PÃ«rdoret si seksion i profilit nÃ« vend tÃ« dokumentit fizik/PDF.';
  }

  function syncLegacyFields(rules) {
    const byKey = Object.fromEntries(rules.map((rule) => [rule.rule_key, rule]));
    const minGradeRule = rules.find((rule) => rule.rule_key === 'average_grade' && rule.operator === '>=');
    document.getElementById('legacyMinGrade').value = minGradeRule?.rule_value || '';
    document.getElementById('legacyCity').value = byKey.city?.rule_value || '';
    document.getElementById('legacyUniversity').value = byKey.university?.rule_value || '';
    document.getElementById('legacySocialStatus').value = '';
    document.getElementById('legacyVeteranChild').checked = byKey.is_veteran_child?.rule_value === 'po' && Number(byKey.is_veteran_child?.is_required) === 1;
    document.getElementById('legacyOrphan').checked = byKey.is_orphan?.rule_value === 'po' && Number(byKey.is_orphan?.is_required) === 1;
    document.getElementById('legacySocialAssistance').checked = byKey.receives_social_assistance?.rule_value === 'po' && Number(byKey.receives_social_assistance?.is_required) === 1;
    if (byKey.receives_social_assistance?.rule_value === 'po' && Number(byKey.receives_social_assistance?.is_required) === 1) document.getElementById('legacySocialStatus').value = 'Ndihme sociale';
    if (byKey.is_orphan?.rule_value === 'po' && Number(byKey.is_orphan?.is_required) === 1) document.getElementById('legacySocialStatus').value = 'Jetim';
    if (byKey.is_veteran_child?.rule_value === 'po' && Number(byKey.is_veteran_child?.is_required) === 1) document.getElementById('legacySocialStatus').value = 'Femije veterani';
  }

  function loadSelectedTemplate() {
    const isOjQ = categorySelect.value === 'Burse humanitare nga OJQ';
    if (isOjQ) {
      providerNameInput.value = providerSelect.selectedOptions[0]?.dataset.providerName || '';
      templateIdInput.value = '';
      renderRuleRows([]);
      renderDocumentRows([]);
      return;
    }

    const template = templates.find((item) => String(item.id) === templateProviderSelect.value);
    if (!template) return;
    templateIdInput.value = template.id;
    setProviderAccount(template.provider_name);
    document.getElementById('scholarshipTitle').value = template.title || '';
    document.getElementById('scholarshipDescription').value = template.description || '';
    document.getElementById('scholarshipStartDate').value = template.start_date || '';
    document.getElementById('scholarshipEndDate').value = template.end_date || '';
    renderRuleRows(template.rules || []);
    renderDocumentRows(template.documents || [], template.rules || []);
    syncLegacyFields(template.rules || []);
  }

  categorySelect.addEventListener('change', refreshTemplateProviders);
  loadTemplateButton.addEventListener('click', loadSelectedTemplate);
  providerSelect.addEventListener('change', () => {
    providerNameInput.value = providerSelect.selectedOptions[0]?.dataset.providerName || '';
  });
  adminScholarshipForm.addEventListener('submit', (event) => {
    const isOjQ = categorySelect.value === 'Burse humanitare nga OJQ';
    if (!isOjQ && !templateIdInput.value) {
      event.preventDefault();
      alert('Zgjedhni kategorine dhe ofruesin, pastaj klikoni "Ngarko kriteret".');
    }
  });
  refreshTemplateProviders();
}

function escapeHtml(value) {
  return String(value)
    .replaceAll('&', '&amp;')
    .replaceAll('<', '&lt;')
    .replaceAll('>', '&gt;')
    .replaceAll('"', '&quot;');
}
