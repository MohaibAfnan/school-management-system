# Developer portal

The developer portal manages SaaS school workspaces separately from school users. Its accounts use a dedicated authentication guard and have no public registration flow.

## First platform administrator

After database migrations have run, create the first platform administrator from the project terminal:

```powershell
php artisan platform:admin:create "Your Name" admin@example.com
```

The command prompts for a password without echoing it. Sign in at `/developer/login`.

## Available controls

- Create a school workspace and its first administrator account
- Update the workspace name, code, and primary administrator
- Set an initial password or issue a one-time temporary password; clients must change temporary passwords at first use
- Enable or disable school access
- Send a rate-limited password reset link to the primary administrator
- Assign a package and subscription state
- Archive a school and restore it later; archiving retains the school data
- Create, update, enable, disable, and archive packages
- Review recent platform actions in the audit history

Package limits and feature lists are stored with each plan for administration. Payment processing and automatic quota enforcement are separate integrations and are not enabled by this portal alone.

Client passwords are stored only as hashes and cannot be retrieved later. A developer can set a new temporary password, which is shown once in the portal and is never written to audit logs. Use a secure channel to share it with the client.

## Email delivery

School reset links use that school's SMTP settings when configured. Initial setup for a new school falls back to the platform mail transport. Configure a real platform mail transport in the deployment environment before relying on onboarding links; a `log` mailer only records messages locally.
