# 🎓 EduHub Learning Management System

A comprehensive web-based Learning Management System built with PHP and MySQL, featuring role-based access control for administrators, lecturers, and students. The system provides complete academic management including course enrollment, assignment submissions, quizzes, real-time notifications, and progress tracking.

## ✨ Features

### 👨‍🎓 For Students
- **User Registration & Authentication** - Secure login with password hashing
- **Course Enrollment** - View and enroll in assigned classes
- **Assignment Management** - Submit assignments and track grades
- **Quiz System** - Take timed quizzes with instant grading
- **Progress Tracking** - Monitor academic performance with detailed analytics
- **Digital Library** - Access and download study materials
- **Announcements** - Receive updates from lecturers and administration
- **Interactive Calendar** - View class schedules and important dates
- **Classmate Directory** - Connect with peers in enrolled classes
- **Message Center** - Receive feedback from lecturers
- **Profile Management** - Update personal information and profile picture

### 👨‍🏫 For Lecturers
- **Class Management** - Create and manage multiple classes
- **Student Management** - Add, edit, and monitor student records
- **Assignment Creation** - Upload and distribute assignments
- **Grade Management** - Grade submissions and provide feedback
- **Quiz Builder** - Create custom quizzes with multiple-choice questions
- **Material Upload** - Share study materials with students
- **Progress Reports** - Generate detailed student performance analytics
- **Timetable Management** - Schedule and organize class sessions
- **Announcement System** - Post updates visible to all students
- **Messaging System** - Send direct feedback to students with email notifications
- **Digital Library** - Upload and manage digital books and resources
- **Dashboard Analytics** - Real-time overview of classes and students

### 👔 For Administrators
- **Comprehensive Dashboard** - System-wide analytics and statistics
- **User Management** - Manage students, lecturers, and their profiles
- **Department Management** - Organize lecturers into departments
- **Class & Subject Management** - Create and assign classes and subjects
- **Assignment Oversight** - Monitor all assignments across the system
- **Quiz Management** - Oversee quiz creation and assignments
- **Timetable Coordination** - Manage institution-wide schedules
- **Library Administration** - Manage digital library resources
- **Announcement Broadcasting** - Send system-wide announcements
- **Message Management** - Facilitate communication between users
- **Activity Logs** - Track system usage and user activities
- **Analytics & Reporting** - Generate comprehensive reports
- **System Settings** - Configure system-wide parameters

## 🛠️ Technology Stack

- **Backend**: PHP 8.0+
- **Database**: MySQL 5.7+ / MariaDB 10.4+
- **Frontend**: HTML5, CSS3, JavaScript (ES6+)
- **CSS Framework**: Bootstrap 5.3.3
- **Icons**: Font Awesome 6.5.0
- **Charts**: Chart.js 4.4.0
- **Calendar**: FullCalendar 6.1.8
- **Email**: PHPMailer
- **Authentication**: Session-based with bcrypt password hashing
- **File Upload**: Native PHP file handling

## 📋 Prerequisites

- PHP 8.0 or higher
- MySQL 5.7+ or MariaDB 10.4+
- Apache/Nginx web server
- phpMyAdmin (optional, for database management)
- Composer (optional, for dependency management)

## 🚀 Installation

### 1. Clone the Repository

```bash
git clone https://github.com/Azha-Nasar/learning-management-system.git
cd learning-management-system
```

### 2. Database Setup

1. Create a new database named `capstone`
2. Import the database schema:

```bash
mysql -u root -p capstone < "capstone (5).sql"
```

Or use phpMyAdmin to import `capstone (5).sql`

### 3. Configure Database Connection

Edit `dbcon.php` with your database credentials:

```php
<?php
$conn = mysqli_connect('localhost','root','','capstone');

if (!$conn) {
    die("Connection failed: " . mysqli_connect_error());
}
?>
```

### 4. Configure File Permissions

```bash
chmod 755 uploads/
chmod 755 uploads/materials/
chmod 755 uploads/books/
chmod 755 uploads/profiles/
chmod 755 uploads/student_submissions/
chmod 644 dbcon.php
```

### 5. Email Configuration (Optional)

For email notifications, configure PHPMailer in `t_messages.php`:

```php
$mail->Username   = 'your-email@gmail.com';
$mail->Password   = 'your-app-password';
```

### 6. Start the Development Server

```bash
# Using PHP built-in server
php -S localhost:8000

# Or configure Apache/Nginx virtual host
```

## 🎯 Usage

### Default Access

After installation, you'll need to create initial accounts:

#### Create Admin Account
1. Register through the student registration form
2. Manually update the `users` table in the database:
```sql
UPDATE users SET user_type = 'admin' WHERE email = 'your-admin@email.com';
```

#### Create Lecturer Account
1. Use the teacher registration page at `/register_teacher.php`
2. Login credentials will be created during registration

#### Create Student Account
1. Use the student registration page at `/register_student.php`
2. Students will receive a unique student number upon registration

### System URLs

- **Homepage**: `http://localhost:8000/index.php`
- **Admin Login**: `http://localhost:8000/admin_login.php`
- **Teacher Login**: `http://localhost:8000/register_teacher.php`
- **Student Registration**: `http://localhost:8000/register_student.php`

