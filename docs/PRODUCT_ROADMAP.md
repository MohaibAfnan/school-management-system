# School Management System — Product Roadmap

## Product vision

Build a dependable, secure, and easy-to-use school platform that connects school administration, teachers, students, and families. The system should make everyday work faster, give each person a clear view of what they need to do, and provide school leaders with accurate information for decisions.

The product should grow from a strong single-school foundation into a configurable platform that can support multiple campuses and different school policies. Advanced capabilities should follow stable core workflows rather than add complexity before the underlying records and permissions are reliable.

## Product principles

1. **One trusted record:** Keep student, staff, academic, attendance, assessment, and finance records consistent and traceable.
2. **Role-appropriate experiences:** Show each user only the tools and records needed for their responsibilities.
3. **Clear workflows:** Make common tasks—admissions, attendance, grading, fee collection, and reporting—quick to complete and easy to audit.
4. **Secure by default:** Protect personal and student data through least-privilege access, strong authentication, audit history, and safe data handling.
5. **Configurable, not hard-coded:** Let schools configure academic years, grading scales, attendance rules, fee schedules, and notification preferences.
6. **Accessible and responsive:** Support keyboard use, readable contrast, mobile screens, and low-bandwidth conditions.
7. **Measured growth:** Deliver useful end-to-end capabilities in phases, with clear acceptance criteria and operational monitoring.

## Current project foundation

The repository currently uses Laravel 13, PHP 8.3, Inertia, Vue 3, and Tailwind CSS. It has authentication and account settings, role-aware user helpers, a dashboard, and initial models and migrations for:

- Students and teachers
- Classrooms, courses, and course enrollments
- Attendance and grades
- Lessons and assignments

This is a useful starting point, not a complete school system. The current role model is basic, guardian information is stored as fields on a student, and there are not yet full workflows for admissions, timetables, grading periods, fees, parent accounts, notifications, or audit records. Existing data structures should be reviewed and strengthened before adding production workflows.

## Users and access

### Primary users

- **School owner / system administrator:** Configures the school, campuses, academic years, users, permissions, and integrations.
- **Principal / academic administrator:** Oversees admissions, classes, staffing, timetables, attendance, academic progress, and reports.
- **Finance officer:** Manages fee schedules, invoices, receipts, adjustments, reconciliation, and financial reports.
- **Teacher:** Views assigned classes, takes attendance, manages lessons and assignments, records assessments, and communicates with families.
- **Student:** Views their timetable, attendance, assignments, results, announcements, and permitted learning materials.
- **Parent / guardian:** Views linked students, attendance, academic progress, fee balances, school notices, and communications.
- **Operations staff:** Handles assigned tasks in areas such as transport, library, inventory, or staff administration.

### Access model

Use role-based permissions with campus, class, and record scope where appropriate. Avoid relying on UI hiding alone: every read and write must be authorized on the server. Support multiple roles per person where a school needs them, and keep an audit record for sensitive actions. A parent account may be linked to more than one student; a student may have more than one authorized guardian.

## Feature scope

### 1. School setup and administration

- School profile, branding, contact details, timezone, locale, and academic calendar
- Academic years, terms/semesters, holidays, and configurable grading periods
- Campuses, buildings, rooms, capacity, and room facilities
- User invitations, role assignment, permissions, account status, and onboarding
- Import and export tools with validation previews, error reports, and duplicate detection
- Configurable identifiers and numbering conventions for students, staff, invoices, and classes

### 2. Admissions and student records

- Enquiry and application intake, document checklist, review stages, and admission decisions
- Student profiles, enrollment history, status changes, transfers, and withdrawal records
- Guardian profiles, relationships, emergency contacts, pickup permissions, and communication preferences
- Medical and support notes with restricted access and a clear record of who viewed or changed them
- Document storage with access controls and retention rules
- Bulk enrollment, class placement, waitlists, and promotion to the next grade/year

### 3. Academic structure and scheduling

- Grade levels, sections, subjects/courses, teacher assignments, and class rosters
- Academic-year-specific enrollment and course offerings
- Timetable creation with teacher, room, class, and student conflict checks
- Recurring and one-off schedule events, substitutions, room changes, and calendar views
- Room capacity and resource constraints
- Timetable publication with change notifications and history

### 4. Attendance and safeguarding

