# LifeFlow | Smart Blood Donation Response

LifeFlow is a web-based blood donation management system designed to connect blood donors with people in need (patients and hospitals) efficiently. It facilitates faster and safer blood donation responses by bridging the gap between donors and seekers.

## 🌟 Features

*   **Donor Management**: 
    *   Secure donor registration and login.
    *   Donors can manage their availability status and track last donation dates.
*   **Find Donors**: 
    *   Search functionality to find available donors based on blood group and location (city/area).
*   **Blood Requests**:
    *   Patients or hospitals can post urgent or scheduled blood requests.
    *   Tracking of request status (Pending, Matching donors, Completed).
*   **Admin Dashboard**:
    *   Secure admin login to manage donors, blood requests, donations, and messages.
*   **Contact & Messaging**:
    *   Built-in contact form for general inquiries and messages.

## 🛠️ Technology Stack

*   **Frontend**: HTML5, CSS3 (Bootstrap 5), JavaScript
*   **Backend**: PHP
*   **Database**: MySQL

## 📂 Project Structure

*   `admin/` - Admin panel scripts and assets.
*   `donor/` - Donor dashboard and authentication scripts.
*   `assets/` - CSS, JS, and image files.
*   `includes/` - Reusable components (headers, footers, DB connection).
*   `database.sql` - Database schema and initial data.
*   `index.html` - Homepage.
*   `*.php` - Core logic pages (e.g., `blood-request.php`, `search-donor.php`, `contact.php`).

## 🚀 Installation & Setup

1.  **Prerequisites**: Ensure you have a local server environment like XAMPP, WAMP, or MAMP installed with PHP and MySQL running.
2.  **Clone the Repository**:
    ```bash
    git clone https://github.com/Shaik-Mahmud/LifeFlow.git
    ```
3.  **Move to Server Directory**: Place the project folder in your local server's document root (e.g., `htdocs` for XAMPP).
4.  **Database Configuration**:
    *   Open phpMyAdmin (or any MySQL client).
    *   Create a new database named `lifeflow_db`.
    *   Import the provided `database.sql` file into the new database.
5.  **Run the Project**:
    *   Open your web browser and navigate to `http://localhost/LifeFlow/`.

## 🔐 Default Credentials

**Admin Login**
*   **Username**: `admin`
*   **Password**: *(Check the `database.sql` or your hashing implementation for the plain text password, commonly `admin123` or similar for defaults)*

## 🤝 Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## 📜 License

This project is open-source and available under the [MIT License](LICENSE).
