
```markdown
# KYC Record Management System

This is a simple KYC (Know Your Customer) Record Management System implemented in PHP.

## Table of Contents
- [Features](#features)
- [Installation](#installation)
- [Usage](#usage)
- [Endpoints](#endpoints)
- [Dependencies](#dependencies)
- [License](#license)

## Features

- User authentication (login/logout)
- Edit, delete user KYC records
- File upload for user KYC documents
- Secure authentication using JWT tokens

## Installation

1. Clone the repository:
```
   ```bash
   git clone https://github.com/youngyusuff6/kyc_app.git
   ```

2. Configure your web server (e.g., Apache, Nginx) to point to the project's root directory.

3. Import the database schema from `db.sql` into your MySQL database.

4. Configure the database connection in `config.php` with your database credentials.

5. Install Composer dependencies:

   ```bash
   composer install
   ```

## Usage

1. Register a new user or use an existing one.

2. Log in to the system.

3. Use the provided API endpoints to manage user records.

## Endpoints

- `POST /api.php?action=login`: User login
- `POST /api.php?action=logout`: User logout and end session
- `GET /api.php?action=get_user&id=={id}`: Get user detail
- `PUT /api.php?action=edit_profile&id=={id}`: Edit a user record
- `DELETE /api.php?action=delete_profile&id{id}`: Delete a user record

For more details, refer to the source code

## Dependencies

- [PHP](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [MySQL](https://www.mysql.com/)

```
