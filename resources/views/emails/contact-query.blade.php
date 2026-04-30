<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background: linear-gradient(135deg, #41B883, #E23744); padding: 20px; border-radius: 8px 8px 0 0; color: white; }
        .content { background: #f9fafb; padding: 24px; border: 1px solid #e5e7eb; border-top: none; border-radius: 0 0 8px 8px; }
        .field { margin-bottom: 16px; }
        .label { font-weight: 600; color: #6b7280; font-size: 12px; text-transform: uppercase; letter-spacing: 0.05em; }
        .value { margin-top: 4px; color: #111827; }
        .message-box { background: white; padding: 16px; border-radius: 6px; border: 1px solid #e5e7eb; margin-top: 4px; white-space: pre-wrap; }
        .footer { text-align: center; margin-top: 20px; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin: 0;">New Contact Query</h2>
        <p style="margin: 4px 0 0; opacity: 0.9;">Someone reached out via the contact form</p>
    </div>
    <div class="content">
        <div class="field">
            <div class="label">Name</div>
            <div class="value">{{ $contactQuery->name }}</div>
        </div>
        <div class="field">
            <div class="label">Email</div>
            <div class="value"><a href="mailto:{{ $contactQuery->email }}">{{ $contactQuery->email }}</a></div>
        </div>
        <div class="field">
            <div class="label">Subject</div>
            <div class="value">{{ $contactQuery->subject ?: '—' }}</div>
        </div>
        <div class="field">
            <div class="label">Message</div>
            <div class="message-box">{{ $contactQuery->message }}</div>
        </div>
    </div>
    <div class="footer">
        This email was sent from the LaraVue contact form.
    </div>
</body>
</html>
