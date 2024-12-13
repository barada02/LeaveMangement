
techStach:
html, css, javascript, ajax,php,jquery mysql
xampp Server

database name: leave_ms
database schema:

-- Create employee_details Table
CREATE TABLE employee_details (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department VARCHAR(50),
    date_joined DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);

-- Create user_credentials Table (with petname and password hashing placeholder)
CREATE TABLE user_credentials (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL, -- For bcrypt-hashed passwords
    petname VARCHAR(50) NOT NULL,  -- Secondary verification option
    role ENUM('employee', 'manager', 'admin') DEFAULT 'employee',
    FOREIGN KEY (employee_id) REFERENCES employee_details(employee_id) ON DELETE CASCADE
);

-- Create leave_balance Table
CREATE TABLE leave_balance (
    balance_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    sick_leave INT DEFAULT 0,
    casual_leave INT DEFAULT 0,
    earned_leave INT DEFAULT 0,
    festival_leave INT DEFAULT 0,
    total_leave INT DEFAULT 0,
    total_leave_left INT DEFAULT 0,
    FOREIGN KEY (employee_id) REFERENCES employee_details(employee_id) ON DELETE CASCADE
);

-- Create leave_log Table
CREATE TABLE leave_log (
    leave_log_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    leave_type ENUM('sick', 'casual', 'earned', 'festival') NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    reason TEXT,
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    manager_id INT,
    date_applied TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    date_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    note TEXT,
    FOREIGN KEY (employee_id) REFERENCES employee_details(employee_id) ON DELETE CASCADE,
    FOREIGN KEY (manager_id) REFERENCES user_credentials(user_id) ON DELETE SET NULL
);