- Daily and lesson-level attendance, late arrival, early departure, excused absence, and other configurable statuses
- Teacher-friendly roster entry, bulk actions, correction requests, and authorized approval
- Attendance alerts to guardians through configured channels, with quiet hours and delivery status
- Absence reasons and supporting evidence with limited access
- Attendance summaries by student, class, course, date range, and academic period
- Late pickup and safeguarding incident workflows with restricted visibility and audit history

### 5. Teaching, assignments, and learning

- Course pages, lesson plans, learning resources, and reusable lesson templates
- Assignment creation, due dates, rubrics, attachments, extensions, and class-level distribution
- Student submissions, teacher feedback, resubmissions, and plagiarism-check integration as an optional later capability
- Progress tracking and overdue-work reminders
- Optional learning-management integrations through documented APIs

### 6. Assessments and report cards

- Assessment categories, rubrics, grading scales, weighting, and moderation workflow
- Exams, quizzes, coursework, practical assessments, and configurable grade boundaries
- Grade entry with validation, missing-grade indicators, and bulk import
- Grade review, approval, locking, and controlled amendment after publication
- Report cards with attendance, grades, teacher comments, school branding, and downloadable PDF output
- Student progress history and parent/student access after publication
- Clear treatment of withdrawn, incomplete, exempt, and reassessed work

### 7. Fees and finance

- Fee structures by academic year, grade, campus, student category, and optional service
- Invoices, installment plans, discounts, scholarships, waivers, credits, and overdue balances
- Payment recording, receipt numbering, refunds, adjustments, and reconciliation
- Cash, bank transfer, card, and region-appropriate payment-provider support
- Finance permissions separated from academic permissions; immutable transaction history
- Automated reminders with configurable schedules and guardian delivery preferences
- Financial reports for collections, arrears, discounts, refunds, and reconciliation status

Finance rules vary by institution and jurisdiction. Confirm local tax, accounting, payment, and record-retention requirements before implementing or integrating payment flows.

### 8. Communication and family engagement

- Announcements targeted by campus, grade, class, course, or user role
- Teacher-parent messages with student context, moderation controls, and communication history
- Email and in-app notifications first; SMS, push, and messaging integrations as configured options
- Notification preferences, delivery tracking, retry handling, and quiet hours
- School calendar, events, permission slips, and response tracking
- Translation-ready content and locale-aware date, time, and number formats

### 9. Operations modules (later, configurable)

- Staff directory, leave requests, substitutions, and basic HR records
- Library catalog, lending, returns, reservations, and overdue reminders
- Transport routes, stops, vehicle/driver assignments, student riders, and pickup changes
- Inventory and asset register, stock movement, purchasing requests, and issue tracking
- Facility maintenance requests, priority, assignment, and resolution history

These modules should be added only when the school's operational priorities are confirmed. Keep them modular so a school can enable only what it needs.

### 10. Dashboards, reports, and insights

- Role-specific dashboards with clear links to the underlying records
- Enrollment, attendance, academic progress, finance, and operational reports
- Filters by campus, year, term, class, subject, and date range
- CSV/PDF exports governed by the same authorization rules as the screens
- Scheduled reports and delivery to approved recipients
- Data quality indicators and exception queues (for example, missing attendance or unassigned guardians)
- Later: trend analysis and early-support indicators with explainable rules, human review, and safeguards against automated high-impact decisions

### 11. Platform and integration capabilities

- Responsive web application and installable progressive web app for common staff workflows
- Stable, documented API with scoped access, versioning, rate limits, and integration logs
- Optional integrations for identity providers, email/SMS delivery, payment providers, accounting, and learning platforms
- Import templates and data migration support for onboarding from spreadsheets or legacy systems
- Background queues for reports, imports, bulk notifications, and document generation
- Monitoring, error reporting, backups, restore procedures, and operational runbooks

## Delivery roadmap

### Phase 0 — Product and data foundation

- Confirm target school type, country/region, terminology, grading approach, fee rules, and first user groups
- Review existing migrations and models; define academic year, term, campus, section, guardian, and permission boundaries
- Establish naming conventions, data validation, authorization patterns, audit strategy, and migration policy
- Seed a safe demonstration environment with fictional data
- Define UI patterns for tables, search, filters, empty states, forms, and mobile workflows

**Exit criteria:** agreed initial scope, data dictionary, role/permission matrix, and a working development setup with documented configuration.

### Phase 1 — Secure operational MVP

