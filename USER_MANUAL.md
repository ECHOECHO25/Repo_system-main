# REPO Research Monitoring System - User Manual

Welcome to the **REPO Research Monitoring System**, designed to streamline the management of research publications, track faculty metrics, and monitor institutional research outputs for Benguet State University. 

This manual will guide you through the system's core features.

---

## 1. Getting Started

### Logging In
1. Navigate to the system URL (e.g., `http://localhost:5174/login`).
2. Enter your **Username** and **Password**.
   - *Note: If you exceed 5 failed login attempts, you will be locked out for 60 seconds.*
3. Click **Login**. You will be directed to the Dashboard.

### Roles and Permissions
Depending on your assigned role, your access will vary:
- **Viewer/Guest:** Can only view data and dashboards. Cannot add or edit records.
- **Editor:** Can add, edit, and manage Publications, Faculty, Author Matches, and Acknowledgements.
- **Admin:** Has Editor privileges plus the ability to add and manage system Users and view Audit Logs.

---

## 2. Dashboard
The Dashboard provides a bird's-eye view of the institution's research performance.
- **KPI Cards:** View total publications, total faculty, and other top-level metrics.
- **Trend Charts:** Analyze publication trends over the years (e.g., yearly output, breakdowns by college or publication type).
- **Recent Activity:** See a quick log of recent actions taken within the system.

---

## 3. Managing Publications
Navigate to the **Publications** tab to track all research outputs.

### Adding a Publication
1. Click **+ Add Publication**.
2. Fill in the required details:
   - **Title**, **Year**, and **College/Institute**.
   - **Authors:** List the authors exactly as they appear on the paper.
   - **Publication Type** (e.g., Journal, Book, Conference Proceeding).
   - **Citations:** Update the current citation count.
3. Click **Save**.

### Bulk Importing Publications
1. On the Publications page, click **Bulk Import**.
2. Upload your prepared Excel/CSV file matching the system's template format.
3. The system will automatically check for duplicates and process the data.

---

## 4. Faculty Management
Navigate to the **Faculty** tab to track individual researchers and their metrics.

### Viewing Faculty Profiles
- The table displays faculty names, affiliated colleges, and key metrics like **Google Scholar Citations**, **H-Index**, and **i10-Index**.
- You can sort and filter the table to find specific researchers or view the top-ranking faculty.

### Adding/Editing Faculty
1. Click **+ Add Faculty** or click the **Edit** icon next to an existing name.
2. Update their basic details and research metrics.
3. Ensure their **Google Scholar Account** link is accurate for future reference.

---

## 5. Author Matching
Because publications often list authors differently (e.g., "Doe, J." vs "John Doe"), the system includes a matching feature to link publications to the correct faculty member.

1. Navigate to **Author Matches**.
2. You will see a list of **Pending** matches detected during publication imports.
3. Review the suggested publication-to-faculty link.
4. Click **Confirm** if it is a correct match, or **Reject/Reassign** if it is incorrect.

---

## 6. Acknowledgements
Navigate to the **Acknowledgements** tab to track physical or digital copies of journals, books, or certificates received by the office.

1. Click **+ Add Acknowledgement**.
2. Enter the Date, Time, and details of the person who issued/received the item.
3. **Add Items:** List the specific items received (e.g., "Volume 5, Issue 2", "10 copies").
4. Click **Save**.

---

## 7. Audit Logs & Admin Features (Admin Only)

### User Management
1. Navigate to **Users** or **Add User**.
2. Create new accounts for your team, assigning them as Viewers, Editors, or Admins.
3. You can also deactivate users who no longer need access.

### Audit Logs
1. Navigate to **Audit Logs**.
2. This screen provides a permanent history of who did what (e.g., "Admin deleted Publication #12"). 
3. Use the search function to track down specific actions by user, date, or entity.

---
*For technical support, please contact your system administrator.*
