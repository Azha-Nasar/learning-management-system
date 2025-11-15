-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 13, 2025 at 03:21 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `capstone`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `activity_log_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `date` varchar(100) NOT NULL,
  `action` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `activity_log`
--

INSERT INTO `activity_log` (`activity_log_id`, `username`, `date`, `action`) VALUES
(1, 'admin', '2025-09-29 13:32:39', 'Add User ifaz'),
(2, 'admin', '2025-09-29 13:35:12', 'Add User Afraaz');

-- --------------------------------------------------------

--
-- Table structure for table `announcements`
--

CREATE TABLE `announcements` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `poster` varchar(255) DEFAULT NULL,
  `posted_by` int(11) NOT NULL,
  `role` enum('teacher','admin','student') NOT NULL DEFAULT 'teacher',
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `announcements`
--

INSERT INTO `announcements` (`id`, `title`, `message`, `poster`, `posted_by`, `role`, `created_at`) VALUES
(1, 'Midterm Exam Schedule', 'The midterm exams will be held from 10th to 15th October. Please check your class timetable and prepare accordingly', 'uploads/1759170029_Untitled design.png', 3, 'teacher', '2025-09-29 23:50:29'),
(2, 'final semester', 'final semester is going to held on next month', 'uploads/1762010392_Untitled design.png', 3, 'teacher', '2025-11-01 18:19:52'),
(3, 'asdfgh', 'wertyhujk', 'uploads/1762010999_0514687d-834d-4958-9023-e6975eb333bb.jpg', 3, 'teacher', '2025-11-01 18:29:59'),
(4, 'asdfghjklASDFGBHN', '234567YIGHVB', 'uploads/1762012079_Grow Your Business With Our “HR Services.png', 3, 'teacher', '2025-11-01 18:47:59'),
(5, 'FINAL YEAR SEMESTER EXAM', 'FINAL YEAR SEMESTER EXAM GOING TO HELD ON NEXT MONTH', 'uploads/1762012264_Untitled design.png', 8, 'teacher', '2025-11-01 18:51:04'),
(6, 'Viva Date scheduled', 'Viva Date have been schedule on next week', 'uploads/1762025822_Sequence Diagram AD2.png', 12, 'teacher', '2025-11-01 22:37:02'),
(7, 'Upcoming Halloween Party', 'Get ready for a night of frights, fun, and laughter! The Administration is pleased to announce and welcome you all to our annual Halloween celebration.', 'uploads/1762028121_Connecting you to the right career OPPORTUNITIES.png', 2, 'admin', '2025-11-01 23:15:21');

-- --------------------------------------------------------

--
-- Table structure for table `answer`
--

CREATE TABLE `answer` (
  `answer_id` int(11) NOT NULL,
  `quiz_question_id` int(11) NOT NULL,
  `answer_text` varchar(100) NOT NULL,
  `choices` varchar(3) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `assignment`
--

CREATE TABLE `assignment` (
  `assignment_id` int(11) NOT NULL,
  `floc` varchar(300) NOT NULL,
  `fdatein` varchar(100) NOT NULL,
  `fdesc` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `assignment`
--

INSERT INTO `assignment` (`assignment_id`, `floc`, `fdatein`, `fdesc`, `teacher_id`, `class_id`, `fname`) VALUES
(1, 'uploads/2709-1668161376936-1150-1619622396900-Unit-01 Programming Assignment_FINAL (1).doc', '2025-09-29', 'Object oriented Programming Assignment', 3, 1, '2709-1668161376936-1150-1619622396900-Unit-01 Programming Assignment_FINAL (1).doc'),
(2, 'uploads/3. Software Engineering -Final Project Report Template .docx', '2025-11-01', 'Web Development', 3, 1, '3. Software Engineering -Final Project Report Template .docx'),
(3, 'uploads/7234_File_sample.pdf', '2025-11-02', 'Advanced Software Engineer', 3, 1, '7234_File_sample.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `class_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`class_id`, `class_name`) VALUES
(1, 'Computer Science - Year 1'),
(2, 'Computer Science - Year 2'),
(3, 'Computer Science - Year 3'),
(4, 'Computer Science - Year 4'),
(5, 'Information Technology - Year 1'),
(6, 'Information Technology - Year 2'),
(7, 'Information Technology - Year 3'),
(8, 'Information Technology - Year 4'),
(9, 'Software Engineering - Year 1'),
(10, 'Software Engineering - Year 2'),
(11, 'Software Engineering - Year 3'),
(12, 'Software Engineering - Year 4'),
(13, 'Data Science - Year 1'),
(14, 'Data Science - Year 2'),
(15, 'Data Science - Year 3'),
(16, 'Data Science - Year 4');

-- --------------------------------------------------------

--
-- Table structure for table `class_quiz`
--

