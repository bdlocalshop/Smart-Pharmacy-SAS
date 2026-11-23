================================================================================
SMART PHARMACY - LIVE SERVER DEPLOYMENT GUIDE (cPanel)
Domain: https://pharmacy.bdlocalshop.com
Folder: /home/{username}/pharmacy.bdlocalshop.com
================================================================================

This package contains the fully pre-built application with all vendor libraries,
production assets, database dump, and pre-configured environment settings.

--------------------------------------------------------------------------------
STEP 1: UPLOAD & EXTRACT FILES IN CPANEL
--------------------------------------------------------------------------------
1. Log into your cPanel (cpny1.hostypanel.com:2083).
2. Open "File Manager".
3. In the left panel, navigate into:
   pharmacy.bdlocalshop.com
4. Click the "Upload" button at the top.
5. Upload: pharmacy_bdlocalshop_deploy.zip (or upload the files from deployment_package).
6. Once uploaded, right-click the zip file in File Manager and select "Extract".
   Extract directly into: /pharmacy.bdlocalshop.com/

--------------------------------------------------------------------------------
STEP 2: CREATE MYSQL DATABASE IN CPANEL
--------------------------------------------------------------------------------
1. In cPanel, go to "MySQL Databases" (or "MySQL Database Wizard").
2. Create a new database, e.g.:
   bdlocal_pharmacy
3. Create a new MySQL user, e.g.:
   bdlocal_pharmauser
   Choose a strong password and save it.
4. Scroll down to "Add User To Database":
   - Select the user and database you just created.
   - Click "Add".
   - Check the box "ALL PRIVILEGES" and click "Make Changes".

--------------------------------------------------------------------------------
STEP 3: IMPORT DATABASE DUMP (phpMyAdmin)
--------------------------------------------------------------------------------
1. In cPanel, open "phpMyAdmin".
2. In the left sidebar, click on your newly created database (e.g. bdlocal_pharmacy).
3. Click the "Import" tab at the top.
4. Click "Choose File" and select:
   database_dump.sql (located in your deployment folder).
5. Click "Import" at the bottom.
   All tables, indexes, and initial data will be imported immediately!

--------------------------------------------------------------------------------
STEP 4: UPDATE .env FILE IN FILE MANAGER
--------------------------------------------------------------------------------
1. In cPanel File Manager inside /pharmacy.bdlocalshop.com/:
   (Make sure "Show Hidden Files (dotfiles)" is enabled in File Manager Settings).
2. Right-click the ".env" file and choose "Edit".
3. Update the database credentials to match Step 2:
   DB_DATABASE=bdlocal_pharmacy
   DB_USERNAME=bdlocal_pharmauser
   DB_PASSWORD=your_actual_password_here
4. Click "Save Changes".

--------------------------------------------------------------------------------
STEP 5: FILE PERMISSIONS (If needed)
--------------------------------------------------------------------------------
Ensure the following directories have write permissions (usually 755 or 775):
- storage/ (and all subfolders)
- bootstrap/cache/

--------------------------------------------------------------------------------
STEP 6: ACCESS YOUR LIVE APPLICATION
--------------------------------------------------------------------------------
1. Public Site: https://pharmacy.bdlocalshop.com
2. Filament Admin Portal: https://pharmacy.bdlocalshop.com/admin
   Default Admin Credentials:
   - Email: pharmacy@bdlocalshop.com
   - Password: 1Pharmacy12
================================================================================
