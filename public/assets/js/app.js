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
    if (title) title.textContent = 'Duke verifikuar te dhenat e profilit tuaj...';
    if (text) text.textContent = form.dataset.scholarshipTitle || 'Ju lutemi prisni pak.';

    setTimeout(() => {
      if (title) title.textContent = 'Urime! Ju keni fituar bursen.';
      if (text) {
        text.textContent = form.dataset.scholarshipAmount
          ? `Shuma e fituar: ${form.dataset.scholarshipAmount}. Raporti i aplikimit po pergatitet.`
          : 'Raporti i aplikimit po pergatitet.';
      }
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
  let templates = [];
  try {
    const parsedTemplates = JSON.parse(adminScholarshipForm.dataset.templates || '[]');
    templates = Array.isArray(parsedTemplates) ? parsedTemplates : [];
  } catch (error) {
    const body = document.getElementById('templateDocumentsBody');
    if (body) body.innerHTML = '<p class="muted-text">Nuk ka te dhena.</p>';
  }
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
  const scholarshipAmountInput = document.getElementById('scholarshipAmount');
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

  function normalizeText(value) {
    return String(value || '')
      .trim()
      .toLowerCase()
      .replaceAll('e', 'e')
      .replaceAll('\u00c3\u00a7', 'c')
      .replaceAll('e', 'e')
      .replaceAll('ç', 'c');
  }

  function documentSectionKey(section) {
    const text = normalizeText(section);
    if (text === 'id_card' || text.includes('idcard')) return 'id_card';
    if (text.includes('leternjoftimi') || text.includes('id /')) return 'id_card';
    if (text.includes('studentit aktiv')) return 'student_active';
    if (text.includes('notave')) return 'grade_certificate';
    if (text.includes('vendbanimit')) return 'residence_certificate';
    if (text.includes('bashkesise familjare') || text.includes('studenteve ne familje')) return 'family_declaration';
    if (text.includes('ndihm') && text.includes('sociale')) return 'social_assistance';
    if (text.includes('kategori') && text.includes('luft')) return 'war_category';
    if (text.includes('vdekjes') && text.includes('prind')) return 'parent_death';
    if (text.includes('nevoja') && text.includes('vecanta')) return 'special_needs';
    if (text.includes('drejtime deficitare') || text.includes('drejtim deficitar')) return 'deficit_program';
    if (text.includes('administrata tatimore') || text.includes('atk')) return 'tax_confirmation';
    if (text.includes('bankar')) return 'bank_confirmation';
    if (text.includes('regjistri i bursave')) return 'scholarship_registry';
    return text.replace(/[^a-z0-9]+/g, '');
  }

  function documentSectionKeyForRule(rule) {
    const key = String(rule?.rule_key || '');
    const mapped = {
      id_card_completed: 'id_card',
      student_active: 'student_active',
      full_time: 'student_active',
      repeating_year: 'student_active',
      study_level: 'student_active',
      study_year: 'student_active',
      university: 'student_active',
      faculty: 'student_active',
      public_university: 'student_active',
      public_university_after_first_year: 'student_active',
      first_year_university: 'student_active',
      correspondence: 'student_active',
      self_financing: 'student_active',
      is_final_year: 'student_active',
      average_grade: 'grade_certificate',
      previous_year_exams_completed: 'grade_certificate',
      september_exams_completed: 'grade_certificate',
      residence_municipality: 'residence_certificate',
      city: 'residence_certificate',
      bank_confirmed: 'bank_confirmation',
      bank_completed: 'bank_confirmation',
      war_category: 'war_category',
      veteran_child: 'war_category',
      is_veteran_child: 'war_category',
      martyr_child: 'war_category',
      receives_social_assistance: 'social_assistance',
      social_assistance: 'social_assistance',
      one_parent_missing: 'parent_death',
      two_parents_missing: 'parent_death',
      missing_one_parent: 'parent_death',
      missing_both_parents: 'parent_death',
      special_needs: 'special_needs',
      family_students_count: 'family_declaration',
      student_employed: 'tax_confirmation',
      active_worker: 'tax_confirmation',
    };
    return mapped[key] || documentSectionKey(rule?.display_info?.document_section || '');
  }

  function canonicalDocumentSectionName(key, fallback = '') {
    return {
      id_card: 'ID / Leternjoftimi',
      student_active: 'Vertetimi i Studentit Aktiv',
      grade_certificate: 'Certifikata e Notave',
      residence_certificate: 'Certifikata e Vendbanimit',
      bank_confirmation: 'Konfirmimi Bankar',
      war_category: 'Vertetimi per Kategori te Luftes',
      social_assistance: 'Vertetimi per Ndihme Sociale',
      parent_death: 'Certifikata e Vdekjes se Prinderve',
      special_needs: 'Vertetimi per Nevoja te Vecanta',
      family_declaration: 'Deklarata e Bashkesise Familjare',
      tax_confirmation: 'Vertetimi nga Administrata Tatimore e Kosoves',
      deficit_program: 'Deshmi per Drejtime Deficitare',
      scholarship_registry: 'Regjistri i Bursave',
    }[key] || fallback;
  }

  function hasVariableAmount(template) {
    return normalizeText(template?.category).includes('burse komunale')
      && normalizeText(template?.provider_name).includes('komuna e kamenic');
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
    if (rule.rule_key === 'residence_municipality' && value) return `Studenti duhet te jete banor i Komunes se ${value}.`;
    if (rule.rule_key === 'full_time') return 'Studenti duhet te jete student i rregullt.';
    if (rule.rule_key === 'student_active') return 'Studenti duhet te jete student aktiv.';
    if (rule.rule_key === 'repeating_year' && value === 'jo') return 'Studenti nuk duhet te jete perserites i vitit.';
    if (rule.rule_key === 'study_level' && op === '!=') return `Niveli i studimeve nuk duhet te jete ${value}.`;
    if (rule.rule_key === 'average_grade' && op === 'between') return `Nota mesatare ${value.replace('-', ' deri ')}.`;
    if (rule.rule_key === 'average_grade' && op === '>=') return `Nota mesatare duhet te jete se paku ${value}.`;
    if (rule.rule_key === 'first_year_university') return `Per vitin e pare, universiteti duhet te jete ${value}.`;
    if (rule.rule_key === 'public_university_after_first_year') return 'Per vitin e dyte e tutje, universiteti duhet te jete publik.';
    if (rule.rule_key === 'family_students_count') return 'Dy ose me shume studente ne familje.';
    if (['receives_social_assistance', 'social_assistance'].includes(rule.rule_key)) return 'Studenti eshte perfitues i ndihmes sociale.';
    if (['two_parents_missing', 'missing_both_parents'].includes(rule.rule_key)) return 'Studenti eshte pa dy prinder.';
    if (['one_parent_missing', 'missing_one_parent'].includes(rule.rule_key)) return 'Studenti eshte pa njerin prind.';
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
            <span class="badge muted">${totalOptionalPoints > 0 ? `Deri ne ${totalOptionalPoints} pike` : 'Piket: nuk aplikohet'}</span>
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
            <strong>Kriteret opsionale / pikezuese</strong>
            <ul>${group.optional.map(({ rule }) => `<li>${escapeHtml(ruleDisplayText(rule))}${Number(rule.points || 0) > 0 ? ` = ${Number(rule.points || 0)} pike` : ''}</li>`).join('')}</ul>
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
    documents = Array.isArray(documents) ? documents : [];
    rules = Array.isArray(rules) ? rules : [];
    body.innerHTML = '';
    const documentSections = new Map();

    function addDocument(documentItem) {
      const sectionName = documentItem?.document_section_name || '';
      const sectionKey = documentSectionKey(sectionName);
      if (!sectionKey) return;

      if (documentSections.has(sectionKey)) {
        const existing = documentSections.get(sectionKey);
        existing.is_required = Number(existing.is_required) === 1 || Number(documentItem.is_required) === 1 ? 1 : 0;
        existing.is_optional_bonus = Number(existing.is_optional_bonus) === 1 || Number(documentItem.is_optional_bonus) === 1 ? 1 : 0;
        if (!existing.description && documentItem.description) existing.description = documentItem.description;
        return;
      }

      documentSections.set(sectionKey, {
        ...documentItem,
        document_section_name: canonicalDocumentSectionName(sectionKey, sectionName),
        requiredRules: [],
        optionalRules: [],
      });
    }

    function addRule(rule) {
      const sectionKey = documentSectionKeyForRule(rule);
      if (!sectionKey) return;
      if (!documentSections.has(sectionKey)) {
        const sectionName = canonicalDocumentSectionName(sectionKey, rule.display_info?.document_section || '');
        addDocument({
          document_section_name: sectionName,
          is_required: Number(rule.is_required) === 1 ? 1 : 0,
          is_optional_bonus: Number(rule.is_required) === 1 ? 0 : 1,
          description: documentUseText(sectionName),
        });
      }

      const section = documentSections.get(sectionKey);
      const target = Number(rule.is_required) === 1 ? section.requiredRules : section.optionalRules;
      const ruleText = ruleDisplayText(rule);
      if (!target.some((item) => item.text === ruleText)) {
        target.push({ rule, text: ruleText });
      }
      section.is_required = Number(section.is_required) === 1 || Number(rule.is_required) === 1 ? 1 : 0;
      section.is_optional_bonus = Number(section.is_optional_bonus) === 1 || Number(rule.is_required) !== 1 ? 1 : 0;
    }

    documents.forEach(addDocument);
    rules.forEach(addRule);

    if (documentSections.size === 0) {
      body.innerHTML = '<p class="muted-text">Nuk ka te dhena.</p>';
      return;
    }

    Array.from(documentSections.values()).forEach((documentItem, index) => {
      const sectionName = documentItem.document_section_name || '';
      const isRequired = Number(documentItem.is_required) === 1;
      const isBonus = Number(documentItem.is_optional_bonus) === 1;
      const statusValue = isRequired ? 'required' : (isBonus ? 'bonus' : 'optional');
      const requiredRules = documentItem.requiredRules || [];
      const optionalRules = documentItem.optionalRules || [];
      const card = document.createElement('article');
      card.className = 'template-document-card';
      card.innerHTML = `
        <h4>${escapeHtml(sectionName || 'Seksioni i profilit')}</h4>
        <label class="document-status-control">Statusi
          <select class="document-status-select" data-required-target="documentRequired${index}" data-bonus-target="documentBonus${index}">
            <option value="required" ${statusValue === 'required' ? 'selected' : ''}>Obligativ</option>
            <option value="bonus" ${statusValue === 'bonus' ? 'selected' : ''}>Opsional / Pikezues</option>
            <option value="optional" ${statusValue === 'optional' ? 'selected' : ''}>Opsional</option>
          </select>
        </label>
        <p>${escapeHtml(documentItem.description || documentUseText(sectionName))}</p>
        ${requiredRules.length ? `
          <div class="template-rule-section">
            <strong>Kriteret obligative</strong>
            <ul>${requiredRules.map(({ text }) => `<li>${escapeHtml(text)}</li>`).join('')}</ul>
          </div>` : ''}
        ${optionalRules.length ? `
          <div class="template-rule-section">
            <strong>Kriteret opsionale / pikezuese</strong>
            <ul>${optionalRules.map(({ rule, text }) => `<li>${escapeHtml(text)}${Number(rule.points || 0) > 0 ? ` = ${Number(rule.points || 0)} pike` : ''}</li>`).join('')}</ul>
          </div>` : ''}
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
      'ID / Leternjoftimi': 'Perdoret per identifikimin e studentit.',
      'ID / Leternjoftimi': 'Perdoret per identifikimin e studentit.',
      'Vertetimi i Studentit Aktiv': 'Perdoret per universitetin, nivelin, vitin e studimit dhe statusin aktiv.',
      'Vertetimi i Studentit Aktiv': 'Perdoret per universitetin, nivelin, vitin e studimit dhe statusin aktiv.',
      'Certifikata e Notave': 'Perdoret per noten mesatare dhe provimet.',
      'Certifikata e Vendbanimit': 'Perdoret per komunen e vendbanimit.',
      'Konfirmimi Bankar': 'Perdoret per pagesen e burses.',
      'Vertetimi per Kategori te Luftes': 'Perdoret per pike shtese.',
      'Vertetimi per Kategori te Luftes': 'Perdoret per pike shtese.',
    };
    return map[section] || 'Perdoret si seksion i profilit ne vend te dokumentit fizik/PDF.';
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
    if (scholarshipAmountInput) {
      scholarshipAmountInput.value = hasVariableAmount(template) ? 'varet nga piket' : '500';
    }
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
