// Employee Management Functions
let employees = [];

// Load employees when the page loads
document.addEventListener('DOMContentLoaded', loadEmployees);

// Function to load employees
async function loadEmployees() {
    try {
        const response = await fetch('../api/get_employees.php');
        const data = await response.json();
        
        if (data.success) {
            employees = data.employees;
            renderEmployeeTable();
        } else {
            showAlert('Error loading employees: ' + data.message, 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('Error loading employees', 'error');
    }
}

// Render employee table
function renderEmployeeTable() {
    const tbody = document.getElementById('employeeTableBody');
    tbody.innerHTML = '';

    employees.forEach(employee => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${employee.employee_id}</td>
            <td>${employee.name}</td>
            <td>${employee.email}</td>
            <td>${employee.department || '-'}</td>
            <td>${formatDate(employee.date_joined)}</td>
            <td>${employee.status}</td>
            <td>
                <button onclick="editEmployee(${employee.employee_id})" class="edit-btn">
                    <i class="fas fa-edit"></i>
                </button>
                <button onclick="toggleEmployeeStatus(${employee.employee_id})" class="status-btn">
                    ${employee.status === 'active' ? '<i class="fas fa-user-slash"></i>' : '<i class="fas fa-user-check"></i>'}
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    });
}

// Modal functions
function openAddEmployeeModal() {
    document.getElementById('addEmployeeModal').style.display = 'block';
    document.getElementById('addEmployeeForm').reset();
}

function closeAddEmployeeModal() {
    document.getElementById('addEmployeeModal').style.display = 'none';
}

// Handle form submission
async function handleAddEmployee(event) {
    event.preventDefault();
    
    const formData = new FormData(event.target);
    const employeeData = {
        name: formData.get('name'),
        email: formData.get('email'),
        department: formData.get('department'),
        date_joined: formData.get('dateJoined'),
        username: formData.get('username'),
        password: formData.get('password'),
        petname: formData.get('petname'),
        role: formData.get('role')
    };

    try {
        console.log('Sending data:', employeeData);
        
        const response = await fetch('../api/add_employee.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(employeeData)
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', [...response.headers.entries()]);
        
        // Log the raw response text
        const responseText = await response.text();
        console.log('Raw response:', responseText);

        // Don't try to parse if empty
        if (!responseText.trim()) {
            throw new Error('Server returned empty response');
        }

        // Try to parse JSON
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (e) {
            console.error('Failed to parse JSON:', e);
            showAlert('Server error: ' + responseText.substring(0, 100), 'error');
            return;
        }

        if (data.success) {
            showAlert('Employee added successfully!', 'success');
            closeAddEmployeeModal();
            loadEmployees(); // Reload the employee list
        } else {
            throw new Error(data.message || 'Error adding employee');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert(error.message || 'Error adding employee', 'error');
    }
}

// Utility functions
function formatDate(dateString) {
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return new Date(dateString).toLocaleDateString(undefined, options);
}

function showAlert(message, type = 'info') {
    // You can implement a nice alert/toast system here
    alert(message);
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('addEmployeeModal');
    if (event.target === modal) {
        closeAddEmployeeModal();
    }
}
