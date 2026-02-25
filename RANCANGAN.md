# 🎯 RANCANGAN WEBSITE ABSENSI - ROLE-BASED SYSTEM

**Last Updated:** February 9, 2026  
**Status:** In Development 🚧

---

## 📋 KONSEP UTAMA

Website Absensi dengan **2 Role Utama:**
1. **Admin** - Full access & management
2. **User** - Limited access (register, view own progress)

---

## 👥 ROLE & PERMISSIONS

### **🔴 ADMIN**
**Access Level:** Full Control

**Fitur:**
1. ✅ Login
2. ✅ Dashboard (stats & overview)
3. 📊 Manajemen User (CRUD)
4. 📋 Manajemen Absensi (CRUD & monitoring)
5. 📄 Export Absensi to PDF
6. 👤 Profile Management
7. ⚙️ Settings

---

### **🔵 USER**
**Access Level:** Limited (Own Data Only)

**Fitur:**
1. ✅ Login
2. 📝 Register (self-registration)
3. 📊 View Own Progress
4. 📥 Download Laporan Progress (PDF)
5. 👤 Profile Management

---

## 🏗️ STRUKTUR HALAMAN

### **PUBLIC PAGES** (No Auth Required)
```
/                    → Landing/Welcome page
/login               → Login page (Admin & User)
/register            → User self-registration
/forgot-password     → Password recovery (optional)
```

---

### **ADMIN PAGES** (Role: Admin)
```
/admin/dashboard              → Dashboard overview
/admin/users                  → Manajemen User (List)
/admin/users/create           → Tambah User
/admin/users/{id}/edit        → Edit User
/admin/users/{id}             → Detail User

/admin/absensi                → Manajemen Absensi (List)
/admin/absensi/create         → Input Absensi Manual
/admin/absensi/{id}/edit      → Edit Absensi
/admin/absensi/{id}           → Detail Absensi
/admin/absensi/export         → Export to PDF

/admin/members                → Manajemen Members
/admin/offices                → Manajemen Offices
/admin/progress               → Manajemen Progress

/admin/profile                → Admin Profile
/admin/settings               → Settings
```

---

### **USER PAGES** (Role: User)
```
/user/dashboard               → User Dashboard (own stats)
/user/progress                → My Progress (view only)
/user/progress/download       → Download PDF
/user/profile                 → User Profile
```

---

## 🗂️ DATABASE SCHEMA (Existing from API)

### **Users Table**
```sql
- id
- name
- email
- password
- role (admin/user)
- is_active
- created_at
- updated_at
```

### **Members Table**
```sql
- id
- name
- email
- phone
- position
- office_id
- created_at
- updated_at
```

### **Offices Table**
```sql
- id
- name
- address
- latitude
- longitude
- created_at
- updated_at
```

### **Attendances Table**
```sql
- id
- member_id
- office_id
- check_in_time
- check_out_time
- notes
- created_at
- updated_at
```

### **Progresses Table**
```sql
- id
- member_id
- title
- description
- status
- date
- created_at
- updated_at
```

---

## 📊 FITUR DETAIL

### **1. ADMIN - MANAJEMEN USER**

**List Users (`/admin/users`)**
- Table dengan kolom: Name, Email, Role, Status, Actions
- Search & Filter (by role, status)
- Pagination
- Actions: Edit, Delete, Toggle Status

**Create User (`/admin/users/create`)**
Form fields:
- Name (required)
- Email (required, unique)
- Password (required, min 8 chars)
- Role (dropdown: Admin/User)
- Status (active/inactive)

**Edit User (`/admin/users/{id}/edit`)**
Same form as create, pre-filled

**Delete User**
Confirmation modal → Soft delete

---

### **2. ADMIN - MANAJEMEN ABSENSI**

**List Absensi (`/admin/absensi`)**
- Table: Member Name, Office, Check-in, Check-out, Date, Status
- Filter: Date range, Member, Office
- Search by member name
- Bulk actions: Export selected
- Export All to PDF button

**Create/Edit Absensi**
Form fields:
- Member (dropdown)
- Office (dropdown)
- Check-in Time (datetime)
- Check-out Time (datetime, optional)
- Notes (textarea, optional)

**Export to PDF**
Features:
- Date range selection
- Filter by member/office
- Include: Logo, Date, Member info, Check-in/out times
- Table format with totals

---

### **3. USER - REGISTER**

**Register Page (`/register`)**
Form fields:
- Full Name (required)
- Email (required, unique)
- Password (required, min 8 chars)
- Confirm Password (required, match)
- Phone (optional)
- Accept Terms & Conditions (checkbox)

After register:
- Auto redirect to login
- Email verification (optional)

---

### **4. USER - DOWNLOAD LAPORAN PROGRESS**

**My Progress (`/user/progress`)**
- List own progress/activities
- Filter by date range, status
- Summary stats (total tasks, completed, pending)

**Download PDF**
Button: "Download Report"
Content:
- User info (name, email, period)
- Progress list (date, title, status)
- Charts/graphs (optional)
- Total summary

---

### **5. PROFILE PAGE** (Admin & User)

**Profile Page (`/admin/profile` or `/user/profile`)**
Tabs:
1. **Personal Info**
   - Name, Email, Phone
   - Photo upload
   - Edit button

2. **Change Password**
   - Current Password
   - New Password
   - Confirm Password

