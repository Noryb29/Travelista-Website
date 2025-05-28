<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['user_id'])) {
    header('Location: ../auth/login.php');
    exit();
}

include '../config/config.php';

$user_id = $_SESSION['user_id'];

// Fetch user information
$user_query = "SELECT username, firstname, lastname, gender, email, profile_img FROM users WHERE user_id = :user_id";
$user_stmt = $pdo->prepare($user_query);
$user_stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$user_stmt->execute();
$user_info = $user_stmt->fetch(PDO::FETCH_ASSOC);

// Include header after session and database operations
include '../includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Profile - Travelista</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/profile.css">
    <link rel="icon" href="../assets/images/logo.png">

</head>
<body>

<div class="profile-container">
    <div class="profile-header">
        <div class="profile-picture-container">
            <?php if (!empty($user_info['profile_img'])): ?>
                <img src="data:image/jpeg;base64,<?php echo base64_encode($user_info['profile_img']); ?>" 
                     alt="Profile Picture" 
                     class="profile-picture"
                     onerror="this.onerror=null; this.src='../assets/images/default-profile.jpg';">
            <?php else: ?>
                <div class="profile-picture-placeholder">
                    <i class="fas fa-user"></i>
                </div>
            <?php endif; ?>
            <label for="profile_img" class="profile-picture-upload edit-mode-only" style="display: none;">
                <i class="fas fa-camera"></i>
            </label>
        </div>
        <h2><?php echo htmlspecialchars($user_info['firstname'] . ' ' . $user_info['lastname']); ?></h2>
        <p class="user-email"><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($user_info['email']); ?></p>
        <button id="editProfileBtn" class="btn-edit">
            <i class="fas fa-edit"></i> Edit Profile
        </button>
    </div>

    <form id="profileForm" class="profile-form" method="POST" action="../controller/update_profile_controller.php" enctype="multipart/form-data">
        <input type="file" id="profile_img" name="profile_img" accept="image/*" class="d-none">
        
        <div class="form-group">
            <label for="username"><i class="fas fa-user"></i> Username</label>
            <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_info['username']); ?>" required disabled>
        </div>

        <div class="form-group">
            <label for="firstname"><i class="fas fa-user"></i> First Name</label>
            <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user_info['firstname']); ?>" required disabled>
        </div>

        <div class="form-group">
            <label for="lastname"><i class="fas fa-user"></i> Last Name</label>
            <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user_info['lastname']); ?>" required disabled>
        </div>

        <div class="form-group">
            <label for="email"><i class="fas fa-envelope"></i> Email</label>
            <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user_info['email']); ?>" required disabled>
        </div>

        <div class="form-group">
            <label for="gender"><i class="fas fa-venus-mars"></i> Gender</label>
            <select id="gender" name="gender" required disabled>
                <option value="male" <?php echo $user_info['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                <option value="female" <?php echo $user_info['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                <option value="other" <?php echo $user_info['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
            </select>
        </div>

        <div class="form-actions edit-mode-only" style="display: none;">
            <button type="submit" class="btn-update">
                <i class="fas fa-save"></i> Save Changes
            </button>
            <button type="button" class="btn-cancel" id="cancelEditBtn">
                <i class="fas fa-times"></i> Cancel
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const editProfileBtn = document.getElementById('editProfileBtn');
    const cancelEditBtn = document.getElementById('cancelEditBtn');
    const form = document.getElementById('profileForm');
    const formInputs = form.querySelectorAll('input, select');
    const editModeElements = document.querySelectorAll('.edit-mode-only');
    const profileImgInput = document.getElementById('profile_img');
    const profilePicture = document.querySelector('.profile-picture');
    const profilePlaceholder = document.querySelector('.profile-picture-placeholder');

    // Function to toggle edit mode
    function toggleEditMode(isEditing) {
        formInputs.forEach(input => {
            input.disabled = !isEditing;
        });
        
        editModeElements.forEach(element => {
            element.style.display = isEditing ? 'block' : 'none';
        });

        editProfileBtn.style.display = isEditing ? 'none' : 'block';
    }

    // Edit Profile button click handler
    editProfileBtn.addEventListener('click', function() {
        toggleEditMode(true);
    });

    // Cancel Edit button click handler
    cancelEditBtn.addEventListener('click', function() {
        // Reset form values to original
        formInputs.forEach(input => {
            input.value = input.defaultValue;
        });
        toggleEditMode(false);
    });

    // Handle profile picture upload
    profileImgInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (profilePlaceholder) {
                    profilePlaceholder.style.display = 'none';
                }
                if (profilePicture) {
                    profilePicture.src = e.target.result;
                } else {
                    const newImg = document.createElement('img');
                    newImg.src = e.target.result;
                    newImg.alt = 'Profile Picture';
                    newImg.className = 'profile-picture';
                    document.querySelector('.profile-picture-container').insertBefore(
                        newImg,
                        document.querySelector('.profile-picture-upload')
                    );
                }
            }
            reader.readAsDataURL(file);
        }
    });

    // Form submission with loading state
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const submitBtn = this.querySelector('.btn-update');
        const originalText = submitBtn.innerHTML;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Updating...';
        submitBtn.disabled = true;

        const formData = new FormData(this);
        
        fetch('../controller/update_profile_controller.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    confirmButtonColor: '#6B73FF'
                }).then(() => {
                    // Update displayed name and email
                    document.querySelector('.profile-header h2').textContent = 
                        formData.get('firstname') + ' ' + formData.get('lastname');
                    document.querySelector('.user-email').innerHTML = 
                        '<i class="fas fa-envelope"></i> ' + formData.get('email');
                    
                    // Exit edit mode
                    toggleEditMode(false);
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: data.message,
                    confirmButtonColor: '#6B73FF'
                });
            }
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Something went wrong! Please try again later.',
                confirmButtonColor: '#6B73FF'
            });
        })
        .finally(() => {
            submitBtn.innerHTML = originalText;
            submitBtn.disabled = false;
        });
    });
});
</script>

<?php include '../includes/footer.php'; ?>
</body>
</html> 