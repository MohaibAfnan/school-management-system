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
- A class teacher can enter attendance for their assigned class and can correct or remove an entry for up to 10 seconds after saving. Measure this window using the server's saved timestamp, never the device clock.
- After the 10-second correction window, a teacher must submit an admin request to update or remove student attendance. The approved correction is recorded as an auditable amendment; preserve the original event rather than physically erasing history.
- Admin attendance review, bulk actions, correction requests, and class/date locking
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

### 12. Teacher mobile app and approval workflows

- Teachers have a dedicated, responsive web portal as well as the mobile app. Both show only their own profile, assigned classes, subjects, timetable, and students needed for teaching.
- A class teacher can record attendance for their assigned class and can update/delete a just-saved entry for 10 seconds, measured on the server. After that window, changes require an admin request and approval; retain the original record and correction history.
- Teachers can create result/assessment entries for their assigned classes and subjects. New entries should be drafts until the configured review/publish workflow is complete.
- Teachers can read records in their assignment scope. Updates and deletions to protected records (including published results and subject assignments) require a formal request unless the administrator has granted a time-limited permission. Attendance follows its separate 10-second correction window and admin approval rule.
- A teacher may request temporary update permission for a specific module, class/subject scope, and duration, with a reason. The administrator can approve or reject it; an approval must automatically expire at the stated time and cannot exceed the requested scope.
- Subject catalogue and official class/teacher assignments should remain administratively governed. Teachers can request a new subject or a change; an administrator approves it before it becomes a shared school record.
- Teacher profile displays their own permitted HR information, timetable, attendance history, leave request/status, and salary statements. Salary and HR data are private to the teacher and authorized HR/finance staff.

### 13. Teacher attendance, leave, and payroll self-service

- Teacher check-in/attendance is a separate workflow from student classroom attendance. The teacher submits their own attendance through the app; it remains pending until an authorized administrator approves it. Corrections also go through an admin request.
- Teachers may submit leave requests with leave type, date range, reason, optional supporting document, and handover/substitute details where required. The leave ledger, balances, approvals, rejections, and official leave record are managed only by authorized administrators. Validate date conflicts and available balance before an admin decision.
- Admins receive an in-app notification and an email when a teacher submits an attendance approval request, attendance correction, or leave request. Teachers receive an in-app status update and optional email when a decision is made.
- Approval, rejection, comments, changes, and timestamps are recorded in an audit trail. Email notifications alert the administrator but do not themselves approve a request.
- Payroll/HR users maintain salary structures, pay periods, payslips, and authorized adjustments. Teachers can view and download only their own published salary statements; the app should not calculate statutory payroll obligations until the deployment region and rules are confirmed.

### 14. Student mobile app and student self-service

- Students have a dedicated, responsive web portal and mobile app. They can read only their own published results, fee statements, invoices/receipts, LMS courses and materials, timetable, subjects, class teacher, announcements, and profile.
- Students can edit only an explicit allowlist of low-risk profile fields. Changes save to the shared application database and become visible to authorized web and mobile users; sensitive changes such as legal name, date of birth, guardian links, class placement, and student identifier require an administrator request/approval.
- Every profile change is validated, timestamped, and attributable to its user. Students cannot edit grades, attendance records, fees, class/course membership, or another student's information.
- Parent/guardian access is a separate role linked to explicitly authorized students. Guardians can view their linked students' permitted records and receive school communications, but cannot impersonate the student.

## Deployment and licensing modes

Use one application codebase and one permission model with explicit deployment profiles. Deployment mode changes integrations and connectivity behavior; it must not weaken record-level authorization. The hosted product is a multi-tenant SaaS: tenant isolation is a core data-model and authorization boundary, not a later feature.

### SaaS tenant isolation foundation

