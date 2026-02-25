# 🏗️ Frontend Architecture Documentation

## 📋 Overview
Arsitektur frontend untuk Absensi App menggunakan **Laravel Blade** dengan pendekatan **modular dan scalable**. Sistem ini dirancang untuk project kecil-menengah dengan fokus pada maintainability dan developer experience.

---

## 📁 Folder Structure

```
resources/
├── views/
│   ├── layouts/              # Master templates
│   │   ├── app.blade.php     # Main layout (with sidebar)
│   │   └── guest.blade.php   # Auth layout (no sidebar)
│   │
│   ├── partials/             # Reusable partials
│   │   ├── sidebar.blade.php # Main navigation
│   │   ├── navbar.blade.php  # Top bar
│   │   ├── footer.blade.php  # Footer
│   │   └── alerts.blade.php  # Flash messages
│   │
│   ├── components/           # Reusable UI components
│   │   ├── button.blade.php  # Button component
│   │   └── card.blade.php    # Card component
│   │
│   ├── auth/                 # Authentication pages
│   │   ├── login-example.blade.php
│   │   └── register.blade.php
│   │
│   └── dashboard/            # Feature pages
│       └── index-example.blade.php
│
├── css/
│   └── app.css               # Tailwind CSS
│
└── js/
    ├── app.js                # Main JavaScript (Alpine.js)
    └── bootstrap.js          # Laravel Echo, Axios
```

---

## 🎨 Architecture Patterns

### **1. Layout System**

#### **app.blade.php** - Main Application Layout
**Purpose**: Template untuk halaman yang memerlukan sidebar dan navbar (authenticated pages)

**Usage**:
```blade
@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
    {{-- Your content here --}}
@endsection
```

**Features**:
- ✅ Sidebar navigation (responsive)
- ✅ Top navbar dengan search & notifications
- ✅ Flash message support
- ✅ Footer
- ✅ Alpine.js reactive sidebar toggle

---

#### **guest.blade.php** - Guest Layout
**Purpose**: Template untuk halaman publik (login, register, forgot password)

**Usage**:
```blade
@extends('layouts.guest')

@section('title', 'Login')

@section('content')
    {{-- Login form --}}
@endsection

@section('footer-links')
    <a href="{{ route('register') }}">Sign up</a>
@endsection
```

---

### **2. Partials System**

#### **sidebar.blade.php**
**Features**:
- Responsive (hidden on mobile, visible on desktop)
- Mobile toggle via Alpine.js
- Active link highlighting: `{{ request()->routeIs('dashboard') }}`
- User profile section dengan logout

**Customization**:
```blade
{{-- Add new menu item --}}
<a href="{{ route('your.route') }}" 
   class="flex items-center px-4 py-3 text-sm font-medium rounded-lg
          {{ request()->routeIs('your.route') ? 'bg-blue-50 text-blue-700' : 'text-gray-700 hover:bg-gray-100' }}">
    <svg>...</svg>
    Your Menu
</a>
```

---

#### **navbar.blade.php**
**Features**:
- Mobile hamburger menu
- Search bar (desktop only)
- Notifications icon dengan badge
- User dropdown menu

---

#### **alerts.blade.php**
**Supports**:
- `session('success')` - Green alert
- `session('error')` - Red alert
- `session('warning')` - Yellow alert
- `session('info')` - Blue alert
- `$errors` - Validation errors

**Usage in Controller**:
```php
return redirect()->route('dashboard')
    ->with('success', 'Data berhasil disimpan!');
```

---

#### **footer.blade.php**
Simple footer dengan:
- Copyright info
- Links (Privacy, Terms, Support)
- Version info (development only)

---

### **3. Components System**

#### **button.blade.php**
Reusable button component dengan variants

**Usage**:
```blade
{{-- Primary button --}}
@include('components.button', [
    'text' => 'Save',
    'type' => 'primary',
    'size' => 'md',
    'buttonType' => 'submit'
])

{{-- Danger button --}}
@include('components.button', [
    'text' => 'Delete',
    'type' => 'danger',
    'size' => 'sm'
])
```

