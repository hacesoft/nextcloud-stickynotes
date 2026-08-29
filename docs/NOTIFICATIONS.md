# Notifications

[English](NOTIFICATIONS.md) | [Česky](NOTIFICATIONS_CZ.md) | [← Documentation](README.md)

## Version 1.1.0

Sticky Notes 1.1.0 supports two independent notification channels: built-in Nextcloud notifications and optional ntfy delivery. Preferences are stored **per user**.

Each user can enable or disable Nextcloud notifications, enable ntfy, and configure a personal server URL, topic and optional access token. The token is encrypted through Nextcloud's crypto service and is not returned to the browser after saving.

## Events

Users can independently enable or disable notifications for direct assignments, group assignments, note sharing, completion of a task they created, reopening of such a task, a due time within 24 hours, a due time within 1 hour, and the moment the task becomes due.

For group assignments, each group member's own preferences are evaluated separately.

## ntfy

ntfy is optional. A user may use the public service or a self-hosted ntfy server. The settings page includes **Send test notification** to verify the configuration immediately.

External messages contain only a short event description, the note/task title and a link back to the authenticated Sticky Notes application. **The note body and the identity of the user who performed the action are not sent to ntfy.** This makes ntfy suitable as a simple shared event channel for notifications from different applications and systems without sending detailed Sticky Notes content.

Built-in Nextcloud notifications may provide richer context. Each Sticky Notes notification links to the specific note and also includes an **Open note** action for reliable access from the desktop notification panel.

## Due reminders

A Sticky Notes background job checks deadlines about every five minutes. Actual delivery time therefore depends on how frequently Nextcloud executes background jobs. System cron is recommended for reliable reminders.

The same reminder is not repeatedly sent for the same due timestamp. Changing a task's due time can generate a fresh reminder sequence for the new deadline.
