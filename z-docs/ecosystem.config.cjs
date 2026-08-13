module.exports = {
    apps: [
        {
            name: 'rendee-server:web',
            script: 'artisan',
            args: 'serve --host=0.0.0.0 --port=18040',
            interpreter: 'php',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '500M',
            env: {
                NODE_ENV: 'development',
            },
            env_production: {
                NODE_ENV: 'production',
            },
        },
        {
            name: 'rendee-server:queue',
            script: 'artisan',
            args: 'queue:work --sleep=3 --tries=3 --max-time=3600',
            interpreter: 'php',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '500M',
        },
        {
            name: 'rendee-server:schedule',
            script: 'artisan',
            args: 'schedule:work',
            interpreter: 'php',
            instances: 1,
            autorestart: true,
            watch: false,
            max_memory_restart: '200M',
        },
    ],
};
