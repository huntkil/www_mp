<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exception - My Playground</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 20px;
            color: #1e293b;
        }
        .error-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .error-header {
            background: #dc2626;
            color: white;
            padding: 20px;
            border-bottom: 1px solid #b91c1c;
        }
        .error-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
        }
        .error-content {
            padding: 20px;
        }
        .error-detail {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .error-detail h3 {
            margin: 0 0 10px 0;
            color: #dc2626;
            font-size: 16px;
        }
        .error-detail p {
            margin: 5px 0;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 14px;
        }
        .error-detail strong {
            color: #374151;
        }
        .back-button {
            display: inline-block;
            background: #3b82f6;
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            border-radius: 6px;
            margin-top: 20px;
            transition: background-color 0.2s;
        }
        .back-button:hover {
            background: #2563eb;
        }
        .stack-trace {
            background: #1e293b;
            color: #e2e8f0;
            padding: 15px;
            border-radius: 6px;
            font-family: 'Monaco', 'Menlo', 'Ubuntu Mono', monospace;
            font-size: 12px;
            overflow-x: auto;
            white-space: pre-wrap;
            max-height: 400px;
            overflow-y: auto;
        }
        .exception-type {
            background: #fbbf24;
            color: #92400e;
            padding: 5px 10px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="error-container">
        <div class="error-header">
            <h1>💥 Exception Thrown</h1>
        </div>
        <div class="error-content">
            <div class="error-detail">
                <div class="exception-type"><?php echo htmlspecialchars($error['class'] ?? 'Exception'); ?></div>
                <h3>Exception Details</h3>
                <p><strong>Message:</strong> <?php echo htmlspecialchars($error['message'] ?? 'Unknown exception'); ?></p>
                <p><strong>File:</strong> <?php echo htmlspecialchars($error['file'] ?? 'Unknown'); ?></p>
                <p><strong>Line:</strong> <?php echo htmlspecialchars($error['line'] ?? 'Unknown'); ?></p>
                <p><strong>Time:</strong> <?php echo htmlspecialchars($error['timestamp'] ?? 'Unknown'); ?></p>
                <?php if (isset($error['url'])): ?>
                    <p><strong>URL:</strong> <?php echo htmlspecialchars($error['url']); ?></p>
                <?php endif; ?>
            </div>
            
            <?php if (isset($error['trace'])): ?>
                <div class="error-detail">
                    <h3>Stack Trace</h3>
                    <div class="stack-trace"><?php echo htmlspecialchars($error['trace']); ?></div>
                </div>
            <?php endif; ?>
            
            <a href="/mp/" class="back-button">← Back to Home</a>
        </div>
    </div>
</body>
</html> 