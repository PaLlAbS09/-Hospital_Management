# Hospital Management System

A comprehensive, full-stack healthcare management application built with Laravel designed to streamline clinic operations, patient appointments, and administrative workflows.

## 🚀 Key Features

### Multi-Role Architecture

* **Admin Dashboard:** Oversee system queries, patient records, doctors, clinics, and appointments.


* **Clinic Portal:** Manage clinic profiles, doctor schedules, promotional offers, and announcements.


* **Doctor Dashboard:** Access patient queues, manage session logs, and issue prescriptions.


* **Patient Portal:** Book appointments, review clinic doctors, and track appointment history.



### Core Modules

* **Appointment Management:** Comprehensive booking service with automated handling for confirmed, cancelled, and no-show appointments.


* **Notification System:** Integrated SMS gateway (including Twilio support) for real-time alerts and appointment updates.


* **Rating & Reviews:** A dedicated system for patients to leave ratings and reviews for doctors.


* **Location Services:** Google Maps integration for mapping clinic locations.



## 🛠️ Tech Stack

* **Backend:** Laravel (PHP).


* **Frontend:** Blade templating, Vite for asset bundling, and customized UI components.


* **Testing:** PHPUnit with comprehensive Feature and Unit test suites.


* **Package Management:** Composer for PHP dependencies and NPM for frontend assets.



## ⚙️ Installation & Setup

1. **Clone the repository:**
```bash
git clone <your-repository-url>
cd Hospital_Management

```


2. **Install PHP dependencies:**
```bash
composer install

```


3. **Environment Setup:**
Copy the example environment file and generate an application key:


```bash
cp .env.example .env
php artisan key:generate

```


4. **Database Configuration:**
Update your `.env` file with your database credentials. Then, run the migrations and seeders:


```bash
php artisan migrate --seed

```


5. **Install Frontend Assets:**
Build the frontend assets using Vite:


```bash
npm install
npm run dev

```


6. **Run the application:**
```bash
php artisan serve

```



## 🧪 Testing

Run the automated test suite to ensure core workflows, password resets, public pages, and SMS notifications function correctly:

```bash
php artisan test

```
