<!DOCTYPE html>
<html>
<head>
    <title>Contact Form Message</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background-color: #ffffff;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
        }
        .field {
            margin-bottom: 15px;
        }
        .label {
            font-weight: bold;
            color: #495057;
        }
        .value {
            margin-top: 5px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 3px;
        }
        .footer {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Contact Form Message</h2>
        <p>You have received a new message through the InfoDot contact form.</p>
    </div>

    <div class="content">
        <div class="field">
            <div class="label">Name:</div>
            <div class="value">{{ $details['name'] }}</div>
        </div>

        <div class="field">
            <div class="label">Email:</div>
            <div class="value">{{ $details['email'] }}</div>
        </div>

        <div class="field">
            <div class="label">Message:</div>
            <div class="value">{{ $details['message'] }}</div>
        </div>
    </div>

    <div class="footer">
        <p>This message was sent from the InfoDot contact form at {{ now()->format('Y-m-d H:i:s') }}.</p>
        <p>Please reply directly to {{ $details['email'] }} to respond to this inquiry.</p>
    </div>
</body>
</html>
