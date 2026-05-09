# REPO Research Monitoring System - Technical Documentation

## 1. System Overview
The REPO System is a full-stack web application designed to track and manage research publications, faculty research metrics, author matching, and related records for Benguet State University. 

### Technology Stack
- **Frontend:** Vue 3 + Vite + Tailwind CSS + Axios + Chart.js
- **Backend:** PHP 8.1+ + CodeIgniter 4 (REST APIs)
- **Database:** MySQL (via MySQLi driver)
- **Authentication:** Session-based (with role-based access control)

---

## 2. Local Development Setup

### Backend Configuration
1. Navigate to the `backend/` directory:
   ```bash
   cd backend
   ```
2. Install PHP dependencies:
   ```bash
   composer install
   ```
3. Set up your environment file:
   Copy `env` to `.env` (or configure `app/Config/Database.php` directly) with the following database credentials:
   - Host: `localhost` (or `127.0.0.1`)
   - Database: `remis_db`
   - User: `root`
   - Password: `[your_password]`
   - Port: `3306` (or `3307` depending on your local MySQL setup)
4. Run database migrations to build the schema:
   ```bash
   php spark migrate
   ```
5. Seed initial data (like the default Admin user):
   ```bash
   php spark db:seed UsersSeeder
   ```
6. Start the CodeIgniter development server:
   ```bash
   php spark serve --host localhost --port 8080
   ```

### Frontend Configuration
1. Navigate to the `frontend/` directory:
   ```bash
   cd frontend
   ```
2. Install Node dependencies:
   ```bash
   npm install
   ```
3. Start the Vite development server:
   ```bash
   npm run dev
   ```
4. Access the application at `http://localhost:5174` (or whichever port Vite assigns).

---

## 3. Database Architecture (ERD Summary)

The system relies on a relational database structure. Key tables include:

- **`users`**: Stores login credentials (`username`, `password_hash`), system `role` (Admin, Editor, Viewer), and `status`.
- **`faculty`**: Stores faculty metrics (`google_scholar_citations`, `h_index`, `i10_index`), affiliation, and contact info. Uses soft deletes.
- **`publications`**: Stores metadata for journals, books, and conference proceedings (`title`, `year`, `authors`, `citations`).
- **`publication_author_links`**: A junction table that resolves raw publication author strings to specific `faculty_id` records. Tracks `match_type` and `status` (pending, confirmed, rejected).
- **`acknowledgements` & `acknowledgement_items`**: Tracks physical/digital document deliveries (One-to-Many relationship).
- **`audit_logs`**: Immutable ledger recording all `CREATE`, `UPDATE`, and `DELETE` actions taken by users for security tracking.

---

## 4. API & Routing

### Authentication Flow
The system utilizes stateful session-based auth leveraging CodeIgniter's session library.
- **Login:** `POST /api/auth/login`
- **Verify Session:** `GET /api/auth/me`
- **Logout:** `POST /api/auth/logout`

*CORS Notice:* The backend is configured to accept requests from the frontend origin (e.g., `http://localhost:5174`) with `withCredentials=true` enabled to allow secure cookie transmission.

### Core API Endpoints
All main endpoints follow standard RESTful conventions:
- **Dashboard Data:** `GET /api/dashboard/*`
- **Publications:** `GET / POST / PUT / DELETE /api/publications`
  - Bulk Import: `POST /api/publications/bulk-import`
- **Faculty:** `GET / POST / PUT / DELETE /api/publications`
- **Author Matches:** `GET /api/publication-author-links/pending`
- **Acknowledgements:** `GET / POST / PUT / DELETE /api/acknowledgements`
- **Audit Logs:** `GET /api/audit-logs`

---

## 5. Security & Permissions

Access control is managed at both the Frontend (Route Guards) and Backend (Controller Filters/Logic) layers.

**Roles:**
- `admin`: Full system control. Can manage users and view audit logs.
- `editor`: Can create, update, and delete operational data (Publications, Faculty, Acknowledgements).
- `viewer` (guest): Read-only access to dashboards and tables.

**Audit Logging:**
The `AuditLogger.php` library is invoked across controllers to permanently record state-changing actions. It captures the user ID, action type, entity modified, and IP address.

---

## 6. Project Structure

```
Repo_system-main/
├── backend/
│   ├── app/
│   │   ├── Config/        # Database, Routes, and CORS settings
│   │   ├── Controllers/   # REST API Controllers (Api/...)
│   │   ├── Database/      # Migrations and Seeders
│   │   ├── Libraries/     # Custom helpers (e.g., AuditLogger)
│   │   └── Models/        # CodeIgniter 4 Entity Models
│   └── spark              # CLI Tool
│
└── frontend/
    ├── public/            # Static assets
    ├── src/
    │   ├── composables/   # Shared Vue logic (e.g., useAuth.js)
    │   ├── views/         # Vue Page Components (Dashboard, Publications, etc.)
    │   ├── App.vue        # Root Component
    │   └── main.js        # Vue app initialization and Vue Router setup
    ├── package.json
    └── vite.config.js
```
