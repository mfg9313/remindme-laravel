<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reminder Due</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f4f4;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f4f4; padding:20px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background-color:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.1);">
                <tr>
                    <td align="center" style="background-color:#4CAF50; padding:20px;">
                        <h1 style="color:#ffffff; margin:0; font-family: Arial, sans-serif;">RemindMe</h1>
                    </td>
                </tr>
                <tr>
                    <td style="padding:30px; font-family: Arial, sans-serif; color:#333333;">
                        <h2 style="color:#4CAF50;">Hello, {{ $userName }}!</h2>
                        <p style="font-size:16px; line-height:1.5;">
                            This is a friendly reminder that your reminder <strong>{{ $reminderTitle }}</strong> is due.
                        </p>
                        @if(!empty($reminderDescription))
                            <p style="font-size:16px; line-height:1.5;">
                                <strong>Description:</strong> {{ $reminderDescription }}
                            </p>
                        @endif
                        <p style="font-size:16px; line-height:1.5;">
                            <strong>Remind At:</strong> {{ $remindAt }}
                        </p>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="background-color:#f4f4f4; padding:20px; font-family: Arial, sans-serif; color:#777777; font-size:14px;">
                        <p style="margin:0;">You're receiving this email because you have an account with RemindMe.</p>
                        <p style="margin:5px 0 0 0;">&copy; {{ date('Y') }} RemindMe. All rights reserved.</p>
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
