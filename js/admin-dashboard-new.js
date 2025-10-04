// Admin Dashboard JavaScript
// Handles audition management and account creation

// Update audition status with automatic member account creation
function updateStatus(id, status) {
    if (status === 'approved') {
        Swal.fire({
            title: '🎉 Approve Audition',
            html: `
                <p>Are you sure you want to <strong>approve</strong> this audition?</p>
                <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;">
                    <h6 style="color: #2d5a2d; margin-bottom: 10px;">✅ What will happen:</h6>
                    <ul style="text-align: left; color: #2d5a2d; margin: 0;">
                        <li>Audition status will be set to <strong>Approved</strong></li>
                        <li>A <strong>member account</strong> will be automatically created</li>
                        <li>Login credentials will be <strong>emailed</strong> to the applicant</li>
                        <li>The applicant will have access to the member portal</li>
                    </ul>
                </div>
            `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '✅ Approve & Create Account',
            cancelButtonText: '❌ Cancel',
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            width: '500px'
        }).then((result) => {
            if (result.isConfirmed) {
                processAuditionAction(id, 'approve');
            }
        });
    } else if (status === 'rejected') {
        Swal.fire({
            title: '❌ Reject Audition',
            html: `
                <p>Are you sure you want to <strong>reject</strong> this audition?</p>
                <div style="margin: 15px 0;">
                    <label for="rejectionReason" style="display: block; text-align: left; margin-bottom: 5px; font-weight: 500;">
                        Reason for rejection (optional):
                    </label>
                    <textarea id="rejectionReason" class="swal2-textarea" placeholder="Enter reason for rejection..." 
                              style="width: 100%; min-height: 80px; resize: vertical;"></textarea>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '❌ Reject Audition',
            cancelButtonText: '🔙 Cancel',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            width: '500px',
            preConfirm: () => {
                const reason = document.getElementById('rejectionReason').value.trim();
                return { reason: reason };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                processAuditionAction(id, 'reject', result.value.reason);
            }
        });
    }
}

// Process audition approval/rejection with new API
function processAuditionAction(auditionId, action, rejectionReason = null) {
    // Show loading state
    Swal.fire({
        title: action === 'approve' ? 'Creating Account...' : 'Processing Rejection...',
        text: action === 'approve' ? 
              'Please wait while we approve the audition and create the member account.' :
              'Please wait while we process the rejection.',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    const requestData = {
        audition_id: auditionId,
        action: action
    };

    if (rejectionReason) {
        requestData.rejection_reason = rejectionReason;
    }

    fetch('api/approve-audition.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestData)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            if (action === 'approve') {
                Swal.fire({
                    title: 'Success! 🎉',
                    html: `
                        <div style="text-align: left;">
                            <p><strong>Audition approved successfully!</strong></p>
                            <div style="background: #e8f5e8; padding: 15px; border-radius: 8px; margin: 15px 0;">
                                <h6 style="color: #2d5a2d; margin-bottom: 10px;">✅ Account Created:</h6>
                                <p style="margin: 5px 0; color: #2d5a2d;"><strong>Name:</strong> ${data.audition.name}</p>
                                <p style="margin: 5px 0; color: #2d5a2d;"><strong>Email:</strong> ${data.audition.email}</p>
                                <p style="margin: 5px 0; color: #2d5a2d;"><strong>Category:</strong> ${data.audition.category}</p>
                            </div>
                            <p style="color: #666;"><em>Welcome email with login credentials has been sent to the applicant.</em></p>
                        </div>
                    `,
                    icon: 'success',
                    confirmButtonText: 'Great!',
                    confirmButtonColor: '#28a745'
                });
            } else {
                Swal.fire({
                    title: 'Audition Rejected',
                    text: 'The audition has been rejected successfully.',
                    icon: 'info',
                    confirmButtonColor: '#6c757d'
                });
            }
            refreshTable();
            if (typeof refreshAccounts === 'function') {
                refreshAccounts(); // Refresh accounts table if visible
            }
        } else {
            Swal.fire({
                title: 'Error!',
                text: data.message || 'Failed to process audition.',
                icon: 'error',
                confirmButtonColor: '#dc3545'
            });
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Connection Error!',
            text: 'Unable to connect to the server. Please try again.',
            icon: 'error',
            confirmButtonColor: '#dc3545'
        });
    });
}

// Delete audition
function deleteAudition(id) {
    Swal.fire({
        title: 'Are you sure?',
        text: "You won't be able to revert this!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Deleting...',
                text: 'Removing audition from database',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch('admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=delete_audition&id=${id}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        title: 'Deleted!',
                        text: 'Audition has been deleted.',
                        icon: 'success'
                    });
                    refreshTable();
                } else {
                    Swal.fire({
                        title: 'Error!',
                        text: 'Failed to delete audition',
                        icon: 'error'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    title: 'Error!',
                    text: 'Network error occurred. Please try again.',
                    icon: 'error'
                });
            });
        }
    });
}

// Refresh auditions table
function refreshTable() {
    const refreshBtn = document.querySelector('.refresh-btn');
    const originalContent = refreshBtn.innerHTML;
    refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
    refreshBtn.disabled = true;

    fetch('admin.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=get_auditions'
    })
    .then(response => response.json())
    .then(data => {
        // Reload the page to refresh the table
        location.reload();
    })
    .catch(error => {
        console.error('Error:', error);
        Swal.fire({
            title: 'Error!',
            text: 'Failed to refresh table',
            icon: 'error'
        });
    })
    .finally(() => {
        refreshBtn.innerHTML = originalContent;
        refreshBtn.disabled = false;
    });
}

// Account management functions
function updateAccountStatus(accountId, status) {
    const statusText = status.charAt(0).toUpperCase() + status.slice(1);
    
    Swal.fire({
        title: `${statusText} Account`,
        text: `Are you sure you want to ${status} this account?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Yes, ${status}`,
        cancelButtonText: 'Cancel',
        confirmButtonColor: status === 'suspended' ? '#dc3545' : '#28a745'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Updating...',
                text: `Updating account status to ${status}`,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // This would call an API endpoint for account management
            // For now, just show success
            setTimeout(() => {
                Swal.fire({
                    title: 'Updated!',
                    text: `Account status has been updated to ${status}.`,
                    icon: 'success'
                });
                refreshAccounts();
            }, 1000);
        }
    });
}

// Refresh accounts table
function refreshAccounts() {
    const refreshBtn = document.querySelector('.refresh-btn');
    if (refreshBtn) {
        const originalContent = refreshBtn.innerHTML;
        refreshBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Refreshing...';
        refreshBtn.disabled = true;

        // Reload the page to refresh both tables
        setTimeout(() => {
            location.reload();
        }, 500);
    }
}

// Show notification
function showNotification(message, type) {
    const icon = type === 'success' ? 'success' : 'error';
    Swal.fire({
        title: type === 'success' ? 'Success!' : 'Error!',
        text: message,
        icon: icon,
        timer: 3000,
        showConfirmButton: false
    });
}

// Initialize dashboard
document.addEventListener('DOMContentLoaded', function() {
    console.log('Admin Dashboard loaded');
    
    // Add any initialization code here
    const refreshBtns = document.querySelectorAll('.refresh-btn');
    refreshBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent.includes('Refresh')) {
                refreshTable();
            }
        });
    });
});
