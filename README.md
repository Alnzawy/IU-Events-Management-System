# IU EVENTS
## Intelligent & Effective Event Management System for the Islamic University

IU EVENTS is a web-based event management platform developed for the **Islamic University of Madinah**. It provides a centralized environment for creating and managing university events, student registration, attendance tracking, certificate issuance, feedback collection, AI-powered recommendations, and event analytics.

The system is designed around three main user roles: **Students, Hosts, and Organizers**, supporting the event lifecycle from publication to post-event evaluation.

---

## Table of Contents

- [Project Overview](#project-overview)
- [Problem Statement](#problem-statement)
- [Project Objectives](#project-objectives)
- [User Roles](#user-roles)
- [Key Features](#key-features)
- [Technology Stack](#technology-stack)
- [Database Design](#database-design)
- [System Diagrams](#system-diagrams)
- [Screenshots](#screenshots)
  - [Project Plan](#1-project-plan)
  - [Survey Results](#2-survey-results)
  - [System Analysis and Design](#3-system-analysis-and-design)
  - [Implementation](#4-implementation)
- [AI Recommendation Module](#ai-recommendation-module)
- [Analytics Dashboard](#analytics-dashboard)
- [Development Methodology](#development-methodology)
- [Running the Project](#running-the-project)
- [Future Enhancements](#future-enhancements)
- [Academic Information](#academic-information)
- [Team Members](#team-members)
- [Acknowledgment](#acknowledgment)

---

## Project Overview

University events play an important role in student engagement, campus activities, and community development. However, traditional announcement methods such as messaging groups, social media, and posters can make event information difficult to access and event coordination challenging.

IU EVENTS addresses these challenges through a centralized web platform that allows:

- Organizers to create, publish, schedule, and monitor events.
- Students to discover events, register, and track their participation.
- Hosts to record attendance and issue certificates.
- Organizers to evaluate event performance using analytics and feedback.
- Students to receive personalized event recommendations based on their selected interests.

The platform was developed as a Bachelor's Degree project at the Islamic University of Madinah.

## Problem Statement

Traditional university event management methods can result in:

- Event announcements being distributed across multiple communication channels.
- Incomplete or unclear information about event dates, locations, and registration.
- Difficulty tracking registrations and available seats.
- Limited attendance records and participation statistics.
- Lack of structured student feedback.
- Difficulty evaluating event effectiveness and planning future activities.

IU EVENTS provides a centralized solution to improve event accessibility, coordination, and evaluation.

## Project Objectives

- Provide a centralized platform for university event announcements.
- Simplify event browsing and student registration.
- Display available seats and enforce hall capacity limits.
- Prevent scheduling conflicts involving halls and hosts.
- Support attendance recording and certificate issuance.
- Collect student feedback after events.
- Provide AI-based event recommendations.
- Offer organizers useful statistics and visual analytics.

## User Roles

### 1. Student

Students can:

- Browse upcoming university events.
- View event details, dates, times, halls, and available seats.
- Register for events.
- View registered events and participation status.
- Access and download issued certificates.
- Submit feedback after attending events.
- Receive personalized AI-based event recommendations.

### 2. Host

Hosts can:

- View their assigned events.
- Preview event session details.
- View registered students.
- Record student attendance.
- Issue certificates to students marked as attended.

### 3. Organizer

Organizers can:

- Create, edit, and delete events.
- Assign hosts and halls.
- Select event dates and time slots.
- Check hall and host scheduling conflicts.
- Monitor registrations and remaining seat capacity.
- Access event statistics and analytics.

## Key Features

| Feature | Description |
|---|---|
| Role-Based Access | Separate interfaces and permissions for students, hosts, and organizers. |
| Event Management | Create, edit, publish, and delete university events. |
| Hall Scheduling | Assign halls and check scheduling conflicts. |
| Host Assignment | Assign hosts and check their availability. |
| Student Registration | Allow students to register for available events. |
| Seat Availability | Display remaining seats and enforce capacity limits. |
| Attendance Tracking | Allow hosts to record attendance. |
| Certificates | Issue attendance certificates and provide student downloads. |
| Feedback | Collect student ratings and comments. |
| AI Recommendations | Suggest events based on student-selected interests. |
| Analytics | Display attendance, satisfaction, registration, and hall utilization insights. |

## Technology Stack

| Technology | Purpose |
|---|---|
| HTML5 | Structure of the web pages. |
| CSS3 | Styling, layouts, dark mode, glassmorphism, and animations. |
| JavaScript | Client-side interactivity, event wizard, calendar, and scheduling visualization. |
| PHP | Server-side logic, authentication, sessions, and database operations. |
| MySQL | Relational database management. |
| MySQLi | Database connectivity using prepared statements. |
| Bootstrap 5.3 | Responsive interface components and layouts. |
| Chart.js | Analytics charts and visualizations. |
| Google Gemini 2.0 Flash API | AI-powered event recommendations. |
| XAMPP | Local development environment. |

## Database Design

The system uses a relational MySQL database consisting of nine core tables:

| Table | Purpose |
|---|---|
| Students | Student profiles, academic information, interests, and credentials. |
| Hosts | Host profiles, contact information, and certificate signatures. |
| Organizers | Organizer information and account credentials. |
| Events | Event descriptions, dates, times, assigned halls, and hosts. |
| Halls | Hall information and seating capacities. |
| Registrations | Student registrations and registration status. |
| Attendance | Attendance records and attendance status. |
| Certificates | Issued certificates and their associated students and events. |
| Feedback | Student ratings and comments about events. |

Primary keys and foreign keys are used to define relationships and maintain data integrity.

## System Diagrams

The project includes system analysis and design diagrams that describe its structure, behavior, and workflows.

- **Use Case Diagram:** Illustrates the interactions between students, hosts, organizers, and the system.
- **Activity Diagram:** Describes the event browsing and registration workflow.
- **Data Flow Diagram (DFD):** Represents the movement of data between system processes.
- **Entity Relationship Diagram (ERD):** Defines the database entities and relationships.
- **Class Diagram:** Illustrates the system's classes and associations.
- **Sequence Diagrams:** Describe authentication, event creation, registration, attendance, and post-event activities.

## Screenshots

The following screenshots document the project planning, survey findings, system design, and implemented interfaces.

> **Image organization:** Place the screenshots in the `screenshots/` directory in the repository root. The filenames below are the recommended naming convention used by this README.

### 1. Project Plan

**Figure 1 – Incremental SDLC Project Plan**

![Project Plan](screenshots/Figure-01.png)

This Gantt chart presents the development schedule, project phases, team responsibilities, and timeline using the Incremental Software Development Life Cycle model.

### 2. Survey Results

The project collected 48 student responses to understand existing event announcement practices and evaluate the proposed system's features.

**Figure 2 – Current Event Announcement Methods**

![Current Event Announcement Methods](screenshots/Figure-02.png)

Shows the channels students currently use to discover university events, including messaging groups and social media.

**Figure 3 – Satisfaction with the Current Announcement Process**

![Announcement Satisfaction](screenshots/Figure-03.png)

Presents students' satisfaction levels with the existing event announcement methods.

**Figure 4 – Ease of Finding Event Announcements**

![Ease of Finding Announcements](screenshots/Figure-04.png)

Illustrates how easily students can find information about university events.

**Figure 5 – Opinion on a Centralized Event Portal**

![Centralized Event Portal](screenshots/Figure-05.png)

Shows students' opinions about having one centralized platform for university event information.

**Figure 6 – Helpfulness of Real-Time Seat Availability**

![Real-Time Seat Availability](screenshots/Figure-06.png)

Presents students' evaluation of the usefulness of viewing available seats before registering.

**Figure 7 – Usefulness of AI-Based Event Suggestions**

![AI Event Suggestions Survey](screenshots/Figure-07.png)

Illustrates students' opinions about receiving personalized event recommendations.

**Figure 8 – Most Helpful Features Preferred by Students**

![Preferred Features](screenshots/Figure-08.png)

Summarizes the features students considered most useful in the proposed IU EVENTS platform.

**Figure 9 – Preferred Registration Confirmation Method**

![Registration Confirmation Preferences](screenshots/Figure-09.png)

Shows students' preferred method for receiving confirmation after event registration.

**Figure 10 – Organizers' Evaluation of Management Features**

![Organizer Feature Evaluation](screenshots/Figure-10.png)

Presents the perceived usefulness of registration statistics, attendance tracking, and feedback collection.

**Figure 11 – Usefulness of Event Feedback Systems**

![Feedback System Survey](screenshots/Figure-11.png)

Illustrates students' opinions about using feedback to improve future university events.

**Figure 12 – Additional Suggested Features**

![Additional Suggested Features](screenshots/Figure-12.png)

Summarizes additional improvements and features suggested by survey participants.

### 3. System Analysis and Design

**Figure 13 – Use Case Diagram**

![Use Case Diagram](screenshots/Figure-13.png)

Illustrates the main system functions and how students, hosts, and organizers interact with IU EVENTS.

**Figure 14 – Activity Diagram**

![Activity Diagram](screenshots/Figure-14.png)

Describes the workflow of browsing events and completing the registration process.

**Figure 15 – Data Flow Diagram (DFD)**

![Data Flow Diagram](screenshots/Figure-15.png)

Shows how information moves between users, system processes, and data storage.

**Figure 16 – Entity Relationship Diagram (ERD)**

![Entity Relationship Diagram](screenshots/Figure-16.png)

Presents the database entities and their relationships, including events, users, registrations, attendance, certificates, and feedback.

**Figure 17 – Class Diagram**

![Class Diagram](screenshots/Figure-17.png)

Illustrates the main system classes and their relationships.

**Figure 18 – Authentication and Dashboard Access Sequence Diagram**

![Authentication Sequence Diagram](screenshots/Figure-18.png)

Shows the interaction sequence for user authentication and accessing the appropriate dashboard.

**Figure 19 – Event Creation Sequence Diagram**

![Event Creation Sequence Diagram](screenshots/Figure-19.png)

Describes the interactions involved in creating an event.

**Figure 20 – Event Registration Sequence Diagram**

![Event Registration Sequence Diagram](screenshots/Figure-20.png)

Illustrates the registration process and the communication between the student and system components.

**Figure 21 – Event Execution and Attendance Sequence Diagram**

![Attendance Sequence Diagram](screenshots/Figure-21.png)

Shows the workflow for managing an event session and recording student attendance.

**Figure 22 – Post-Event Activities Sequence Diagram**

![Post Event Sequence Diagram](screenshots/Figure-22.png)

Describes the activities that take place after an event, including related attendance, certificates, and feedback operations.

### 4. Implementation

#### Project Management

**Figure 23 – Project Development Gantt Chart**

![Project Gantt Chart](screenshots/Figure-23.png)

Illustrates the project development plan, assigned team responsibilities, and implementation timeline.

#### Organizer Module

**Figure 24 – Event Creation Interface**

![Event Creation Interface](screenshots/Figure-24.png)

Shows the organizer's event creation interface, where event information and required details are entered through a multi-step wizard.

**Figure 25 – Hall Scheduling and Time Selection**

![Hall Scheduling](screenshots/Figure-25.png)

Displays the hall and time selection interface, supporting scheduling and conflict detection for halls and assigned hosts.

**Figure 26 – Event Monitoring and Registration Overview**

![Event Monitoring](screenshots/Figure-26.png)

Shows the organizer's event overview, including registration information and remaining seat capacity.

#### Host Module

**Figure 27 – Attendance Recording Interface**

![Attendance Recording](screenshots/Figure-27.png)

Displays the host interface for viewing registered students and marking their attendance.

**Figure 28 – Certificate Issuance and Attendance Confirmation**

![Certificate Issuance](screenshots/Figure-28.png)

Shows the certificate issuance process for students recorded as attending an event.

**Figure 29 – Upcoming Event Session Preview**

![Upcoming Host Event](screenshots/Figure-29.png)

Presents the details of an upcoming event assigned to a host, including its date, time, and hall.

**Figure 30 – Host Dashboard Overview**

![Host Dashboard](screenshots/Figure-30.png)

Displays the host dashboard, providing an overview of assigned event activities.

#### Student Module

**Figure 31 – Browsing Upcoming Events**

![Upcoming Events](screenshots/Figure-31.png)

Shows the student event browsing interface, where available events and their details can be explored.

**Figure 32 – Event Registration and Seat Availability**

![Event Registration](screenshots/Figure-32.png)

Displays the registration confirmation interface, including event details, certification availability, and remaining seats.

**Figure 33 – Personalized Student Dashboard**

![Student Dashboard](screenshots/Figure-33.png)

Shows the student's personal dashboard, including registered events and their participation statuses.

#### Recommendation Module

**Figure 34 – Personalized AI Event Recommendations**

![AI Recommendations](screenshots/Figure-34.png)

Displays personalized event suggestions generated using the student's selected interests and the Google Gemini 2.0 Flash API.

#### Analytics Module

**Figure 35 – Organizer Analytics Dashboard**

![Organizer Analytics Dashboard](screenshots/Figure-35.png)

Presents the organizer's analytics dashboard, providing visual insights into attendance, satisfaction, registration drop-off, feedback participation, hall utilization, and event performance.

## AI Recommendation Module

IU EVENTS integrates the **Google Gemini 2.0 Flash API** to provide personalized event recommendations.

The recommendation process consists of:

1. Students select their interests from predefined categories.
2. The system uses the selected interests and available event information as input to the recommendation process.
3. The Gemini API generates event suggestions.
4. Recommended events are displayed in a dedicated section of the student event exploration interface.

This feature aims to make event discovery more relevant to students' interests.

## Analytics Dashboard

The organizer analytics dashboard supports event evaluation and decision-making through the following indicators:

| Metric | Description |
|---|---|
| Overall Attendance Rate | Percentage of registered students who attended. |
| Average Student Satisfaction | Average rating from submitted feedback forms, out of 5. |
| Registration Drop-off Rate | Percentage of registered students who did not attend. |
| Feedback Participation Rate | Percentage of attendees who submitted feedback. |
| Attendance Trend | Attendance patterns across event dates. |
| Hall Utilization Rate | Percentage of hall capacity used. |
| Event Retention Funnel | Tracks the stages Registered → Attended → Feedback. |
| Top 6 Events | Ranks events using 70% satisfaction and 30% registration count. |

Chart.js is used to visualize attendance trends and hall utilization.

## Development Methodology

The project follows the **Incremental Software Development Life Cycle (SDLC)** model.

Development was divided into functional increments, including:

1. Authentication and role-based access.
2. Event creation and management.
3. Student browsing and registration.
4. Attendance tracking and certificate issuance.
5. AI-based recommendations.
6. Analytics and dashboard functionality.

This approach supported progressive implementation and testing of the system's interconnected modules.

## Running the Project

IU EVENTS was developed and tested in a local XAMPP environment.

To run the project locally:

1. Install and open **XAMPP**.
2. Start the Apache and MySQL services.
3. Place the project folder inside the XAMPP `htdocs` directory.
4. Create the required MySQL database using the database structure or SQL export included with the project, if provided.
5. Configure the application's database connection settings to match your local MySQL configuration.
6. Configure the Google Gemini API credentials if you want to use the AI recommendation feature.
7. Open the project through your browser using the corresponding localhost project path.

**Note:** The exact database name, SQL import filename, and database configuration file depend on the source-code files included in the repository.

### API Key Security

Do not publish private API keys or database passwords in a public repository. Keep credentials in a local configuration file and exclude that file from Git using `.gitignore`.

## Future Enhancements

The following improvements were identified for future development:

- **Email Notifications:** Automated registration confirmations and certificate notifications.
- **Mobile Application:** A dedicated mobile experience for students and event participants.
- **University Portal Integration:** Integration with the official university student information system.
- **Cloud Deployment:** Deployment to a cloud environment to improve scalability and availability.
- **Role Expansion:** Additional roles and event approval workflows.
- **Multi-Language Support:** Support for additional languages to improve accessibility.

## Academic Information

**Project Title:** IU EVENTS: Intelligent & Effective Event Management System for the Islamic University

**Degree:** Bachelor's Degree in Information Technology

**Institution:** Islamic University of Madinah

**Faculty:** Faculty of Computer and Information Systems

**Supervisor:** Dr. Sohail Mohamed Alhazmi

**Date:** April 2026

## Team Members

| Name | Student ID |
|---|---|
| Abdulrahman Abdulaziz Alnzawy | 431011003 |
| Abdullah Mohamed Aljohani | 431013303 |
| Jawad Fouad Alahmadi | 431004163 |

## Acknowledgment

We would like to express our sincere gratitude to our project supervisor, **Dr. Sohail Mohamed Alhazmi**, for his generous support, guidance, and valuable feedback throughout the development of IU EVENTS.

His experience and advice played an important role in guiding the project and improving its overall quality.

---

**IU EVENTS — Making University Events More Accessible, Organized, and Effective.**
