# Notifications

[English](NOTIFICATIONS.md) | [Česky](NOTIFICATIONS_CZ.md) | [← Documentation](README.md)

## Version 1.0.0

Sticky Notes 1.0.0 uses the built-in Nextcloud notification mechanism for supported events such as task assignment. External ntfy delivery is not part of 1.0.0.

## Planned model for 1.1.0

Notifications will use a channel-independent event layer. A Sticky Notes event is generated once and the notification service determines which user should receive it and through which enabled channel.

```text
Sticky Notes event
        │
        ├── NextcloudNotificationProvider
        │
        └── NtfyNotificationProvider
```

ntfy configuration will be per-user: server URL, topic, optional access token, channel enablement, and event preferences. A **Send test notification** action is planned.

Users should be able to choose Nextcloud and/or ntfy independently for direct assignment, group assignment, sharing, completion of a task they created, reopening, approaching deadline, and deadline reached/overdue.

Group assignment notifications must respect the individual preferences of each group member.

Deadline reminders may support several lead times, such as one day before, one hour before, at the deadline, or another user-selected value. The exact model will be finalized during 1.1.0 implementation.

## Privacy

The external ntfy message should not automatically contain the full private note body. Prefer only the event type, short title, deadline, and a link back to the authenticated Sticky Notes application. Tokens and other sensitive configuration must not be exposed to other users or logs.
