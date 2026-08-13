server {
server_name rendee.jervi.dev;

    # Allow Let's Encrypt SSL renewals but block other access
    location ^~ /.well-known/acme-challenge/ {
        allow all;
    }
    location ~* ^/.well-known/ {
        return 403;
    }

    location / {
        proxy_pass http://localhost:18040;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;

        proxy_buffer_size 128k;
        proxy_buffers 4 256k;
        proxy_busy_buffers_size 256k;
    }

    listen 443 ssl; # managed by Certbot
    ssl_certificate /etc/letsencrypt/live/rendee.jervi.dev/fullchain.pem; # managed by Certbot
    ssl_certificate_key /etc/letsencrypt/live/rendee.jervi.dev/privkey.pem; # managed by Certbot
    include /etc/letsencrypt/options-ssl-nginx.conf; # managed by Certbot
    ssl_dhparam /etc/letsencrypt/ssl-dhparams.pem; # managed by Certbot

}
server {
if ($host = rendee.jervi.dev) {
        return 301 https://$host$request_uri;
} # managed by Certbot

    listen 80;
    server_name rendee.jervi.dev;
    return 404; # managed by Certbot

}
