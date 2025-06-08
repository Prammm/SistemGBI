<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - GBI Situbondo</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            background-color: #f4f4f4;
            padding: 20px;
        }
        .container {
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .header .subtitle {
            margin: 5px 0 0 0;
            font-size: 14px;
            opacity: 0.9;
        }
        .content {
            padding: 30px;
        }
        .greeting {
            font-size: 18px;
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 500;
        }
        .event-details {
            background-color: #f8f9ff;
            border-left: 4px solid #667eea;
            padding: 20px;
            margin: 20px 0;
            border-radius: 0 8px 8px 0;
        }
        .event-details h3 {
            margin: 0 0 15px 0;
            color: #667eea;
            font-size: 18px;
        }
        .detail-item {
            display: flex;
            margin-bottom: 10px;
        }
        .detail-label {
            font-weight: 600;
            min-width: 100px;
            color: #555;
        }
        .detail-value {
            color: #333;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            margin: 10px 5px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: 600;
            text-align: center;
            transition: all 0.3s ease;
        }
        .button-primary {
            background-color: #667eea;
            color: white;
        }
        .button-secondary {
            background-color: #f1f3f4;
            color: #667eea;
            border: 2px solid #667eea;
        }
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .footer {
            background-color: #f8f9ff;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #666;
        }
        .contact-info {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #eee;
            font-size: 14px;
            color: #666;
        }
        .alert {
            padding: 15px;
            margin: 20px 0;
            border-radius: 5px;
        }
        .alert-warning {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }
        .alert-info {
            background-color: #d1ecf1;
            border: 1px solid #74c0fc;
            color: #0c5460;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>@yield('header-title', 'GBI Situbondo')</h1>
            <p class="subtitle">@yield('header-subtitle', 'Gereja Bethel Indonesia')</p>
        </div>
        
        <div class="content">
            @yield('content')
        </div>
        
        <div class="footer">
            <p><strong>GBI Situbondo</strong></p>
            <p>Jl. Contoh No. 123, Situbondo, Jawa Timur</p>
            <p>Telepon: {{ env('CHURCH_PHONE', '+62 123 456 789') }} | Email: {{ env('CHURCH_EMAIL', 'info@gbisitubondo.org') }}</p>
            <p style="margin-top: 15px; font-size: 12px; color: #999;">
                Email ini dikirim secara otomatis oleh sistem. Mohon jangan membalas email ini.
            </p>
        </div>
    </div>
</body>
</html>