- Create a school/tenant record for every customer and require a tenant context for every school-owned read, write, background job, notification, export, and uploaded file.
- Store a `school_id` foreign key on tenant-owned records, including users, student and teacher profiles, classes, courses, enrollments, attendance, grades, lessons, assignments, finance, and school settings. Add tenant-aware unique constraints (for example, student number, teacher number, course code, and class name within a school). Keep email globally unique in the initial release because Laravel's standard reset-token store is keyed by email; move to a shared identity/membership model before allowing one email to belong to multiple school workspaces.
- Resolve the tenant from a validated school workspace code during login (with a later option for verified custom domains/subdomains). Store the active tenant in the authenticated session and validate membership on every request.
- Apply server-side tenant scopes and authorization policies. Fail closed when tenant context is missing. Never trust a `school_id` supplied by a form, URL, mobile client, or job payload without checking it against the authenticated tenant.
- Keep school roles, permissions, SMTP settings, license/plan state, and configuration scoped to the tenant. Platform support access, if introduced, must be a separate privileged role with explicit, audited access.
- Treat tenant isolation as a release-blocking security requirement: test cross-tenant access on direct IDs, relationships, searches, exports, file downloads, queues, notifications, password-reset flows, and API endpoints.

For the initial release, a person account is scoped to one school workspace and email is globally unique. If guardians or staff must use one identity across multiple schools, add an explicit tenant-membership model, tenant-aware password-reset storage, and a school switcher; do not weaken tenant filters to make shared logins work.

### Local / offline installation

- The Laravel application and database run on a school computer or local server. A single-computer setup is reachable at localhost; a school-wide installation uses a local network host reachable by staff devices.
- Core attendance, timetable, student, teaching, and reporting workflows remain available without internet after setup. Integrations that require the internet (SMTP, SMS, payment gateways, push notifications, cloud backups, and remote updates) are clearly marked unavailable until connectivity returns.
- For offline activation, the administrator submits an activation request when outbound internet is available. The app sends a minimal request through configured SMTP to the software provider. The provider issues a signed, installation-scoped license credential that can be delivered by call, WhatsApp, or file and verified locally.
- Bind licenses to a generated installation ID, not an IP address. IP addresses change, can be shared behind NAT, and are not a reliable installation identity. The request may include an IP as an optional audit signal, subject to privacy policy.
- The app stores outbound requests locally if the network is unavailable and offers a clear retry action. After activation, ordinary school use does not require a continuous connection to the provider.
- Keep the license-signing private key outside the application. The application embeds only the verification key. Re-activation or device replacement follows the same request/review/issue process and creates a new auditable license event.

### Online hosted installation

- Host the Laravel application and database on a secure live server. The web app and mobile apps connect to that same backend over HTTPS; mobile clients must never connect directly to the database.
- Operate as a multi-tenant SaaS with school-level data isolation, tenant-aware login/workspace selection, tenant-specific settings, backups, and subscription/feature entitlements.
- Keep platform-level activation and SaaS subscription state separate from a school's users, role permissions, and optional school SMTP configuration.
- The backend is the source of truth for authentication, permissions, approvals, data validation, audit history, notifications, and synchronization.
- Use a versioned API with scoped access tokens, rate limits, request validation, and revocation. Store tokens securely on devices and support lost-device/session revocation.
- Queue email, push notifications, report generation, and other slow work. Use a supported push provider for mobile alerts, with in-app notification records retained by the backend.
- Decide whether each school gets a separate deployment/database or whether the service will host multiple schools. If multi-school hosting is planned, implement tenant isolation from the beginning and test every query, file, notification, export, and API route against cross-tenant access.

### Dynamic behavior shared by both modes