CREATE TABLE `class_quiz` (
  `class_quiz_id` int(11) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `quiz_time` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `class_quiz`
--

INSERT INTO `class_quiz` (`class_quiz_id`, `teacher_class_id`, `quiz_time`, `quiz_id`) VALUES
(1, 33, 15, 1),
(2, 33, 10, 1),
(3, 33, 15, 1);

-- --------------------------------------------------------

--
-- Table structure for table `class_subject_overview`
--

CREATE TABLE `class_subject_overview` (
  `class_subject_overview_id` int(11) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `content` varchar(10000) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `class_subject_teacher`
--

CREATE TABLE `class_subject_teacher` (
  `id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `teacher_id` int(11) DEFAULT NULL,
  `academic_year` varchar(10) DEFAULT NULL,
  `semester` varchar(10) DEFAULT NULL,
  `schedule` varchar(100) DEFAULT NULL,
  `room` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `content`
--

CREATE TABLE `content` (
  `content_id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `content` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `department`
--

CREATE TABLE `department` (
  `department_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `dean` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `department`
--

INSERT INTO `department` (`department_id`, `department_name`, `dean`) VALUES
(1, 'Computing Department', 'Infa'),
(2, 'Information Technology', 'fathima');

-- --------------------------------------------------------

--
-- Table structure for table `event`
--

CREATE TABLE `event` (
  `event_id` int(11) NOT NULL,
  `event_title` varchar(100) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `date_start` varchar(100) NOT NULL,
  `date_end` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `event`
--

INSERT INTO `event` (`event_id`, `event_title`, `teacher_class_id`, `date_start`, `date_end`) VALUES
(1, 'Halloween party ', 0, '11/04/2025', '11/05/2025');

-- --------------------------------------------------------

--
-- Table structure for table `files`
--

CREATE TABLE `files` (
  `file_id` int(11) NOT NULL,
  `file_name` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `uploaded_by` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `upload_date` datetime NOT NULL DEFAULT current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `files`
--

INSERT INTO `files` (`file_id`, `file_name`, `file_path`, `uploaded_by`, `class_id`, `upload_date`, `description`) VALUES
(3, 'Introduction_to_PHP', 'uploads/materials/1759163942_Introduction_to_PHP.pdf', 3, 7, '2025-09-29 22:09:02', 'This document explains the basics of PHP programming for beginners'),
(4, 'Web Development Notes', 'uploads/materials/1759164094_Web_Development_Notes.pdf', 3, 1, '2025-09-29 22:11:34', 'Web Development: Learn to build responsive and dynamic websites using HTML, CSS, JavaScript, and server-side technologies. Gain skills to design, develop, and deploy web applications for real-world projects.');

-- --------------------------------------------------------

--
-- Table structure for table `lecturer_timetable`
--

CREATE TABLE `lecturer_timetable` (
  `timetable_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_name` varchar(100) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `start_datetime` datetime NOT NULL,
  `end_datetime` datetime NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `lecturer_timetable`
--

INSERT INTO `lecturer_timetable` (`timetable_id`, `teacher_id`, `class_name`, `subject`, `start_datetime`, `end_datetime`, `description`, `created_at`) VALUES
(1, 3, 'Computer Science - Year 1', 'Web Development', '2025-09-25 03:00:00', '2025-09-29 05:00:00', '', '2025-09-29 23:14:14'),
(2, 3, 'Information Technology - Year 3', 'Computer Networks', '2025-09-29 01:00:00', '2025-09-29 02:30:00', 'Introduction of networking', '2025-09-29 23:17:20'),
(4, 3, 'Information Technology - Year 4', 'Programming Fundamentals', '2025-09-30 12:30:00', '2025-09-30 02:30:00', 'Basics of Programming Language', '2025-09-29 23:38:17'),
(5, 3, 'Information Technology - Year 4', 'Programming Fundamentals', '2025-09-30 12:30:00', '2025-09-30 02:30:00', 'Basics of Programming Language', '2025-09-29 23:39:05'),
(6, 3, 'Computer Science - Year 1', 'Artificial Intelligence', '2025-10-03 07:00:00', '2025-10-03 09:45:00', 'Advance Artificial intelligence', '2025-09-29 23:40:45'),
(7, 3, 'Computer Science - Year 1', 'Programming Fundamentals', '2025-11-03 15:00:00', '2025-11-03 18:00:00', 'Don\'t miss the impotent class', '2025-11-01 18:03:31');

-- --------------------------------------------------------

--
-- Table structure for table `library_book`
--

CREATE TABLE `library_book` (
  `book_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `external_url` varchar(500) DEFAULT NULL,
  `file_path` varchar(500) DEFAULT NULL,
  `added_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `library_book`
--

INSERT INTO `library_book` (`book_id`, `title`, `author`, `description`, `category`, `external_url`, `file_path`, `added_by`, `created_at`) VALUES
(1, 'Introduction to Python Programming', 'John Doe', 'Beginner-friendly guide to Python programming', 'Programming / Computer Science', 'https://assets.openstax.org/oscms-prodcms/media/documents/Introduction_to_Python_Programming_-_WEB.pdf', 'uploads/books/1759170441_Introduction_to_Python_Programming_-_WEB.pdf', 3, '2025-09-29 18:27:21'),
(2, 'Database Management Systems', 'Robert Brown', 'Fundamentals of relational databases and SQL Database.', 'Database', 'https://scs.dypvp.edu.in/documents/e-books/DMBS/database-management-systems-raghu-ramakrishnan.pdf', 'uploads/books/1759170820_database-management-systems-raghu-ramakrishnan.pdf', 3, '2025-09-29 18:33:40'),
(3, 'Beginning of Artificial intelligence', 'Azha Nasar', 'Beginning of Artificial intelligence', 'Artificial intelligence', 'https://link.springer.com/book/10.1007/978-3-658-43102-0?utm_source=chatgpt.com', 'uploads/books/1762121198_3952_File_sample.pdf', 3, '2025-11-02 22:06:38');

-- --------------------------------------------------------

--
-- Table structure for table `message`
--

CREATE TABLE `message` (
  `message_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `content` varchar(200) NOT NULL,
  `date_sended` varchar(100) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `reciever_name` varchar(50) NOT NULL,
  `sender_name` varchar(200) NOT NULL,
  `message_status` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `message`
--

INSERT INTO `message` (`message_id`, `reciever_id`, `content`, `date_sended`, `sender_id`, `reciever_name`, `sender_name`, `message_status`) VALUES
(9, 1, 'keep goingg..... you are doing well', '2025-11-01 17:54:08', 3, '', '', ''),
(10, 1, 'You have pending assignments to done', '2025-11-01 17:56:10', 3, '', '', ''),
(11, 3, 'Hii Sir,\r\nYour Leave has been approved', '2025-11-01 23:03:48', 2, '', '', ''),
(12, 1, 'Good Morning Azha,\r\nGood luck on your upcoming exam! Remember to stay focused, take a deep breath, and trust in your preparation. You\'ve got this!', '2025-11-01 23:11:12', 2, '', '', ''),
(13, 1, 'Good Morning Azha,\r\nGood luck on your upcoming exam! Remember to stay focused, take a deep breath, and trust in your preparation. You\'ve got this!', '2025-11-01 23:23:37', 2, '', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `message_sent`
--

CREATE TABLE `message_sent` (
  `message_sent_id` int(11) NOT NULL,
  `reciever_id` int(11) NOT NULL,
  `content` varchar(200) NOT NULL,
  `date_sended` varchar(100) NOT NULL,
  `sender_id` int(11) NOT NULL,
  `reciever_name` varchar(100) NOT NULL,
  `sender_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification`
--

CREATE TABLE `notification` (
  `notification_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `user_type` enum('teacher','student','admin') NOT NULL,
  `message` text NOT NULL,
  `type` enum('info','warning','success','error') DEFAULT 'info',
  `seen` tinyint(1) DEFAULT 0,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notification`
--

INSERT INTO `notification` (`notification_id`, `user_id`, `user_type`, `message`, `type`, `seen`, `timestamp`) VALUES
(1, 8, 'teacher', 'Welcome to the Teacher Dashboard!', 'success', 0, '2025-09-29 15:40:04'),
(2, 8, 'teacher', 'You have been assigned to 14 classes.', 'info', 0, '2025-09-29 15:40:04');

-- --------------------------------------------------------

--
-- Table structure for table `notification_read`
--

CREATE TABLE `notification_read` (
  `notification_read_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_read` varchar(50) NOT NULL,
  `notification_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notification_read_teacher`
--

CREATE TABLE `notification_read_teacher` (
  `notification_read_teacher_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_read` varchar(100) NOT NULL,
  `notification_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `question_type`
--

CREATE TABLE `question_type` (
  `question_type_id` int(11) NOT NULL,
  `question_type` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `quiz`
--

CREATE TABLE `quiz` (
  `quiz_id` int(11) NOT NULL,
  `quiz_title` varchar(50) NOT NULL,
  `quiz_description` varchar(100) NOT NULL,
  `date_added` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `quiz`
--

INSERT INTO `quiz` (`quiz_id`, `quiz_title`, `quiz_description`, `date_added`, `teacher_id`) VALUES
(1, 'Web Development Basics', 'quiz content Covers (HTML, CSS, and JavaScript)', '2025-09-29 22:24:37', 3);

-- --------------------------------------------------------

--
-- Table structure for table `quiz_question`
--

CREATE TABLE `quiz_question` (
  `question_id` int(11) NOT NULL,
  `quiz_id` int(11) NOT NULL,
  `question_text` text NOT NULL,
  `option_a` varchar(255) NOT NULL,
  `option_b` varchar(255) NOT NULL,
  `option_c` varchar(255) NOT NULL,
  `option_d` varchar(255) NOT NULL,
  `correct_option` char(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quiz_question`
--

INSERT INTO `quiz_question` (`question_id`, `quiz_id`, `question_text`, `option_a`, `option_b`, `option_c`, `option_d`, `correct_option`) VALUES
(1, 1, 'What does HTML stand for?', 'HyperText Markup Language', 'Hyperlinks and Text Markup Language', 'Home Tool Markup Language', 'Hyperlinking Text Management Language', 'A'),
(2, 1, 'Which language is used for styling web pages?', 'HTML', 'CSS', 'JavaScript', 'Python', 'B'),
(3, 1, 'What is the correct HTML element for inserting a line break?', '<break>', '<lb>', '<br>', '<line>', 'C'),
(4, 1, 'Which of the following is a JavaScript framework?', 'Django', 'Flask', 'Laravel', 'React', 'D'),
(6, 1, 'What is the purpose of the <head> section in an HTML document?', 'To display the main content on the page', 'To contain metadata, title, and links to scripts/styles', 'To create links to other web pages', 'To insert images', 'B'),
(8, 1, 'What is the purpose of the <head> section in an HTML document?', 'To display the main content on the page', 'To contain metadata, title, and links to scripts/styles', 'To create links to other web pages', 'To insert images', 'B'),
(10, 1, 'sample ', 'a', 'b', 'c', 'd', 'C');

-- --------------------------------------------------------

--
-- Table structure for table `school_year`
--

CREATE TABLE `school_year` (
  `school_year_id` int(11) NOT NULL,
  `school_year` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `student_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `class_id` int(11) NOT NULL,
  `student_number` varchar(20) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `enrollment_date` date DEFAULT curdate(),
  `profile_image` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('Active','Suspended') NOT NULL DEFAULT 'Active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`student_id`, `user_id`, `name`, `email`, `password`, `class_id`, `student_number`, `phone`, `address`, `date_of_birth`, `enrollment_date`, `profile_image`, `created_at`, `updated_at`, `status`) VALUES
(1, 1, 'Azha Nasar', 'azhafathi03@gmail.com', '$2y$10$I1rTT7LhNSfjGklz7uZjWuEEm7igYdqNoUkGMPQWWCjT4X4vMi01y', 1, NULL, NULL, NULL, NULL, '2025-09-29', 'uploads/avatar_68da32c7111943.22233419.png', '2025-09-29 06:35:57', '2025-09-29 21:11:28', 'Active'),
(4, 9, 'Azka Nasar', 'azka@gmail.com', '', 8, NULL, NULL, NULL, NULL, '2025-09-29', NULL, '2025-09-29 16:17:21', '2025-09-30 19:51:50', 'Suspended'),
(5, 10, 'Ifaz Ahmed', 'ifaz@gmail.com', '', 7, 'STU20250003', '0771060601', '0', '2003-12-14', '2025-09-29', 'uploads/avatar_68dadf7e1c66d5.24778913.jpg', '2025-09-29 19:15:07', '2025-09-29 20:20:47', 'Active'),
(6, 11, 'anha', 'anha@gmail.com', '', 1, NULL, NULL, NULL, NULL, '2025-11-01', NULL, '2025-11-01 15:12:08', '2025-11-01 17:31:08', 'Active'),
(7, 13, 'Ayna Maryam', 'Ayna@gmail.com', '$2y$10$7ZqJQz/9Uin7/FKkkJukfuY9zMVwn3yw6rkyoSIHPUVm7S9crIG1i', 1, 'STU20250005', '7138263838', '0', '2011-07-08', '2025-11-01', 'uploads/profiles/student_1762030821_690674e50c553.jpg', '2025-11-01 21:00:21', '2025-11-01 21:00:21', 'Active'),
(8, 14, 'Infa Zameer', 'infa@gmail.com', '$2y$10$ukb/q1rMNeLtaOlKNjG9MucKo9rPDPJwNfw/BPPh.zZHhlgkkv/8K', 1, 'STU20250006', '0772345678', '0', '2011-10-06', '2025-11-02', 'uploads/profiles/student_1762117389_6907c70da1a6b.jpg', '2025-11-02 21:03:09', '2025-11-02 21:03:09', 'Active'),
(9, 15, 'Ishra Zameer', 'ishra@gmail.com', '$2y$10$4cSmOkNeIhobI/MMhYHthucprHPDADgY5F1pYMWeMtlABEZYxbhB.', 1, 'STU20250007', '0775679246', '0', '2014-06-18', '2025-11-02', 'uploads/profiles/student_1762117822_6907c8be4a1a9.jpg', '2025-11-02 21:10:22', '2025-11-02 21:10:22', 'Active');

-- --------------------------------------------------------

--
-- Table structure for table `student_assignment`
--

CREATE TABLE `student_assignment` (
  `student_assignment_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL,
  `floc` varchar(100) NOT NULL,
  `assignment_fdatein` varchar(50) NOT NULL,
  `fdesc` varchar(100) NOT NULL,
  `fname` varchar(50) NOT NULL,
  `student_id` int(11) NOT NULL,
  `grade` varchar(5) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_assignment`
--

INSERT INTO `student_assignment` (`student_assignment_id`, `assignment_id`, `floc`, `assignment_fdatein`, `fdesc`, `fname`, `student_id`, `grade`) VALUES
(1, 1, 'uploads/student_submissions/1762007851_How We Help Companies Thrive with the Right Talent.pdf', '2025-11-01', 'asdfghj', 'How We Help Companies Thrive with the Right Talent', 1, '');

-- --------------------------------------------------------

--
-- Table structure for table `student_backpack`
--

CREATE TABLE `student_backpack` (
  `file_id` int(11) NOT NULL,
  `floc` varchar(100) NOT NULL,
  `fdatein` varchar(100) NOT NULL,
  `fdesc` varchar(100) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `student_class_quiz`
--

CREATE TABLE `student_class_quiz` (
  `student_class_quiz_id` int(11) NOT NULL,
  `class_quiz_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `student_quiz_time` varchar(100) NOT NULL,
  `grade` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `student_class_quiz`
--

INSERT INTO `student_class_quiz` (`student_class_quiz_id`, `class_quiz_id`, `student_id`, `student_quiz_time`, `grade`) VALUES
(1, 1, 1, '2025-11-01 15:49:32', '83');

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `subject_id` int(11) NOT NULL,
  `subject_code` varchar(20) DEFAULT NULL,
  `subject_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`subject_id`, `subject_code`, `subject_name`, `description`, `created_at`) VALUES
(1, 'CS101', 'Programming Fundamentals', 'Introduction to Programming', '2025-09-29 15:37:12'),
(2, 'CS102', 'Data Structures', 'Data Structures and Algorithms', '2025-09-29 15:37:12'),
(3, 'CS103', 'Database Systems', 'Database Design and Management', '2025-09-29 15:37:12'),
(4, 'CS104', 'Web Development', 'Frontend and Backend Web Development', '2025-09-29 15:37:12'),
(5, 'CS105', 'Software Engineering', 'Software Development Lifecycle', '2025-09-29 15:37:12'),
(6, 'CS106', 'Operating Systems', 'OS Concepts and Management', '2025-09-29 15:37:12'),
(7, 'CS107', 'Computer Networks', 'Network Architecture and Protocols', '2025-09-29 15:37:12'),
(8, 'CS108', 'Artificial Intelligence', 'AI and Machine Learning', '2025-09-29 15:37:12'),
(9, 'CS109', 'Mobile App Development', 'iOS and Android Development', '2025-09-29 15:37:12'),
(10, 'CS110', 'Cybersecurity', 'Information Security', '2025-09-29 15:37:12'),
(11, 'CS111', 'Cloud Computing', 'Cloud Services and Architecture', '2025-09-29 15:37:12'),
(12, 'CS112', 'DevOps', 'Continuous Integration and Deployment', '2025-09-29 15:37:12'),
(13, 'MATH201', 'Mathematics for CS', 'Discrete Mathematics and Linear Algebra', '2025-09-29 15:37:12'),
(14, 'STAT201', 'Statistics', 'Probability and Statistics', '2025-09-29 15:37:12'),
(15, 'MGT201', 'Project Management', 'IT Project Management', '2025-09-29 15:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `subject_backup`
--

CREATE TABLE `subject_backup` (
  `subject_id` int(11) NOT NULL DEFAULT 0,
  `subject_code` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `subject_title` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `category` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `description` longtext CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `unit` int(11) NOT NULL,
  `Pre_req` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL,
  `semester` varchar(100) CHARACTER SET latin1 COLLATE latin1_swedish_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject_backup`
--

INSERT INTO `subject_backup` (`subject_id`, `subject_code`, `subject_title`, `category`, `description`, `unit`, `Pre_req`, `semester`) VALUES
(1, 'CS101', 'Introduction to Programming', 'Core', 'Fundamentals of programming using Python and problem-solving techniques', 3, 'None', '1'),
(2, 'CS102', 'Discrete Mathematics', 'Core', 'Mathematical foundations for computer science including logic, sets, and relations', 3, 'None', '1'),
(3, 'CS103', 'Computer Organization', 'Core', 'Introduction to computer architecture and organization', 3, 'None', '1'),
(4, 'CS104', 'Technical Communication', 'General', 'Written and oral communication skills for technical professionals', 2, 'None', '1'),
(5, 'MATH101', 'Calculus I', 'Mathematics', 'Differential and integral calculus', 3, 'None', '1'),
(6, 'CS105', 'Object-Oriented Programming', 'Core', 'Object-oriented programming concepts using Java', 3, 'CS101', '2'),
(7, 'CS106', 'Data Structures', 'Core', 'Fundamental data structures and algorithms', 4, 'CS101', '2'),
(8, 'CS107', 'Digital Logic Design', 'Core', 'Boolean algebra, logic gates, and digital circuits', 3, 'None', '2'),
(9, 'MATH102', 'Calculus II', 'Mathematics', 'Multivariable calculus and differential equations', 3, 'MATH101', '2'),
(10, 'ENG101', 'English Composition', 'General', 'Academic writing and critical thinking', 2, 'None', '2'),
(11, 'CS201', 'Database Management Systems', 'Core', 'Relational database design, SQL, and database administration', 4, 'CS106', '3'),
(12, 'CS202', 'Computer Networks', 'Core', 'Network protocols, architectures, and communication systems', 3, 'CS103', '3'),
(13, 'CS203', 'Operating Systems', 'Core', 'Process management, memory management, and file systems', 4, 'CS106', '3'),
(14, 'CS204', 'Web Development', 'Elective', 'HTML, CSS, JavaScript, and modern web frameworks', 3, 'CS105', '3'),
(15, 'MATH201', 'Linear Algebra', 'Mathematics', 'Vectors, matrices, and linear transformations', 3, 'MATH102', '3'),
(16, 'CS205', 'Software Engineering', 'Core', 'Software development lifecycle, design patterns, and project management', 4, 'CS105', '4'),
(17, 'CS206', 'Algorithm Design and Analysis', 'Core', 'Advanced algorithms, complexity analysis, and optimization', 4, 'CS106', '4'),
(18, 'CS207', 'Computer Graphics', 'Elective', 'Graphics primitives, transformations, and rendering techniques', 3, 'MATH201', '4'),
(19, 'CS208', 'Mobile Application Development', 'Elective', 'Android and iOS app development', 3, 'CS105', '4'),
(20, 'STAT201', 'Probability and Statistics', 'Mathematics', 'Statistical methods and probability theory', 3, 'MATH101', '4'),
(21, 'CS301', 'Artificial Intelligence', 'Core', 'Machine learning, neural networks, and AI algorithms', 4, 'CS206', '5'),
(22, 'CS302', 'Computer Security', 'Core', 'Cryptography, network security, and secure coding practices', 3, 'CS202', '5'),
(23, 'CS303', 'Compiler Design', 'Elective', 'Lexical analysis, parsing, and code generation', 3, 'CS206', '5'),
(24, 'CS304', 'Cloud Computing', 'Elective', 'Cloud architectures, virtualization, and distributed systems', 3, 'CS202', '5'),
(25, 'CS305', 'Human-Computer Interaction', 'Elective', 'User interface design and usability principles', 2, 'CS204', '5'),
(26, 'CS306', 'Machine Learning', 'Core', 'Supervised and unsupervised learning algorithms', 4, 'CS301,STAT201', '6'),
(27, 'CS307', 'Big Data Analytics', 'Elective', 'Hadoop, Spark, and data processing at scale', 3, 'CS201', '6'),
(28, 'CS308', 'Internet of Things', 'Elective', 'IoT architectures, sensors, and embedded systems', 3, 'CS202', '6'),
(29, 'CS309', 'Software Testing and QA', 'Core', 'Testing methodologies, automation, and quality assurance', 3, 'CS205', '6'),
(30, 'CS310', 'Blockchain Technology', 'Elective', 'Distributed ledgers, smart contracts, and cryptocurrencies', 2, 'CS202', '6'),
(31, 'CS401', 'Capstone Project I', 'Project', 'Research and development of major project - Phase 1', 4, 'CS205,CS206', '7'),
(32, 'CS402', 'Advanced Database Systems', 'Elective', 'NoSQL databases, data warehousing, and distributed databases', 3, 'CS201', '7'),
(33, 'CS403', 'Natural Language Processing', 'Elective', 'Text processing, sentiment analysis, and language models', 3, 'CS306', '7'),
(34, 'CS404', 'DevOps and CI/CD', 'Elective', 'Continuous integration, deployment pipelines, and automation', 3, 'CS205', '7'),
(35, 'CS405', 'Research Methodology', 'General', 'Research methods, academic writing, and ethics', 2, 'None', '7'),
(36, 'CS406', 'Capstone Project II', 'Project', 'Completion and presentation of major project - Phase 2', 4, 'CS401', '8'),
(37, 'CS407', 'Ethical Hacking', 'Elective', 'Penetration testing, vulnerability assessment, and security auditing', 3, 'CS302', '8'),
(38, 'CS408', 'Game Development', 'Elective', 'Game engines, physics simulation, and interactive entertainment', 3, 'CS207', '8'),
(39, 'CS409', 'Quantum Computing', 'Elective', 'Quantum algorithms and quantum information processing', 2, 'CS206', '8'),
(40, 'CS410', 'Professional Practice', 'General', 'Career development, interview skills, and industry practices', 2, 'None', '8'),
(41, 'IT201', 'Network Administration', 'Core', 'Server configuration, network management, and system administration', 4, 'CS202', '3'),
(42, 'IT202', 'IT Project Management', 'Core', 'Project planning, risk management, and team coordination', 3, 'None', '4'),
(43, 'IT203', 'IT Service Management', 'Core', 'ITIL framework, service delivery, and support', 3, 'None', '5'),
(44, 'IT204', 'Enterprise Systems', 'Elective', 'ERP, CRM, and business intelligence systems', 3, 'CS201', '6'),
(45, 'DS201', 'Data Mining', 'Core', 'Pattern recognition, clustering, and association rules', 4, 'CS206,STAT201', '5'),
(46, 'DS202', 'Data Visualization', 'Core', 'Visual analytics and interactive data representation', 3, 'CS201', '4'),
(47, 'DS203', 'Deep Learning', 'Core', 'Neural networks, CNNs, RNNs, and advanced architectures', 4, 'CS306', '6'),
(48, 'DS204', 'Time Series Analysis', 'Elective', 'Forecasting, trend analysis, and temporal data processing', 3, 'STAT201', '6'),
(49, 'SE201', 'Agile Software Development', 'Core', 'Scrum, Kanban, and agile methodologies', 3, 'CS205', '5'),
(50, 'SE202', 'Software Architecture', 'Core', 'Architectural patterns, microservices, and system design', 4, 'CS205', '5'),
(51, 'SE203', 'Requirements Engineering', 'Core', 'Gathering, analyzing, and managing software requirements', 3, 'CS205', '4'),
(52, 'SE204', 'User Experience Design', 'Elective', 'UX research, prototyping, and usability testing', 3, 'CS305', '6');

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `teacher_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `profile_image` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `employee_id` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `office_location` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `hire_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`teacher_id`, `user_id`, `name`, `profile_image`, `email`, `password`, `employee_id`, `department`, `phone`, `office_location`, `specialization`, `hire_date`, `status`, `created_at`) VALUES
(3, 8, 'Afraaz Ahmed', 'uploads/avatar_68da9e1e02d960.19445545.jpg', 'Afraaz', '$2y$10$UY5XdVwO3mR5z5oSjr6gYen7SMCJnhl/24EfyOyGCFN8HucpRmVXa', 'EMP001', 'Computer Science', '0771234567', 'Main Campus', 'Software Engineering', '2025-09-29', 'Active', '2025-09-29 08:13:04'),
(4, 12, 'fathima', 'uploads/avatar_690660a4a28222.39421862.jpg', 'fathima@gmail.com', '$2y$10$PUm/4FZYmzT/mlRMz0NrRuFfOFezBU7ljs2L.7i/JUGvkEE1032ra', 'E003', 'Business Department', '077562963', 'main Campus', 'business studies', '2025-11-01', 'Inactive', '2025-11-01 19:28:27');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_backpack`
--

CREATE TABLE `teacher_backpack` (
  `file_id` int(11) NOT NULL,
  `floc` varchar(100) NOT NULL,
  `fdatein` varchar(100) NOT NULL,
  `fdesc` varchar(100) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `fname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class`
--

CREATE TABLE `teacher_class` (
  `teacher_class_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `school_year` varchar(20) NOT NULL,
  `thumbnails` varchar(255) DEFAULT 'uploads/default-class.jpg',
  `status` enum('active','inactive','completed') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_class`
--

INSERT INTO `teacher_class` (`teacher_class_id`, `teacher_id`, `class_id`, `subject_id`, `school_year`, `thumbnails`, `status`, `created_at`, `updated_at`) VALUES
(33, 3, 1, 8, '2024-2025', 'uploads/1759160784_1754074801_Machine Learning realistic image.jpg', 'active', '2025-09-29 15:46:24', '2025-09-29 15:46:24'),
(36, 3, 7, 1, '2024-2025', 'uploads/1759161035_1753987230_Object-Oriented Programming (OOP) realistic image.jpg', 'active', '2025-09-29 15:50:35', '2025-09-29 15:50:35'),
(37, 3, 8, 4, '2024-2025', 'uploads/1759161476_web development image realistic.jpg', 'active', '2025-09-29 15:57:56', '2025-09-29 15:57:56'),
(38, 3, 9, 9, '2024-2025', 'uploads/1759176790_Mobile app development realistic.jpg', 'active', '2025-09-29 20:13:10', '2025-09-29 20:13:10'),
(39, 4, 13, 12, '2025-2026', 'uploads/1762025704_web development image realistic.jpg', 'active', '2025-11-01 19:35:04', '2025-11-01 19:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class_announcements`
--

CREATE TABLE `teacher_class_announcements` (
  `teacher_class_announcements_id` int(11) NOT NULL,
  `content` varchar(500) NOT NULL,
  `teacher_id` varchar(100) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `date` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_class_student`
--

CREATE TABLE `teacher_class_student` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `enrollment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('enrolled','dropped','completed') DEFAULT 'enrolled'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher_class_student`
--

INSERT INTO `teacher_class_student` (`id`, `teacher_id`, `teacher_class_id`, `student_id`, `enrollment_date`, `status`) VALUES
(1, 3, 33, 1, '2025-09-29 18:30:00', ''),
(2, 3, 36, 5, '2025-09-29 18:30:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `teacher_notification`
--

CREATE TABLE `teacher_notification` (
  `teacher_notification_id` int(11) NOT NULL,
  `teacher_class_id` int(11) NOT NULL,
  `notification` varchar(100) NOT NULL,
  `date_of_notification` varchar(100) NOT NULL,
  `link` varchar(100) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assignment_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `teacher_shared`
--

CREATE TABLE `teacher_shared` (
  `teacher_shared_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `shared_teacher_id` int(11) NOT NULL,
  `floc` varchar(100) NOT NULL,
  `fdatein` varchar(100) NOT NULL,
  `fdesc` varchar(100) NOT NULL,
  `fname` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `user_type` enum('admin','teacher','student') DEFAULT 'student',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `email`, `password`, `firstname`, `lastname`, `user_type`, `created_at`, `status`) VALUES
(1, 'azhafathi03@gmail.com', NULL, '$2y$10$ovESxp.c2uOF/NWtpJ0Gcu.I91kHpdushITzfXm05CsKa77jOaoHq', 'Azha', 'Nasar', 'student', '2025-09-29 06:35:57', 'active'),
(2, 'admin', NULL, 'admin123', 'Admin', 'User', 'admin', '2025-09-29 06:42:23', 'active'),
(8, 'Afraaz', NULL, '$2y$10$JDFTRDBTe20qaSmV3Dek3uSTDogd3cRKx/ZwuplWc4W1z.lQBcfFW', 'Afraaz', 'Ahmed', 'teacher', '2025-09-29 08:05:12', 'active'),
(9, '', 'azka@gmail.com', '$2y$10$Q7uTpANr99ddnONkNId25exQPAIxVBZmANbCGhLZyU6Mcs98uHlq6', 'Azka', 'Nasar', 'student', '2025-09-29 16:17:21', 'active'),
(10, 'ifaz@gmail.com', NULL, '$2y$10$Y3OmFmEA7tTlK1/QTk58EeeTmSMAwPHEHblO1iwc3eAzUSz1O9wJq', 'Ifaz', 'Ahmed', 'student', '2025-09-29 19:15:07', 'active'),
(11, '', 'anha@gmail.com', '$2y$10$4hb35ukMoYVVGvNfUIwneOUgPdjlRf2F/9tJVFRmWHAhkk0bKDduS', 'anha', '', 'student', '2025-11-01 15:12:08', 'active'),
(12, 'fathima@gmail.com', 'fathima@gmail.com', '$2y$10$PUm/4FZYmzT/mlRMz0NrRuFfOFezBU7ljs2L.7i/JUGvkEE1032ra', 'fathima', '', 'teacher', '2025-11-01 19:28:27', 'active'),
(13, 'Ayna@gmail.com', NULL, '$2y$10$7ZqJQz/9Uin7/FKkkJukfuY9zMVwn3yw6rkyoSIHPUVm7S9crIG1i', 'Ayna', 'Maryam', 'student', '2025-11-01 21:00:21', 'active'),
(14, 'infa@gmail.com', NULL, '$2y$10$ukb/q1rMNeLtaOlKNjG9MucKo9rPDPJwNfw/BPPh.zZHhlgkkv/8K', 'Infa', 'Zameer', 'student', '2025-11-02 21:03:09', 'active'),
(15, 'ishra@gmail.com', NULL, '$2y$10$4cSmOkNeIhobI/MMhYHthucprHPDADgY5F1pYMWeMtlABEZYxbhB.', 'Ishra', 'Zameer', 'student', '2025-11-02 21:10:22', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `user_log`
--

CREATE TABLE `user_log` (
  `user_log_id` int(11) NOT NULL,
  `username` varchar(25) NOT NULL,
  `login_date` varchar(30) NOT NULL,
  `logout_date` varchar(30) NOT NULL,
  `user_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `user_log`
--

INSERT INTO `user_log` (`user_log_id`, `username`, `login_date`, `logout_date`, `user_id`) VALUES
(1, 'admin', '2025-09-29 12:14:46', '', 2),
(2, 'admin', '2025-09-29 12:14:47', '', 2),
(3, 'admin', '2025-09-29 12:21:40', '', 2),
(4, 'admin', '2025-09-29 13:03:41', '', 2),
(5, 'admin', '2025-11-01 18:44:36', '', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`activity_log_id`);

--
-- Indexes for table `announcements`
--
ALTER TABLE `announcements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `answer`
--
ALTER TABLE `answer`
  ADD PRIMARY KEY (`answer_id`);

--
-- Indexes for table `assignment`
--
ALTER TABLE `assignment`
  ADD PRIMARY KEY (`assignment_id`);

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`class_id`);

--
-- Indexes for table `class_quiz`
--
ALTER TABLE `class_quiz`
  ADD PRIMARY KEY (`class_quiz_id`);

--
-- Indexes for table `class_subject_overview`
--
ALTER TABLE `class_subject_overview`
  ADD PRIMARY KEY (`class_subject_overview_id`);

--
-- Indexes for table `class_subject_teacher`
--
ALTER TABLE `class_subject_teacher`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `class_id` (`class_id`,`subject_id`,`semester`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `content`
--
ALTER TABLE `content`
  ADD PRIMARY KEY (`content_id`);

--
-- Indexes for table `department`
--
ALTER TABLE `department`
  ADD PRIMARY KEY (`department_id`);

--
-- Indexes for table `event`
--
ALTER TABLE `event`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `files`
--
ALTER TABLE `files`
  ADD PRIMARY KEY (`file_id`),
  ADD KEY `uploaded_by_idx` (`uploaded_by`),
  ADD KEY `class_id_idx` (`class_id`);

--
-- Indexes for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  ADD PRIMARY KEY (`timetable_id`),
  ADD KEY `teacher_id` (`teacher_id`);

--
-- Indexes for table `library_book`
--
ALTER TABLE `library_book`
  ADD PRIMARY KEY (`book_id`);

--
-- Indexes for table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`message_id`);

--
-- Indexes for table `message_sent`
--
ALTER TABLE `message_sent`
  ADD PRIMARY KEY (`message_sent_id`);

--
-- Indexes for table `notification`
--
ALTER TABLE `notification`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `user_type` (`user_type`),
  ADD KEY `seen` (`seen`);

--
-- Indexes for table `notification_read`
--
ALTER TABLE `notification_read`
  ADD PRIMARY KEY (`notification_read_id`);

--
-- Indexes for table `notification_read_teacher`
--
ALTER TABLE `notification_read_teacher`
  ADD PRIMARY KEY (`notification_read_teacher_id`);

--
-- Indexes for table `question_type`
--
ALTER TABLE `question_type`
  ADD PRIMARY KEY (`question_type_id`);

--
-- Indexes for table `quiz`
--
ALTER TABLE `quiz`
  ADD PRIMARY KEY (`quiz_id`);

--
-- Indexes for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD PRIMARY KEY (`question_id`),
  ADD KEY `quiz_id` (`quiz_id`);

--
-- Indexes for table `school_year`
--
ALTER TABLE `school_year`
  ADD PRIMARY KEY (`school_year_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `uk_student_user` (`user_id`),
  ADD UNIQUE KEY `uk_student_email` (`email`),
  ADD KEY `idx_class` (`class_id`);

--
-- Indexes for table `student_assignment`
--
ALTER TABLE `student_assignment`
  ADD PRIMARY KEY (`student_assignment_id`);

--
-- Indexes for table `student_backpack`
--
ALTER TABLE `student_backpack`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `student_class_quiz`
--
ALTER TABLE `student_class_quiz`
  ADD PRIMARY KEY (`student_class_quiz_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`subject_id`),
  ADD UNIQUE KEY `subject_code` (`subject_code`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`teacher_id`),
  ADD UNIQUE KEY `uk_teacher_user` (`user_id`),
  ADD UNIQUE KEY `uk_teacher_email` (`email`);

--
-- Indexes for table `teacher_backpack`
--
ALTER TABLE `teacher_backpack`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `teacher_class`
--
ALTER TABLE `teacher_class`
  ADD PRIMARY KEY (`teacher_class_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `class_id` (`class_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `teacher_class_announcements`
--
ALTER TABLE `teacher_class_announcements`
  ADD PRIMARY KEY (`teacher_class_announcements_id`);

--
-- Indexes for table `teacher_class_student`
--
ALTER TABLE `teacher_class_student`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment` (`teacher_class_id`,`student_id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `teacher_class_id` (`teacher_class_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `teacher_notification`
--
ALTER TABLE `teacher_notification`
  ADD PRIMARY KEY (`teacher_notification_id`);

--
-- Indexes for table `teacher_shared`
--
ALTER TABLE `teacher_shared`
  ADD PRIMARY KEY (`teacher_shared_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_log`
--
ALTER TABLE `user_log`
  ADD PRIMARY KEY (`user_log_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `activity_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `announcements`
--
ALTER TABLE `announcements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `answer`
--
ALTER TABLE `answer`
  MODIFY `answer_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `assignment`
--
ALTER TABLE `assignment`
  MODIFY `assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `class_quiz`
--
ALTER TABLE `class_quiz`
  MODIFY `class_quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `class_subject_overview`
--
ALTER TABLE `class_subject_overview`
  MODIFY `class_subject_overview_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `class_subject_teacher`
--
ALTER TABLE `class_subject_teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `content`
--
ALTER TABLE `content`
  MODIFY `content_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `department`
--
ALTER TABLE `department`
  MODIFY `department_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `event`
--
ALTER TABLE `event`
  MODIFY `event_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `files`
--
ALTER TABLE `files`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  MODIFY `timetable_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `library_book`
--
ALTER TABLE `library_book`
  MODIFY `book_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `message`
--
ALTER TABLE `message`
  MODIFY `message_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `message_sent`
--
ALTER TABLE `message_sent`
  MODIFY `message_sent_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification`
--
ALTER TABLE `notification`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `notification_read`
--
ALTER TABLE `notification_read`
  MODIFY `notification_read_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notification_read_teacher`
--
ALTER TABLE `notification_read_teacher`
  MODIFY `notification_read_teacher_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `question_type`
--
ALTER TABLE `question_type`
  MODIFY `question_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `quiz`
--
ALTER TABLE `quiz`
  MODIFY `quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `quiz_question`
--
ALTER TABLE `quiz_question`
  MODIFY `question_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `school_year`
--
ALTER TABLE `school_year`
  MODIFY `school_year_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `student_assignment`
--
ALTER TABLE `student_assignment`
  MODIFY `student_assignment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `student_backpack`
--
ALTER TABLE `student_backpack`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `student_class_quiz`
--
ALTER TABLE `student_class_quiz`
  MODIFY `student_class_quiz_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `subject_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `teacher_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `teacher_backpack`
--
ALTER TABLE `teacher_backpack`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_class`
--
ALTER TABLE `teacher_class`
  MODIFY `teacher_class_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;

--
-- AUTO_INCREMENT for table `teacher_class_announcements`
--
ALTER TABLE `teacher_class_announcements`
  MODIFY `teacher_class_announcements_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_class_student`
--
ALTER TABLE `teacher_class_student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `teacher_notification`
--
ALTER TABLE `teacher_notification`
  MODIFY `teacher_notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teacher_shared`
--
ALTER TABLE `teacher_shared`
  MODIFY `teacher_shared_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `user_log`
--
ALTER TABLE `user_log`
  MODIFY `user_log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class_subject_teacher`
--
ALTER TABLE `class_subject_teacher`
  ADD CONSTRAINT `class_subject_teacher_ibfk_1` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subject_teacher_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`subject_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `class_subject_teacher_ibfk_3` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE SET NULL;

--
-- Constraints for table `files`
--
ALTER TABLE `files`
  ADD CONSTRAINT `fk_files_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_files_teacher` FOREIGN KEY (`uploaded_by`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `lecturer_timetable`
--
ALTER TABLE `lecturer_timetable`
  ADD CONSTRAINT `lecturer_timetable_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`teacher_id`) ON DELETE CASCADE;

--
-- Constraints for table `quiz_question`
--
ALTER TABLE `quiz_question`
  ADD CONSTRAINT `quiz_question_ibfk_1` FOREIGN KEY (`quiz_id`) REFERENCES `quiz` (`quiz_id`) ON DELETE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `fk_student_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`class_id`),
  ADD CONSTRAINT `fk_student_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `fk_teacher_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
