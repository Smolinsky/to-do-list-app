# To-Do List App (Kanban Style)

Task Management API built with Laravel, featuring Boards, Tasks, and Drag-and-drop functionality.

## Features

### 🔐 Authentication
- User registration and login.
- Secure API access using Laravel Sanctum (Bearer Tokens).
- Forgot/Reset password functionality (via Mailhog for local testing).

### 📋 Board Management
- Create, view, update, and delete Boards.
- Organize tasks within specific boards.
- Boards help categorize your workflow (e.g., "Personal", "Work", "Project X").

### 🏗️ Task Management (Kanban Style)
- Full CRUD for Tasks (Create, Read, Update, Delete).
- **Drag-and-drop support**: Move tasks between boards or reorder them within a board using the `move` endpoint.
- Task attributes: Title, Description, Status (Enums), Priority (Enums), Due Date, and Position.
- **Soft Deletes**: Tasks are not immediately removed from the database, allowing for recovery.

### 📎 Task Attachments
- Upload files/images directly to tasks.
- Download or delete attachments.
- Supports multiple attachment types.

### 🛠️ Architecture & Tech Stack
- **Laravel 11+**
- **DTOs (Data Transfer Objects)**: Using `spatie/laravel-data` for structured data flow between requests and services.
- **PostgreSQL**: Robust relational database.
- **Docker (Laravel Sail)**: Easy environment setup.
- **Swagger/OpenAPI**: Interactive API documentation.

---

## Installation

1. **Clone the project:**
    ```bash 
    git clone git@github.com:Smolinsky/to-do-list-app.git
    cd to-do-list-app
    ```

2. **Setup environment:**
    ```bash
    cp .env.example .env
    ```

3. **Run Docker containers:**
    ```bash
    ./vendor/bin/sail up -d
    # OR if you have sail alias
    sail up -d
    ```

4. **Install dependencies and setup database:**
    ```bash
    sail composer install
    sail artisan migrate
    ```

5. **Access the Application:**
    - **API Base URL**: `http://localhost:8080`
    - **Swagger Documentation**: `http://localhost:8080/specification` (Check here for all endpoints and request/response structures)
    - **Mailhog (Email testing)**: `http://localhost:8025`

6. **Postman Collection:**
   A `ToDoList.postman_collection.json` file is available in the root directory for easy testing.

---

## API Highlights: Drag and Drop

To move a task (reorder or change board), use the `PATCH /api/tasks/{task}/move` endpoint.
It accepts:
- `board_id` (optional): To move the task to another board.
- `position` (optional): To change its order (rank).
- `status` (optional): To change its status (e.g., from "todo" to "done").
   
