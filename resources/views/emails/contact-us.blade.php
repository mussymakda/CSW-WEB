<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Contact Us - {{ $title }}</title>
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
            background: #f4f4f4;
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
        .field {
            margin-bottom: 15px;
        }
        .field strong {
            display: inline-block;
            width: 120px;
            color: #666;
        }
        .description {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin-top: 10px;
            white-space: pre-wrap;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>New Contact Us Message</h2>
        <p>You have received a new message from the CSW mobile app.</p>
    </div>
    
    <div class="content">
        <div class="field">
            <strong>From:</strong> {{ $participant_name }} ({{ $participant_email }})
        </div>
        
        <div class="field">
            <strong>Participant ID:</strong> {{ $participant_id }}
        </div>
        
        <div class="field">
            <strong>Subject:</strong> {{ $title }}
        </div>
        
        <div class="field">
            <strong>Submitted:</strong> {{ $submitted_at }}
        </div>
        
        @if($attachment_name)
        <div class="field">
            <strong>Attachment:</strong> {{ $attachment_name }}
        </div>
        @endif
        
        <div class="field">
            <strong>Message:</strong>
            <div class="description">{{ $description }}</div>
        </div>
    </div>
    
    <p><small>This email was sent from the CSW mobile app contact form.</small></p>
</body>
</html>