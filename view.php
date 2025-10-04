<?php
// Set page-specific variables
$current_page = 'view';
$nav_type = 'bootstrap';

// Include head component
include 'includes/head.php';
?>

<!-- Overlay (fixed missing elements) -->
<div id="popupOverlay" class="popup-overlay"></div>
<div id="formOverlay" class="popup-overlay"></div>

<?php
// Include navigation component
include 'includes/navigation.php';
?>

<!-- Content -->
<div class="content container-fluid my-4">
    <div class="announcement-wrapper">
        <div class="announcement-list">
            <div class="announcement-item">
                <img src="asset/img/audition-15.jpg" alt="Announcement Image" class="img-fluid rounded shadow-sm">
            </div>
            <div class="announcement-item">
                <img src="asset/img/singer.jpg" alt="Announcement Image" class="img-fluid rounded shadow-sm">
            </div>
            <div class="announcement-item">
                <img src="asset/img/dancer.jpg" alt="Announcement Image" class="img-fluid rounded shadow-sm">
            </div>
        </div>
    </div>
</div>

<!-- Audition Form Popup -->
<div class="popup-container" id="formPopup">
    <div class="popup-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Audition Form</h2>
        <button class="close-btn btn btn-sm btn-outline-secondary">&times;</button>
    </div>
    <div class="scrollable-text">
        <form id="auditionForm">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="firstName" class="form-label">First Name:</label>
                    <input type="text" id="firstName" name="firstName" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="lastName" class="form-label">Last Name:</label>
                    <input type="text" id="lastName" name="lastName" required class="form-control">
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="birthDate" class="form-label">Birth Date:</label>
                    <input type="date" id="birthDate" name="birthDate" required class="form-control">
                </div>
                <div class="col-md-6 mb-3">
                    <label for="gender" class="form-label">Gender:</label>
                    <select id="gender" name="gender" required class="form-select">
                        <option value="">Select Gender</option>
                        <option value="male">Male</option>
                        <option value="female">Female</option>
                        <option value="other">Other</option>
                        <option value="prefer_not_to_say">Prefer not to say</option>
                    </select>
                </div>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email:</label>
                <input type="email" id="email" name="email" required class="form-control">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone Number:</label>
                <input type="tel" id="phone" name="phone" required class="form-control">
            </div>

            <div class="mb-3">
                <label for="category" class="form-label">Audition Category:</label>
                <select id="category" name="category" required class="form-select">
                    <option value="">Select Category</option>
                    <option value="singer">Singer</option>
                    <option value="dancer">Dancer</option>
                    <option value="solo-musician">Solo Musician</option>
                    <option value="band">Band</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="studentLevel" class="form-label">Student Level:</label>
                <select id="studentLevel" name="studentLevel" required class="form-select">
                    <option value="">Select Level</option>
                    <option value="shs">Senior High School</option>
                    <option value="college">College</option>
                    <option value="other">Other</option>
                </select>
            </div>

            <div class="mb-3" id="shsStrandWrapper" style="display:none;">
                <label for="shsStrand" class="form-label">SHS Strand:</label>
                <select id="shsStrand" name="shsStrand" class="form-select">
                    <option value="">Select SHS Strand</option>
                    <option value="ABM">ABM</option>
                    <option value="HUMSS">HUMSS</option>
                    <option value="STEM">STEM</option>
                    <option value="Culinary Arts (TVL-HE)">Culinary Arts (TVL-HE)</option>
                    <option value="Hotel & Restaurant Services (TVL-HE)">Hotel & Restaurant Services (TVL-HE)</option>
                </select>
            </div>

            <div class="mb-3" id="collegeCourseWrapper" style="display:none;">
                <label for="collegeCourse" class="form-label">College Course:</label>
                <select id="collegeCourse" name="collegeCourse" class="form-select">
                    <option value="">Select College Course</option>
                    <option value="ACT">ACT</option>
                    <option value="AOM">AOM</option>
                    <option value="AB Comm">AB Comm</option>
                    <option value="BSA">BSA</option>
                    <option value="BS Arch">BS Arch</option>
                    <option value="BSBA-FM">BSBA-FM</option>
                    <option value="BSBA-MM">BSBA-MM</option>
                    <option value="BSBA-OM">BSBA-OM</option>
                    <option value="BSCrim">BSCrim</option>
                    <option value="BSCA">BSCA</option>
                    <option value="BSCpE">BSCpE</option>
                    <option value="BSCS">BSCS</option>
                    <option value="BSECE">BSECE</option>
                    <option value="BSEntrep">BSEntrep</option>
                    <option value="BSHM">BSHM</option>
                    <option value="BSIE">BSIE</option>
                    <option value="BSISM">BSISM</option>
                    <option value="BSIT">BSIT</option>
                    <option value="BSMA">BSMA</option>
                    <option value="BSOA">BSOA</option>
                    <option value="BSPsy">BSPsy</option>
                    <option value="BSPA">BSPA</option>
                    <option value="BSREM">BSREM</option>
                    <option value="BSTM">BSTM</option>
                    <option value="BSEd-Eng">BSEd-Eng</option>
                    <option value="BSEd-Fil">BSEd-Fil</option>
                    <option value="BSEd-Math">BSEd-Math</option>
                    <option value="BSEd-SS">BSEd-SS</option>
                </select>
            </div>

            <div class="mb-3">
                <label for="details" class="form-label">Additional Details:</label>
                <textarea id="details" name="details" rows="4"
                    placeholder="Anything else you want us to know like Course and Year Level, etc."
                    class="form-control"></textarea>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-3">Submit</button>
        </form>
    </div>
</div>

<!-- About Popup -->
<div class="popup-container" id="aboutPopup">
    <div class="popup-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">About Us</h2>
        <button class="close-btn btn btn-sm btn-outline-secondary">&times;</button>
    </div>
    <img src="<?php echo $site_config['logo']; ?>" alt="About" class="popup-img">
    <div class="scrollable-text">
        <p>
            <?php echo $site_config['name']; ?> is the official student organization and cultural group
            at the National College of Science and Technology (NCST) located in
            Dasmariñas, Cavite, Philippines.
        </p>
        <p>
            All musicians, singers, and dancers are welcome. Come be a part of our
            talented and dynamic community.
        </p>
    </div>
</div>

<!-- Contact Popup -->
<div class="popup-container" id="contactPopup">
    <div class="popup-header d-flex justify-content-between align-items-center">
        <h2 class="m-0">Contact Us</h2>
        <button class="close-btn btn btn-sm btn-outline-secondary">&times;</button>
    </div>
    <img src="<?php echo $site_config['logo']; ?>" alt="Contact" class="popup-img">
    <div class="scrollable-text">
        <p>Feel free to reach out via Facebook or phone:</p>
        <p>
            Facebook:
            <a href="https://www.facebook.com/BulwagangNcst" target="_blank">https://www.facebook.com/BulwagangNcst</a>
        </p>
        <p>Phone: <a target="_blank">0909 435 5164</a></p>
    </div>
</div>

<?php
// Include footer component
include 'includes/footer.php';
?>