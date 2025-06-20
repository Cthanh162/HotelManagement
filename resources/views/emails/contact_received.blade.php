<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Liên hệ mới từ khách hàng</title>
</head>
<body style="font-family: Arial, sans-serif; background-color: #f8f8f8; padding: 20px;">
    <div style="background-color: #ffffff; border-radius: 8px; padding: 20px; max-width: 600px; margin: auto;">
        <h2 style="color: #333;">Bạn có một liên hệ mới!</h2>

        <p><strong>Tên:</strong> {{ $contact['name'] }}</p>
        <p><strong>Email:</strong> {{ $contact['email'] }}</p>
        <p><strong>Nội dung:</strong></p>
        <div style="background-color: #f1f1f1; padding: 10px; border-radius: 5px;">
            {{ $contact['message'] }}
        </div>

        <p style="margin-top: 20px; color: #888;">Email này được gửi từ hệ thống ChiThanhHotel</p>
    </div>
</body>
</html>