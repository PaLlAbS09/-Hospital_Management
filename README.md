Hospital Management System 🏥

A comprehensive, multi-role Hospital and Clinic Management System built with Laravel. This application streamlines operations for medical facilities, simplifies appointment booking for patients, and provides powerful tools for doctors and administrators.

✨ Features

The system is designed around four primary user roles, each with specialized dashboards and capabilities:

👨‍⚕️ For Doctors

Queue Management: View and manage daily patient queues in real-time.

Prescriptions: Digital prescription generation and management (_prescription_modal).

Session Logs: Track working hours, sessions, and patient consultations.

Reviews & Ratings: View patient feedback and maintain a professional profile.

🏥 For Clinics

Staff Management: Add, update, and manage doctors associated with the clinic.

Schedule Management: Define operating hours, doctor availability, and appointment slots.

Promotions & Announcements: Publish clinic offers, announcements, and health notices.

Interactive Maps: Google Maps integration for easy patient navigation.

🧍 For Patients

Smart Search: Find clinics and doctors based on location, specialty, and ratings.

Appointment Booking: Seamlessly book, reschedule, or cancel appointments.

Medical History: Access past appointments, prescriptions, and session logs.

Notifications: Automated alerts for appointment confirmations, cancellations, and "no-shows".

🛡️ For Administrators

Approval Workflow: Review and approve new clinic registrations.

System Oversight: Monitor global appointments, active clinics, and user queries.

Analytics: Centralized dashboard for system-wide statistics and reporting.

🛠️ Tech Stack

Backend: PHP 8.x, Laravel (Framework)

Frontend: Blade Templating, Tailwind CSS, Vite

Database: MySQL / PostgreSQL

Integrations: Google Maps API (Location/Routing), SMTP (Email Notifications)

🚀 Getting Started

Follow these instructions to get a copy of the project up and running on your local machine for development and testing purposes.

Prerequisites

PHP >= 8.2

Composer

Node.js & NPM

MySQL or similar relational database

Installation

Clone the repository:

git clone https://github.com/yourusername/Hospital_Management.git
cd Hospital_Management


Install PHP dependencies:

composer install


Install NPM dependencies:

npm install


Environment Setup:
Copy the .env.example file to .env and configure your database and mail credentials.

cp .env.example .env


Generate Application Key:

php artisan key:generate


Run Migrations & Seeders:
This will set up your database schema and populate it with initial dummy data (Admins, dummy clinics, etc.).

php artisan migrate --seed


Link Storage:

php artisan storage:link


Start the Development Servers:
You will need two terminal windows to run both the backend and the frontend build tool.

# Terminal 1
php artisan serve

# Terminal 2
npm run dev


Visit http://localhost:8000 in your browser to access the application.

⚙️ Configuration

Email Notifications

To enable appointment confirmations, cancellations, and rating requests, configure your SMTP settings in the .env file:

MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@yourhospital.com"
MAIL_FROM_NAME="${APP_NAME}"


Google Maps API

To enable the clinic mapping and navigation features, add your Google Maps API key to the .env file:

GOOGLE_MAPS_API_KEY=your_api_key_here


📜 Scheduled Tasks (Cron Jobs)

This application relies on Laravel's task scheduling for automated tasks (e.g., handling "No-Show" appointments). Add the following Cron entry to your server to run every minute:

* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1


🤝 Contributing

Contributions, issues, and feature requests are welcome!
Feel free to check issues page.

Fork the Project

Create your Feature Branch (git checkout -b feature/AmazingFeature)

Commit your Changes (git commit -m 'Add some AmazingFeature')

Push to the Branch (git push origin feature/AmazingFeature)

Open a Pull Request

📄 License

This project is licensed under the MIT License - see the LICENSE.md file for details.
