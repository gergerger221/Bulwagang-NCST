// Quick script to create test user data for email-based login
// Run this in browser console or include in any page

function createTestUser() {
    const userData = {
        username: 'Admin',
        email: 'admin@bulwagang-ncst.com',
        password: 'admin123',
        role: 'admin',
        created: new Date().toISOString()
    };

    localStorage.setItem('userData', JSON.stringify(userData));
    
    console.log('✅ Test user created successfully!');
    console.log('📧 Email: admin@bulwagang-ncst.com');
    console.log('🔑 Password: admin123');
    console.log('👤 Username: Admin');
    
    return userData;
}

// Auto-create test user when script loads
createTestUser();

// Also create alternative test users
const alternativeUsers = [
    {
        username: 'Moderator',
        email: 'moderator@bulwagang-ncst.com', 
        password: 'mod123',
        role: 'moderator'
    },
    {
        username: 'Staff',
        email: 'staff@bulwagang-ncst.com',
        password: 'staff123', 
        role: 'staff'
    }
];

console.log('\n🎭 Alternative test users available:');
alternativeUsers.forEach(user => {
    console.log(`📧 ${user.email} | 🔑 ${user.password} | 👤 ${user.username}`);
});

console.log('\n🔧 To switch users, run:');
console.log('localStorage.setItem("userData", JSON.stringify({username: "Moderator", email: "moderator@bulwagang-ncst.com", password: "mod123"}));');
