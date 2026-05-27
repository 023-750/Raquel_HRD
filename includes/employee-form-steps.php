<?php
/**
 * Shared employee form steps (12 sections)
 * Used by both add-employee.php and edit-employee.php
 * Expects: $emp (array|null), $branches (mysqli_result)
 * In add mode $emp is null; in edit mode $emp has current values.
 */
$e = $emp ?? [];
$v = function ($key, $default = '') use ($e) {
    return htmlspecialchars($e[$key] ?? $default, ENT_QUOTES, 'UTF-8');
};
$sel = function ($key, $val) use ($e) {
    return (($e[$key] ?? '') === $val) ? 'selected' : '';
};
$chk = function ($key) use ($e) {
    return !empty($e[$key]) ? 'checked' : '';
};
$isEdit = !empty($e);
$totalSteps = 12;
$currentStep = isset($_GET['step']) ? (int)$_GET['step'] : 1;
$rankCategories = $rankCategories ?? [
    ['rank_category_id' => 1, 'rank_name' => 'Executives'],
    ['rank_category_id' => 2, 'rank_name' => 'Management Team'],
    ['rank_category_id' => 3, 'rank_name' => 'Manager'],
    ['rank_category_id' => 5, 'rank_name' => 'R&F'],
    ['rank_category_id' => 4, 'rank_name' => 'Supervisor'],
];
?>

<input type="hidden" name="current_step" id="currentStepInput" value="<?php echo $currentStep; ?>">

