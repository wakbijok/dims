# Work In Progress
- Environment feature
- Location feature
- Login/session
- License tweak

# Data Center Inventory Management System (DIMS)

A web-based inventory management system to track and manage data center resources, replacing Excel-based management.

## Features

- Server inventory management
- Hardware specifications tracking
- Service configurations
- License management
- Backup configuration records
- Import/Export functionality
- System change logging

## Prerequisites

- Docker
- Docker Compose
- MariaDB (external database)

## Installation

1. Clone the repository:
```bash
git clone https://github.com/wakbijok/dims.git
cd dims
```

2. Configure database connection:
   - Default configuration points to `192.168.0.30` for the database
   - To use different database settings, modify the parameters when running install.sh

3. Run the installation script:
```bash
chmod +x install.sh
./install.sh [DB_HOST] [DB_PORT] [DB_ROOT_PASSWORD] [DB_NAME] [DB_USER] [DB_PASSWORD]
```

Example with default values:
```bash
./install.sh "192.168.0.30" "3306" "rootpass" "dims_db" "dims_user" "dims_password"
```

4. Access the application:
   - Open your browser and navigate to `http://localhost:8080`

## Directory Structure

```
dims/
├── src/                    # Source code
│   ├── api/               # API endpoints
│   ├── assets/            # JS, CSS files
│   ├── config/            # Configuration files
│   └── includes/          # PHP includes
├── database/              # Database schema
├── docker-compose.yml     # Docker configuration
├── Dockerfile            # Docker build file
├── install.sh            # Installation script
└── README.md             # This file
```

## Usage

### Inventory Management
- Navigate through different sections using the top navigation menu
- Use search and filters to find specific items
- Add, edit, or delete inventory items through the web interface

### Import/Export
- Export current inventory to Excel or CSV format
- Import data using provided templates
- Validate import data before committing changes

### System Logs
- Track all changes made to the inventory
- View modification history
- Monitor system activities

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/AmazingFeature`)
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`)
4. Push to the branch (`git push origin feature/AmazingFeature`)
5. Open a Pull Request

## License
This system is open-source and provided "as is" without warranty of any kind. I'm just doing this for personal and internal use
