# StudyAid

StudyAid is a web-based application designed to assist students with their learning process. It provides a suite of tools for document management, content creation, and interactive learning, leveraging various Google Cloud services for advanced features.

## Prerequisites

Before you begin, ensure you have the following installed:

*   **XAMPP:** A web server solution that includes Apache, MySQL, and PHP.
*   **Composer:** A dependency manager for PHP.
*   **Git:** For version control.

## Setup Instructions

### 1. Clone the Repository

Clone the project to your local machine. It is recommended to clone it directly into your XAMPP `htdocs` directory.

```bash
git clone <repository-url> /Applications/XAMPP/xamppfiles/htdocs/studyaid
```

### 2. Install Dependencies

Navigate to the project directory and install the required PHP dependencies using Composer.

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/studyaid
composer install
```

### 3. Database Setup

1.  **Start XAMPP:** Open the XAMPP control panel and start the Apache and MySQL modules.
2.  **Create Database:**
    *   Open your web browser and navigate to `http://localhost/phpmyadmin`.
    *   Create a new database named `studyaid`.
3.  **Import SQL File:**
    *   Select the `studyaid` database in phpMyAdmin.
    *   Click on the "Import" tab.
    *   Choose the `studyaid.sql` file from the root of the project directory and click "Go".

### 4. Configure the Application

1.  **Database Connection:**
    *   Open `app/config/database.php`.
    *   Ensure the database credentials match your MySQL setup (the default XAMPP credentials are usually correct).

2.  **Google Cloud Services:**
    *   **Google Cloud Storage & Vision:**
        *   Place your Google Cloud JSON key file in the `credentials/` directory.
        *   Update `app/config/cloud_storage.php` with your bucket name and the correct path to your key file.
    *   **Google Gemini API:**
        *   Set your Gemini API key as an environment variable or directly in `app/config/gemini.php`.

3.  **Email Service:**
    *   Open `app/config/email.php` and configure your SMTP settings (e.g., for Gmail).

## Running the Application

1.  **Start the Server:** Ensure that Apache and MySQL are running in your XAMPP control panel.
2.  **Access the Application:** Open your web browser and navigate to:
    ```
    http://localhost/studyaid
    ```

## Setup for Windows

The setup steps for Windows are nearly identical, with the main difference being the path to your `htdocs` directory.

1.  **Clone the Repository:** Clone the project into your XAMPP `htdocs` folder (e.g., `C:\xampp\htdocs\studyaid`).
2.  **Install Dependencies:** Open a command prompt or terminal, navigate to the project directory (`cd C:\xampp\htdocs\studyaid`), and run `composer install`.
3.  **Database Setup:** Follow the same database setup steps as above using phpMyAdmin.
4.  **Configuration:** Configure the application files in the `app/config/` directory as described above.
5.  **Run the Application:** Start Apache and MySQL via the XAMPP control panel and access `http://localhost/studyaid` in your browser.
