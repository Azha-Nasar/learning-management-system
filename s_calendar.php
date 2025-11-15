<?php
session_start();
include('dbcon.php');

if (!isset($_SESSION['student_id'])) {
    header("Location: index.php");
    exit();
}

$student_id = $_SESSION['student_id'];

// Fetch student's timetable events
$events_query = "SELECT 
                   lt.timetable_id,
                   lt.class_name,
                   lt.subject,
                   lt.start_datetime,
                   lt.end_datetime,
                   lt.description,
                   t.name as teacher_name
                 FROM lecturer_timetable lt
                 JOIN teacher t ON lt.teacher_id = t.teacher_id
                 JOIN teacher_class tc ON lt.teacher_id = tc.teacher_id
                 JOIN teacher_class_student tcs ON tc.teacher_class_id = tcs.teacher_class_id
                 WHERE tcs.student_id = ?
                 ORDER BY lt.start_datetime DESC
                 LIMIT 50";

$stmt = $conn->prepare($events_query);

if (!$stmt) {
    die("Query preparation failed: " . $conn->error);
}

$stmt->bind_param("i", $student_id);
$stmt->execute();
$events = $stmt->get_result();

include('student_layout.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Calendar | EduHub LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
    <style>
        .calendar-container {
            background: white;
            padding: 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            height: 100%;
        }

        #calendar {
            max-width: 100%;
        }

        .fc-event {
            cursor: pointer;
            border-radius: 4px;
            padding: 2px 4px;
        }

        .event-card {
            background: white;
            border-radius: 8px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            box-shadow: 0 1px 4px rgba(0,0,0,0.08);
            border-left: 4px solid #3498db;
            transition: all 0.3s ease;
        }

        .event-card:hover {
            transform: translateX(5px);
            box-shadow: 0 2px 8px rgba(0,0,0,0.12);
        }

        .event-card.assignment {
            border-left-color: #f39c12;
        }

        .event-card.assessment {
            border-left-color: #e74c3c;
        }

        .event-title {
            font-size: 1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 0.5rem;
        }

        .event-meta {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .event-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            margin-bottom: 0.5rem;
        }

        .event-badge.assignment {
            background-color: #fff3cd;
            color: #f39c12;
        }

        .event-badge.assessment {
            background-color: #f8d7da;
            color: #e74c3c;
        }

        .event-badge.class {
            background-color: #d1ecf1;
            color: #3498db;
        }

        .upcoming-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 1.5rem;
            border-radius: 10px;
            margin-bottom: 1rem;
        }

        .events-sidebar {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 1.5rem;
            max-height: 700px;
            overflow-y: auto;
        }

        .events-sidebar::-webkit-scrollbar {
            width: 6px;
        }

        .events-sidebar::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 10px;
        }

        .events-sidebar::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 10px;
        }

        .events-sidebar::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .no-events {
            text-align: center;
            padding: 2rem;
            color: #6c757d;
        }

        .no-events i {
            font-size: 3rem;
            opacity: 0.3;
            margin-bottom: 1rem;
        }

        .page-header {
            margin-bottom: 1.5rem;
        }
    </style>
</head>
<body>

<div class="container-fluid mt-4">
    <div class="page-header">
        <h4>
            <i class="fas fa-calendar-alt me-2"></i>My Calendar & Schedule <hr>
        </h4>
    </div>

    <div class="row">
        <!-- Left Column - Calendar -->
        <div class="col-lg-8 mb-4">
            <div class="calendar-container">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Right Column - Upcoming Events -->
        <div class="col-lg-4">
            <div class="upcoming-header">
                <h5 class="mb-0">
                    <i class="fas fa-clock me-2"></i>Upcoming Events
                </h5>
            </div>
            
            <div class="events-sidebar">
                <?php if ($events->num_rows > 0): ?>
                    <?php 
                    $current_datetime = date('Y-m-d H:i:s');
                    $has_upcoming = false;
                    $events_array = [];
                    
                    while($event = $events->fetch_assoc()) {
                        $events_array[] = $event;
                    }
                    
                    // Sort by date ascending for upcoming view
                    usort($events_array, function($a, $b) {
                        return strtotime($a['start_datetime']) - strtotime($b['start_datetime']);
                    });
                    
                    foreach($events_array as $event):
                        if ($event['start_datetime'] >= $current_datetime):
                            $has_upcoming = true;
                    ?>
                        <div class="event-card class">
                            <span class="event-badge class">Class</span>
                            <div class="event-title">
                                <?= htmlspecialchars($event['subject']) ?>
                            </div>
                            <div class="event-meta">
                                <strong><?= htmlspecialchars($event['class_name']) ?></strong>
                            </div>
                            <div class="event-meta">
                                <i class="fas fa-user-tie me-1"></i>
                                <?= htmlspecialchars($event['teacher_name']) ?>
                            </div>
                            <div class="event-meta">
                                <i class="fas fa-calendar me-1"></i>
                                <?= date('M j, Y', strtotime($event['start_datetime'])) ?>
                            </div>
                            <div class="event-meta">
                                <i class="fas fa-clock me-1"></i>
                                <?= date('h:i A', strtotime($event['start_datetime'])) ?> - 
                                <?= date('h:i A', strtotime($event['end_datetime'])) ?>
                            </div>
                            <?php if (!empty($event['description'])): ?>
                                <div class="event-meta mt-2">
                                    <i class="fas fa-info-circle me-1"></i>
                                    <?= htmlspecialchars($event['description']) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php 
                        endif;
                    endforeach;
                    
                    if (!$has_upcoming):
                    ?>
                        <div class="no-events">
                            <i class="fas fa-calendar-check"></i>
                            <p class="mb-0">No upcoming events scheduled</p>
                        </div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="no-events">
                        <i class="fas fa-calendar-times"></i>
                        <p class="mb-0">No events found</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var calendarEl = document.getElementById('calendar');
    
    var calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: [
            <?php 
            // Reset pointer for calendar events
            $event_items = [];
            foreach($events_array as $event): 
                $event_items[] = sprintf(
                    "{title: '%s', start: '%s', end: '%s', color: '#3498db', extendedProps: {className: '%s', teacher: '%s'}}",
                    addslashes($event['subject']),
                    $event['start_datetime'],
                    $event['end_datetime'],
                    addslashes($event['class_name']),
                    addslashes($event['teacher_name'])
                );
            endforeach;
            echo implode(",\n", $event_items);
            ?>
        ],
        eventClick: function(info) {
            var eventDetails = 'Subject: ' + info.event.title + '\n' +
                             'Class: ' + info.event.extendedProps.className + '\n' +
                             'Teacher: ' + info.event.extendedProps.teacher + '\n' +
                             'Time: ' + info.event.start.toLocaleString();
            alert(eventDetails);
        },
        height: 'auto',
        eventTimeFormat: {
            hour: '2-digit',
            minute: '2-digit',
            meridiem: 'short'
        }
    });
    
    calendar.render();
});
</script>
</body>
</html>