<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Message Received - {{ $title }}</title>
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
            background: #4CAF50;
            color: white;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .content {
            background: #fff;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .highlight {
            background: #f0f8ff;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Thank You for Contacting Us!</h2>
    </div>
    
    <div class="content">
        <p>Dear {{ $participant_name }},</p>
        
        <p>We have successfully received your message regarding: <strong>{{ $title }}</strong></p>
        
        <div class="highlight">
            <p><strong>What happens next?</strong></p>
            <ul>
                <li>Our support team will review your message</li>
                <li>We will respond within 24 hours during business days</li>
                <li>You will receive a reply at: {{ $participant_email }}</li>
            </ul>
        </div>
        
        <p><strong>Your message summary:</strong></p>
        <p style="background: #f9f9f9; padding: 10px; border-radius: 5px;">{{ Str::limit($description, 200) }}</p>
        
        @if($attachment_name)
        <p><strong>Attachment received:</strong> {{ $attachment_name }}</p>
        @endif
        
        <p><strong>Submitted on:</strong> {{ $submitted_at }}</p>
        
        <p>If you have any urgent concerns, please contact us directly through the app or our support channels.</p>
        
        <p>Thank you for using CSW!</p>
        
        <p>Best regards,<br>
The CSW Support Team</p>
    </div>
    
    <p><small>This is an automated confirmation email. Please do not reply to this message.</small></p>
</body>
</html>