- School setup and academic-year configuration
- User and role management with server-side authorization
- Student, guardian, teacher, class, and course records
- Admissions/enrollment basics and class rosters
- Attendance entry, correction, and summary views
- Dashboard and essential CSV exports
- Audit history for sensitive record changes

**Exit criteria:** an administrator can set up a school, enroll a student, assign a teacher, take attendance, and produce an authorized attendance report end to end.

### Phase 2 — Teaching and academic outcomes

- Timetables and conflict validation
- Assignments, lesson resources, assessments, and configurable grading
- Grade review and report-card publication
- Student and guardian portal views
- Notifications for attendance, due dates, and published results

**Exit criteria:** a teacher can manage a course from roster and lessons through assessments and published results, with students and guardians seeing only permitted information.

### Phase 3 — Fees and communication

- Fee schedules, invoices, receipts, adjustments, and balances
- Payment recording and reconciliation; provider integration only after finance requirements are confirmed
- Announcements, calendar events, guardian messaging, and delivery tracking
- Finance reports and controlled exports

**Exit criteria:** finance staff can reconcile a billing period and families can see their own invoices and receipts without access to other students' data.

### Phase 4 — Campus operations and scale

- Select and deliver the highest-priority optional operations modules
- Multi-campus administration, advanced reporting, API integrations, and scheduled exports
- PWA/offline-aware attendance capture if field conditions require it
- Performance, accessibility, backup/restore, and disaster-recovery improvements

**Exit criteria:** the system supports the confirmed scale and operational workflows with documented monitoring, recovery, and support procedures.

## Technical direction

- Continue with the current Laravel + Inertia + Vue stack unless a measured product need justifies a change.
- Keep business rules in well-defined server-side actions/services and enforce authorization through policies and scoped queries.
- Use database constraints and transactions for enrollment, timetable conflicts, grades, and financial records.
- Treat academic year and campus as first-class scope where records need them; avoid overwriting history when a student changes class or status.
- Prefer append-only audit and transaction history for sensitive changes; use explicit correction records where financial or academic history must be amended.
- Use queues for long-running or bulk work and store generated exports with temporary, authorized access.
- Design integrations behind small interfaces so providers can be replaced without rewriting school workflows.
- Add database indexes and performance tuning based on expected roster sizes and measured query behavior.

## Security, privacy, and reliability requirements

- Enforce least-privilege access and test access boundaries for students, guardians, teachers, and staff.
- Protect authentication with verified accounts, secure sessions, rate limiting, and multi-factor options for privileged users.
- Validate and sanitize uploads; restrict file types and sizes; store private documents outside public paths.
- Encrypt transport and secrets; never commit `.env` files, credentials, student data, or production exports.
- Record actor, time, action, and relevant change context for sensitive data and administration events.
- Define backup frequency, retention, restore testing, incident response, data retention, and account offboarding.
- Minimize personal data collection and make guardian/student data visible only to authorized users.
- Confirm applicable privacy, education-record, accessibility, and finance requirements for the deployment region before launch.

## Definition of done for each feature

- The workflow and permissions are documented and approved for the target role(s).
- Validation, empty states, loading states, errors, and success feedback are handled.
- Server-side authorization and data-scope checks are in place.
- Changes preserve required historical records and produce audit events where appropriate.
- The interface works on supported screen sizes and can be used with a keyboard.
- Tests cover core success paths, validation failures, permission boundaries, and important edge cases.
- Operational notes cover configuration, migrations, backup implications, and any external service dependency.

## Open product decisions

Resolve these with the school before locking detailed implementation requirements:

1. Which country/region, school levels, and languages are in the first release?
2. Is the first deployment for one school or multiple campuses/schools?
3. Which academic-year, term, grading, promotion, and report-card rules apply?
4. What are the initial user roles and what can each role view, create, approve, and export?
5. Which attendance rules and guardian alert channels are required?
6. What fee categories, payment methods, discounts, tax rules, and accounting exports are needed?
7. Which optional module matters most after academics: library, transport, HR, inventory, or facilities?
8. What data must be migrated, and what retention/deletion rules apply?
9. What hosting, backup, availability, and support expectations apply?

## Immediate next step

Use this roadmap to agree the first school's workflows and priority order. Then convert Phase 0 and Phase 1 into user stories, acceptance criteria, and a reviewed data model before changing production-facing schemas or beginning a broad feature build.
