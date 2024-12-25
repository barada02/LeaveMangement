# Leave Management System

A comprehensive web-based leave management system built with PHP, MySQL, and modern web technologies. This system allows organizations to efficiently manage employee leave requests, track leave balances, and streamline the approval process.

## Features

### Employee Features
- View leave balances
- Submit leave requests
- Track request status
- View leave history
- Update personal information

### Admin Features
- Dashboard with overview statistics
- Manage employee records
- Process leave requests (Approve/Reject)
- Add notes to leave requests
- View leave balances across organization
- Filter requests by status

## Technical Stack

### Frontend
- HTML5, CSS3, JavaScript
- Bootstrap for responsive design
- Font Awesome icons
- Custom animations and transitions
- AJAX for asynchronous operations

### Backend
- PHP 7.4+
- MySQL Database
- RESTful API architecture
- JSON for data exchange

### Database Structure
- employee_details: Employee information
- leave_balance: Leave quota tracking
- leave_log: Leave request management
- user_credentials: Authentication data

## Installation

1. Prerequisites:
   - XAMPP/WAMP server
   - PHP 7.4 or higher
   - MySQL 5.7 or higher

2. Setup:
   ```bash
   # Clone repository to htdocs folder
   git clone [repository-url] leaveSystem

   # Import database
   mysql -u root -p < database/leavems.sql
   ```

3. Configuration:
   - Update database credentials in `api/config.php`
   - Configure SMTP settings for email notifications (if enabled)

## Database Schema

### employee_details
```sql
CREATE TABLE `employee_details` (
  `employee_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `department` varchar(50) DEFAULT NULL,
  `date_joined` date NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active'
)
```

### leave_balance
```sql
CREATE TABLE `leave_balance` (
  `balance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `sick_leave` int(11) DEFAULT 0,
  `casual_leave` int(11) DEFAULT 0,
  `earned_leave` int(11) DEFAULT 0,
  `festival_leave` int(11) DEFAULT 0,
  `total_leave` int(11) DEFAULT 0,
  `total_leave_left` int(11) DEFAULT 0
)
```

### leave_log
```sql
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
)
```

## Key Features Implementation

### Leave Request Processing
- Automatic leave balance validation
- Real-time status updates
- Optional admin notes for decisions
- Email notifications (if configured)

### User Interface
- Clean, modern design
- Responsive layout
- Interactive notifications
- Status-based color coding
- Tooltip-based information display

### Security Features
- Session-based authentication
- Role-based access control
- SQL injection prevention
- XSS protection
- CSRF token validation

## API Endpoints

### Employee Management
- GET `/api/get_employees.php`: Fetch employee list
- POST `/api/add_employee.php`: Add new employee
- PUT `/api/update_employee.php`: Update employee details

### Leave Management
- GET `/api/get_leave_requests.php`: Fetch leave requests
- POST `/api/update_leave_request.php`: Process leave request
- GET `/api/get_leave_balance.php`: Check leave balance

## Future Enhancements

1. Planned Features
   - Email notifications
   - Document attachments for leave requests
   - Leave calendar view
   - Advanced reporting
   - Bulk request processing

2. Technical Improvements
   - API rate limiting
   - Caching implementation
   - Advanced logging
   - Unit testing
   - Docker containerization

## Contributing

1. Fork the repository
2. Create feature branch
3. Commit changes
4. Push to branch
5. Create Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For support and queries, please create an issue in the repository or contact the development team.
