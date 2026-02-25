# 📋 TODO LIST - WEBSITE ABSENSI

## ✅ **COMPLETED**

- [x] Setup environment variables (.env)
- [x] Konfigurasi koneksi API (axios, baseURL)
- [x] Install dependencies (Alpine.js, Tailwind, Axios)
- [x] Fix bugs di kode (api.js, bootstrap.js, app.js)
- [x] Test koneksi API berhasil (CORS, authentication)
- [x] Buat API service layer (resources/js/services/api.js)
- [x] Auto-load token dari localStorage

---

## 🔄 **IN PROGRESS**

### **TODO #4: Buat/Seed User untuk Testing**
**Priority:** HIGH  
**File:** `api/database/seeders/UserSeeder.php`

**Tasks:**
- [ ] Buat seeder untuk user test
- [ ] Seed user: admin@test.com / password
- [ ] Verify user bisa login via API

**Command:**
```bash
cd D:\PKL\api
php artisan make:seeder UserSeeder
php artisan db:seed --class=UserSeeder
```

---

## 📝 **TODO**

### **TODO #5: Test Login Page & Simpan Token**
**Priority:** HIGH  
**File:** `resources/views/auth/login.blade.php`

**Tasks:**
- [ ] Test form login dengan user yang sudah dibuat
- [ ] Verify token tersimpan di localStorage
- [ ] Verify redirect ke /dashboard berhasil
- [ ] Test error handling (wrong password, etc)

**Test URL:** `http://localhost:8000/login`

---

### **TODO #6: Buat Layout Dashboard dengan Sidebar**
**Priority:** HIGH  
**Files:** 
- `resources/views/layouts/app.blade.php`
- `resources/views/partials/sidebar.blade.php`

**Tasks:**
- [ ] Design sidebar navigation
- [ ] Tambah menu: Dashboard, Absensi, Members, Offices
- [ ] Tambah header dengan user info
- [ ] Responsive mobile menu (toggle sidebar)
- [ ] Active menu highlighting
- [ ] Logo & branding

**Features:**
- Sidebar collapse/expand
- User dropdown (profile, logout)
- Mobile-friendly

---

### **TODO #7: Fetch & Tampilkan Data Attendances**
**Priority:** HIGH  
**File:** `resources/views/dashboard/index.blade.php`

**Tasks:**
- [ ] Fetch data attendances dari API
- [ ] Display data dalam table/cards
- [ ] Tambah filter (tanggal, member)
- [ ] Pagination
- [ ] Search functionality
- [ ] Empty state jika tidak ada data

**API Endpoint:** `GET /api/attendances`

---

### **TODO #8: Implementasi Logout Functionality**
**Priority:** MEDIUM  
**Files:** 
- `resources/views/partials/sidebar.blade.php`
- `resources/js/app.js`

**Tasks:**
- [ ] Tambah logout button di sidebar/header
- [ ] Panggil `window.app.logout()`
- [ ] Clear localStorage token
- [ ] Redirect ke /login
- [ ] Optional: Call API logout endpoint

---

### **TODO #9: Error Handling & Loading States**
**Priority:** MEDIUM  
**Files:** All views with API calls

**Tasks:**
- [ ] Loading spinner/skeleton saat fetch data
- [ ] Error message display (toast/alert)
- [ ] Network error handling
- [ ] 401 auto-redirect ke login
- [ ] Empty state components
- [ ] Retry mechanism untuk failed requests

---

### **TODO #10: Test Full Flow**
**Priority:** HIGH  
**Steps:**
- [ ] Login dengan user test
- [ ] Token tersimpan dengan benar
- [ ] Dashboard load data attendances
- [ ] Navigate ke halaman lain
- [ ] Token persist di semua halaman
- [ ] Logout berhasil
- [ ] Redirect ke login setelah logout
- [ ] Access protected page tanpa token → redirect login

---

## 🎨 **FUTURE ENHANCEMENTS**

### **TODO #11: Halaman Members**
- [ ] List all members
- [ ] Create new member
- [ ] Edit member
- [ ] Delete member
- [ ] Search & filter

### **TODO #12: Halaman Offices**
- [ ] List all offices
- [ ] CRUD operations
- [ ] Map integration (optional)

### **TODO #13: Halaman Absensi/Check-in**
- [ ] Manual check-in form
- [ ] Geolocation verification
- [ ] Upload photo (optional)
- [ ] History absensi per member

### **TODO #14: Halaman Progress**
- [ ] List progresses
- [ ] CRUD operations
- [ ] Filter by date/member

### **TODO #15: User Profile & Settings**
- [ ] Edit profile
- [ ] Change password
- [ ] Notification settings

### **TODO #16: Reports & Analytics**
- [ ] Attendance reports
- [ ] Export to Excel/PDF
- [ ] Charts & statistics
- [ ] Summary per periode

### **TODO #17: UI/UX Improvements**
- [ ] Toast notifications (success/error)
- [ ] Confirmation modals
- [ ] Better loading states
- [ ] Animations & transitions
- [ ] Dark mode (optional)

### **TODO #18: Middleware & Route Protection**
- [ ] Create auth middleware di Laravel
- [ ] Protect dashboard routes
- [ ] Role-based access control
- [ ] Redirect unauthenticated users

---

## 🐛 **KNOWN ISSUES**

- None (semua bug sudah di-fix)

---

## 📝 **NOTES**

### **Environment Setup:**
- API URL: `http://localhost:1337`
- Website URL: `http://localhost:8000`
- Vite Dev: `http://localhost:5173`

### **Credentials Test:**
- Email: `admin@test.com`
- Password: `password`

### **Commands:**
```bash
# API Server
cd D:\PKL\api
php artisan serve --port=1337

# Website Server
cd D:\PKL\absensi
php artisan serve --port=8000

# Vite Dev Server
cd D:\PKL\absensi
npm run dev
```

---

**Last Updated:** February 9, 2026  
**Status:** In Development 🚧