**Available variants**:
- **Types**: `primary`, `secondary`, `danger`, `success`, `outline`
- **Sizes**: `sm`, `md`, `lg`
- **Button types**: `button`, `submit`, `reset`

---

#### **card.blade.php**
Reusable card component

**Usage**:
```blade
@component('components.card', ['title' => 'Statistics'])
    <p>Your card content here</p>
    
    @slot('footer')
        <button>View More</button>
    @endslot
@endcomponent
```

---

## 🔧 Technical Stack

### **CSS Framework: Tailwind CSS**
- Utility-first CSS framework
- Responsive design (mobile-first)
- Custom color palette dapat dikonfigurasi di `tailwind.config.js`

**Common classes used**:
```css
/* Layout */
.container, .max-w-7xl, .mx-auto
.grid, .grid-cols-{n}, .gap-{n}
.flex, .items-center, .justify-between

/* Spacing */
.p-{n}, .px-{n}, .py-{n}, .m-{n}

/* Colors */
.bg-blue-600, .text-white
.hover:bg-blue-700, .focus:ring-blue-500

/* Borders & Shadows */
.rounded-lg, .shadow, .border
```

---

### **JavaScript Framework: Alpine.js**
Lightweight JavaScript framework untuk interaktivity

**Key directives**:
```html
<!-- Data binding -->
<div x-data="{ open: false }">
    <!-- Toggle visibility -->
    <button @click="open = !open">Toggle</button>
    <div x-show="open">Content</div>
</div>

<!-- Click outside -->
<div @click.away="open = false">...</div>

<!-- Transitions -->
<div x-transition>...</div>
```

**Used in**:
- Sidebar mobile toggle (`sidebarOpen`)
- Dropdown menus
- Alert dismissal

---

## 🚀 Quick Start Guide

### **Step 1: Install Dependencies**
```bash
npm install alpinejs
```

### **Step 2: Build Assets**
```bash
npm run dev    # Development
npm run build  # Production
```

### **Step 3: Create a New Page**

1. **Create blade file** in appropriate folder
```bash
# Example: resources/views/absensi/index.blade.php
```

2. **Extend layout**
```blade
@extends('layouts.app')

@section('title', 'Absensi')
@section('page-title', 'Daftar Absensi')

@section('content')
    {{-- Your content --}}
@endsection
```

3. **Define route** in `routes/web.php`
```php
Route::get('/absensi', [AbsensiController::class, 'index'])
    ->name('absensi.index');
```

4. **Add to sidebar** navigation
```blade
{{-- In partials/sidebar.blade.php --}}
<a href="{{ route('absensi.index') }}" ...>
    Absensi
</a>
```

---

## 📊 Design System Tokens

### **Colors**
```css
Primary (Blue):   #2563eb (blue-600)
Success (Green):  #16a34a (green-600)
Warning (Yellow): #ca8a04 (yellow-600)
Danger (Red):     #dc2626 (red-600)
Gray Scale:       gray-50 to gray-900
```

### **Typography**
```css
Headings:  text-2xl, text-xl, text-lg (font-bold)
Body:      text-base, text-sm (font-normal)
Labels:    text-sm (font-medium)
```

### **Spacing Scale**
```
1 = 0.25rem (4px)
2 = 0.5rem  (8px)
4 = 1rem    (16px)
6 = 1.5rem  (24px)
8 = 2rem    (32px)
```

---

## 🎯 Best Practices

### **1. Layout Selection**
```php
// ✅ Use 'app' layout for authenticated pages
@extends('layouts.app')

// ✅ Use 'guest' layout for public pages
@extends('layouts.guest')
```

### **2. Component Reusability**
```blade
// ❌ Don't repeat button markup
<button class="px-4 py-2 bg-blue-600...">Submit</button>

// ✅ Use component
@include('components.button', ['text' => 'Submit', 'type' => 'primary'])
```

