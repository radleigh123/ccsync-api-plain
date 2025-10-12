# CCSync API (Plain PHP Version)

### Onboarding Instructions

**PART 1: Server Setup**
1. Navigate to **xampp/htdocs** directory.
2. Clone the repository using this link:
    ```bash
    git clone https://github.com/radleigh123/ccsync-api-plain.git
    ```
3. Open **XAMPP Control Panel** and start **Apache** and **MySQL** services.
4. To check if everything is working, open your web browser and go to `http://localhost:8080/ccsync-plain-php`.
5. DONE

**PART 2: Database Setup**
1. Open your web browser and go to `http://localhost:8080/phpmyadmin`
2. Create a new database named `ccsync_api`.
3. Import the `ccsync_api.sql` file located in the `config/database` folder of the cloned repository.
4. DONE

**PART 3: Firebase Setup**
1. Go to the project's [Firebase Console](https://console.firebase.google.com/u/0/project/ccsync-elphp-2025/overview).
2. Navigate to **Project Settings** > **Service Accounts**.
3. Click on **Generate new private key** to download the `firebase_credentials.json` file (<u>Rename if different</u>).
4. Place the `firebase_credentials.json` file in the `config/firebase` directory of the cloned repository.
5. DONE

#### FAQ
1. **What if I encounter a "403 Forbidden" error when accessing the API?**
   - Ensure that the `.htaccess` file is present in the root directory of the project. This file is crucial for routing requests correctly.
2. **What if I face issues with Firebase authentication?**
   - Double-check that the `firebase_credentials.json` file is correctly placed in the `config/firebase` directory and that it contains valid credentials.
3. **What if the database connection fails?**
   - Verify that the database credentials in the `config/database/config.php` file match those of your MySQL setup. Ensure that the MySQL service is running in XAMPP.
4. **What if I need to change the port number for Apache?**
   - You can change the port number in the XAMPP Control Panel by clicking on "Config" next to Apache and selecting "httpd.conf". Look for the line `Listen 80` and change it to your desired port (e.g., `Listen 8080`). Remember to update the URL accordingly when accessing the API.
   - DONE
6. **What if I want to reset the database?**
   - You can drop the existing `ccsync_api` database in phpMyAdmin and re-import the `ccsync_api.sql` file from the `config/database` folder.