- Expose a small set of explicit, validated server-side capabilities such as `deployment_mode`, `mail_available`, `push_available`, and `license_status`; clients should adapt their interface to these capabilities rather than infer them from URLs.
- An offline deployment may continue using local accounts and local data. Online email, SMS, payment, remote license, and cloud backup features require their configured service and connectivity.
- Backups, updates, license renewal, notification retries, and time-sensitive approvals need defined behavior when a device is offline and when it reconnects.
- School SMTP settings are tenant-specific and used for school-user invitations, verification, password recovery, and school notices. Store SMTP secrets encrypted server-side, mask them in the UI, and provide an authorized test-send action. Never load one school's mail credentials for another school's messages.
- Use a separate platform mail channel for SaaS operator notices and hosted activation/subscription messages. In a local installation, the configured local SMTP transport may send the activation request to the software provider.
- Show connection/sync state and the last successful sync in mobile apps. If offline mobile use is supported later, cache only necessary records, encrypt local storage, and resolve edits through server-side conflict handling after reconnection.

## Role-based access and approval model

Do not rely on one broad role string or hide buttons in the interface as the security boundary. Use role-based permissions combined with record scope and server-side policy checks.

| Role | Default access |
| --- | --- |
| Owner / system administrator | School configuration, user/role management, deployment settings, and oversight; sensitive actions are audited. |
| Principal / academic administrator | Academic structure, class assignments, student records, approvals, and academic reports for assigned school/campus scope. |
| Teacher | Read assigned classes, subjects, timetable, and necessary student records; create allowed attendance/result drafts; request protected changes and temporary permissions. |
| Student | Read their own published academic, LMS, fee, timetable, and profile data; edit only approved profile fields. |
| Parent / guardian | Read permitted records for explicitly linked students and receive communications; cannot access unrelated students. |
| Finance / HR staff | Role-specific finance or employment/payroll records, with scope and audit history; no automatic academic administration access. |

### Account lifecycle and default permissions

- Disable unrestricted public self-registration. Create the first school administrator through a one-time setup flow; after setup, admins invite or provision users and assign roles. Students and guardians receive accounts only after their school records and relationships are established.
- Keep account authentication separate from school role assignment and product activation. A valid login alone must not grant access to school records.
- When a teacher is hired or a student is admitted, an administrator creates or provisions the account and assigns its role/scope. If the person has an email address and SMTP is available, send a time-limited, single-use invitation link so they can set their own password; do not email a permanent password or plaintext credential.
- Administrators can resend/revoke an invitation and see delivery and account-status events, but never see the user's password, reset token, or activation secret. Resending issues a new token and invalidates the prior unused token.
- Users can change their password after authentication and request a reset through their verified email when SMTP is configured. Reset links are single-use, expire, are rate-limited, and revoke other sessions after successful reset. Log the event metadata for admin oversight without exposing the password or token.
- In a fully offline installation, provide an administrator-assisted reset/activation path using a one-time credential that must be replaced at first use. Clearly distinguish this from email verification; do not expose verification codes or reset secrets in the UI or application logs.
- The shared account database is the canonical source: a successful profile or credential update applies to the same account across web and mobile clients. Invalidate old sessions/tokens as appropriate, notify the user, and show admins an audit event (who/which account, when, channel, and outcome), never the credential value.
- In SaaS mode, scope account lookup, invitations, password reset, verification, and throttling to the selected school workspace. A reset request in one school must never reveal or reset an account in another school.
- Apply these defaults on both web and mobile. “Request” means a durable approval record with status and audit history, not a client-side button that temporarily bypasses authorization.

