# 🏗️ Building Task Manager

An efficient task manager to organize and track team activities within buildings.

---

## **Project Description**

Our clients operate in the real estate sector, managing multiple buildings within their accounts. We need to provide a tool that allows our owners to create tasks for their teams to perform within each building and add comments to their tasks for tracking progress. These tasks should be assignable to any team member and have statuses such as **Open**, **In Progress**, **Completed**, or **Rejected**.

---

## 🚀 **Features**

- **Develop an application using Laravel 10 with REST architecture.**
- **Implement GET endpoint** for listing tasks of a building along with their comments.
- **Implement POST endpoint** for creating a new task.
- **Implement POST endpoint** for creating a new comment for a task.
- **Define the payload structure** for task and comment creation, considering necessary relationships and information for possible filters.
- **Implement filtering functionality**, considering at least three filters such as:
  - Date range of creation.
  - Assigned user.
  - Task status.
  - Building it belongs to.

---

## 🛠️ **Installation & Setup**

### 📥 **Requirements**

Before getting started, make sure you have the following installed:

- Docker & Docker Compose

---

## 📌 Installation & Execution

### 1️⃣ Clone the Repository

```bash
git clone https://github.com/joaomarcosns/building_task_manager.git
cd building_task_manager
```

### 2️⃣ Configure Environment Variables

Copy the example environment file and update database credentials if necessary:

```bash
cp .env.example .env
```

### 3️⃣ Start Containers with Docker Compose

Run the following command to start the application:

```bash
docker-compose up -d
```

### 4️⃣ Install Dependencies Inside the Container

Access the Laravel container:

```bash
docker exec -it app bash
```

Inside the container, run:

```bash
cp .env.example .env
```

Change the following lines in the `.env` file:

```env
DB_CONNECTION=pgsql
DB_HOST=db
DB_PORT=5432
DB_DATABASE=building_task_manager
DB_USERNAME=username
DB_PASSWORD=userpass
```

```
composer install
php artisan key:generate
php artisan migrate
```

### 5️⃣ Access the Application

Once the setup is complete, the application will be available at:

```
http://localhost:8989
```

---

## 🏗️ Database Schema

(The database schema can be described here, including main tables and relationships.)

<p align="center">
    <img src="https://i.postimg.cc/V64HRNv2/building-task-manager.png" alt="Database Schema">
</p>
---

## 📩 Postman Collection

To test API endpoints easily, import the `collection.postman_collection.json` file into Postman.

---

## 📜 Users exemple

| Email               | Password | Role   |
|---------------------|---------|---------|
| <owner@email1.com>  | 123456  | Owner  |
| <owner@email2.com>  | 123456  | Owner  |
| <employee@email1.com>  | 123456  | Employee  |
| <employee@email2.com>  | 123456  | Employee  |