## 📁 Project Structure

```
learning-management-system/
├── index.php                      # Main login page
├── dbcon.php                      # Database configuration
├── style.css                      # Global styles
│
├── Admin Module/
│   ├── admin_login.php           # Admin authentication
│   ├── admin_layout.php          # Admin dashboard layout
│   ├── admin_dashboard.php       # Admin overview
│   ├── admin_students.php        # Student management
│   ├── admin_teachers.php        # Lecturer management
│   ├── admin_departments.php     # Department management
│   ├── admin_assignments.php     # Assignment oversight
│   ├── admin_quizzes.php         # Quiz management
│   ├── admin_announcements.php   # Announcement broadcasting
│   ├── admin_messages.php        # Message management
│   ├── admin_analytics.php       # System analytics
│   ├── admin_reports.php         # Report generation
│   └── admin_logs.php            # Activity logging
│
├── Teacher Module/
│   ├── register_teacher.php      # Teacher login/registration
│   ├── teacher_login.php         # Teacher authentication handler
│   ├── teacher_layout.php        # Teacher dashboard layout
│   ├── teacher_Dashboard.php     # Teacher overview
│   ├── t_my_class.php            # Class management
│   ├── t_students.php            # Student management
│   ├── t_assignment.php          # Assignment creation
│   ├── t_grade_assignments.php   # Grade submissions
│   ├── t_quiz.php                # Quiz management
│   ├── add_quiz_questions.php    # Quiz question builder
│   ├── assign_quiz.php           # Quiz assignment
│   ├── t_upload_materials.php    # Material upload
│   ├── t_messages.php            # Student messaging
│   ├── t_timetable.php           # Timetable management
│   ├── t_progress_report.php     # Student progress reports
│   ├── add_announcement.php      # Post announcements
│   ├── library.php               # Digital library management
│   ├── library_add.php           # Add library resources
│   └── update_teacher_profile.php # Profile management
│
├── Student Module/
│   ├── register_student.php      # Student registration
│   ├── student_login.php         # Student authentication handler
│   ├── student_layout.php        # Student dashboard layout
│   ├── student_Dashboard.php     # Student overview
│   ├── s_classes.php             # Enrolled classes
│   ├── s_classmates.php          # Classmate directory
│   ├── s_subject_overview.php    # Subject information
│   ├── s_assignments.php         # Assignment submission
│   ├── s_quiz.php                # Take quizzes
│   ├── s_progress.php            # Performance tracking
│   ├── s_materials.php           # Download materials
│   ├── s_announcements.php       # View announcements
│   ├── s_messages.php            # Receive feedback
│   ├── s_calendar.php            # Class calendar
│   ├── s_notifications.php       # Notification center
│   ├── library_student.php       # Digital library access
│   └── update_profile.php        # Profile management
│
├── Database/
│   └── capstone (5).sql          # Database schema
│
├── Uploads/
│   ├── materials/                # Study materials
│   ├── books/                    # Digital library books
│   ├── profiles/                 # Profile pictures
│   ├── student_submissions/      # Assignment submissions
│   └── announcements/            # Announcement posters
│
└── PHPMailer-master/             # Email library
    ├── src/
    │   ├── Exception.php
    │   ├── PHPMailer.php
    │   └── SMTP.php
    └── ...
```

## 🔐 Security Features

- **Password Hashing**: Bcrypt algorithm for secure password storage
- **Prepared Statements**: SQL injection prevention
- **Session Management**: Secure session-based authentication
- **Role-Based Access Control**: Strict permission management
- **XSS Protection**: Input sanitization with htmlspecialchars()
- **File Upload Validation**: Type and size restrictions
- **CSRF Protection**: Recommended to add tokens for production

## 📊 Database Schema

### Main Tables

- **users** - User accounts with role-based authentication
- **student** - Student profiles and enrollment data
- **teacher** - Lecturer profiles and department assignments
- **class** - Class definitions (Year 1-4 for each program)
- **subject** - Subject/course information
- **teacher_class** - Lecturer-class-subject assignments
- **teacher_class_student** - Student enrollment records
- **assignment** - Assignment details
- **student_assignment** - Assignment submissions and grades
- **quiz** - Quiz definitions
- **quiz_question** - Quiz questions and answers
- **class_quiz** - Quiz assignments to classes
- **student_class_quiz** - Quiz attempts and scores
- **announcements** - System-wide announcements
- **message** - Direct messaging between users
- **notification** - System notifications
- **files** - Study material uploads
- **library_book** - Digital library resources
- **lecturer_timetable** - Class schedules
- **department** - Academic departments
- **activity_log** - System activity tracking
- **user_log** - User login history

## 🎨 Key Features Explained

### 📝 Assignment System
- Teachers upload assignments with due dates
- Students submit files with descriptions
- Teachers grade submissions and provide feedback
- Automated notifications on grade updates

### 📊 Quiz System
- Multiple-choice question format
- Timed quiz sessions
- Automatic grading
- Immediate score feedback
- Question bank management