| Resource / action | Student | Teacher | Admin / authorized staff |
| --- | --- | --- | --- |
| Own profile | Read; edit allow-listed fields; request protected changes | Read; edit allow-listed own fields; request protected changes | Manage within assigned school scope; sensitive changes audited |
| Student profile and guardian data | Own permitted profile only | Read assigned students, minimum necessary fields | Manage within campus/school scope; restricted fields audited |
| Published results | Read own results | Read assigned records; create drafts; request update/delete or use an approved temporary grant | Review, publish, correct, or approve within assigned scope |
| Subjects and course assignments | Read own current subjects | Read assigned subjects; request catalogue/assignment changes | Create and manage official catalogue and assignments |
| Student classroom attendance | Read own attendance | Assigned class teacher records attendance; update/delete for 10 seconds after save; thereafter request admin approval | Review/approve later corrections, manage policy, and audit decisions |
| Teacher attendance and leave | Not applicable | Submit own attendance/leave requests; read own history and decisions | Owns and manages the leave ledger; approve/reject teacher attendance and leave requests |
| Fees and payments | Read own permitted statements and receipts | No finance access by default | Finance role manages transactions; admin access only when explicitly granted |
| Salary and payslips | Not applicable | Read own published records only | HR/finance roles manage payroll records; access audited |
| LMS, timetable, announcements | Read own published/assigned items | Manage permitted content for assigned classes; protected changes follow workflow | Configure and oversee within assigned scope |

For records with an approval workflow, creation should produce a draft or pending item where required. Do not expose pending grades, attendance changes, or requests to students and guardians as if they were final.

### Temporary permissions and approval requests

- Model permissions as explicit actions such as `results.create`, `results.update`, `results.delete`, `attendance.submit`, `attendance.correct`, `subjects.request`, `profile.update`, and `leave.submit`.
- Scope each permission to a school/campus and, where appropriate, specific class, subject, student, or record. Deny access when no scope is present.
- A temporary grant records who requested it, the reason, requested scope, approver, granted scope, start/end time, and revocation/expiry. Enforce the grant on every backend request and expire it automatically.
- For student classroom attendance, the 10-second edit/delete window starts at the server-recorded save time. After it expires, even the class teacher must use the admin-request workflow; administrator approval is required and the original attendance event remains auditable.
- A request has a type, target record or proposed change, reason, attachments if needed, state, assigned approver, decision, comments, and full audit history. States include submitted, pending, approved, rejected, cancelled, and expired where relevant.
- Email and in-app messages alert the administrator to new requests. Decisions happen in the authenticated admin interface, not by replying to email. Notify the requester when the request is decided or a temporary grant is about to expire.
- Where safe, let teachers create a draft immediately but require review before publishing. For destructive or historical changes, use a correction workflow rather than deleting the original record.
- Add a configurable approval chain for leave, attendance corrections, grade publication, fee adjustments, and other sensitive workflows. Do not allow a user to approve their own request.

## Mobile application architecture

- Build teacher/student mobile clients as API consumers of the same Laravel service and database used by the web app.
- Keep authorization, business rules, and validation on the backend. A mobile screen hiding a button is never a substitute for backend permission checks.
- Use a shared API contract for login/session renewal, profiles, class/subject assignments, timetable, attendance, results, fees, LMS content, approvals, and notifications.
- Support device/session revocation, app version policy, secure token storage, and clear handling for lost connectivity. The API returns only the user's permitted records.
- Require an authenticated tenant context on every API request and ensure device tokens cannot switch schools by changing a request parameter.
- Start with online mobile use against the hosted backend. Add encrypted read-only offline access or queued submissions only after data sensitivity, conflict resolution, and device security requirements are agreed.

## Activation request and notification workflow

1. The school administrator completes local setup and enters the school/contact details needed for activation.
2. The administrator selects **Request activation**. If SMTP is configured and outbound internet is available, the app emails a request ID and generated installation ID to the provider. Do not include student data, passwords, or SMTP credentials.
3. The app confirms submission and says: “Your activation request was sent. Please allow up to 5 minutes. If it has not been activated, contact your software provider.” Treat five minutes as an expected response time, not a guaranteed system timer.
4. The provider reviews the request and issues a cryptographically signed license scoped to that installation and selected plan/entitlements. The private signing key is held only by the provider.
5. The provider relays the credential through the agreed channel. The administrator enters/imports it; the application verifies it locally with the embedded public key and records activation.
6. For renewal, reinstall, or hardware replacement, the administrator submits a new request. The app shows request history and the current license state; it does not silently copy a license to a different installation.
7. If SMTP cannot send, save the request as pending and show how to retry when connectivity returns. Do not display verification codes or license secrets in logs or the browser page.

