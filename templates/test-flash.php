<!DOCTYPE html>
<html>
<head>
    <title>Test Flash Messages</title>
    <style>
        .flash-success { background: green; color: white; padding: 10px; margin: 10px 0; }
        .flash-error { background: red; color: white; padding: 10px; margin: 10px 0; }
        .debug { background: #f0f0f0; padding: 10px; margin: 10px 0; border: 1px solid #ccc; }
    </style>
</head>
<body>
    <h1>Flash Message Test Page</h1>
    
    <!-- Debug session information -->
    <div class="debug">
        <h3>Debug Info:</h3>
        <p><strong>Session flash_success:</strong> {{ session['flash_success'] ?? 'NOT SET' }}</p>
        <p><strong>Session flash_error:</strong> {{ session['flash_error'] ?? 'NOT SET' }}</p>
        <p><strong>Full session dump:</strong></p>
        <pre>{{ dump(session) }}</pre>
    </div>
    
    <!-- Flash messages (your current implementation) -->
    {% if session['flash_success'] %}
        <div class="flash-success">
            SUCCESS: {{ session['flash_success'] }}
        </div>
    {% endif %}
    
    {% if session['flash_error'] %}
        <div class="flash-error">
            ERROR: {{ session['flash_error'] }}
        </div>
    {% endif %}
    
    <!-- Test links -->
    <p><a href="/test-flash">Set Flash Messages and Redirect</a></p>
    <p><a href="/expenses">Go to Expenses Page</a></p>
</body>
</html>
