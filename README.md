# GigDaemon: A Freelance Management System

GigDaemon is a full-stack web application designed to serve as a comprehensive project management and invoicing tool for freelancers and independent contractors. Built with Laravel 12 and Vue 3, this project demonstrates a complete development lifecycle, from initial architecture to production deployment with CI/CD.

This application is engineered to handle core business logic, including client and project management, time tracking, and invoice generation, showcasing the ability to build robust, scalable, and professional-grade systems.

### Screenshots

| Dashboard | Invoicing |
| :---: | :---: |
| ![Dashboard Screenshot](link_to_dashboard_screenshot.png) | ![Invoices Screenshot](link_to_invoices_screenshot.png) |

| Invoice Detail View | Generated PDF Invoice |
| :---: | :---: |
| ![Invoice Detail Screenshot](link_to_invoice_detail_screenshot.png) | ![Invoice PDF Screenshot](link_to_invoice_pdf_screenshot.png) |

---

## Key Features

*   **Client & Project Management (CRUD):** Full capabilities to create, read, update, and delete clients and their associated projects. Projects are hierarchically managed under their respective clients.
*   **Advanced Time Tracking:**
    *   **Live Timer:** Start and stop a timer for any active project.
    *   **Manual Entry:** Manually add time entries for work performed offline.
    *   **Full Edit Control:** Modify or delete any time record to ensure accurate billing.
*   **Invoicing & Financials:**
    *   **Smart Invoice Generation:** Create detailed invoices by selecting a client and their unbilled time entries.
    *   **Customizable Rates:** Set a default hourly rate for each client, which is automatically used during invoice creation.
    *   **Status Management:** Update invoice statuses (`Draft`, `Sent`, `Paid`, `Overdue`) directly from the UI.
    *   **PDF Generation:** Download professionally formatted PDF invoices for client delivery.
*   **Interactive Dashboard:**
    *   Provides an at-a-glance summary of total outstanding payments, monthly income, and total unbilled hours.
    *   A persistent indicator for any currently active timer.
*   **Security & Authorization:**
    *   API authentication powered by **Laravel Sanctum**.
    *    granular access control using **Laravel Policies** to ensure users can only access their own data.

## Technology Stack

*   **Backend:**
    *   **Laravel 12** / PHP 8.2
    *   **PostgreSQL**
    *   RESTful API
    *   **Laravel Sanctum** (API Authentication)
    *   `barryvdh/laravel-dompdf` (PDF Generation)
*   **Frontend:**
    *   **Vue.js 3** (Composition API)
    *   **Vite** (Build Tool)
    *   **Bootstrap 5** / Sass (Styling)
*   **Testing & Deployment:**
    *   **PHPUnit** (Backend Testing)
    *   **Docker** (Containerization)
    *   **Render.com** (Hosting)
    *   **CI/CD** via `render.yaml` (Infrastructure as Code)

---

## Local Setup & Installation

1.  **Clone the repository:**
    ```bash
    git clone https://github.com/kovalyoff232/gigdaemon.git
    cd gigdaemon
    ```

2.  **Install dependencies:**
    ```bash
    composer install
    npm install
    ```

3.  **Configure environment:**
    *   Copy `.env.example` to `.env`: `cp .env.example .env`
    *   Configure your local database connection (PostgreSQL/MySQL) in the `.env` file.
    *   Generate the application key: `php artisan key:generate`

4.  **Run database migrations:**
    ```bash
    php artisan migrate
    ```

5.  **Run the development servers:**
    *   In one terminal, start the Vite server: `npm run dev`
    *   In a second terminal, start the Laravel server: `php artisan serve`

6.  Access the application at `http://127.0.0.1:8000`. Register a new user to begin.

## Running Tests

To execute the backend test suite, run the following command:

```bash
php artisan test



Deployment
This project is configured for zero-downtime, continuous deployment to Render.com using a render.yaml Blueprint file.
On every push to the master branch, a new Docker image is built automatically.
The build process installs all dependencies, compiles frontend assets, and runs Laravel-specific optimizations.
Database migrations are applied automatically before the new version goes live.
The application is connected to a PostgreSQL database, also managed by the Blueprint.
All necessary environment variables (excluding secrets like APP_KEY) are defined as Infrastructure as Code within the render.yaml file.