This flow is a manual product-license activation process, not proof that a school user's email address belongs to them. User account verification should be handled separately and can be optional/configurable for a truly offline deployment.

## Delivery roadmap

### Phase 0 — Product and data foundation

- Confirm target school type, country/region, terminology, grading approach, fee rules, and first user groups
- Confirm local/offline, hosted single-school, or hosted multi-school deployment targets and initial licensing expectations
- Review existing migrations and models; define academic year, term, campus, section, guardian, role, permission, approval, and installation boundaries
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
- Teacher account setup and approval request foundation
- Deployment profiles for local/offline and hosted operation, with signed installation-scoped activation verification
- Dashboard and essential CSV exports
- Audit history for sensitive record changes

**Exit criteria:** an administrator can set up a school, enroll a student, assign a teacher, take attendance, and produce an authorized attendance report end to end.

### Phase 2 — Teaching and academic outcomes

- Timetables and conflict validation
- Assignments, lesson resources, assessments, and configurable grading
- Grade review and report-card publication
- Student and guardian portal views
- Dedicated teacher and student web portals with responsive layouts and the same scoped permissions as the mobile apps
- Notifications for attendance, due dates, and published results
- Teacher and student mobile API foundations, role-scoped data access, and core mobile workflows
- Teacher attendance, leave requests, and admin approval notifications
- Student self-service with allow-listed profile updates

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
- License renewal, re-activation, installation recovery, and entitlement-management workflows
- Performance, accessibility, backup/restore, and disaster-recovery improvements

**Exit criteria:** the system supports the confirmed scale and operational workflows with documented monitoring, recovery, and support procedures.

## Technical direction

- Continue with the current Laravel + Inertia + Vue stack unless a measured product need justifies a change.
- Keep business rules in well-defined server-side actions/services and enforce authorization through policies and scoped queries.
- Use a permission system with role assignments, record scope, temporary grants, approvals, and expiry; avoid hard-coding role checks throughout controllers and views.
- Serve web and mobile clients from the same backend. Mobile apps use a secured API and never connect directly to the database.
- Separate account authentication, school-user authorization, deployment mode, and product-license state; none should imply another.
- Use database constraints and transactions for enrollment, timetable conflicts, grades, and financial records.
- Treat academic year and campus as first-class scope where records need them; avoid overwriting history when a student changes class or status.
- Preserve approval and audit events for grade changes, attendance corrections, profile changes, temporary permission grants, leave, salary access, and activation.
- Prefer append-only audit and transaction history for sensitive changes; use explicit correction records where financial or academic history must be amended.
- Use queues for long-running or bulk work and store generated exports with temporary, authorized access.
- Design integrations behind small interfaces so providers can be replaced without rewriting school workflows.
- Add database indexes and performance tuning based on expected roster sizes and measured query behavior.

## Security, privacy, and reliability requirements

- Enforce least-privilege access and test access boundaries for students, guardians, teachers, and staff.
- Require backend authorization for every API/web read and write, including mobile calls, file downloads, exports, and background jobs; enforce school/campus and person-level scoping.
- Require MFA or passkeys for privileged hosted accounts where feasible; provide safe local recovery procedures without a hidden bypass account.
- Protect license issuance keys and activation credentials; use installation-scoped signed licenses and prevent secrets from appearing in client props, logs, or notifications.
  - Package only the public license-verification key with the application. Keep the Ed25519 private signing key in a separate trusted issuer environment, back it up securely, and require PHP's Sodium extension where license keys are issued or activated.
- Keep email as an alert channel only for approval workflows; require authenticated approval and record the approver and decision.
- Protect mobile sessions with secure token storage and revocation; avoid storing sensitive student records on-device unless encrypted offline support is explicitly designed.
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
