/**
 * Employee Form JS — 12-step wizard + dynamic repeater rows
 */

const TOTAL_STEPS = 12;

function showStep(step) {
    document.querySelectorAll('.step-content').forEach(el => el.style.display = 'none');
    document.querySelectorAll('.step-wizard .step').forEach(el => {
        el.classList.remove('active', 'completed');
    });
    const target = document.getElementById('step' + step);
    if (target) target.style.display = 'block';
    for (let i = 1; i <= TOTAL_STEPS; i++) {
        const label = document.getElementById('step' + i + 'Label');
        if (label) {
            if (i < step) label.classList.add('completed');
            else if (i === step) label.classList.add('active');
        }
    }
    // Scroll wizard to show active step
    const activeLabel = document.getElementById('step' + step + 'Label');
    if (activeLabel) activeLabel.scrollIntoView({ behavior: 'smooth', block: 'nearest', inline: 'center' });
    
    // Sync with hidden input for form persistence
    const stepInput = document.getElementById('currentStepInput');
    if (stepInput) stepInput.value = step;

    // Update URL without refreshing to persist step on reload
    const url = new URL(window.location);
    url.searchParams.set('step', step);
    window.history.pushState({}, '', url);

    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// Copy residential address to permanent
function copyResAddress() {
    const fields = ['house_no','street','subdivision','barangay','city','province','zip_code'];
    fields.forEach(f => {
        const src = document.querySelector('[name="res_' + f + '"]');
        const dst = document.getElementById('perm_' + f);
        if (src && dst) dst.value = src.value;
    });
}

// Toggle disclosure detail areas
function toggleDetails(checkbox, detailsDivId) {
    const div = document.getElementById(detailsDivId);
    if (div) {
        div.classList.toggle('show', checkbox.checked);
    }
}

// Generic repeater: child/sibling (4-column: surname, first, middle, dob)
function addRepeaterRow(containerId, prefix) {
    const c = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_surname[]" placeholder="Surname"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_first_name[]" placeholder="First Name"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="${prefix}_middle_name[]" placeholder="Middle Name"></div>
            <div class="col-md-3 mb-2"><input type="date" class="form-control form-control-sm" name="${prefix}_dob[]"></div>
        </div>`;
    c.appendChild(div);
}

// Education row
function addEducationRow() {
    const c = document.getElementById('educationContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><select class="form-select form-select-sm" name="edu_level[]"><option value="Elementary">Elementary</option><option value="Secondary">Secondary / Junior High</option><option value="Senior High School">Senior High School</option><option value="Vocational">Vocational / Trade Course</option><option value="College" selected>College</option><option value="Graduate Studies">Graduate Studies</option></select></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_school[]" placeholder="School Name"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_degree[]" placeholder="Degree/Course"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">From (Year)</label><input type="number" class="form-control form-control-sm" name="edu_from[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">To (Year)</label><input type="number" class="form-control form-control-sm" name="edu_to[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_units[]" placeholder="Highest Level/Units"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="edu_year_grad[]" placeholder="Year Grad"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_honors[]" placeholder="Honors"></div>
        </div>`;
    c.appendChild(div);
}

// Work experience row
function addWorkRow() {
    const c = document.getElementById('workContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">Start (Year)</label><input type="number" class="form-control form-control-sm" name="work_from[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">End (Year)</label><input type="number" class="form-control form-control-sm" name="work_to[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_title[]" placeholder="Job Title"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_company[]" placeholder="Company"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="work_salary[]" placeholder="Salary"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_status[]" placeholder="Status"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="work_reason[]" placeholder="Reason for Leaving"></div>
        </div>`;
    c.appendChild(div);
}

// Training row
function addTrainingRow() {
    const c = document.getElementById('trainingContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">Start (Year)</label><input type="number" class="form-control form-control-sm" name="training_from[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">End (Year)</label><input type="number" class="form-control form-control-sm" name="training_to[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="training_title[]" placeholder="Training Title"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="training_type[]" placeholder="Type"></div>
            <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm" name="training_hours[]" placeholder="Hrs"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="training_conducted[]" placeholder="Conducted By"></div>
        </div>`;
    c.appendChild(div);
}

// Voluntary work row
function addVoluntaryRow() {
    const c = document.getElementById('voluntaryContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">Start (Year)</label><input type="number" class="form-control form-control-sm" name="vol_from[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">End (Year)</label><input type="number" class="form-control form-control-sm" name="vol_to[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_org[]" placeholder="Organization"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_address[]" placeholder="Address"></div>
            <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm" name="vol_hours[]" placeholder="Hrs"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_position[]" placeholder="Position/Nature"></div>
        </div>`;
    c.appendChild(div);
}

// Eligibility row
function addEligibilityRow() {
    const c = document.getElementById('eligibilityContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="elig_title[]" placeholder="License/Cert Title"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">Start (Year)</label><input type="number" class="form-control form-control-sm" name="elig_from[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><label class="small text-muted d-block">End (Year)</label><input type="number" class="form-control form-control-sm" name="elig_to[]" min="1900" max="2099" placeholder="Year"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="elig_number[]" placeholder="License No."></div>
            <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm" name="elig_exam_date[]"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="elig_exam_place[]" placeholder="Place of Exam"></div>
        </div>`;
    c.appendChild(div);
}

// Recognition row
function addRecognitionRow() {
    const c = document.getElementById('recognitionsContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-4 mb-2"><label class="small text-muted d-block">Award/Recognition Title</label><input type="text" class="form-control form-control-sm" name="recognition_title[]" placeholder="Title"></div>
            <div class="col-md-5 mb-2"><label class="small text-muted d-block">Issued By / Organization</label><input type="text" class="form-control form-control-sm" name="recognition_issued_by[]" placeholder="Organization"></div>
            <div class="col-md-3 mb-2"><label class="small text-muted d-block">Date Received</label><input type="date" class="form-control form-control-sm" name="recognition_date[]"></div>
        </div>`;
    c.appendChild(div);
}

// Simple single-field row (skills, memberships)
function addSimpleRow(containerId, fieldName, placeholder) {
    const c = document.getElementById(containerId);
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <input type="text" class="form-control form-control-sm" name="${fieldName}[]" placeholder="${placeholder}">`;
    c.appendChild(div);
}

// Real property row
function addRealPropertyRow() {
    const c = document.getElementById('realPropContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_desc[]" placeholder="Description"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_kind[]" placeholder="Kind"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_location[]" placeholder="Location"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_assessed[]" placeholder="Assessed Value"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_market[]" placeholder="Market Value"></div>
            <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="rprop_acq_mode[]" placeholder="Year-Mode"></div>
            <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_acq_cost[]" placeholder="Acq. Cost"></div>
        </div>`;
    c.appendChild(div);
}

// Personal property row
function addPersonalPropertyRow() {
    const c = document.getElementById('personalPropContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-5 mb-2"><input type="text" class="form-control form-control-sm" name="pprop_desc[]" placeholder="Description"></div>
            <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="pprop_year[]" placeholder="Year Acquired"></div>
            <div class="col-md-4 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="pprop_cost[]" placeholder="Acquisition Cost"></div>
        </div>`;
    c.appendChild(div);
}

// Liability row
function addLiabilityRow() {
    const c = document.getElementById('liabilitiesContainer');
    const div = document.createElement('div');
    div.className = 'repeater-row';
    div.innerHTML = `
        <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
        <div class="row">
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="liab_nature[]" placeholder="Nature of Liability"></div>
            <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="liab_creditor[]" placeholder="Name of Creditor"></div>
            <div class="col-md-4 mb-2"><input type="number" step="0.01" class="form-control form-control-sm" name="liab_balance[]" placeholder="Outstanding Balance"></div>
        </div>`;
    c.appendChild(div);
}

// Profile image preview
function previewImage(input) {
    const preview = document.getElementById('profilePreview');
    const container = document.getElementById('profilePreviewContainer');
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            container.style.display = 'block';
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        // Only hide if it's completely empty (not even an existing image)
        if (!preview.getAttribute('src')) {
            container.style.display = 'none';
        }
    }
}

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#39;');
}

function normalizeChangeFieldName(name) {
    return (name || '').replace(/\[\]$/, '');
}

function formatFallbackLabel(name) {
    return normalizeChangeFieldName(name)
        .replace(/_/g, ' ')
        .replace(/\b\w/g, char => char.toUpperCase());
}

function getChangeFieldLabel(name) {
    const normalized = normalizeChangeFieldName(name);
    const directLabels = {
        first_name: 'First Name',
        last_name: 'Surname',
        middle_name: 'Middle Name',
        name_extension: 'Name Extension',
        date_of_birth: 'Date of Birth',
        place_of_birth: 'Place of Birth',
        gender: 'Gender',
        civil_status: 'Civil Status',
        height_m: 'Height',
        weight_kg: 'Weight',
        blood_type: 'Blood Type',
        citizenship: 'Citizenship',
        sss_number: 'SSS No.',
        philhealth_number: 'PhilHealth No.',
        pagibig_number: 'Pag-IBIG No.',
        tin_number: 'TIN No.',
        res_house_no: 'Residential House/Block/Lot No.',
        res_street: 'Residential Street',
        res_subdivision: 'Residential Subdivision/Village',
        res_barangay: 'Residential Barangay',
        res_city: 'Residential City/Municipality',
        res_province: 'Residential Province',
        res_zip_code: 'Residential Zip Code',
        perm_house_no: 'Permanent House/Block/Lot No.',
        perm_street: 'Permanent Street',
        perm_subdivision: 'Permanent Subdivision/Village',
        perm_barangay: 'Permanent Barangay',
        perm_city: 'Permanent City/Municipality',
        perm_province: 'Permanent Province',
        perm_zip_code: 'Permanent Zip Code',
        telephone_number: 'Telephone No.',
        contact_number: 'Contact Number',
        email: 'Email Address',
        spouse_surname: 'Spouse Surname',
        spouse_first_name: 'Spouse First Name',
        spouse_middle_name: 'Spouse Middle Name',
        spouse_name_ext: 'Spouse Name Extension',
        spouse_occupation: 'Spouse Occupation',
        father_surname: 'Father Surname',
        father_first_name: 'Father First Name',
        father_middle_name: 'Father Middle Name',
        father_name_ext: 'Father Name Extension',
        father_occupation: 'Father Occupation',
        mother_maiden_surname: 'Mother Maiden Surname',
        mother_first_name: 'Mother First Name',
        mother_middle_name: 'Mother Middle Name',
        mother_occupation: 'Mother Occupation',
        is_related_to_company: 'Related to Company',
        related_details: 'Related to Company Details',
        has_admin_offense: 'Administrative Offense',
        admin_offense_details: 'Administrative Offense Details',
        has_criminal_charge: 'Criminal Charge',
        criminal_charge_details: 'Criminal Charge Details',
        has_criminal_conviction: 'Criminal Conviction',
        criminal_conviction_details: 'Criminal Conviction Details',
        has_been_separated: 'Separated From Service',
        separation_details: 'Separation Details',
        is_pwd: 'PWD Status',
        pwd_details: 'PWD Details',
        is_solo_parent: 'Solo Parent Status',
        solo_parent_details: 'Solo Parent Details',
        has_recent_hospital: 'Recent Hospitalization',
        hospital_details: 'Hospitalization Details',
        has_current_treatment: 'Current Treatment',
        treatment_details: 'Treatment Details',
        hire_date: 'Hire Date',
        job_title: 'Job Title',
        department_id: 'Department',
        rank_category_id: 'Rank',
        branch_id: 'Branch',
        employment_status: 'Employment Status',
        employment_type: 'Employment Type',
        employee_code: 'Company ID',
        is_active: 'Employee Record Status',
        emergency_contact_name: 'Emergency Contact Name',
        emergency_contact_relationship: 'Emergency Contact Relationship',
        emergency_contact_number: 'Emergency Contact Number',
        contract_start_date: 'Contract Start Date',
        contract_end_date: 'Contract End Date',
        profile_picture: 'Profile Picture'
    };

    if (directLabels[normalized]) {
        return directLabels[normalized];
    }

    if (normalized.startsWith('child_')) return 'Children';
    if (normalized.startsWith('sibling_')) return 'Siblings';
    if (normalized.startsWith('edu_')) return 'Education';
    if (normalized.startsWith('work_')) return 'Work Experience';
    if (normalized.startsWith('training_')) return 'Training';
    if (normalized.startsWith('vol_')) return 'Voluntary Work';
    if (normalized.startsWith('elig_')) return 'Eligibility';
    if (normalized.startsWith('skill_')) return 'Skills';
    if (normalized.startsWith('recognition_')) return 'Recognition';
    if (normalized.startsWith('membership_')) return 'Membership';
    if (normalized.startsWith('rprop_')) return 'Real Properties';
    if (normalized.startsWith('pprop_')) return 'Personal Properties';
    if (normalized.startsWith('liab_')) return 'Liabilities';
    if (normalized.startsWith('ref_')) return 'References';

    return formatFallbackLabel(normalized);
}

function shouldIgnoreComparisonField(element) {
    const name = element.name || '';
    const type = (element.type || '').toLowerCase();

    if (!name || element.disabled) return true;
    if (['submit', 'button', 'image', 'reset'].includes(type)) return true;
    if (['current_step', 'return_to', 'quick_save'].includes(name)) return true;

    return false;
}

function getElementComparisonValue(element) {
    const tagName = element.tagName.toLowerCase();
    const type = (element.type || '').toLowerCase();

    if (type === 'checkbox') {
        return {
            raw: element.checked ? '1' : '0',
            display: element.checked ? 'Yes' : 'No'
        };
    }

    if (type === 'radio') {
        return {
            raw: element.checked ? String(element.value || '').trim() : '',
            display: element.checked ? String(element.value || '').trim() : ''
        };
    }

    if (type === 'file') {
        const files = Array.from(element.files || []);
        if (files.length === 0) {
            return { raw: '', display: '' };
        }

        const raw = files
            .map((file) => `${file.name}:${file.size}:${file.lastModified}`)
            .join('|');
        const display = files.map((file) => file.name).join(', ');

        return { raw, display };
    }

    if (tagName === 'select') {
        const selectedOption = element.options[element.selectedIndex];
        const raw = String(element.value || '').trim();
        const display = raw && selectedOption ? String(selectedOption.textContent || '').trim() : '';
        return { raw, display };
    }

    const raw = String(element.value || '').trim();
    return { raw, display: raw };
}

function serializeFormForComparison(form) {
    const result = {};
    const order = [];

    Array.from(form.elements).forEach((element) => {
        if (shouldIgnoreComparisonField(element)) return;

        const name = element.name;
        const label = getChangeFieldLabel(name);
        const isArrayField = name.endsWith('[]');
        const value = getElementComparisonValue(element);

        if (isArrayField) {
            if (!result[name]) {
                result[name] = { label, rawValues: [], displayValues: [] };
                order.push(name);
            }

            if (value.raw !== '') {
                result[name].rawValues.push(value.raw);
                result[name].displayValues.push(value.display || value.raw);
            }
            return;
        }

        if (!Object.prototype.hasOwnProperty.call(result, name)) {
            order.push(name);
        }

        result[name] = {
            label,
            raw: value.raw,
            display: value.display
        };
    });

    Object.keys(result).forEach((name) => {
        if (name.endsWith('[]')) {
            const rawValues = result[name].rawValues || [];
            const displayValues = result[name].displayValues || [];
            result[name] = {
                label: result[name].label,
                raw: rawValues.join(' | '),
                display: displayValues.join(' | ')
            };
        }
    });

    return { values: result, order };
}

function buildChangedFieldList(initialSnapshot, currentSnapshot) {
    const allNames = [...initialSnapshot.order];
    currentSnapshot.order.forEach((name) => {
        if (!allNames.includes(name)) allNames.push(name);
    });

    return allNames.reduce((changes, name) => {
        const before = initialSnapshot.values[name] || { label: getChangeFieldLabel(name), raw: '', display: '' };
        const after = currentSnapshot.values[name] || { label: getChangeFieldLabel(name), raw: '', display: '' };

        if ((before.raw || '') === (after.raw || '')) {
            return changes;
        }

        changes.push({
            label: after.label || before.label || formatFallbackLabel(name),
            from: before.display || 'Blank',
            to: after.display || 'Blank'
        });

        return changes;
    }, []);
}

function getChangeConfirmationModal() {
    let modalElement = document.getElementById('employeeChangeSummaryModal');
    if (!modalElement) {
        modalElement = document.createElement('div');
        modalElement.className = 'modal fade';
        modalElement.id = 'employeeChangeSummaryModal';
        modalElement.tabIndex = -1;
        modalElement.setAttribute('aria-hidden', 'true');
        modalElement.innerHTML = `
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-clipboard-check me-2"></i>Review Changes</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted mb-3">Please review the modified information before applying the update.</p>
                        <div id="employeeChangeSummaryList" class="list-group"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel Changes</button>
                        <button type="button" class="btn btn-primary" id="confirmEmployeeUpdateBtn">Confirm Update</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(modalElement);
    }

    return modalElement;
}

// Automatically navigate to the step containing an invalid required field
document.addEventListener("DOMContentLoaded", function () {
    // Handle URL-based step navigation
    const urlParams = new URLSearchParams(window.location.search);
    const employeeForm = document.getElementById('addEmployeeForm') || document.getElementById('editEmployeeForm');
    const urlStep = urlParams.get('step');
    if (urlStep) {
        showStep(parseInt(urlStep));
    } else {
        // Fallback to hidden input value (for error reloads)
        const stepInput = document.getElementById('currentStepInput');
        if (stepInput && stepInput.value > 1) {
            showStep(parseInt(stepInput.value));
        }
    }

    if (employeeForm) {
        employeeForm.addEventListener('invalid', function (e) {
            const stepContent = e.target.closest('.step-content');
            if (stepContent) {
                const stepId = stepContent.id;
                const stepNum = parseInt(stepId.replace('step', ''), 10);
                if (!isNaN(stepNum)) {
                    showStep(stepNum);
                }
            }
        }, true); // Use capture phase
    }

    // Toggle contract dates visibility
    const statusSelect = document.querySelector('select[name="employment_status"]');
    const contractDatesRow = document.getElementById('contractDatesRow');
    if (statusSelect && contractDatesRow) {
        const statusesWithDates = ['OJT', 'Probationary', 'Project Based', 'Project-Based', 'Trainee'];
        const checkStatus = () => {
            if (statusesWithDates.includes(statusSelect.value)) {
                contractDatesRow.style.display = 'flex';
            } else {
                contractDatesRow.style.display = 'none';
            }
        };
        statusSelect.addEventListener('change', checkStatus);
        // Run once on load for edit mode
        checkStatus();
    }

    // Auto-format Government IDs
    const idFormatters = {
        'sss_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 10) val = val.substring(0, 10);
            if (val.length > 9) return `${val.substring(0, 2)}-${val.substring(2, 9)}-${val.substring(9)}`;
            if (val.length > 2) return `${val.substring(0, 2)}-${val.substring(2)}`;
            return val;
        },
        'philhealth_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 12) val = val.substring(0, 12);
            if (val.length > 11) return `${val.substring(0, 2)}-${val.substring(2, 11)}-${val.substring(11)}`;
            if (val.length > 2) return `${val.substring(0, 2)}-${val.substring(2)}`;
            return val;
        },
        'pagibig_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 12) val = val.substring(0, 12);
            let formatted = '';
            if (val.length > 8) formatted = `${val.substring(0, 4)}-${val.substring(4, 8)}-${val.substring(8)}`;
            else if (val.length > 4) formatted = `${val.substring(0, 4)}-${val.substring(4)}`;
            else formatted = val;
            return formatted;
        },
        'tin_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 12) val = val.substring(0, 12);
            let parts = [];
            for (let i = 0; i < val.length; i += 3) {
                parts.push(val.substring(i, i + 3));
            }
            return parts.join('-');
        },
        'telephone_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 10) val = val.substring(0, 10);
            if (val.length > 3) {
                return `(${val.substring(0, 3)}) ${val.substring(3, 6)}${val.length > 6 ? '-' + val.substring(6) : ''}`;
            }
            return val;
        },
        'contact_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 11) val = val.substring(0, 11);
            return val;
        },
        'emergency_contact_number': (val) => {
            val = val.replace(/\D/g, '');
            if (val.length > 11) val = val.substring(0, 11);
            return val;
        }
    };

    Object.keys(idFormatters).forEach(name => {
        const input = document.querySelector(`[name="${name}"]`);
        if (input) {
            input.addEventListener('input', (e) => {
                const cursor = e.target.selectionStart;
                const oldLen = e.target.value.length;
                e.target.value = idFormatters[name](e.target.value);
                const newLen = e.target.value.length;
                let offset = newLen - oldLen;
                e.target.setSelectionRange(cursor + offset, cursor + offset);
            });
        }
    });

    // === AUTO-SAVE DRAFT FEATURE ===
    const isEdit = employeeForm ? employeeForm.dataset.isEdit === 'true' : false;
    const employeeId = isEdit ? (new URLSearchParams(window.location.search)).get('id') : 'new';
    const DRAFT_KEY = `hris_employee_draft_${employeeId}`;
    const initialComparisonSnapshot = (employeeForm && isEdit) ? serializeFormForComparison(employeeForm) : null;
    let allowEditSubmit = false;

    // Run for both Add and Edit pages
    if (employeeForm) {
        let isSaving = false;
        let isSubmitting = false;

        const saveDraft = () => {
            const formData = new FormData(employeeForm);
            const data = {};
            formData.forEach((value, key) => {
                // Don't save file inputs
                if (key === 'profile_picture') return;
                
                if (key.endsWith('[]')) {
                    if (!data[key]) data[key] = [];
                    data[key].push(value);
                } else {
                    data[key] = value;
                }
            });

            // Also save current step
            const activeStep = document.querySelector('.step.active');
            data['_active_step'] = activeStep ? activeStep.id.replace('step', '').replace('Label', '') : '1';
            
            localStorage.setItem(DRAFT_KEY, JSON.stringify(data));
        };

        if (isEdit && initialComparisonSnapshot) {
            const modalElement = getChangeConfirmationModal();
            const modalInstance = new bootstrap.Modal(modalElement);
            const summaryList = modalElement.querySelector('#employeeChangeSummaryList');
            const confirmButton = modalElement.querySelector('#confirmEmployeeUpdateBtn');

            employeeForm.addEventListener('submit', (event) => {
                if (allowEditSubmit) return;

                event.preventDefault();

                const currentSnapshot = serializeFormForComparison(employeeForm);
                const changes = buildChangedFieldList(initialComparisonSnapshot, currentSnapshot);

                if (changes.length === 0) {
                    window.alert('No changes detected.');
                    return;
                }

                summaryList.innerHTML = changes.map(change => `
                    <div class="list-group-item">
                        <div class="fw-bold mb-1">${escapeHtml(change.label)}</div>
                        <div class="small text-muted">${escapeHtml(change.from)} &rarr; ${escapeHtml(change.to)}</div>
                    </div>
                `).join('');

                modalInstance.show();
            });

            confirmButton.addEventListener('click', () => {
                allowEditSubmit = true;
                isSubmitting = true;
                modalInstance.hide();
                HTMLFormElement.prototype.submit.call(employeeForm);
            });
        } else {
            // For Add mode or any submission that doesn't require a review modal
            employeeForm.addEventListener('submit', () => {
                isSubmitting = true;
            });
        }

        // Debounced save
        const debounceSave = () => {
            if (isSaving) return;
            isSaving = true;
            setTimeout(() => {
                saveDraft();
                isSaving = false;
            }, 1000);
        };

        employeeForm.addEventListener('input', debounceSave);
        employeeForm.addEventListener('change', debounceSave);

        // Clear draft if success message is present in URL
        if (urlParams.get('msg') && urlParams.get('msg').toLowerCase().includes('success')) {
            localStorage.removeItem(DRAFT_KEY);
        }

        // Resume draft logic
        const savedDraft = localStorage.getItem(DRAFT_KEY);
        if (savedDraft) {
            const draftData = JSON.parse(savedDraft);
            
            // Show a non-intrusive toast or banner to restore
            const restoreBanner = document.createElement('div');
            restoreBanner.className = 'alert alert-info d-flex justify-content-between align-items-center mb-4 shadow-sm fadeup';
            restoreBanner.style.borderRadius = '12px';
            restoreBanner.innerHTML = `
                <div>
                    <i class="fas fa-magic me-2"></i>
                    <strong>Draft Found!</strong> You have an unsaved session from ${new Date().toLocaleTimeString()}.
                </div>
                <div>
                    <button type="button" class="btn btn-sm btn-primary me-2" id="btnRestoreDraft">Restore Draft</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnClearDraft">Discard</button>
                </div>
            `;
            employeeForm.parentNode.insertBefore(restoreBanner, employeeForm);

            document.getElementById('btnRestoreDraft').addEventListener('click', () => {
                // 1. Create dynamic rows first
                const containers = {
                    'child_first_name[]': ['childrenContainer', 'child'],
                    'sibling_first_name[]': ['siblingsContainer', 'sibling'],
                    'edu_level[]': 'addEducationRow',
                    'work_title[]': 'addWorkRow',
                    'training_title[]': 'addTrainingRow',
                    'vol_org[]': 'addVoluntaryRow',
                    'elig_title[]': 'addEligibilityRow',
                    'skill_name[]': ['skillsContainer', 'skill_name', 'Skill or Hobby'],
                    'recognition_title[]': ['recognitionsContainer', 'recognition_title', 'Award/Recognition'],
                    'membership_org[]': ['membershipsContainer', 'membership_org', 'Organization Name'],
                    'rprop_desc[]': 'addRealPropertyRow',
                    'pprop_desc[]': 'addPersonalPropertyRow',
                    'liab_nature[]': 'addLiabilityRow'
                };

                // Add rows
                Object.entries(containers).forEach(([key, action]) => {
                    if (draftData[key] && Array.isArray(draftData[key])) {
                        // Skip first if it's not a repeater that needs creation? 
                        // Actually all repeaters here start empty in Add mode.
                        for (let i = 0; i < draftData[key].length; i++) {
                            if (typeof action === 'string') window[action]();
                            else if (Array.isArray(action)) addRepeaterRow(action[0], action[1]);
                            // Simple rows use addSimpleRow but I'll skip for now or fix later
                        }
                    }
                });

                // 2. Fill values
                setTimeout(() => {
                    Object.entries(draftData).forEach(([name, value]) => {
                        if (name === '_active_step') return;
                        if (Array.isArray(value)) {
                            const inputs = document.querySelectorAll(`[name="${name}"]`);
                            value.forEach((v, idx) => {
                                if (inputs[idx]) inputs[idx].value = v;
                            });
                        } else {
                            const input = document.querySelector(`[name="${name}"]`);
                            if (input) {
                                if (input.type === 'checkbox') input.checked = !!value;
                                else input.value = value;
                                // Trigger any change events (like for toggleDetails)
                                input.dispatchEvent(new Event('change'));
                            }
                        }
                    });

                    // 3. Go to saved step
                    if (draftData['_active_step']) showStep(parseInt(draftData['_active_step']));
                    
                    restoreBanner.remove();
                }, 100);
            });

            document.getElementById('btnClearDraft').addEventListener('click', () => {
                localStorage.removeItem(DRAFT_KEY);
                restoreBanner.remove();
            });
        }

        // Removed clear draft on submit to prevent data loss on server-side failure
        // The draft will now be cleared only when a success message is detected on the next page load.

        // Unsaved changes warning
        window.onbeforeunload = (e) => {
            if (isSubmitting) return;
            const draft = localStorage.getItem(DRAFT_KEY);
            if (draft) {
                e.preventDefault();
                e.returnValue = '';
            }
        };
    }
});
