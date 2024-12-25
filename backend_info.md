
techStach:
html, css, javascript, ajax,php,jquery mysql
xampp Server

database name: leavems
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

//From xampp server

CREATE TABLE `employee_details` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `date_joined` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `leave_balance` (
  `balance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sick_leave` int(11) DEFAULT 0,
  `casual_leave` int(11) DEFAULT 0,
  `earned_leave` int(11) DEFAULT 0,
  `festival_leave` int(11) DEFAULT 0,
  `total_leave` int(11) DEFAULT 0,
  `total_leave_left` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `leave_log` (
  `leave_log_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('sick','casual','earned','festival') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `manager_id` int(11) DEFAULT NULL,
  `date_applied` timestamp NOT NULL DEFAULT current_timestamp(),
  `date_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `note` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


CREATE TABLE `user_credentials` (
  `user_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('employee','manager','admin') DEFAULT 'employee',
  `petname` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