### **3. Active Link Highlighting**
```blade
// ✅ Use request()->routeIs() for accuracy
{{ request()->routeIs('dashboard') ? 'active-class' : 'inactive-class' }}

// ❌ Don't use URL comparison (fragile)
{{ url()->current() == route('dashboard') ? 'active' : '' }}
```

### **4. Flash Messages**
```php
// ✅ Use specific message types
return redirect()->back()->with('success', 'Saved!');
return redirect()->back()->with('error', 'Failed!');

// ✅ Validation errors automatically displayed
$request->validate([...]); // Errors shown via alerts.blade.php
```

### **5. Asset Organization**
```blade
// ✅ Use @vite directive
@vite(['resources/css/app.css', 'resources/js/app.js'])

// ✅ Add page-specific styles/scripts
@push('styles')
    <link href="..." />
@endpush

@push('scripts')
    <script>...</script>
@endpush
```

---

## 🔍 Troubleshooting

### **Issue: Sidebar not showing**
**Solution**: Ensure you're using `layouts.app` not `layouts.guest`

### **Issue: Alpine.js not working**
**Solution**: 
1. Check `npm install` ran successfully
2. Run `npm run dev`
3. Verify `<script>` tag includes Alpine

### **Issue: Tailwind classes not applying**
**Solution**:
1. Run `npm run dev`
2. Check `tailwind.config.js` includes your blade paths
3. Clear browser cache

### **Issue: Flash messages not showing**
**Solution**:
1. Ensure `@include('partials.alerts')` exists in layout
2. Check session flash data: `return redirect()->with('success', '...')`

---

## 📈 Performance Considerations

### **Optimization Checklist**
- ✅ **CSS**: Tailwind purges unused classes in production
- ✅ **JS**: Alpine.js is lightweight (~15KB gzipped)
- ✅ **Images**: Use WebP format, lazy loading
- ✅ **Fonts**: Preconnect to font CDN
- ✅ **Assets**: Use Vite for optimized bundling

### **Production Build**
```bash
npm run build  # Minifies CSS/JS, purges unused Tailwind
```

---

## 🛠️ Extending the Architecture

### **Adding a New Layout**
1. Create `resources/views/layouts/admin.blade.php`
2. Copy structure from `app.blade.php`
3. Customize sidebar, navbar as needed

### **Adding a New Partial**
1. Create file in `resources/views/partials/`
2. Include where needed: `@include('partials.your-partial')`

### **Adding a New Component**
1. Create file in `resources/views/components/`
2. Use `@include` or `@component` directive

### **Custom Alpine.js Components**
```javascript
// resources/js/app.js
Alpine.data('dropdown', () => ({
    open: false,
    toggle() { this.open = !this.open }
}));
```

---

## 📚 Additional Resources

- **Tailwind CSS Docs**: https://tailwindcss.com/docs
- **Alpine.js Docs**: https://alpinejs.dev/start-here
- **Laravel Blade Docs**: https://laravel.com/docs/blade
- **Vite Docs**: https://vitejs.dev/guide/

---

## ✅ Architecture Decision Records (ADR)

### **Why Tailwind CSS?**
- ✅ Rapid prototyping dengan utility classes
- ✅ Small production bundle (PurgeCSS)
- ✅ Consistent design system
- ✅ No CSS naming conflicts

### **Why Alpine.js?**
- ✅ Lightweight alternative to Vue/React
- ✅ Declarative syntax similar to Vue
- ✅ No build step required (but we use Vite anyway)
- ✅ Perfect for Laravel projects

### **Why Partials over Components?**
- ✅ Simpler mental model untuk small projects
- ✅ Faster rendering (no component overhead)
- ✅ Easier debugging
- ⚠️ Consider Livewire/Inertia for complex SPA needs

---

**Version**: 1.0.0  
**Last Updated**: February 2026  
**Maintained By**: Development Team
