# Leave Management System

A comprehensive web-based leave management system built with PHP, MySQL, and modern front-end technologies.

## Current Features

- **Authentication System**
  - Secure login with password hashing
  - Role-based user system (admin, employee)
  - Dynamic user information display
  - Session management

- **Dashboard**
  - Dynamic content loading
  - Responsive sidebar navigation
  - Statistics overview
  - Recent activity display

## Database Structure

- `employee_details`: Store employee information
- `user_credentials`: Manage authentication and roles
- `leave_balance`: Track different types of leave balances
- `leave_log`: Record all leave applications and their status

## Future Tasks

### 1. Leave Management Implementation
- [ ] Display current leave balance for each type
- [ ] Leave application form with validation
- [ ] Leave history view with filters
- [ ] Status tracking for leave applications
- [ ] Email notifications for leave status updates

### 2. Employee Management (Admin)
- [ ] Add new employee functionality
- [ ] Employee details management
- [ ] Employee list with search and filters
- [ ] Leave request management interface
- [ ] Bulk actions for leave requests

### 3. Dashboard Enhancements
- [ ] Real-time notifications system
- [ ] Interactive leave statistics charts
- [ ] Department-wise leave summaries
- [ ] Calendar view for leave tracking
- [ ] Export functionality for reports

### 4. Security Enhancements
- [ ] Session timeout implementation
- [ ] Password reset functionality
- [ ] Two-factor authentication option
- [ ] Role-based access control refinement
- [ ] Activity logging system
- [ ] Security audit logging

### 5. User Experience Improvements
- [ ] Profile management page
- [ ] User preferences settings
- [ ] Dark/Light theme toggle
- [ ] Mobile-responsive optimizations
- [ ] Quick actions menu

### 6. System Administration
- [ ] Leave policy management
- [ ] Department management
- [ ] System settings configuration
- [ ] Backup and restore functionality
- [ ] System logs viewer

## Technical Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser
- XAMPP/WAMP server

## Installation

1. Clone the repository
2. Import the database schema
3. Configure database connection in `api/config.php`
4. Start your XAMPP/WAMP server
5. Access the application through the web browser

## Contributing

Feel free to contribute to this project by:
1. Forking the repository
2. Creating a feature branch
3. Committing your changes
4. Creating a pull request

## License

This project is licensed under the MIT License - see the LICENSE file for details.
