<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Something went wrong - My Playground</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #1e293b;
        }
        .error-container {
            max-width: 500px;
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            text-align: center;
            padding: 40px 30px;
        }
        .error-icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .error-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 10px;
        }
        .error-message {
            font-size: 16px;
            color: #64748b;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        .error-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-primary {
            background: #3b82f6;
            color: white;
        }
        .btn-primary:hover {
            background: #2563eb;
            transform: translateY(-1px);
        }
        .btn-secondary {
            background: #f1f5f9;
            color: #475569;
        }
        .btn-secondary:hover {
            background: #e2e8f0;
            transform: translateY(-1px);
        }
        .error-code {
            font-size: 12px;
            color: #94a3b8;
            margin-top: 20px;
            font-family: 'Monaco', 'Menlo', monospace;
        }
        @media (max-width: 480px) {
            .error-container {
                padding: 30px 20px;
            }
            .error-title {
                font-size: 24px;
            }
            .error-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-icon">😔</div>
        <h1 class="error-title">Oops! Something went wrong</h1>
        <p class="error-message">
            We're sorry, but something unexpected happened. Our team has been notified and is working to fix the issue.
        </p>
        <div class="error-actions">
            <a href="/mp/" class="btn btn-primary">Go Home</a>
            <button onclick="window.history.back()" class="btn btn-secondary">Go Back</button>
        </div>
        <div class="error-code">
            Error ID: <?php echo substr(md5(uniqid()), 0, 8); ?>
        </div>
    </div>
    
    <script>
        // 자동으로 홈으로 리다이렉트 (30초 후)
        setTimeout(function() {
            window.location.href = '/mp/';
        }, 30000);
    </script>
</body>
</html> 