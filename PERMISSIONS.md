# ELKCMS Permissions Management

## Current Setup

**Owner:** `koki:koki` (UID 1000)  
**Base Permissions:** `755` (rwxr-xr-x)  
**Laravel Writable:** `775` (rwxrwxr-x)  

### Directory Structure

```
/home/koki/elkcms/
├── All files/folders: koki:koki 755
├── storage/: koki:koki 775 (Docker writes logs, cache, sessions)
├── bootstrap/cache/: koki:koki 775 (Laravel cache)
└── public/: koki:koki 775 (Vite builds, uploaded files)
```

## How It Works

1. **Your user (koki)** owns all files
   - You can edit, git commit, run npm/composer from host
   
2. **Docker (running as root)** can write to specific directories
   - Docker user UID 0 (root) can write to 775 directories
   - Nginx serves files as www-data but doesn't write

3. **Git ignores file mode changes**
   - `core.fileMode = false` prevents chmod from appearing as changes

## Fix Permissions

If permissions get messed up, run:

```bash
./fix-permissions.sh
```

Or manually:

```bash
# From host
docker exec elkcms_app chown -R 1000:1000 /var/www
docker exec elkcms_app chmod -R 755 /var/www
docker exec elkcms_app chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/public
git config core.fileMode false
git config --global --add safe.directory /home/koki/elkcms
```

## Common Issues

### Git says "dubious ownership"
**Solution:**
```bash
git config --global --add safe.directory /home/koki/elkcms
```

### Permission denied when writing files
**Solution:**
```bash
./fix-permissions.sh
```

### New files created by Docker are owned by www-data
**Solution:** Run `./fix-permissions.sh` after Docker operations

## Best Practices

1. **Run fix-permissions.sh after:**
   - Fresh clone/pull
   - Docker writes to filesystem
   - Permission errors appear

2. **Don't run composer/npm as root:**
   - Always run from host or as your user
   - Docker exec commands run as root

3. **Git operations:**
   - Always from host as your user
   - Never from inside Docker container

## Technical Details

### Why 775 not 777?

- `775` = Owner write, Group write, Others read
- Secure: Others can't write
- Docker (root) can write as it bypasses permissions
- Web server (www-data) can read

### Why disable fileMode in git?

- `chmod` changes show as modifications in git
- We control permissions via script
- Cleaner git status

### Docker User Mapping

```
Host:     koki (UID 1000, GID 1000)
Docker:   root (UID 0, GID 0)
Nginx:    www-data (UID 82, GID 82) - read only
```

Root (UID 0) can write to any file, making this setup work without complex user mapping.
