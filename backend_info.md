
techStach:
html, css, javascript, ajax,php,jquery mysql
xampp Server

database name: leavems
database schema:

CREATE TABLE employee_details (
    employee_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    department VARCHAR(50),
    date_joined DATE NOT NULL,
    status ENUM('active', 'inactive') DEFAULT 'active'
);


CREATE TABLE user_credentials (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    employee_id INT NOT NULL,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('employee', 'manager', 'admin') DEFAULT 'employee',
    FOREIGN KEY (employee_id) REFERENCES employee_details(employee_id) ON DELETE CASCADE
);


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
