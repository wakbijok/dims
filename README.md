# Data Center Inventory Management System (DCIMS)

A web-based system for managing data center assets across multiple locations.

## Features

- Asset management (servers, storage, network equipment, etc.)
- Multi-location support
- Environment tracking (Production, Staging, Development)
- Project organization
- Access credentials management
- Asset history tracking
- User management with role-based access

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache 2.4 or higher
- Modern web browser

## Installation

1. Clone the repository:
```bash
git clone https://github.com/wakbijok/dims.git
cd dims/install
```

2. Make the installation script executable:
```bash
chmod +x install.sh
```

3. Run the installation script:
```bash
sudo ./install.sh
```

4. Update your hosts file or DNS settings to point to your server:
```bash
sudo echo "127.0.0.1 inventory.local" >> /etc/hosts
```

5. Access the system through your web browser:
```
http://inventory.local
```

Default login credentials:
- Username: admin
- Password: admin123

**Important**: Change the default password after first login!

## Directory Structure

```
dims/
├── install/
│   ├── install.sh        # Installation script
│   └── schema.sql       # Database schema
├── includes/            # PHP include files
│   └── navbar.php      # Navigation bar template
├── css/                # CSS stylesheets
│   └── style.css      # Custom styles
├── config.php          # Configuration file
├── functions.php       # Common functions
├── index.php          # Main dashboard
├── asset_form.php     # Asset add/edit form
├── asset_delete.php   # Asset deletion handler
├── login.php         # Login page
└── logout.php        # Logout handler
```

## Configuration

Edit `config.php` to update:
- Database connection settings
- Site name and URL
- Timezone settings
- Other system configurations

## Security Considerations

1. Change default credentials immediately after installation
2. Keep PHP and MySQL updated
3. Use HTTPS in production
4. Regularly backup your database
5. Monitor login attempts and system logs
6. Follow security best practices for production deployment

## Development

To contribute to this project:

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

## Database Backup

To backup the database:
```bash
mysqldump -u [username] -p dcims_db > backup.sql
```

To restore from backup:
```bash
mysql -u [username] -p dcims_db < backup.sql
```

## Troubleshooting

Common issues and solutions:

1. Permission errors:
```bash
sudo chown -R www-data:www-data /var/www/dcims
sudo chmod -R 755 /var/www/dcims
```

2. Apache configuration:
```bash
sudo a2enmod rewrite
sudo systemctl restart apache2
```

3. Database connection issues:
- Check config.php settings
- Verify MySQL service is running
- Check user permissions

## License

This project is licensed under the MIT License - see the LICENSE file for details.

## Support

For bugs and feature requests, please use the GitHub issues system:
https://github.com/wakbijok/dims/issues

## Acknowledgments

- Bootstrap for the UI framework
- DataTables for enhanced table functionality
- Icons from Bootstrap Icons

## Version History

- 1.0.0: Initial release
  - Basic asset management
  - User authentication
  - Multi-location support