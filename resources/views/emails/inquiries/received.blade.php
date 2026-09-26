<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>New inquiry</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f6f1e8; color: #1d3c34; margin: 0; padding: 24px;">
    <div style="max-width: 620px; margin: 0 auto; background: #ffffff; border-radius: 16px; padding: 24px; border: 1px solid #d9cab3;">
        <h2 style="margin-top: 0; color: #1d3c34;">New property inquiry</h2>

        <p><strong>Name:</strong> {{ $inquiry->name }}</p>
        <p><strong>Email:</strong> {{ $inquiry->email }}</p>
        @if ($inquiry->phone)
            <p><strong>Phone:</strong> {{ $inquiry->phone }}</p>
        @endif
        <p><strong>Subject:</strong> {{ $inquiry->subject }}</p>

        <div style="margin-top: 20px; padding: 16px; background: #f8f3eb; border-radius: 12px; border: 1px solid #e7ddca;">
            <p style="margin: 0; white-space: pre-line;">{{ $inquiry->message }}</p>
        </div>
    </div>
</body>
</html>
