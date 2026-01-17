<div align="center">
  <img src="assets/images/logo-unsrat.png" alt="Logo UNSRAT" width="120" draggable="false" />

  <h1>INSPIRE Portal</h1>
  <p>
    <strong>Sistem Informasi Akademik dan Perencanaan Studi</strong><br>
    Universitas Sam Ratulangi
  </p>

  <br>

  <img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP">
  <img src="https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white" alt="MySQL">
  <img src="https://img.shields.io/badge/Bootstrap-4.6-7952B3?style=for-the-badge&logo=bootstrap&logoColor=white" alt="Bootstrap">
  <img src="https://img.shields.io/badge/AdminLTE-3.2-343a40?style=for-the-badge&logo=adminlte&logoColor=white" alt="AdminLTE">

  <br><br>
</div>

---

## Table of Contents

- [System Overview](#system-overview)
- [System Capabilities](#system-capabilities)
- [Technology Architecture](#technology-architecture)
- [Deployment Guide](#deployment-guide)
- [Access Credentials](#access-credentials)
- [Project Hierarchy](#project-hierarchy)
- [Author](#author)

---

## System Overview

**INSPIRE Portal** is a comprehensive academic information system designed to replicate and enhance the capabilities of the Universitas Sam Ratulangi student portal. This project serves as a robust demonstration of native PHP development, implementing secure authentication, complex database relationships, and PDF generation.

The core innovation of this system is the **Academic Planning (Perencanaan Studi)** module. Unlike the standard portal, this feature empowers students to simulate their course selections for upcoming semesters, validating prerequisites and credit limits before the official study planner (KRS) period opens.

---

## System Capabilities

<details open>
<summary><b>01. Academic Planning Module</b></summary>
<br>

This module allows students to draft their study plan strategically.

| Feature | Description |
| :--- | :--- |
| **Course Validation** | Automated checking of course availability and prerequisites. |
| **Retake Recommendations** | Algorithmically suggests courses with D or E grades for retaking. |
| **Credit Calculation** | Real-time calculation of SKS (Credit Units) limits based on previous GPA. |
| **Mandatory Course Tracking** | specific interface to identify mandatory courses not yet completed. |

</details>

<details>
<summary><b>02. Student Dashboard</b></summary>
<br>

A centralized hub for student information.

- **Academic Summary**: Displays current IPK (GPA), total credits, and semester status.
- **Financial Status**: Verification of tuition payment status for the active semester.
- **Advisor Information**: Direct access to academic advisor details.

</details>

<details>
<summary><b>03. Academic Records & Administration</b></summary>
<br>

Digital management of essential academic documents.

- **Transcript Generation**: Dynamic generation of full academic transcripts with PDF export capability.
- **Study Card (KRS)**: Digital viewing and printing of the current semester's study plan.
- **Result Card (KHS)**: detailed breakdown of grades per semester with downloadable reports.

</details>

<details>
<summary><b>04. Security & Architecture</b></summary>
<br>

Built with industry-standard security practices.

- **Secure Authentication**: Implementation of `bcrypt` for password hashing and session management.
- **SQL Injection Prevention**: Comprehensive use of **Prepared Statements** for all database interactions.
- **XSS Protection**: rigorous output escaping using `htmlspecialchars` to prevent Cross-Site Scripting.
- **CSRF Tokens**: Form validations included to prevent Cross-Site Request Forgery attacks.

</details>

---

## Technology Architecture

The application is built upon a monolithic architecture ensuring stability and ease of deployment.

| Component | Technology | Description |
| :--- | :--- | :--- |
| **Backend Language** | PHP 8.1+ | Core server-side logic (Native implementation). |
| **Database** | MySQL / MariaDB | Relational database management system. |
| **Frontend Framework** | Bootstrap 4.6 | Response UI grid and component library. |
| **Admin Template** | AdminLTE 3.2 | Professional dashboard interface. |
| **PDF Engine** | DomPDF | Engine for server-side PDF generation. |

---

## Deployment Guide

Follow these instructions to deploy the application in a local development environment.

### Prerequisites

Ensure the following software is installed:
*   **Web Server**: Apache or Nginx (Laragon Recommended)
*   **PHP**: Version 8.0 or higher
*   **Database**: MySQL 5.7 or higher

### Installation Steps

1.  **Clone the Repository**

    ```bash
    git clone https://github.com/dapzx2/project_inspire_2026.git
    cd project_inspire_2026
    ```

2.  **Database Configuration**

    Create a new database named `db_inspire_project` and import the schema.

    ```bash
    mysql -u root -p db_inspire_project < config/database.sql
    ```

3.  **Application Configuration**

    Verify database credentials in `config/database.php`.

    ```php
    $host = "localhost";
    $user = "root";
    $pass = "";
    $db   = "db_inspire_project";
    ```

4.  **Launch**

    Access the application through your web server.
    
    ```text
    http://localhost/project_inspire_2026/
    ```

---

## Access Credentials

Use the following credentials for testing and demonstration purposes.

| Role | Name | Username (NIM) | Password |
| :--- | :--- | :--- | :--- |
| **Student** | Dava Oktavito Josua L. Ulus | `220211060323` | `DAVAulus123` |
| **Student** | Romal Putra Lengkong | `220211060242` | `Romal11#` |

---

## Project Hierarchy

```text
project_inspire_2026/
├── assets/                  # Static resources (CSS, JS, Images)
├── config/                  # Configuration & Database files
├── layout/                  # Reusable UI components (Header, Footer)
├── auth.php                 # Authentication controller
├── dashboard.php            # Main student dashboard
├── perencanaan.php          # Academic planning controller
├── transkrip.php            # Transcript viewer
└── index.php                # Entry point (Login)
```

---

## Author

<table style="border: none;">
  <tr>
    <td align="center" style="border: none;">
      <a href="https://github.com/dapzx2">
        <img src="https://github.com/dapzx2.png" width="100" style="border-radius: 50%"><br>
        <b>Dava Oktavito Josua L. Ulus</b>
      </a>
      <br>
      <i>Informatics Engineering Student</i><br>
      Universitas Sam Ratulangi
    </td>
  </tr>
</table>

---

<p align="center">
  <small>&copy; 2026 Dava Oktavito Josua L. Ulus. All Rights Reserved.</small>
</p>
