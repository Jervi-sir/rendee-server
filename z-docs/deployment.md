# Supervisor Setup & Deployment Guide

This guide explains how to set up and manage Supervisor for `rendee-server`.

---

## 1. Copy Configuration File
Copy the supervisor configuration from `z-docs/supervisor.conf` into Supervisor's configuration directory `/etc/supervisor/conf.d/`:

```bash
sudo cp /home/jervi/projects/rendee-server/z-docs/supervisor.conf /etc/supervisor/conf.d/rendee-server.conf
```

---

## 2. Load Configuration into Supervisor
Inform Supervisor about the new configuration file and start the processes:

```bash
sudo supervisorctl reread
sudo supervisorctl update
```

---

## 3. Manage Supervisor Processes

### Check Status
```bash
sudo supervisorctl status
```
*Output will display state (e.g. `RUNNING`), PID, and uptime for `rendee-web` and `rendee-scheduler`.*

### Start Processes
```bash
sudo supervisorctl start rendee-web rendee-scheduler
# Or start all:
sudo supervisorctl start all
```

### Stop Processes
```bash
sudo supervisorctl stop rendee-web rendee-scheduler
# Or stop all:
sudo supervisorctl stop all
```

### Restart Processes
```bash
sudo supervisorctl restart rendee-web rendee-scheduler
```

---

## 4. View Logs

The stdout and stderr logs are stored in your Laravel `storage/logs` directory:

- **Web Server Logs:**
  ```bash
  tail -f /home/jervi/projects/rendee-server/storage/logs/web.log
  ```
- **Scheduler Logs:**
  ```bash
  tail -f /home/jervi/projects/rendee-server/storage/logs/scheduler.log
  ```
- **Supervisor system status log:**
  ```bash
  sudo tail -f /var/log/supervisor/supervisord.log
  ```
