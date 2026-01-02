<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ELKCMS - High-Performance CMS</title>
    <?php echo app('Illuminate\Foundation\Vite')(['resources/css/app.css', 'resources/js/app.js']); ?>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
        .container {
            text-align: center;
            max-width: 800px;
            padding: 2rem;
        }
        h1 {
            font-size: 4rem;
            margin-bottom: 1rem;
            font-weight: 700;
        }
        .subtitle {
            font-size: 1.5rem;
            opacity: 0.9;
            margin-bottom: 2rem;
        }
        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin: 2rem 0;
        }
        .feature {
            background: rgba(255, 255, 255, 0.1);
            padding: 1.5rem;
            border-radius: 10px;
            backdrop-filter: blur(10px);
        }
        .feature h3 {
            margin-bottom: 0.5rem;
        }
        .code {
            background: rgba(0, 0, 0, 0.3);
            padding: 1rem;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            margin: 2rem 0;
            text-align: left;
        }
        a {
            color: #ffd700;
            text-decoration: none;
        }
        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🦌 ELKCMS</h1>
        <p class="subtitle">High-Performance, Attribute-Driven PHP CMS</p>

        <div class="features">
            <div class="feature">
                <h3>⚡ Laravel 11</h3>
                <p>PHP 8.2+ powered</p>
            </div>
            <div class="feature">
                <h3>🌍 Multilingual</h3>
                <p>WPML-inspired</p>
            </div>
            <div class="feature">
                <h3>🎨 Page Builder</h3>
                <p>GrapesJS visual editor</p>
            </div>
            <div class="feature">
                <h3>🚀 SEO Ready</h3>
                <p>Yoast-like features</p>
            </div>
        </div>

        <div class="code">
            <strong>Quick Start:</strong><br>
            1. Run: <code>composer install && npm install</code><br>
            2. Copy: <code>cp .env.example .env</code><br>
            3. Generate: <code>php artisan key:generate</code><br>
            4. Migrate: <code>php artisan migrate</code><br>
            5. Build: <code>npm run build</code>
        </div>

        <p>
            📚 <a href="https://github.com/kokiddp/elkcms/blob/main/README.md" target="_blank">Read the Documentation</a> •
            🐛 <a href="https://github.com/kokiddp/elkcms/issues" target="_blank">Report Issues</a>
        </p>

        <p style="margin-top: 2rem; opacity: 0.7; font-size: 0.9rem;">
            Built with ❤️ by <a href="mailto:gabriele@elk-lab.com">Gabriele Coquillard</a> @ <a href="https://www.elk-lab.com" target="_blank">ELK-Lab</a>
        </p>
    </div>
</body>
</html>
<?php /**PATH /var/www/resources/views/welcome.blade.php ENDPATH**/ ?>