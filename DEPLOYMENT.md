# DIMS Deployment Instructions

## Database Migration Required

Before deploying the updated code, you need to run the database migration script to add the decommission status functionality.

### On Remote Server:

1. **Upload the migration script** to your remote server in the database directory:
   ```bash
   scp migrate-decommission-status.sql user@remote-server:/path/to/dims/database/
   ```

2. **Run the migration script**:
   ```bash
   # Connect to your database container or MySQL instance
   docker exec dims-db-1 mysql -u admin -p'dV5rD/[U(D-p2oM0' < /path/to/migrate-decommission-status.sql
   
   # OR if connecting directly to MySQL:
   mysql -u admin -p'dV5rD/[U(D-p2oM0' dims_db < migrate-decommission-status.sql
   ```

3. **Deploy the updated code** using your existing deployment script:
   ```bash
   ./deploy-app.sh
   ```

## New Features Added:

- **Dynamic numbering column** - Shows row numbers that update with filtering
- **Total count display** - Shows current number of servers in header
- **Bulk selection** - Checkbox column for selecting multiple servers
- **Bulk operations**:
  - Decommission selected servers
  - Activate selected servers  
  - Delete selected servers
- **Decommission status** - Servers can be marked as decommissioned
- **IP reuse** - Decommissioned servers don't block IP reuse
- **Visual styling** - Decommissioned servers appear greyed out

## Database Changes:

The migration adds two new columns to the `servers` table:
- `status` - ENUM('Active', 'Decommissioned') DEFAULT 'Active'
- `decommission_date` - DATE NULL
- Updates IP uniqueness constraint to allow duplicate IPs for decommissioned servers

## Usage:

1. Select servers using checkboxes in the leftmost column
2. Use "Select All" checkbox in header to select/deselect all visible servers
3. Bulk action buttons appear when servers are selected
4. Decommissioned servers appear greyed out with a "Decommissioned" badge
5. Row numbers and total count update dynamically with filtering