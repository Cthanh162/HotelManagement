<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Xác minh đăng ký</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            padding: 0;
            margin: 0;
        }
        .email-container {
            background-color: #ffffff;
            max-width: 600px;
            margin: 30px auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            color: #2c3e50;
        }
        .code {
            font-size: 32px;
            font-weight: bold;
            text-align: center;
            background-color: #f0f0f0;
            padding: 15px;
            margin: 20px 0;
            border-radius: 6px;
            letter-spacing: 5px;
        }
        .footer {
            font-size: 13px;
            text-align: center;
            color: #888;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2 class="header">Chào mừng bạn đến với CHITHANHHOTEL!</h2>
        <p>Để hoàn tất đăng ký, vui lòng nhập mã xác minh bên dưới:</p>
        
        <div class="code">{{ $code }}</div>
        
        <p>Nếu bạn không yêu cầu đăng ký tài khoản, bạn có thể bỏ qua email này.</p>

        <div class="footer">
            &copy; {{ date('Y') }} CHITHANHHOTEL. All rights reserved.
        </div>
    </div>
</body>
</html>