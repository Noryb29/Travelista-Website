// Global variables
let currentSection = new URLSearchParams(window.location.search).get('section') || 'dashboard';

// Initialize the admin panel
document.addEventListener('DOMContentLoaded', function() {
    loadSectionData(currentSection);
    setupEventListeners();
});

// Setup event listeners
function setupEventListeners() {
    // Add event listeners for modals
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('close-modal')) {
            closeModal();
        }
    });

    // Close modal when clicking outside
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('modal')) {
            closeModal();
        }
    });
}

// Load section data
function loadSectionData(section) {
    const contentArea = document.querySelector('.admin-content');
    contentArea.classList.add('loading');

    fetch(`../controller/admin_controller.php?section=${section}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                updateContent(section, data.data);
            } else {
                showError(data.message);
            }
        })
        .catch(error => {
            showError('Failed to load data. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            contentArea.classList.remove('loading');
        });
}

// Update content based on section
function updateContent(section, data) {
    const tbody = document.querySelector('.data-table tbody');
    if (!tbody) return;

    tbody.innerHTML = '';
    
    switch(section) {
        case 'users':
            data.forEach(user => {
                tbody.innerHTML += `
                    <tr>
                        <td>${user.user_id}</td>
                        <td>${user.username}</td>
                        <td>${user.firstname} ${user.lastname}</td>
                        <td>${user.email}</td>
                        <td>${user.is_admin ? 'Admin' : 'User'}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editUser(${user.user_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteUser(${user.user_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            break;

        case 'destinations':
            data.forEach(destination => {
                tbody.innerHTML += `
                    <tr>
                        <td>${destination.destination_id}</td>
                        <td>${destination.destination_name}</td>
                        <td>${destination.destination_desc}</td>
                        <td>${new Date(destination.created_at).toLocaleDateString()}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editDestination(${destination.destination_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteDestination(${destination.destination_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            break;

        case 'hotels':
            data.forEach(hotel => {
                tbody.innerHTML += `
                    <tr>
                        <td>${hotel.hotel_id}</td>
                        <td>${hotel.hotel_name}</td>
                        <td>${hotel.hotel_location}</td>
                        <td>${'★'.repeat(hotel.star_rating)}</td>
                        <td>$${hotel.price}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editHotel(${hotel.hotel_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteHotel(${hotel.hotel_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            break;

        case 'travel-types':
            data.forEach(type => {
                tbody.innerHTML += `
                    <tr>
                        <td>${type.travel_type_id}</td>
                        <td>${type.travel_type_name}</td>
                        <td>$${type.travel_type_price}</td>
                        <td>
                            <button class="action-btn edit-btn" onclick="editTravelType(${type.travel_type_id})">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteTravelType(${type.travel_type_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            break;

        case 'bookings':
            data.forEach(booking => {
                tbody.innerHTML += `
                    <tr>
                        <td>${booking.booking_id}</td>
                        <td>${booking.username}</td>
                        <td>${booking.destination_name}</td>
                        <td>${booking.hotel_name}</td>
                        <td>${booking.travel_type_name}</td>
                        <td>${new Date(booking.departure_date).toLocaleDateString()} - ${new Date(booking.return_date).toLocaleDateString()}</td>
                        <td>${booking.status}</td>
                        <td>
                            <button class="action-btn view-btn" onclick="viewBooking(${booking.booking_id})">
                                <i class="fas fa-eye"></i>
                            </button>
                            <button class="action-btn delete-btn" onclick="deleteBooking(${booking.booking_id})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });
            break;
    }
}

// Modal functions
function showModal(title, content) {
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = `
        <div class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>${title}</h2>
                    <button class="close-modal">&times;</button>
                </div>
                <div class="modal-body">
                    ${content}
                </div>
            </div>
        </div>
    `;
}

function closeModal() {
    const modalContainer = document.getElementById('modalContainer');
    modalContainer.innerHTML = '';
}

// Add/Edit functions
function showAddUserModal() {
    const content = `
        <form id="addUserForm" onsubmit="handleAddUser(event)">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" required>
            </div>
            <div class="form-group">
                <label for="is_admin">Role</label>
                <select id="is_admin" name="is_admin" class="form-control">
                    <option value="0">User</option>
                    <option value="1">Admin</option>
                </select>
            </div>
            <button type="submit" class="btn-primary">Add User</button>
        </form>
    `;
    showModal('Add New User', content);
}

// Handle form submissions
async function handleAddUser(event) {
    event.preventDefault();
    const form = event.target;
    const formData = new FormData(form);

    try {
        const response = await fetch('../controller/admin_controller.php', {
            method: 'POST',
            body: formData
        });
        const data = await response.json();

        if (data.success) {
            showSuccess('User added successfully');
            closeModal();
            loadSectionData('users');
        } else {
            showError(data.message);
        }
    } catch (error) {
        showError('Failed to add user. Please try again.');
        console.error('Error:', error);
    }
}

// Delete functions
function deleteUser(userId) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#6B73FF',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`../controller/admin_controller.php?action=delete&type=user&id=${userId}`, {
                method: 'DELETE'
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showSuccess('User deleted successfully');
                    loadSectionData('users');
                } else {
                    showError(data.message);
                }
            })
            .catch(error => {
                showError('Failed to delete user. Please try again.');
                console.error('Error:', error);
            });
        }
    });
}

// Utility functions
function showSuccess(message) {
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: message,
        confirmButtonColor: '#6B73FF'
    });
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Oops...',
        text: message,
        confirmButtonColor: '#6B73FF'
    });
}

// Export functions for use in HTML
window.showAddUserModal = showAddUserModal;
window.deleteUser = deleteUser;
window.editUser = function(userId) {
    // Implement edit user functionality
};
window.deleteDestination = function(destinationId) {
    // Implement delete destination functionality
};
window.editDestination = function(destinationId) {
    // Implement edit destination functionality
};
window.deleteHotel = function(hotelId) {
    // Implement delete hotel functionality
};
window.editHotel = function(hotelId) {
    // Implement edit hotel functionality
};
window.deleteTravelType = function(typeId) {
    // Implement delete travel type functionality
};
window.editTravelType = function(typeId) {
    // Implement edit travel type functionality
};
window.viewBooking = function(bookingId) {
    // Implement view booking functionality
};
window.deleteBooking = function(bookingId) {
    // Implement delete booking functionality
}; 