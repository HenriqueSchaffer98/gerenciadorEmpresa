# Business Manager

Environment setup for Business Manager application.

## 🚀 Getting Started

This project is fully dockerized. The initial run will automatically install Laravel.

### Prerequisities

- Docker
- Docker Compose

### 🛠 Installation

1. Clone the repository (if you haven't already).
2. Start the containers:

```bash
docker-compose up -d --build
```

3. **Wait a few moments** for the first run. The backend container will:
    - Check if Laravel is installed.
    - Run `composer create-project` if missing.
    - Set permissions.
    - Run migrations.

You can follow the installation logs with:

```bash
docker logs -f business-manager-app
```

### 🔌 Access

- **API/Web**: [http://localhost:8000](http://localhost:8000)
- **Database**: Port `3307`
  - User: `user`
  - Password: `password`
  - Database: `business_manager`

## 📁 Structure

- `backend/`: Laravel application source code
- `docker-compose.yml`: Services orchestration