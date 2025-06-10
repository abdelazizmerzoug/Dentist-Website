function showNotification(type, message) {
// Create notification element
const notification = document.createElement('div');
notification.className = 'notification ' + (type === 'success' ? 'success' : 'error');
notification.innerHTML = `
<span>${message}</span>
<button class="close-btn" onclick="this.parentElement.style.display='none';">&times;</button>
`;

// Append to body
document.body.appendChild(notification);

// Automatically remove notification after 5 seconds
setTimeout(() => {
notification.remove();
}, 5000);
}

// Handle form submission
document.getElementById('appointmentForm').addEventListener('submit', function(event) {
event.preventDefault(); // Prevent default form submission

const formData = new FormData(this); // Collect form data

// Send data via fetch to the server
fetch('../php-Client/add_product.php', {
method: 'POST',
body: formData
})
.then(response => response.json()) // Parse the JSON response
.then(data => {
if (data.status === 'success') {
showNotification('success', data.message);
} else {
showNotification('error', data.message);
}
})
.catch(error => {
console.error('Error:', error);
showNotification('error', 'An error occurred. Please try again.');
});
});