# 🎓 COLLEGE EVENT MANAGEMENT SYSTEM

A comprehensive **web-based platform** designed for educational institutions to manage **events** and **polls**. This lightweight PHP + JSON solution enables **Admins** to create/manage events and polls, and **Students** to register for events and participate in polls — all enhanced with **real-time notifications**.

---

## 📑 TABLE OF CONTENTS

- [Features](#features)
- [Technologies Used](#technologies-used)
- [System Requirements](#system-requirements)
- [Usage Guide](#usage-guide)

---

## 🚀 FEATURES

### 🔐 Authentication System
- Secure login/registration
- Role-based access (Admin / Student)
- Session-based login
- Password encryption using `password_hash()`

### 👩‍💼 Admin Features
- Dashboard with statistics
- Create/edit/delete events
- Set event details (title, date, location, capacity)
- View event registrations
- Create/manage polls
- Activate/deactivate polls
- View results (with who voted)

### 🎓 Student Features
- Personal activity dashboard
- One-click event registration
- Browse registered events
- Vote in active polls
- View polls participated in

### 🔔 Notification System
- Real-time alerts for events/polls
- Unread notification count
- Dedicated notifications page
- "Mark as read" & auto-cleanup

---

## 💻 TECHNOLOGIES USED

**Frontend:**
- HTML5
- CSS3 (responsive design)
- JavaScript (Vanilla JS)

**Backend:**
- PHP 7+

**Data Storage:**
- JSON files (No database required!)

**Authentication:**
- Session-based
- Password hashing (`password_hash()`)

---

## ⚙️ SYSTEM REQUIREMENTS

- **Web Server:** Apache, Nginx or compatible with PHP
- **PHP Version:** 7.0+
- **Browser:** Modern (Chrome, Firefox, Edge, Safari)
- **Permissions:** Write access to `/data/` directory

---

# 📘 USAGE GUIDE – College Event Management System

This section explains how **Administrators** and **Students** can use the Event Management System to perform their respective tasks effectively.

---

## 👩‍💼 For Administrators

### 1. Login to Admin Dashboard
- Use your **admin credentials** (e.g., `admin / admin123`) to log in via `index.php`.
- Upon successful login, you’ll be redirected to the **Admin Dashboard**.

### 2. Create an Event
- Click on the **"Events"** tab in the navigation menu.
- Select **"Create New Event"**.
- Fill in the event details:
  - Title
  - Description
  - Date and time
  - Location
  - Maximum participants
- Click **"Create Event"** to save the event.

### 3. Create a Poll
- Navigate to the **"Polls"** section.
- Click **"Create New Poll"**.
- Enter:
  - Poll Title
  - Description (optional)
  - At least **two options**
- Click **"Create Poll"** to activate the poll.

### 4. Monitor Activity
- Click **"View Registrations"** to see who has signed up for events.
- Click **"View Results"** in the polls section to see live voting stats.
- Use the dashboard to check system usage stats and activity summaries.

---

## 🎓 For Students

### 1. Login to Student Dashboard
- Use your **student credentials** (e.g., `student1 / student123`) to log in.
- You’ll be redirected to the **Student Dashboard** upon successful login.

### 2. Register for an Event
- Go to the **"Events"** tab.
- Browse through upcoming or active events.
- Click **"Register"** next to any event you'd like to attend.

### 3. Participate in Polls
- Open the **"Polls"** tab.
- Select one of the active polls.
- Choose your option and click **"Submit Vote"**.

### 4. Check Notifications
- Click the 🔔 **notification bell icon** in the header.
- View unread alerts or visit the **Notifications** page for full history.

---





