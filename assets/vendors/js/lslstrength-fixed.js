// Password generation function - fixed version
function generatePassword() {
    const upperCase = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
    const lowerCase = "abcdefghijklmnopqrstuvwxyz";
    const numbers = "1234567890";
    const specialChars = "`,.~{}()[]/+_=-!@#$%^&*|\\'\":?";
    
    let password = [];
    
    // Add 1 uppercase letter
    password.push(upperCase[Math.floor(Math.random() * upperCase.length)]);
    
    // Add 2 lowercase letters
    for (let i = 0; i < 2; i++) {
        password.push(lowerCase[Math.floor(Math.random() * lowerCase.length)]);
    }
    
    // Add 2 numbers
    for (let i = 0; i < 2; i++) {
        password.push(numbers[Math.floor(Math.random() * numbers.length)]);
    }
    
    // Add 1 special character
    password.push(specialChars[Math.floor(Math.random() * specialChars.length)]);
    
    // Shuffle the array
    for (let i = password.length - 1; i > 0; i--) {
        const j = Math.floor(Math.random() * (i + 1));
        [password[i], password[j]] = [password[j], password[i]];
    }
    
    return password.join("");
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const generatePasswordBtn = document.querySelector("div.gen-pass");
    const inputPasswordField = document.querySelector("input.password");
    
    if (generatePasswordBtn && inputPasswordField) {
        generatePasswordBtn.addEventListener("click", function(e) {
            e.preventDefault();
            const newPassword = generatePassword();
            inputPasswordField.value = newPassword;
            
            // Trigger strength check if function exists
            if (typeof detPasswordStrength === 'function') {
                detPasswordStrength(newPassword);
            }
        });
    }
});