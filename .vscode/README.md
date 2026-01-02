# VS Code Configuration for ELKCMS

This directory contains VS Code-specific configuration for ELKCMS development.

## Debugging with Xdebug

The `launch.json` file is configured with multiple debugging profiles:

### 1. Listen for Xdebug (Docker)
**Use this for debugging when running ELKCMS in Docker.**

- Port: 9003
- Path mapping: `/var/www` → workspace folder
- Automatically maps Docker paths to your local workspace

**How to use:**
1. Start Docker containers: `docker-compose up -d`
2. Open VS Code debugger panel (Cmd/Ctrl + Shift + D)
3. Select "Listen for Xdebug (Docker)" from dropdown
4. Click green play button (or press F5)
5. Set breakpoints in your PHP code
6. Visit your application in browser (http://localhost:8000)
7. Execution will pause at your breakpoints

### 2. Listen for Xdebug (Local)
**Use this for debugging when running Laravel locally (without Docker).**

- Port: 9003
- No path mapping needed

### 3. Launch currently open script
**Debug a standalone PHP script.**

- Opens and debugs the currently active PHP file
- Useful for testing individual scripts

### 4. Debug Artisan Command
**Debug Laravel Artisan commands.**

Example configured: `php artisan migrate`

To debug a different command, modify the `args` array in launch.json:
```json
"args": ["cms:make-model", "Portfolio"]
```

### 5. Debug PHPUnit Tests
**Debug PHPUnit test files.**

- Runs tests with Xdebug enabled
- Set breakpoints in test files or application code
- Useful for debugging failing tests

## Xdebug Configuration

Xdebug is configured in Docker with these settings:

- **Mode**: debug
- **Client host**: host.docker.internal (allows Docker to connect to your IDE)
- **Client port**: 9003
- **IDE key**: VSCODE
- **Start with request**: yes (automatically starts debugging on every request)

## VS Code Extensions Recommended

Install these extensions for the best experience:

1. **PHP Debug** (xdebug.php-debug) - Required for debugging
2. **PHP Intelephense** (bmewburn.vscode-intelephense-client) - PHP language server
3. **Laravel Extension Pack** - Laravel-specific helpers
4. **EditorConfig** - Maintain consistent coding styles

## Troubleshooting

### Breakpoints not working?

1. **Check Xdebug is installed:**
   ```bash
   docker-compose exec app php -v
   # Should show "with Xdebug"
   ```

2. **Check Xdebug configuration:**
   ```bash
   docker-compose exec app php -i | grep xdebug
   ```

3. **Verify port 9003 is not in use:**
   ```bash
   lsof -i :9003
   ```

4. **Check Docker logs:**
   ```bash
   docker-compose logs app
   ```

### Path mapping issues?

Ensure the path mapping in launch.json matches your Docker setup:
- Docker path: `/var/www`
- Local path: `${workspaceFolder}` (automatically set)

### Debugging not starting?

1. Make sure you clicked the play button in VS Code debugger panel
2. Check the Debug Console for connection messages
3. Verify `XDEBUG_MODE=debug` is set in docker-compose.yml
4. Restart Docker containers: `docker-compose restart app`

## Performance

Xdebug can slow down your application. To disable it temporarily:

1. **Comment out Xdebug in docker-compose.yml:**
   ```yaml
   environment:
     # XDEBUG_MODE: debug
   ```

2. **Restart containers:**
   ```bash
   docker-compose restart app
   ```

Or set `XDEBUG_MODE=off` in your environment.

## Additional Resources

- [Xdebug Documentation](https://xdebug.org/docs/)
- [VS Code PHP Debugging](https://code.visualstudio.com/docs/languages/php#_debugging)
- [Laravel Debugging Guide](https://laravel.com/docs/debugging)