<!-- ====== STEP 1: Personal Information ====== -->
<div class="step-content" id="step1">
    <div class="form-section-title"><i class="fas fa-id-card"></i> Basic Identity</div>
    <div class="row">
        <div class="col-md-12 mb-3">
            <label class="form-label">Profile Picture <?php echo $isEdit ? '' : '(Optional)'; ?></label>
            <div class="d-flex align-items-start gap-4">
                <div id="profilePreviewContainer" class="text-center"
                    style="<?php echo !empty($e['profile_picture']) ? '' : 'display:none;'; ?>">
                    <img id="profilePreview"
                        src="<?php echo !empty($e['profile_picture']) ? BASE_URL . '/assets/img/employees/' . e($e['profile_picture']) : ''; ?>"
                        class="rounded-circle img-thumbnail shadow-sm"
                        style="width:100px;height:100px;object-fit:cover;">
                    <div class="small text-muted mt-1">Current/New</div>
                </div>
                <div class="flex-grow-1">
                    <?php if ($_SESSION['role'] === 'Admin' || $_SESSION['role'] === 'HR Manager' || $_SESSION['role'] === 'HR Supervisor'): ?>
                        <input type="file" class="form-control" name="profile_picture" accept="image/*"
                            onchange="previewImage(this)">
                        <small class="text-muted d-block mt-1">Recommended: Square image, max 2MB (JPG, PNG)</small>
                        <?php if (!empty($e['profile_picture'])): ?>
                            <small class="text-primary d-block mt-1 fw-bold"><i class="fas fa-check-circle me-1"></i>Filename:
                                <?php echo $v('profile_picture'); ?></small>
                        <?php endif; ?>
                    <?php else: ?>
                        <div class="alert alert-light py-2 px-3 border border-dashed text-muted mb-0"
                            style="border-radius: 8px; font-size: 0.85rem;">
                            <i class="fas fa-lock me-2"></i>Avatar management is reserved for Administrators.
                        </div>
                        <?php if (!empty($e['profile_picture'])): ?>
                            <small class="text-muted d-block mt-1"><i class="fas fa-info-circle me-1"></i>This photo was
                                verified and added by the Admin.</small>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Surname <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="last_name" value="<?php echo $v('last_name'); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">First Name <span class="text-danger">*</span></label>
            <input type="text" class="form-control" name="first_name" value="<?php echo $v('first_name'); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="middle_name" value="<?php echo $v('middle_name'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Name Extension</label>
            <select class="form-select" name="name_extension">
                <option value="">N/A</option>
                <?php foreach (['JR', 'SR', 'II', 'III', 'IV', 'V'] as $ext): ?>
                    <option value="<?php echo $ext; ?>" <?php echo $sel('name_extension', $ext); ?>><?php echo $ext; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-birthday-cake"></i> Birth & Status</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Date of Birth</label>
            <input type="date" class="form-control" name="date_of_birth" value="<?php echo $v('date_of_birth'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Place of Birth</label>
            <input type="text" class="form-control" name="place_of_birth" value="<?php echo $v('place_of_birth'); ?>"
                placeholder="City/Province">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Gender</label>
            <select class="form-select" name="gender">
                <option value="">Select</option>
                <option value="Male" <?php echo $sel('gender', 'Male'); ?>>Male</option>
                <option value="Female" <?php echo $sel('gender', 'Female'); ?>>Female</option>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Civil Status</label>
            <select class="form-select" name="civil_status">
                <option value="">Select</option>
                <?php foreach (['Single', 'Married', 'Widowed', 'Separated', 'Divorced'] as $cs): ?>
                    <option value="<?php echo $cs; ?>" <?php echo $sel('civil_status', $cs); ?>><?php echo $cs; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-ruler-vertical"></i> Physical & Citizenship</div>
    <div class="row">
        <div class="col-md-2 mb-3">
            <label class="form-label">Height (m)</label>
            <input type="number" step="0.01" class="form-control" name="height_m" value="<?php echo $v('height_m'); ?>"
                placeholder="1.65">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Weight (kg)</label>
            <input type="number" step="0.1" class="form-control" name="weight_kg" value="<?php echo $v('weight_kg'); ?>"
                placeholder="60">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Blood Type</label>
            <select class="form-select" name="blood_type">
                <option value="">Select</option>
                <?php foreach (['A+', 'A-', 'B+', 'B-', 'O+', 'O-', 'AB+', 'AB-'] as $bt): ?>
                    <option value="<?php echo $bt; ?>" <?php echo $sel('blood_type', $bt); ?>><?php echo $bt; ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Citizenship</label>
            <input type="text" class="form-control" name="citizenship"
                value="<?php echo $v('citizenship', 'Filipino'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-id-badge"></i> Government IDs</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">SSS No.</label>
            <input type="text" class="form-control" name="sss_number" value="<?php echo $v('sss_number'); ?>" 
                placeholder="00-0000000-0" pattern="\d{2}-\d{7}-\d{1}" title="Format: 00-0000000-0 (10 digits)" inputmode="numeric">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">PhilHealth No.</label>
            <input type="text" class="form-control" name="philhealth_number" value="<?php echo $v('philhealth_number'); ?>" 
                placeholder="00-000000000-0" pattern="\d{2}-\d{9}-\d{1}" title="Format: 00-000000000-0 (12 digits)" inputmode="numeric">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Pag-IBIG No.</label>
            <input type="text" class="form-control" name="pagibig_number" value="<?php echo $v('pagibig_number'); ?>" 
                placeholder="0000-0000-0000" pattern="\d{4}-\d{4}-\d{4}" title="Format: 0000-0000-0000 (12 digits)" inputmode="numeric">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">TIN No.</label>
            <input type="text" class="form-control" name="tin_number" value="<?php echo $v('tin_number'); ?>" 
                placeholder="000-000-000-000" pattern="\d{3}-\d{3}-\d{3}-\d{3}" title="Format: 000-000-000-000 (9 or 12 digits)" inputmode="numeric">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-map-marker-alt"></i> Residential Address</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">House/Block/Lot No.</label>
            <input type="text" class="form-control" name="res_house_no" value="<?php echo $v('res_house_no'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Street</label>
            <input type="text" class="form-control" name="res_street" value="<?php echo $v('res_street'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Subdivision/Village</label>
            <input type="text" class="form-control" name="res_subdivision" value="<?php echo $v('res_subdivision'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Barangay</label>
            <input type="text" class="form-control" name="res_barangay" value="<?php echo $v('res_barangay'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">City/Municipality</label>
            <input type="text" class="form-control" name="res_city" value="<?php echo $v('res_city'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Province</label>
            <input type="text" class="form-control" name="res_province" value="<?php echo $v('res_province'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Zip Code</label>
            <input type="text" class="form-control" name="res_zip_code" value="<?php echo $v('res_zip_code'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3">
        <i class="fas fa-home"></i> Permanent Address
        <button type="button" class="btn btn-sm btn-outline-secondary ms-auto" onclick="copyResAddress()">
            <i class="fas fa-copy me-1"></i>Same as Residential
        </button>
    </div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">House/Block/Lot No.</label>
            <input type="text" class="form-control" name="perm_house_no" id="perm_house_no"
                value="<?php echo $v('perm_house_no'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Street</label>
            <input type="text" class="form-control" name="perm_street" id="perm_street"
                value="<?php echo $v('perm_street'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Subdivision/Village</label>
            <input type="text" class="form-control" name="perm_subdivision" id="perm_subdivision"
                value="<?php echo $v('perm_subdivision'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Barangay</label>
            <input type="text" class="form-control" name="perm_barangay" id="perm_barangay"
                value="<?php echo $v('perm_barangay'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">City/Municipality</label>
            <input type="text" class="form-control" name="perm_city" id="perm_city"
                value="<?php echo $v('perm_city'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Province</label>
            <input type="text" class="form-control" name="perm_province" id="perm_province"
                value="<?php echo $v('perm_province'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Zip Code</label>
            <input type="text" class="form-control" name="perm_zip_code" id="perm_zip_code"
                value="<?php echo $v('perm_zip_code'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-phone-alt"></i> Contact Information</div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Telephone No. <span class="text-muted small">(Optional)</span></label>
            <input type="text" class="form-control" name="telephone_number" value="<?php echo $v('telephone_number'); ?>" 
                placeholder="(042) 000-0000" title="Format: (000) 000-0000" inputmode="numeric">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Mobile No.</label>
            <input type="text" class="form-control" name="contact_number" value="<?php echo $v('contact_number'); ?>" 
                placeholder="09171234567" pattern="\d{11}" title="Format: 11 digits (e.g. 09171234567)" inputmode="numeric">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Email Address</label>
            <input type="email" class="form-control" name="email" value="<?php echo $v('email'); ?>">
        </div>
    </div>

</div>


<!-- ====== STEP 2: Family Background ====== -->
<div class="step-content" id="step2" style="display:none;">
    <div class="form-section-title"><i class="fas fa-heart"></i> Spouse Information</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Surname</label>
            <input type="text" class="form-control" name="spouse_surname" value="<?php echo $v('spouse_surname'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="spouse_first_name"
                value="<?php echo $v('spouse_first_name'); ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="spouse_middle_name"
                value="<?php echo $v('spouse_middle_name'); ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Ext.</label>
            <input type="text" class="form-control" name="spouse_name_ext" value="<?php echo $v('spouse_name_ext'); ?>"
                placeholder="JR, SR">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Occupation</label>
            <input type="text" class="form-control" name="spouse_occupation"
                value="<?php echo $v('spouse_occupation'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-male"></i> Father's Information</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Surname</label>
            <input type="text" class="form-control" name="father_surname" value="<?php echo $v('father_surname'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="father_first_name"
                value="<?php echo $v('father_first_name'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="father_middle_name"
                value="<?php echo $v('father_middle_name'); ?>">
        </div>
        <div class="col-md-1 mb-3">
            <label class="form-label">Ext.</label>
            <input type="text" class="form-control" name="father_name_ext" value="<?php echo $v('father_name_ext'); ?>">
        </div>
        <div class="col-md-2 mb-3">
            <label class="form-label">Occupation</label>
            <input type="text" class="form-control" name="father_occupation"
                value="<?php echo $v('father_occupation'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-female"></i> Mother's Maiden Name</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Maiden Surname</label>
            <input type="text" class="form-control" name="mother_maiden_surname"
                value="<?php echo $v('mother_maiden_surname'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">First Name</label>
            <input type="text" class="form-control" name="mother_first_name"
                value="<?php echo $v('mother_first_name'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Middle Name</label>
            <input type="text" class="form-control" name="mother_middle_name"
                value="<?php echo $v('mother_middle_name'); ?>">
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Occupation</label>
            <input type="text" class="form-control" name="mother_occupation"
                value="<?php echo $v('mother_occupation'); ?>">
        </div>
    </div>

    <div class="form-section-title mt-3"><i class="fas fa-child"></i> Children</div>
    <div id="childrenContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeChildren)): ?>
            <?php foreach ($employeeChildren as $i => $child): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="child_surname[]" value="<?php echo e($child['surname']); ?>" placeholder="Surname"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="child_first_name[]" value="<?php echo e($child['first_name']); ?>"
                                placeholder="First Name"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="child_middle_name[]" value="<?php echo e($child['middle_name']); ?>"
                                placeholder="Middle Name"></div>
                        <div class="col-md-3 mb-2"><input type="date" class="form-control form-control-sm" name="child_dob[]"
                                value="<?php echo e($child['date_of_birth']); ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addRepeaterRow('childrenContainer','child')"><i
            class="fas fa-plus me-1"></i> Add Child</button>

    <div class="form-section-title mt-3"><i class="fas fa-users"></i> Siblings</div>
    <div id="siblingsContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeSiblings)): ?>
            <?php foreach ($employeeSiblings as $i => $sib): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="sibling_surname[]" value="<?php echo e($sib['surname']); ?>" placeholder="Surname"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="sibling_first_name[]" value="<?php echo e($sib['first_name']); ?>"
                                placeholder="First Name"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="sibling_middle_name[]" value="<?php echo e($sib['middle_name']); ?>"
                                placeholder="Middle Name"></div>
                        <div class="col-md-3 mb-2"><input type="date" class="form-control form-control-sm" name="sibling_dob[]"
                                value="<?php echo e($sib['date_of_birth']); ?>"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addRepeaterRow('siblingsContainer','sibling')"><i
            class="fas fa-plus me-1"></i> Add Sibling</button>

</div>



<!-- ====== STEP 3: Educational Background ====== -->
<div class="step-content" id="step3" style="display:none;">
    <div class="form-section-title"><i class="fas fa-graduation-cap"></i> Educational Background</div>
    <div id="educationContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeEducation)): ?>
            <?php foreach ($employeeEducation as $edu): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-2 mb-2"><select class="form-select form-select-sm" name="edu_level[]">
                                <option value="Elementary" <?php echo $edu['education_level'] === 'Elementary' ? 'selected' : ''; ?>>
                                    Elementary</option>
                                <option value="Secondary" <?php echo $edu['education_level'] === 'Secondary' ? 'selected' : ''; ?>>
                                    Secondary / Junior High</option>
                                <option value="Senior High School" <?php echo $edu['education_level'] === 'Senior High School' ? 'selected' : ''; ?>>
                                    Senior High School</option>
                                <option value="Vocational" <?php echo $edu['education_level'] === 'Vocational' ? 'selected' : ''; ?>>
                                    Vocational / Trade Course</option>
                                <option value="College" <?php echo $edu['education_level'] === 'College' ? 'selected' : ''; ?>>College
                                </option>
                                <option value="Graduate Studies" <?php echo $edu['education_level'] === 'Graduate Studies' ? 'selected' : ''; ?>>Graduate Studies</option>
                            </select></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_school[]"
                                value="<?php echo e($edu['school_name']); ?>" placeholder="School Name"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_degree[]"
                                value="<?php echo e($edu['degree_course']); ?>" placeholder="Degree/Course"></div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">From (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="edu_from[]"
                                value="<?php echo !empty($edu['period_from']) ? date('Y', strtotime($edu['period_from'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">To (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="edu_to[]"
                                value="<?php echo !empty($edu['period_to']) ? date('Y', strtotime($edu['period_to'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="edu_units[]"
                                value="<?php echo e($edu['highest_level_units']); ?>" placeholder="Highest Level/Units"></div>
                        <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm"
                                name="edu_year_grad[]" value="<?php echo e($edu['year_graduated']); ?>" placeholder="Year Grad">
                        </div>
                        <div class="col-md-12 mb-2">
                            <label class="small text-muted d-block">Honors / Awards / Distinctions Received</label>
                            <textarea class="form-control form-control-sm" name="edu_honors[]" rows="2" placeholder="List honors received (e.g., Cum Laude, Dean's List, etc.)"><?php echo e($edu['honors_received']); ?></textarea>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addEducationRow()"><i class="fas fa-plus me-1"></i> Add
        Education Entry</button>
</div>



<!-- ====== STEP 4: Work Experience ====== -->
<div class="step-content" id="step4" style="display:none;">
    <div class="form-section-title"><i class="fas fa-briefcase"></i> Work Experience</div>
    <div id="workContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeWork)): ?>
            <?php foreach ($employeeWork as $w): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">Start (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="work_from[]"
                                value="<?php echo !empty($w['date_from']) ? date('Y', strtotime($w['date_from'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">End (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="work_to[]"
                                value="<?php echo !empty($w['date_to']) ? date('Y', strtotime($w['date_to'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_title[]"
                                value="<?php echo e($w['job_title']); ?>" placeholder="Job Title"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_company[]"
                                value="<?php echo e($w['company_name']); ?>" placeholder="Company"></div>
                        <div class="col-md-2 mb-2"><input type="number" step="0.01" class="form-control form-control-sm"
                                name="work_salary[]" value="<?php echo e($w['monthly_salary']); ?>" placeholder="Salary"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="work_status[]"
                                value="<?php echo e($w['appointment_status']); ?>" placeholder="Status"></div>
                        <div class="col-md-4 mb-2"><input type="text" class="form-control form-control-sm" name="work_reason[]"
                                value="<?php echo e($w['reason_for_leaving']); ?>" placeholder="Reason for Leaving"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addWorkRow()"><i class="fas fa-plus me-1"></i> Add Work
        Entry</button>
</div>


<!-- ====== STEP 5: Training Programs ====== -->
<div class="step-content" id="step5" style="display:none;">
    <div class="form-section-title"><i class="fas fa-chalkboard-teacher"></i> Training Programs Attended</div>
    <div id="trainingContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeTrainings)): ?>
            <?php foreach ($employeeTrainings as $t): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">Start (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="training_from[]"
                                value="<?php echo !empty($t['date_from']) ? date('Y', strtotime($t['date_from'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">End (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="training_to[]"
                                value="<?php echo !empty($t['date_to']) ? date('Y', strtotime($t['date_to'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="training_title[]" value="<?php echo e($t['training_title']); ?>"
                                placeholder="Training Title"></div>
                        <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm"
                                name="training_type[]" value="<?php echo e($t['training_type']); ?>" placeholder="Type"></div>
                        <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm"
                                name="training_hours[]" value="<?php echo e($t['no_of_hours']); ?>" placeholder="Hrs"></div>
                        <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm"
                                name="training_conducted[]" value="<?php echo e($t['conducted_by']); ?>"
                                placeholder="Conducted By"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addTrainingRow()"><i class="fas fa-plus me-1"></i> Add
        Training</button>
</div>


<!-- ====== STEP 6: Voluntary Work ====== -->
<div class="step-content" id="step6" style="display:none;">
    <div class="form-section-title"><i class="fas fa-hands-helping"></i> Voluntary Work / Civic Involvement</div>
    <div id="voluntaryContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeVoluntary)): ?>
            <?php foreach ($employeeVoluntary as $vol): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">Start (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="vol_from[]"
                                value="<?php echo !empty($vol['date_from']) ? date('Y', strtotime($vol['date_from'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">End (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="vol_to[]"
                                value="<?php echo !empty($vol['date_to']) ? date('Y', strtotime($vol['date_to'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_org[]"
                                value="<?php echo e($vol['organization_name']); ?>" placeholder="Organization"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_address[]"
                                value="<?php echo e($vol['organization_address']); ?>" placeholder="Address"></div>
                        <div class="col-md-1 mb-2"><input type="number" class="form-control form-control-sm" name="vol_hours[]"
                                value="<?php echo e($vol['no_of_hours']); ?>" placeholder="Hrs"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="vol_position[]"
                                value="<?php echo e($vol['position_nature']); ?>" placeholder="Position/Nature"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addVoluntaryRow()"><i class="fas fa-plus me-1"></i> Add
        Voluntary Work</button>
</div>


<!-- ====== STEP 7: Service Eligibility ====== -->
<div class="step-content" id="step7" style="display:none;">
    <div class="form-section-title"><i class="fas fa-certificate"></i> Service Eligibility / Licenses</div>
    <div id="eligibilityContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeEligibility)): ?>
            <?php foreach ($employeeEligibility as $el): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i
                            class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm" name="elig_title[]"
                                value="<?php echo e($el['license_title']); ?>" placeholder="License/Cert Title"></div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">Start (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="elig_from[]"
                                value="<?php echo !empty($el['date_from']) ? date('Y', strtotime($el['date_from'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2">
                            <label class="small text-muted d-block">End (Year)</label>
                            <input type="number" class="form-control form-control-sm" name="elig_to[]"
                                value="<?php echo !empty($el['date_to']) ? date('Y', strtotime($el['date_to'])) : ''; ?>" 
                                min="1900" max="2099" placeholder="Year">
                        </div>
                        <div class="col-md-2 mb-2"><input type="text" class="form-control form-control-sm" name="elig_number[]"
                                value="<?php echo e($el['license_number']); ?>" placeholder="License No."></div>
                        <div class="col-md-2 mb-2"><input type="date" class="form-control form-control-sm"
                                name="elig_exam_date[]" value="<?php echo e($el['date_of_exam']); ?>"></div>
                        <div class="col-md-3 mb-2"><input type="text" class="form-control form-control-sm"
                                name="elig_exam_place[]" value="<?php echo e($el['place_of_exam']); ?>"
                                placeholder="Place of Exam"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addEligibilityRow()"><i class="fas fa-plus me-1"></i> Add
        License/Eligibility</button>
</div>


<!-- ====== STEP 8: Skills, Recognition & Membership ====== -->
<div class="step-content" id="step8" style="display:none;">
    <div class="form-section-title"><i class="fas fa-star"></i> Special Skills & Hobbies</div>
    <div id="skillsContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeSkills)): ?>
            <?php foreach ($employeeSkills as $sk): ?>
                <div class="repeater-row"><button type="button" class="btn-remove-row"
                        onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button><input type="text"
                        class="form-control form-control-sm" name="skill_name[]" value="<?php echo e($sk['skill_name']); ?>">
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3"
        onclick="addSimpleRow('skillsContainer','skill_name','Skill or Hobby')"><i class="fas fa-plus me-1"></i> Add
        Skill</button>

    <div class="form-section-title mt-3"><i class="fas fa-award"></i> Non-Academic Distinctions / Recognition</div>
    <div id="recognitionsContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeRecognitions)): ?>
            <?php foreach ($employeeRecognitions as $rc): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <label class="small text-muted d-block">Award/Recognition Title</label>
                            <input type="text" class="form-control form-control-sm" name="recognition_title[]" value="<?php echo e($rc['recognition_title']); ?>" placeholder="Title">
                        </div>
                        <div class="col-md-5 mb-2">
                            <label class="small text-muted d-block">Issued By / Organization</label>
                            <input type="text" class="form-control form-control-sm" name="recognition_issued_by[]" value="<?php echo e($rc['issued_by'] ?? ''); ?>" placeholder="Organization">
                        </div>
                        <div class="col-md-3 mb-2">
                            <label class="small text-muted d-block">Date Received</label>
                            <input type="date" class="form-control form-control-sm" name="recognition_date[]" value="<?php echo e($rc['date_awarded'] ?? ''); ?>">
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addRecognitionRow()">
        <i class="fas fa-plus me-1"></i> Add Award/Recognition
    </button>

    <div class="form-section-title mt-3"><i class="fas fa-users-cog"></i> Membership in Organizations</div>
    <div id="membershipsContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeMemberships)): ?>
            <?php foreach ($employeeMemberships as $mb): ?>
                <div class="repeater-row"><button type="button" class="btn-remove-row"
                        onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button><input type="text"
                        class="form-control form-control-sm" name="membership_org[]"
                        value="<?php echo e($mb['organization_name']); ?>"></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3"
        onclick="addSimpleRow('membershipsContainer','membership_org','Organization Name')"><i
            class="fas fa-plus me-1"></i> Add Membership</button>

</div>


<!-- ====== STEP 9: Assets & Liabilities ====== -->
<div class="step-content" id="step9" style="display:none;">
    <div class="form-section-title"><i class="fas fa-building"></i> Real Properties</div>
    <div id="realPropContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeRealProps)): ?>
            <?php foreach ($employeeRealProps as $rp): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="rprop_desc[]" value="<?php echo e($rp['description']); ?>" placeholder="Description"></div>
                        <div class="col-md-2"><input type="text" class="form-control form-control-sm" name="rprop_kind[]" value="<?php echo e($rp['kind']); ?>" placeholder="Kind"></div>
                        <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="rprop_location[]" value="<?php echo e($rp['exact_location']); ?>" placeholder="Location"></div>
                        <div class="col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_assessed[]" value="<?php echo e($rp['assessed_value']); ?>" placeholder="Assessed Value"></div>
                        <div class="col-md-2"><input type="number" step="0.01" class="form-control form-control-sm" name="rprop_market[]" value="<?php echo e($rp['market_value']); ?>" placeholder="Market Value"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addRealPropertyRow()"><i class="fas fa-plus me-1"></i> Add
        Real Property</button>

    <div class="form-section-title mt-3"><i class="fas fa-car"></i> Personal Properties</div>
    <div id="personalPropContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeePersonalProps)): ?>
            <?php foreach ($employeePersonalProps as $pp): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-5"><input type="text" class="form-control form-control-sm" name="pprop_desc[]" value="<?php echo e($pp['description']); ?>" placeholder="Description"></div>
                        <div class="col-md-3"><input type="text" class="form-control form-control-sm" name="pprop_year[]" value="<?php echo e($pp['year_acquired']); ?>" placeholder="Year Acquired"></div>
                        <div class="col-md-4"><input type="number" step="0.01" class="form-control form-control-sm" name="pprop_cost[]" value="<?php echo e($pp['acquisition_cost']); ?>" placeholder="Acquisition Cost"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addPersonalPropertyRow()"><i class="fas fa-plus me-1"></i>
        Add Personal Property</button>

    <div class="form-section-title mt-3"><i class="fas fa-file-invoice-dollar"></i> Liabilities</div>
    <div id="liabilitiesContainer" class="repeater-accordion">
        <?php if ($isEdit && !empty($employeeLiabilities)): ?>
            <?php foreach ($employeeLiabilities as $lb): ?>
                <div class="repeater-row">
                    <button type="button" class="btn-remove-row" onclick="this.closest('.repeater-row').remove()"><i class="fas fa-times"></i></button>
                    <div class="row">
                        <div class="col-md-5"><input type="text" class="form-control form-control-sm" name="liab_nature[]" value="<?php echo e($lb['nature_of_liability']); ?>" placeholder="Nature of Liability"></div>
                        <div class="col-md-4"><input type="text" class="form-control form-control-sm" name="liab_creditor[]" value="<?php echo e($lb['creditor_name']); ?>" placeholder="Creditor"></div>
                        <div class="col-md-3"><input type="number" step="0.01" class="form-control form-control-sm" name="liab_balance[]" value="<?php echo e($lb['outstanding_balance']); ?>" placeholder="Balance"></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    <button type="button" class="btn-add-row mb-3" onclick="addLiabilityRow()"><i class="fas fa-plus me-1"></i> Add
        Liability</button>

</div>


<!-- ====== STEP 10: Other Information (Disclosures) ====== -->
<div class="step-content" id="step10" style="display:none;">
    <div class="form-section-title"><i class="fas fa-clipboard-list"></i> Employment-Related Disclosures</div>

    <?php
    $disclosures = [
        ['is_related_to_company', 'related_details', 'Are you related by consanguinity or affinity to any Raquel Pawnshop employee within the third degree?'],
        ['has_admin_offense', 'admin_offense_details', 'Have you ever been found guilty of any administrative offense?'],
        ['has_criminal_charge', 'criminal_charge_details', 'Have you been criminally charged before any court?'],
        ['has_criminal_conviction', 'criminal_conviction_details', 'Have you ever been convicted of any crime or violation of law?'],
        ['has_been_separated', 'separation_details', 'Have you ever been separated from service (resignation, retirement, termination)?'],
    ];
    foreach ($disclosures as $d):
        ?>
        <div class="disclosure-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="<?php echo $d[0]; ?>" id="<?php echo $d[0]; ?>" <?php echo $chk($d[0]); ?> onchange="toggleDetails(this,'<?php echo $d[1]; ?>_div')">
                <label class="form-check-label" for="<?php echo $d[0]; ?>"><?php echo $d[2]; ?></label>
            </div>
            <div class="disclosure-details <?php echo !empty($e[$d[0]]) ? 'show' : ''; ?>" id="<?php echo $d[1]; ?>_div">
                <textarea class="form-control form-control-sm" name="<?php echo $d[1]; ?>" rows="2"
                    placeholder="Provide details..."><?php echo $v($d[1]); ?></textarea>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="form-section-title mt-3"><i class="fas fa-hand-holding-heart"></i> Special Considerations</div>
    <?php
    $specials = [
        ['is_pwd', 'pwd_details', 'Are you a person with disability (PWD)?'],
        ['is_solo_parent', 'solo_parent_details', 'Are you a solo parent?'],
    ];
    foreach ($specials as $d):
        ?>
        <div class="disclosure-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="<?php echo $d[0]; ?>" id="<?php echo $d[0]; ?>" <?php echo $chk($d[0]); ?> onchange="toggleDetails(this,'<?php echo $d[1]; ?>_div')">
                <label class="form-check-label" for="<?php echo $d[0]; ?>"><?php echo $d[2]; ?></label>
            </div>
            <div class="disclosure-details <?php echo !empty($e[$d[0]]) ? 'show' : ''; ?>" id="<?php echo $d[1]; ?>_div">
                <textarea class="form-control form-control-sm" name="<?php echo $d[1]; ?>" rows="2"
                    placeholder="Provide details..."><?php echo $v($d[1]); ?></textarea>
            </div>
        </div>
    <?php endforeach; ?>

    <div class="form-section-title mt-3"><i class="fas fa-heartbeat"></i> Health Information</div>
    <?php
    $health = [
        ['has_recent_hospital', 'hospital_details', 'Have you been hospitalized in the last 6 months?'],
        ['has_current_treatment', 'treatment_details', 'Are you currently undergoing medication or treatment?'],
    ];
    foreach ($health as $d):
        ?>
        <div class="disclosure-item">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="<?php echo $d[0]; ?>" id="<?php echo $d[0]; ?>" <?php echo $chk($d[0]); ?> onchange="toggleDetails(this,'<?php echo $d[1]; ?>_div')">
                <label class="form-check-label" for="<?php echo $d[0]; ?>"><?php echo $d[2]; ?></label>
            </div>
            <div class="disclosure-details <?php echo !empty($e[$d[0]]) ? 'show' : ''; ?>" id="<?php echo $d[1]; ?>_div">
                <textarea class="form-control form-control-sm" name="<?php echo $d[1]; ?>" rows="2"
                    placeholder="Provide details..."><?php echo $v($d[1]); ?></textarea>
            </div>
        </div>
    <?php endforeach; ?>

</div>


<!-- ====== STEP 11: References ====== -->
<div class="step-content" id="step11" style="display:none;">
    <div class="form-section-title"><i class="fas fa-address-book"></i> Character References (3 persons not related)
    </div>
    <?php for ($r = 0; $r < 3; $r++): ?>
        <div class="repeater-row">
            <div class="row">
                <div class="col-md-4 mb-2">
                    <label class="form-label">Full Name</label>
                    <input type="text" class="form-control form-control-sm" name="ref_name[]"
                        value="<?php echo isset($employeeRefs[$r]) ? e($employeeRefs[$r]['reference_name']) : ''; ?>">
                </div>
                <div class="col-md-5 mb-2">
                    <label class="form-label">Address</label>
                    <input type="text" class="form-control form-control-sm" name="ref_address[]"
                        value="<?php echo isset($employeeRefs[$r]) ? e($employeeRefs[$r]['reference_address']) : ''; ?>">
                </div>
                <div class="col-md-3 mb-2">
                    <label class="form-label">Mobile Number</label>
                    <input type="text" class="form-control form-control-sm" name="ref_telephone[]" maxlength="11"
                        value="<?php echo isset($employeeRefs[$r]) ? e($employeeRefs[$r]['reference_telephone']) : ''; ?>" inputmode="numeric">
                </div>
            </div>
        </div>
    <?php endfor; ?>

</div>


<!-- ====== STEP 12: Employment & Submit ====== -->
<div class="step-content" id="step12" style="display:none;">
    <div class="form-section-title"><i class="fas fa-building"></i> Employment Details</div>
    <div class="row">
        <div class="col-md-3 mb-3">
            <label class="form-label">Employee ID (Company ID)</label>
            <input type="text" class="form-control" name="employee_code" value="<?php echo $v('employee_code'); ?>" placeholder="e.g. 026-001">
            <small class="text-muted">Optional: Official company issued ID.</small>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Hire Date <span class="text-danger">*</span></label>
            <input type="date" class="form-control" name="hire_date" value="<?php echo $v('hire_date'); ?>" required>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Department <span class="text-danger">*</span></label>
            <?php if (!empty($departments) && is_array($departments)): ?>
                <select class="form-select" name="department_id" id="department_id" required>
                    <option value="" data-name="">-- Select Department --</option>
                    <?php foreach ($departments as $dept): ?>
                        <option value="<?php echo $dept['department_id']; ?>" data-name="<?php echo e($dept['department_name']); ?>" <?php echo (($e['department_id'] ?? '') == $dept['department_id']) ? 'selected' : ''; ?>>
                            <?php echo e($dept['department_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php else: ?>
                <input type="text" class="form-control" name="department_id" value="<?php echo $v('department_id'); ?>" required>
                <small class="text-muted">No departments defined yet. <a
                        href="<?php echo BASE_URL; ?>/manager/departments.php" target="_blank">Add departments</a></small>
            <?php endif; ?>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">Job Title <span class="text-danger">*</span></label>
            <?php if (!empty($jobTitles) && is_array($jobTitles)): ?>
                <select class="form-select" name="job_title_id" id="job_title_id" required>
                    <option value="">-- Select Job Title --</option>
                    <?php foreach ($jobTitles as $jt): ?>
                        <?php
                        $jt_id = (int) ($jt['job_title_id'] ?? 0);
                        $selected = false;
                        if (!empty($e['job_title_id'])) {
                            $selected = (int) $e['job_title_id'] === $jt_id;
                        } else {
                            $selected = (($e['job_title'] ?? '') === ($jt['job_title'] ?? ''));
                        }
                        ?>
                        <option value="<?php echo $jt_id; ?>"
                            data-title="<?php echo e($jt['job_title']); ?>"
                            data-dept-id="<?php echo (int) ($jt['department_id'] ?? 0); ?>"
                            <?php
                            $group = 'other';
                            if ((int)($jt['is_head'] ?? 0) === 1) {
                                $group = 'head';
                            } elseif ((int)($jt['rank_category_id'] ?? 5) < 5) {
                                $group = 'direct';
                            }
                            ?>
                            data-position-group="<?php echo $group; ?>"
                            <?php echo $selected ? 'selected' : ''; ?>>
                            <?php echo e($jt['job_title']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'HR Manager'): ?>
                    <small class="text-muted">Missing a title? <a href="<?php echo BASE_URL; ?>/manager/positions.php" target="_blank">Manage positions</a></small>
                <?php endif; ?>
            <?php else: ?>
                <input type="text" class="form-control" name="job_title" value="<?php echo $v('job_title'); ?>" required>
                <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'HR Manager'): ?>
                    <small class="text-muted">No positions defined yet. <a href="<?php echo BASE_URL; ?>/manager/positions.php" target="_blank">Add positions</a></small>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <div class="col-md-3 mb-3">
            <label class="form-label">RANK</label>
            <select class="form-select" name="rank_category_id" id="rank_category_id">
                <option value="">Select Rank</option>
                <?php foreach ($rankCategories as $rank): ?>
                    <option value="<?php echo (int) $rank['rank_category_id']; ?>" <?php echo (($e['rank_category_id'] ?? '') == $rank['rank_category_id']) ? 'selected' : ''; ?>>
                        <?php echo e($rank['rank_name']); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Branch</label>
            <select class="form-select" name="branch_id">
                <option value="">Select Branch</option>
                <?php
                if ($branches) {
                    $branches->data_seek(0);
                }
                while ($branches && $branch = $branches->fetch_assoc()): ?>
                    <option value="<?php echo $branch['branch_id']; ?>" <?php echo (($e['branch_id'] ?? '') == $branch['branch_id']) ? 'selected' : ''; ?>>
                        <?php echo e($branch['branch_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Employment Status</label>
            <?php
            $employmentStatuses = ['OJT', 'Probationary', 'Project Based', 'Project-Based', 'Regular', 'Separated', 'Trainee', 'AWOL', 'Retirement', 'Death', 'Permanent of Total Disability', 'Resignation', 'Failed in Training', 'Termination for Cause'];
            $employmentStatusValue = $e['employment_status'] ?? 'Regular';
            ?>
            <select class="form-select" name="employment_status">
                <?php foreach ($employmentStatuses as $status): ?>
                    <option value="<?php echo e($status); ?>" <?php echo $employmentStatusValue === $status ? 'selected' : ''; ?>>
                        <?php echo e($status); ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Employment Type</label>
            <select class="form-select" name="employment_type">
                <option value="Full-time" <?php echo $sel('employment_type', 'Full-time'); ?>>Full-time</option>
                <option value="Part-time" <?php echo $sel('employment_type', 'Part-time'); ?>>Part-time</option>
            </select>
        </div>
    </div>

    <!-- Contract Dates (Visible for temporary employment statuses) -->
    <div class="row" id="contractDatesRow" style="display: <?php echo in_array(($e['employment_status'] ?? 'Regular'), ['OJT', 'Probationary', 'Project Based', 'Project-Based', 'Trainee'], true) ? 'flex' : 'none'; ?>;">
        <div class="col-md-4 mb-3">
            <label class="form-label">Date Start</label>
            <input type="date" class="form-control" name="contract_start_date" value="<?php echo $v('contract_start_date'); ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Date Ended</label>
            <input type="date" class="form-control" name="contract_end_date" value="<?php echo $v('contract_end_date'); ?>">
        </div>
    </div>

    <?php if ($isEdit): ?>
        <div class="row">
            <?php if ($_SESSION['role'] === 'HR Supervisor'): ?>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Employee Record Status</label>
                    <div>
                        <span class="badge <?php echo !empty($e['is_active']) ? 'bg-success' : 'bg-danger'; ?> px-3 py-2">
                            <?php echo !empty($e['is_active']) ? 'Active Employee' : 'Inactive Employee'; ?>
                        </span>
                    </div>
                    <small class="text-muted">Activation changes are handled by HR Manager.</small>
                </div>
            <?php else: ?>
                <div class="col-md-4 mb-3">
                    <div class="form-check form-switch mt-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive" <?php echo $chk('is_active'); ?>>
                        <label class="form-check-label" for="isActive">Active Employee</label>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <div class="form-section-title mt-3"><i class="fas fa-heartbeat"></i> Emergency Contact</div>
    <div class="row">
        <div class="col-md-4 mb-3">
            <label class="form-label">Contact Name</label>
            <input type="text" class="form-control" name="emergency_contact_name"
                value="<?php echo $v('emergency_contact_name'); ?>">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Relationship</label>
            <input type="text" class="form-control" name="emergency_contact_relationship"
                value="<?php echo $v('emergency_contact_relationship'); ?>" placeholder="e.g. Spouse, Parent">
        </div>
        <div class="col-md-4 mb-3">
            <label class="form-label">Contact Number</label>
            <input type="text" class="form-control" name="emergency_contact_number"
                value="<?php echo $v('emergency_contact_number'); ?>"
                placeholder="09171234567" pattern="\d{11}" title="Format: 11 digits (e.g. 09171234567)" inputmode="numeric">
        </div>

        <!-- Summary Cards (populated dynamically by updatePDSSummary() on showStep(12)) -->
        <div class="row mt-4 pt-3 border-top">
            <div class="col-md-12">
                <h5 class="fw-bold text-primary mb-3"><i class="fas fa-clipboard-list me-2"></i>PDS Summary Review</h5>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border border-light shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark"><i class="fas fa-user-circle me-2 text-primary"></i>Core Identity</span>
                                <button type="button" onclick="showStep(1)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Full Name:</div><div class="col-7 fw-bold" id="sum-name">--</div>
                                    <div class="col-5 text-muted">Date of Birth:</div><div class="col-7 fw-bold" id="sum-dob">--</div>
                                    <div class="col-5 text-muted">Gender:</div><div class="col-7 fw-bold" id="sum-gender">--</div>
                                    <div class="col-5 text-muted">Civil Status:</div><div class="col-7 fw-bold" id="sum-civil">--</div>
                                    <div class="col-5 text-muted">Mobile No:</div><div class="col-7 fw-bold" id="sum-mobile">--</div>
                                    <div class="col-5 text-muted">Email Address:</div><div class="col-7 fw-bold text-truncate" id="sum-email">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border border-light shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark"><i class="fas fa-id-card me-2 text-primary"></i>Government IDs</span>
                                <button type="button" onclick="showStep(1)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">SSS No:</div><div class="col-7 fw-bold" id="sum-sss">--</div>
                                    <div class="col-5 text-muted">PhilHealth No:</div><div class="col-7 fw-bold" id="sum-philhealth">--</div>
                                    <div class="col-5 text-muted">Pag-IBIG No:</div><div class="col-7 fw-bold" id="sum-pagibig">--</div>
                                    <div class="col-5 text-muted">TIN No:</div><div class="col-7 fw-bold" id="sum-tin">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border border-light shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark"><i class="fas fa-history me-2 text-primary"></i>Background</span>
                                <button type="button" onclick="showStep(2)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Children:</div><div class="col-7 fw-bold" id="sum-children">--</div>
                                    <div class="col-5 text-muted">Siblings:</div><div class="col-7 fw-bold" id="sum-siblings">--</div>
                                    <div class="col-5 text-muted">Education:</div><div class="col-7 fw-bold" id="sum-education">--</div>
                                    <div class="col-5 text-muted">Work History:</div><div class="col-7 fw-bold" id="sum-work">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <div class="card h-100 border border-light shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark"><i class="fas fa-certificate me-2 text-primary"></i>Qualifications</span>
                                <button type="button" onclick="showStep(7)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Eligibility/PRC:</div><div class="col-7 fw-bold" id="sum-eligibility">--</div>
                                    <div class="col-5 text-muted">Skills:</div><div class="col-7 fw-bold" id="sum-skills">--</div>
                                    <div class="col-5 text-muted">Recognitions:</div><div class="col-7 fw-bold" id="sum-recognitions">--</div>
                                    <div class="col-5 text-muted">Properties/Assets:</div><div class="col-7 fw-bold" id="sum-properties">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-12 mb-3">
                        <div class="card border border-light shadow-sm">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold text-dark"><i class="fas fa-exclamation-triangle me-2 text-primary"></i>Disclosures & Declarations</span>
                                <button type="button" onclick="showStep(10)" class="btn btn-sm btn-link text-decoration-none p-0"><i class="fas fa-edit me-1"></i>Edit</button>
                            </div>
                            <div class="card-body py-3">
                                <div class="row g-2 small">
                                    <div class="col-5 text-muted">Active Declarations:</div><div class="col-7 fw-bold" id="sum-disclosures">--</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const deptSelect    = document.getElementById("department_id");
    const jobTitleSelect = document.getElementById("job_title_id");

    if (!deptSelect || !jobTitleSelect || jobTitleSelect.tagName !== 'SELECT') return;

    // Snapshot all job-title options on page load (before any filtering).
    // Each entry stores value, display text, and the department_id from the DB.
    const allJobTitleOptions = Array.from(jobTitleSelect.options)
        .filter(opt => opt.value !== "")
        .map(opt => ({
            value:  opt.value,
            text:   opt.text.trim(),
            title:  (opt.getAttribute("data-title") || opt.textContent || "").trim(),
            deptId: parseInt(opt.getAttribute("data-dept-id") || "0", 10),
            group:  opt.getAttribute("data-position-group") || "other"
        }));

    // Remember the job title that was pre-selected (edit mode).
    const initialJobTitle = "<?php echo (string) ($e['job_title_id'] ?? ''); ?>";

    function appendJobTitleOption(jobTitle, currentJobTitle) {
        const opt = document.createElement("option");
        opt.value = jobTitle.value;
        opt.textContent = jobTitle.text;
        opt.setAttribute("data-title", jobTitle.title || jobTitle.text);
        opt.setAttribute("data-dept-id", jobTitle.deptId);
        opt.setAttribute("data-position-group", jobTitle.group);
        if (jobTitle.value === currentJobTitle) {
            opt.selected = true;
        }
        jobTitleSelect.appendChild(opt);
    }

    function appendGroupLabel(label) {
        const opt = document.createElement("option");
        opt.value = "";
        opt.textContent = label;
        opt.disabled = true;
        opt.className = "position-group-label";
        jobTitleSelect.appendChild(opt);
    }

    function updateJobTitles() {
        const selectedDeptId = parseInt(deptSelect.value || "0", 10);
        const currentJobTitle = jobTitleSelect.value || initialJobTitle;

        // Rebuild the list
        jobTitleSelect.innerHTML = "";

        if (!selectedDeptId) {
            jobTitleSelect.innerHTML = '<option value="">-- Select Department First --</option>';
            jobTitleSelect.disabled = true;
            return;
        }

        jobTitleSelect.disabled = false;
        jobTitleSelect.innerHTML = '<option value="">-- Select Job Title --</option>';

        const matchingTitles = allJobTitleOptions.filter(jobTitle => jobTitle.deptId === selectedDeptId);
        const headTitles = matchingTitles.filter(jobTitle => jobTitle.group === "head");
        const directTitles = matchingTitles.filter(jobTitle => jobTitle.group === "direct");
        const otherTitles = matchingTitles.filter(jobTitle => jobTitle.group === "other");

        if (headTitles.length) {
            appendGroupLabel("-- HEAD --");
            headTitles.forEach(jobTitle => {
                appendJobTitleOption(jobTitle, currentJobTitle);
            });
        }

        if (directTitles.length) {
            appendGroupLabel("-- DIRECT REPORT --");
            directTitles.forEach(jobTitle => {
                appendJobTitleOption(jobTitle, currentJobTitle);
            });
        }

        if (otherTitles.length) {
            appendGroupLabel("-- OTHERS --");
            otherTitles.forEach(jobTitle => {
                appendJobTitleOption(jobTitle, currentJobTitle);
            });
        }

        if (!matchingTitles.length) {
            const opt = document.createElement("option");
            opt.value = "";
            opt.textContent = "No positions found for this department";
            opt.disabled = true;
            jobTitleSelect.appendChild(opt);
        }

        // If the previously-selected title is no longer visible, clear the field.
        if (jobTitleSelect.value !== currentJobTitle) {
            jobTitleSelect.value = "";
        }
    }

    deptSelect.addEventListener("change", updateJobTitles);

    // Run once on load so the list is correct in edit mode.
    updateJobTitles();
});
</script>