### 📚 Digital Library
- Upload books (PDF, EPUB, DOCX)
- External URL linking
- Category organization
- Download functionality
- Search by title, author, or category

### 📈 Progress Tracking
- Subject-wise performance analytics
- Assignment and quiz averages
- Grade distribution visualization
- Recent activity timeline
- Performance status indicators (Excellent, Good, Fair, Poor, Critical)

### 📅 Timetable Management
- Calendar integration with FullCalendar
- Class scheduling
- Automatic student notifications
- Visual timeline view

### 📧 Notification System
- Real-time alerts for students
- Email integration for important updates
- In-app notification center
- Read/unread status tracking

### 💬 Messaging System
- Direct lecturer-to-student communication
- Email notifications via PHPMailer
- Message history tracking
- Bulk message deletion

## 🐛 Known Issues & Limitations

1. No real-time notifications (requires WebSocket implementation)
2. Limited file size uploads (depends on PHP configuration)
3. Single-language support (English only)
4. No automated backup system
5. No integration with external LMS platforms
6. Limited mobile responsiveness on some pages

## 🔄 Future Enhancements

- [ ] Real-time chat system with WebSocket
- [ ] Video conferencing integration (Zoom/Google Meet)
- [ ] Mobile app (Android/iOS)
- [ ] AI-powered assignment plagiarism detection
- [ ] Automated report card generation
- [ ] Parent portal for student monitoring
- [ ] Multi-language support (i18n)
- [ ] Dark mode theme
- [ ] Advanced analytics with ML insights
- [ ] Integration with payment gateways for fees
- [ ] Automated attendance tracking
- [ ] Discussion forums for each subject
- [ ] Peer-to-peer study groups
- [ ] Video lecture upload and streaming
- [ ] Certificate generation upon course completion

## 🤝 Contributing

Contributions are welcome! Please follow these steps:

1. Fork the repository
2. Create a feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

### Contribution Guidelines
- Follow PSR-12 coding standards for PHP
- Write meaningful commit messages
- Update documentation for new features
- Test thoroughly before submitting
- Ensure backward compatibility

## 🐞 Bug Reports

If you discover a bug, please create an issue on GitHub with:
- Detailed description of the bug
- Steps to reproduce
- Expected vs actual behavior
- Screenshots (if applicable)
- PHP and MySQL versions

## 👥 Author

**Azha Nasar**

## 📧 Contact

For questions, support, or collaboration:
- **Email**: azhanasar03@gmail.com
- **LinkedIn**: [https://www.linkedin.com/in/azha-nasar-3a7ba2330](https://www.linkedin.com/in/azha-nasar-3a7ba2330)
- **GitHub**: [https://github.com/Azha-Nasar](https://github.com/Azha-Nasar)
- **Project Repository**: [https://github.com/Azha-Nasar/learning-management-system](https://github.com/Azha-Nasar/learning-management-system)

## 📝 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Bootstrap team for the responsive UI framework
- Font Awesome for comprehensive icon library
- Chart.js for beautiful data visualizations
- FullCalendar for calendar integration
- PHPMailer for email functionality
- PHP and MySQL communities for excellent documentation
- Open source contributors and testers

## 📚 Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Bootstrap Documentation](https://getbootstrap.com/docs/)
- [Chart.js Documentation](https://www.chartjs.org/docs/)
- [FullCalendar Documentation](https://fullcalendar.io/docs)

## 🔧 Troubleshooting

### Common Issues

**Issue: Database connection failed**
```bash
Solution: Check dbcon.php credentials and ensure MySQL service is running
```

**Issue: File upload fails**
```bash
Solution: Check upload directory permissions (chmod 755) and php.ini upload limits
```

**Issue: Email notifications not working**
```bash
Solution: Configure SMTP settings in t_messages.php with valid credentials
```

**Issue: Sessions not persisting**
```bash
Solution: Ensure session_start() is called and check PHP session configuration
```

## 📊 System Requirements

### Minimum Requirements
- PHP 8.0+
- MySQL 5.7+
- 2GB RAM
- 10GB Storage
- Apache 2.4+ or Nginx 1.18+

### Recommended Requirements
- PHP 8.2+
- MySQL 8.0+ or MariaDB 10.6+
- 4GB RAM
- 20GB Storage
- Apache 2.4+ with mod_rewrite enabled
- SSL Certificate for production

## 🚀 Deployment

### Production Deployment Checklist

1. **Security**
   - [ ] Change default database credentials
   - [ ] Enable HTTPS with SSL certificate
   - [ ] Disable PHP error display
   - [ ] Implement CSRF tokens
   - [ ] Set secure session cookies
   - [ ] Configure file upload limits

2. **Performance**
   - [ ] Enable OPcache
   - [ ] Configure MySQL query caching
   - [ ] Implement CDN for static assets
   - [ ] Enable Gzip compression
   - [ ] Optimize database indices

3. **Monitoring**
   - [ ] Set up error logging
   - [ ] Configure backup automation
   - [ ] Monitor server resources
   - [ ] Track user activity

---

**⭐ If you found this project helpful, please give it a star!**

**Made with ❤️ by Azha Nasar**

---
