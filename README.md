# IU EVENTS
## Intelligent & Effective Event Management System for the Islamic University

A web-based event management system developed to centralize and improve the process of creating, organizing, browsing, and managing university events at the Islamic University of Madinah.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Problem Statement](#problem-statement)
- [Project Objectives](#project-objectives)
- [System Users](#system-users)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Database Design](#database-design)
- [System Diagrams](#system-diagrams)
- [System Screenshots](#system-screenshots)
- [AI-Powered Recommendations](#ai-powered-recommendations)
- [Analytics Dashboard](#analytics-dashboard)
- [Development Methodology](#development-methodology)
- [Installation and Setup](#installation-and-setup)
- [Future Improvements](#future-improvements)
- [Academic Information](#academic-information)
- [Project Team](#project-team)
- [Acknowledgments](#acknowledgments)

---

## Project Overview

IU EVENTS is a web-based event management platform designed to improve how university events are announced, organized, and attended.

The system provides a centralized platform where students can discover upcoming events, register for available sessions, track their registrations, receive personalized event recommendations, and obtain certificates after attending eligible events.

It also provides dedicated interfaces for hosts and organizers to manage events, assign halls, monitor registrations, record attendance, issue certificates, collect feedback, and review event performance.

The project aims to make university event management more organized, accessible, and efficient.

## Problem Statement

University event announcements and management activities can be distributed across different communication channels. This makes it difficult for students to discover events and for organizers to manage registrations, available seats, schedules, and attendance in one place.

IU EVENTS addresses these challenges by providing a centralized system that brings the main event management activities together.

## Project Objectives

- Provide a centralized platform for university event announcements.
- Simplify event creation and management.
- Allow students to browse and register for events.
- Display available seats and registration information.
- Reduce scheduling conflicts involving halls and event time slots.
- Support attendance recording and certificate issuance.
- Collect student feedback to help improve future events.
- Provide AI-powered event recommendations.
- Offer analytics to help organizers evaluate event performance.

## System Users

The system supports three main user roles.

### 1. Student

Students can:
- Browse upcoming university events.
- View event details and available seats.
- Register for events.
- Track their registration status.
- Access their personalized dashboard.
- View AI-based event recommendations.
- Download certificates for eligible attended events.
- Submit feedback after events.

### 2. Host

Hosts can:
- View events assigned to them.
- Preview upcoming event sessions.
- Record student attendance.
- Confirm attendance information.
- Support certificate issuance for attended students.

### 3. Organizer

Organizers can:
- Create, edit, and delete events.
- Assign hosts and halls.
- Select event dates and time slots.
- Monitor event registrations.
- Identify scheduling conflicts.
- Review event statistics and analytics.
- Manage event-related activities.

## Key Features

### Event Management
- Create, update, and delete events.
- Manage event information and schedules.
- Assign halls and hosts.
- Monitor event registration activity.

### Student Registration
- Browse available events.
- Register for events through the student interface.
- View available seats.
- Track registration information through a personal dashboard.

### Hall Scheduling
- Select halls and event time slots.
- Detect scheduling conflicts.
- Help organizers avoid overlapping event bookings.

### Attendance Management
- Allow hosts to record attendance.
- Track student participation.
- Use attendance information to support certificate issuance.

### Certificates
- Generate and provide certificates for eligible attendees.
- Allow students to download their certificates in PDF format.

### Feedback
- Collect student feedback after events.
- Provide information that can help organizers improve future activities.

### AI-Based Recommendations
- Recommend events based on students' selected interest pillars.
- Use the Google Gemini API to support personalized event suggestions.

### Analytics
- Present event performance information.
- Track attendance, registration, satisfaction, and feedback.
- Help organizers evaluate the effectiveness of university events.

## Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 | Web page structure |
| CSS3 | Styling, layouts, dark mode, and visual effects |
| JavaScript | Interactive interfaces and client-side functionality |
| PHP | Server-side logic, authentication, and system operations |
| MySQL | Database management |
| MySQLi | Database connectivity and prepared statements |
| Bootstrap 5.3 | Responsive interface components |
| Chart.js | Data visualization and analytics |
| Google Gemini 2.0 Flash API | AI-powered event recommendations |
| XAMPP | Local development environment |

## Database Design

The system database contains nine main tables:

| Table | Description |
|---|---|
| Students | Stores student-related information |
| Hosts | Stores host information |
| Organizers | Stores organizer information |
| Events | Stores event details and schedules |
| Halls | Stores hall information |
| Registrations | Records student event registrations |
| Attendance | Stores event attendance records |
| Certificates | Stores certificate information |
| Feedback | Stores student feedback |

The database is designed to support the relationships and operations required by the event management system.

## System Diagrams

The following diagrams illustrate the system architecture, workflows, database structure, and interactions between users and system components.

### Figure 1 – Project Development Plan

![Project Plan](screenshots/Figure%201.%20Project%20Plan%20for%20the%20Development%20of%20the%20IU%20EVENTS%20System%20Using%20the%20Incremental%20SDLC%20Model.png)

This figure presents the project development plan based on the Incremental SDLC model.

### Figure 2 – Use Case Diagram

![Use Case Diagram](screenshots/Figure%202.%20Use%20case%20diagram%20illustrating%20the%20main%20interactions%20within%20the%20IU%20EVENTS%20system..svg)

This diagram illustrates the main system users and their interactions with IU EVENTS.

### Figure 3 – Activity Diagram

![Activity Diagram](screenshots/Figure%203.%20Activity%20diagram%20showing%20the%20workflow%20of%20event%20browsing%20and%20registration%20in%20IU%20EVENTS..svg)

This diagram shows the workflow followed when students browse events and complete the registration process.

### Figure 4 – Data Flow Diagram (DFD)

![Data Flow Diagram](screenshots/Figure%204.%20Data%20Flow%20Diagram%20(DFD)%20representing%20the%20movement%20of%20data%20across%20system%20processes..svg)

This diagram represents how data moves between users, system processes, and data storage.

### Figure 5 – Entity Relationship Diagram (ERD)

![Entity Relationship Diagram](screenshots/Figure%205.%20Entity%20Relationship%20(ER)%20diagram%20defining%20the%20system%E2%80%99s%20database%20structure%20and%20relationships..svg)

This diagram illustrates the database entities and the relationships between them.

### Figure 6 – Class Diagram

![Class Diagram](screenshots/Figure%206.%20Class%20diagram%20outlining%20system%20classes%20and%20their%20associations%20in%20IU%20EVENTS..svg)

This diagram outlines the main system classes and their associations.

### Figure 7 – Authentication and Dashboard Access

![Authentication Sequence Diagram](screenshots/Figure%207.%20Sequence%20Diagram%20Shows%20Authentication%20%26%20Dashboard%20Access.svg)

This sequence diagram illustrates the authentication process and how users access their dashboards.

### Figure 8 – Event Creation Sequence

![Event Creation Sequence Diagram](screenshots/Figure%208.%20Sequence%20Diagram%20Shows%20Event%20Creation.svg)

This diagram shows the interactions involved in creating a new event.

### Figure 9 – Event Registration Sequence

![Event Registration Sequence Diagram](screenshots/Figure%209.%20Sequence%20Diagram%20Shows%20Event%20Registration.svg)

This diagram describes the sequence of interactions during student event registration.

### Figure 10 – Event Execution and Attendance

![Event Execution and Attendance](screenshots/Figure%2010.%20Sequence%20Diagram%20Shows%20Event%20Execution%20%26%20Attendance.svg)

This diagram illustrates the event execution process and attendance recording activities.

### Figure 11 – Post-Event Activities

![Post Event Activities](screenshots/Figure%2011%20Sequence%20Diagram%20Shows%20Post%20Event%20Activities.svg)

This diagram presents the activities performed after an event, including post-event processes.

### Figure 12 – Gantt Chart and Project Timeline

![Gantt Chart](screenshots/Figure%2012%20The%20Gantt%20chart%20illustrates%20the%20Project%20development%20plan,%20team%20roles,%20and%20timeline%20for%20IU%20EVENTS..svg)

This chart illustrates the project development timeline, team responsibilities, and planned activities.

## System Screenshots

The following screenshots demonstrate the main interfaces and features implemented in IU EVENTS.

### Figure 13 – Event Creation Interface

![Event Creation Interface](screenshots/Figure%2013%20Event%20Creation%20Interface%20in%20the%20Organizer%20Module.png)

This interface allows organizers to create events and enter the required event information.

### Figure 14 – Hall Scheduling and Time Selection

![Hall Scheduling](screenshots/Figure%2014%20Hall%20Scheduling%20and%20Time%20Selection.png)

This interface allows organizers to select halls and event time slots while supporting scheduling conflict detection.

### Figure 15 – Event Monitoring and Registration Overview

![Event Monitoring](screenshots/Figure%2015%20Event%20Monitoring%20and%20Registration%20Overview.png)

This screen provides organizers with an overview of event information and registration activity.

### Figure 16 – Attendance Recording Interface

![Attendance Recording](screenshots/Figure%2016%20Attendance%20Recording%20Interface%20in%20the%20Host%20Portal.png)

This interface enables hosts to record student attendance for their assigned events.

### Figure 17 – Certificate Issuance and Attendance Confirmation

![Certificate Issuance](screenshots/Figure%2017%20Certificate%20Issuance%20and%20Attendance%20Confirmation.png)

This screen demonstrates the attendance confirmation and certificate issuance process.

### Figure 18 – Upcoming Event Session Preview

![Upcoming Event Session Preview](screenshots/Figure%2018%20Upcoming%20Event%20Session%20Preview%20for%20Hosts.png)

This interface allows hosts to preview their upcoming event sessions.

### Figure 19 – Host Dashboard Overview

![Host Dashboard](screenshots/Figure%2019%20Host%20Dashboard%20Overview.png)

This dashboard provides hosts with an overview of their event-related activities.

### Figure 20 – Browsing Upcoming Events

![Browsing Upcoming Events](screenshots/Figure%2020%20Browsing%20Upcoming%20Events.png)

This screen allows students to explore upcoming university events.

### Figure 21 – Event Registration and Seat Availability

![Event Registration](screenshots/Figure%2021%20Event%20Registration%20and%20Seat%20Availability.png)

This interface displays event registration information and available seats to students.

### Figure 22 – Personalized Student Dashboard

![Student Dashboard](screenshots/Figure%2022%20Personalized%20Student%20Dashboard.png)

This dashboard provides students with a personalized view of their event-related information and registrations.

### Figure 23 – AI Event Recommendations

![AI Event Recommendations](screenshots/Figure%2023%20Personalized%20AI%20Event%20Recommendations%20Display.png)

This screen presents personalized event suggestions based on student interests using the AI recommendation feature.

### Figure 24 – Organizer Analytics Dashboard

![Organizer Analytics Dashboard](screenshots/Figure%2024%20Organizer%20Analytics%20Dashboard.png)

This dashboard presents event analytics to help organizers monitor performance and evaluate activities.

## AI-Powered Recommendations

IU EVENTS integrates the Google Gemini 2.0 Flash API to support personalized event recommendations.

The recommendation feature uses students' selected interest pillars to help suggest events that may be relevant to their interests.

The AI integration is intended to improve event discovery and provide students with a more personalized experience.

## Analytics Dashboard

The organizer analytics dashboard provides information that supports event evaluation and decision-making.

The project includes analytical measures such as:

- Overall attendance.
- Average satisfaction.
- Registration drop-off.
- Feedback participation.
- Attendance trends.
- Hall utilization.
- Event retention funnel.
- Top-performing events.

The top events evaluation uses a weighted approach based on satisfaction (70%) and registration (30%).

## Development Methodology

The project followed the Incremental Software Development Life Cycle (SDLC).

The system was developed through incremental stages, allowing features to be planned, implemented, and evaluated progressively.

The development process included requirements gathering, system analysis, design, implementation, and testing.

## Installation and Setup

IU EVENTS was developed to run in a local XAMPP environment.

### Requirements

- XAMPP.
- Apache Web Server.
- MySQL.
- PHP.
- A modern web browser.
- Google Gemini API key for AI recommendation functionality.

### Setup Instructions

1. Install and open XAMPP.
2. Start Apache and MySQL.
3. Place the project folder inside the XAMPP `htdocs` directory.
4. Open phpMyAdmin.
5. Import the project's database SQL file, if available.
6. Configure the database connection settings in the appropriate configuration file.
7. Configure the Gemini API credentials if you want to use the AI functionality.
8. Open the project through your browser using the local XAMPP address.

**Important:** The exact database export filename and database configuration values depend on the files included in the project. Configure these according to your local setup.

### Security Note

Do not publish API keys, database passwords, or other sensitive credentials in a public repository. Use local configuration or environment variables for private credentials.

## Future Improvements

The following improvements were identified for future development:

- Email notifications for event updates and registration confirmations.
- A dedicated mobile-friendly interface.
- Integration with the university portal.
- Cloud deployment.
- Additional user roles and permissions.
- Multi-language support.

These are proposed future improvements and should not be considered implemented features.

## Academic Information

**Project Title:** IU EVENTS: Intelligent & Effective Event Management System for the Islamic University

**Institution:** Islamic University of Madinah

**Faculty:** Faculty of Computer and Information Systems

**Degree:** Bachelor's Degree

**Date:** April 2026

**Supervisor:** Dr. Sohail Mohamed Alhazmi

## Project Team

| Name | University ID |
|---|---|
| Abdulrahman Abdulaziz Alnzawy | 431011003 |
| Abdullah Mohamed Aljohani | 431013303 |
| Jawad Fouad Alahmadi | 431004163 |

## Acknowledgments

We would like to express our sincere gratitude to our project supervisor, Dr. Sohail Mohamed Alhazmi, for his guidance, support, and valuable feedback throughout the development of this project.

We also thank the Faculty of Computer and Information Systems at the Islamic University of Madinah for providing the academic environment and resources that supported this work.

---

**IU EVENTS – Intelligent & Effective Event Management System for the Islamic University**

*A graduation project developed at the Islamic University of Madinah, April 2026.*
