module.exports = {
    apps: [
        {
            name: 'rendee:laravel-api',
            script: 'artisan',
            interpreter: 'php',
            args: 'serve --host=0.0.0.0 --port=18040',
            cwd: '/home/jervi/projects/rendee-server',
            instances: 1,
            exec_mode: 'fork',
            watch: false,
            max_memory_restart: '512M',
            env: {
                APP_ENV: 'production',
            },
        },
        {
            name: 'rendee:laravel-worker',
            script: 'artisan',
            interpreter: 'php',
            args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
            cwd: '/home/jervi/projects/rendee-server',
            instances: 1,
            exec_mode: 'fork',
            watch: false,
            max_memory_restart: '512M',
            env: {
                APP_ENV: 'production',
            },
        },
    ],
};