3. **Activity Log** (optional)
   - Recent activities
   - Login history

---

## 🛡️ SECURITY & MIDDLEWARE

### **Route Protection**
```php
// Admin only
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', ...);
    Route::resource('/admin/users', ...);
    // etc
});

// User only
Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
    Route::get('/user/dashboard', ...);
    Route::get('/user/progress', ...);
});

// Both Admin & User
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/profile', ...);
});
```

### **API Endpoints (from API folder)**
```
POST   /api/login                 → Login (Admin & User)
POST   /api/logout                → Logout
GET    /api/me                    → Get authenticated user

// Admin endpoints
GET    /api/users                 → List users
POST   /api/users                 → Create user
GET    /api/users/{id}            → Get user
PUT    /api/users/{id}            → Update user
DELETE /api/users/{id}            → Delete user

GET    /api/attendances           → List attendances
POST   /api/attendances           → Create attendance
GET    /api/attendances/{id}      → Get attendance
PUT    /api/attendances/{id}      → Update attendance
DELETE /api/attendances/{id}      → Delete attendance
GET    /api/attendances/export    → Export PDF

// User endpoints
GET    /api/progress/me           → Get own progress
GET    /api/progress/me/export    → Export own progress PDF
```

---

## 🎨 UI/UX DESIGN

### **Color Scheme**
- Primary: Blue (#3B82F6)
- Secondary: Purple (#8B5CF6)
- Success: Green (#10B981)
- Warning: Yellow (#F59E0B)
- Danger: Red (#EF4444)
- Dark: Gray (#1F2937)

### **Components Needed**
- ✅ Sidebar Navigation
- ✅ Top Header with User Dropdown
- ✅ Login Form
- 📝 Register Form
- 📊 Data Tables (with search, filter, pagination)
- 📝 Forms (Create/Edit)
- 🗑️ Delete Confirmation Modal
- 📄 PDF Export Preview
- 📊 Charts & Stats Cards
- 🔔 Toast Notifications
- ⚠️ Alert Messages

---

## 📦 DEPENDENCIES

### **Frontend (absensi folder)**
```json
{
  "alpinejs": "^3.13.0",        // ✅ Installed
  "axios": "^1.6.4",            // ✅ Installed
  "tailwindcss": "^4.1.18",     // ✅ Installed
  
  // Need to install:
  "jspdf": "^2.5.1",            // For PDF generation
  "jspdf-autotable": "^3.8.0",  // For PDF tables
  "chart.js": "^4.4.0"          // For charts (optional)
}
```

### **Backend (API folder)**
```json
{
  "laravel/sanctum": "^3.3",    // ✅ Installed
  "barryvdh/laravel-dompdf": "^2.0"  // For server-side PDF
}
```

---

## 📅 DEVELOPMENT ROADMAP

### **Phase 1: Foundation** ✅ (DONE)
- [x] Setup environment
- [x] API connection
- [x] Login functionality
- [x] Basic dashboard layout

### **Phase 2: Auth & Roles** (IN PROGRESS)
- [ ] Fix dashboard token issue
- [ ] Implement role-based middleware
- [ ] User registration page
- [ ] Redirect based on role after login

### **Phase 3: Admin Features**
- [ ] User management (CRUD)
- [ ] Absensi management (CRUD)
- [ ] PDF export for absensi
- [ ] Stats & reports

### **Phase 4: User Features**
- [ ] User dashboard
- [ ] View own progress
- [ ] Download progress PDF
- [ ] Profile page

### **Phase 5: Polish & Testing**
- [ ] Error handling
- [ ] Loading states
- [ ] Responsive design
- [ ] Performance optimization
- [ ] Full system testing

---

## 🔄 API SERVICES NEEDED

**File: `resources/js/services/api.js`** (需要添加)

```javascript
// User Management (Admin only)
async getUsers(params = {}) {
    return window.axios.get('/users', { params });
},

async createUser(data) {
    return window.axios.post('/users', data);
},

async updateUser(id, data) {
    return window.axios.put(`/users/${id}`, data);
},

async deleteUser(id) {
    return window.axios.delete(`/users/${id}`);
},

// Registration (Public)
async register(data) {
    return window.axios.post('/register', data);
},

// Profile
async updateProfile(data) {
    return window.axios.put('/profile', data);
},

async changePassword(data) {
    return window.axios.post('/profile/password', data);
},

// Export PDF
async exportAbsensiPDF(params = {}) {
    return window.axios.get('/attendances/export', { 
        params,
        responseType: 'blob' 
    });
},

async exportProgressPDF() {
    return window.axios.get('/progress/me/export', { 
        responseType: 'blob' 
    });
}
```

---

## 📝 NEXT STEPS

1. **Immediate Fix:** Dashboard token issue
2. **Add Middleware:** Role-based access control
3. **Build Admin Panel:** User management first
4. **Build User Panel:** Registration & progress view
5. **Implement PDF:** Export functionality
6. **Testing:** Full system test

---

## 🎯 SUCCESS METRICS

- ✅ Admin can manage all users
- ✅ Admin can manage all absensi
- ✅ Admin can export absensi to PDF
- ✅ User can self-register
- ✅ User can view own progress
- ✅ User can download progress PDF
- ✅ Role-based access working
- ✅ All pages responsive
- ✅ No security vulnerabilities

---

**Status:** Ready for Phase 2 Development! 🚀
