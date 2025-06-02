<style>
    /* Sidebar Styles */
    .bg-white.border-end {
        transition: all 0.3s ease-in-out;
        z-index: 1000;
        box-shadow: 0 0 15px rgba(0,0,0,0.05);
        height: 100vh;
        position: fixed;
        left: -280px; /* Start hidden */
        top: 0;
        width: 280px; /* Set sidebar width */
    }

    .bg-white.border-end.show {
        left: 0; /* Show when toggled */
    }

    body {
        padding-bottom: 0;
        min-height: 100vh;
        display: flex;
        flex-direction: column;
        margin: 0;
        padding: 0;
    }

    /* Mobile Menu Toggle Button */
    .menu-toggle {
        /* display: none; removed */
        position: fixed;
        top: 1rem;
        left: 1rem;
        z-index: 1001;
        background: #fff;
        border: none;
        border-radius: 6px;
        padding: 0.5rem;
        box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        cursor: pointer;
        width: 40px;
        height: 40px;
        display: flex; /* Always display */
        flex-direction: column;
        justify-content: center;
        align-items: center;
        gap: 5px;
    }

    .menu-toggle span {
        display: block;
        width: 25px;
        height: 2px;
        background-color: #333;
        transition: all 0.3s ease;
    }

    .menu-toggle.active span:nth-child(1) {
        transform: translateY(7px) rotate(45deg);
    }

    .menu-toggle.active span:nth-child(2) {
        opacity: 0;
    }

    .menu-toggle.active span:nth-child(3) {
        transform: translateY(-7px) rotate(-45deg);
    }

    /* Main Content Adjustment */
    .main-content {
        transition: margin-left 0.3s ease-in-out;
        min-height: calc(100vh - 60px); /* Subtract navbar height */
        padding: 1rem;
        padding-bottom: 5rem;
        display: flex;
        flex-direction: column;
        width: 100%;
    }

    /* .main-content.menu-open { margin-left: 280px; } Add this if main content should shift */

    @media (min-width: 769px) {
        /* Remove or adjust desktop-specific styles if needed */
        /* .bg-white.border-end { width: 280px; } Already set above */
        /* body { padding-bottom: 0; } Already set above */
        /* .main-content { padding-bottom handled by body } Remove this if needed */
    }

    /* Menu Overlay */
    .menu-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,0.5);
        z-index: 999;
        opacity: 0;
        transition: opacity 0.3s ease-in-out;
    }

    .menu-overlay.show {
        display: block;
        opacity: 1;
    }

    /* Existing styles from line 137 onwards... */
    .content-wrapper {
        flex: 1 0 auto;
        display: flex;
        flex-direction: column;
    }

    /* Responsive Breakpoints */
    @media (max-width: 1200px) {
        .container {
            max-width: 100%;
            padding: 0 1rem;
        }
        .card-text {
            font-size: 1.8rem !important;
        }
        .custom-container {
            max-width: 100%;
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    @media (max-width: 992px) {
        .card-text {
            font-size: 1.6rem !important;
        }
        .table th, .table td {
            padding: 0.75rem;
        }
    }

    @media (max-width: 768px) {
        .menu-toggle {
            display: flex;
        }
        
        .bg-white.border-end {
            left: -280px;
            width: 280px;
        }
        
        .bg-white.border-end.show {
            left: 0;
        }
        
        .main-content {
            padding: 0.75rem;
            padding-bottom: 4rem;
        }
        
        .main-content.menu-open {
            margin-left: 0;
        }
        
        .menu-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }
        
        .menu-overlay.show {
            display: block;
            opacity: 1;
        }
        
        .card-text {
            font-size: 1.4rem !important;
        }
        
        .table-responsive {
            margin: 0 -0.5rem;
        }
        
        .table th, .table td {
            padding: 0.5rem;
            font-size: 0.9rem;
        }
        
        .btn {
            padding: 0.4rem 0.8rem;
            font-size: 0.9rem;
        }
        
        .modal-dialog {
            margin: 0.5rem;
        }
        
        .form-control, .form-select {
            font-size: 0.9rem;
        }
        
        .row {
            margin-left: -0.5rem;
            margin-right: -0.5rem;
        }
        
        .col-md-3 {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
        
        .card-body {
            padding: 1rem;
        }
        
        /* Improve mobile navigation */
        .nav-link {
            padding: 0.75rem 1rem;
            font-size: 0.95rem;
        }
        
        .nav-section h6 {
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .badge {
            font-size: 0.75rem;
            padding: 0.25em 0.5em;
        }
        .custom-container {
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }
    }

    @media (max-width: 576px) {
        .container {
            padding: 0.75rem;
        }
        
        .card-text {
            font-size: 1.2rem !important;
        }
        
        .btn {
            width: 100%;
            margin-bottom: 0.5rem;
        }
        
        .btn-group {
            display: flex;
            flex-direction: column;
        }
        
        .btn-group .btn {
            border-radius: 0.375rem !important;
            margin-bottom: 0.5rem;
        }
        
        .modal-body {
            padding: 0.75rem;
        }
        
        .form-group {
            margin-bottom: 0.75rem;
        }
        
        /* Improve mobile card layout */
        .card {
            margin-bottom: 0.75rem;
        }
        
        .card-body {
            padding: 0.75rem;
        }
        
        .card-title {
            font-size: 0.9rem;
        }
        
        /* Improve mobile table layout */
        .table tbody td {
            padding: 0.5rem;
            font-size: 0.85rem;
        }
        
        .table tbody td:before {
            font-size: 0.85rem;
        }
    }

    /* Improve navigation styles */
    .nav-link {
        transition: all 0.3s ease;
        border-radius: 6px;
        padding: 0.6rem 1rem;
        margin-bottom: 0.3rem;
        position: relative;
        overflow: hidden;
    }

    .nav-link:hover {
        background-color: #f8f9fa;
        transform: translateX(5px);
    }

    .nav-link.active {
        background-color: #e3f2fd !important;
        color: #0d6efd !important;
        font-weight: 500;
    }

    .nav-link.active::before {
        content: '';
        position: absolute;
        left: 0;
        top: 0;
        height: 100%;
        width: 3px;
        background-color: #0d6efd;
    }

    /* Improve card styles */
    .card {
        border: none;
        box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
        transition: transform 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        border-radius: 10px;
        margin-bottom: 1rem;
        height: 100%;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
    }

    /* Add smooth scrolling */
    html {
        scroll-behavior: smooth;
    }

    /* Improve touch targets */
    @media (max-width: 768px) {
        .nav-link, .btn, .form-control, .form-select {
            min-height: 44px;
        }
        
        .table tbody td {
            min-height: 44px;
        }
        
        /* Improve mobile spacing */
        .container {
            padding-top: 3.5rem;
        }
        
        .menu-toggle {
            top: 0.75rem;
            left: 0.75rem;
        }
    }

    .nav-section {
        margin-bottom: 1.5rem;
    }
    .nav-section h6 {
        padding-left: 0.5rem;
        margin-bottom: 0.8rem;
        font-weight: 600;
    }
    .badge {
        font-weight: 500;
        padding: 0.2em 0.5em;
        border-radius: 6px;
        font-size: 0.75rem;
    }
    .badge.bg-warning {
        color: #000;
    }

    /* Ensure sidebar badges have uniform width */
    #sidebar .nav-link .badge {
        min-width: 25px; /* Adjust as needed for uniformity */
        text-align: center;
    }

    /* Table Styles */
    .table {
        margin-bottom: 0;
    }
    @media (max-width: 768px) {
        .table-responsive {
            border: 0;
        }
        .table thead {
            display: none;
        }
        .table tbody tr {
            display: block;
            margin-bottom: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.5rem;
            background-color: #fff;
        }
        .table tbody td {
            display: block;
            text-align: right;
            padding: 0.75rem;
            border: none;
            position: relative;
            padding-left: 50%;
            border-bottom: 1px solid #eee;
        }
        .table tbody td:last-child {
            border-bottom: none;
        }
        .table tbody td:before {
            content: attr(data-label);
            position: absolute;
            left: 0.75rem;
            width: 45%;
            padding-right: 0.5rem;
            text-align: left;
            font-weight: 600;
            color: #495057;
        }
    }
    .table tbody tr {
        border-bottom: 1px solid #dee2e6;
    }
    .table tbody tr:last-child {
        border-bottom: none;
    }
    .table tbody tr:nth-child(odd) {
        background-color: #f8f9fa;
    }
    .table th {
        font-weight: 600;
        background-color: #f8f9fa;
        padding: 1rem;
    }
    .table td {
        vertical-align: middle;
        padding: 1rem;
    }

    /* Button Styles */
    .btn {
        font-weight: 500;
        padding: 0.5rem 1rem;
        border-radius: 6px;
    }
    .btn-primary {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }
    .btn-primary:hover {
        background-color: #0b5ed7;
        border-color: #0a58ca;
    }
    .btn-sm {
        padding: 0.25rem 0.5rem;
        font-size: 0.875rem;
    }

    /* Modal Styles */
    .modal-content {
        border-radius: 10px;
        border: none;
    }
    .modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }
    .modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 1rem 1.5rem;
    }
    .modal-body {
        padding: 1.5rem;
    }

    /* Form Styles */
    .form-control, .form-select {
        border-radius: 6px;
        padding: 0.5rem 0.75rem;
    }
    .form-control:focus, .form-select:focus {
        box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);
    }
    .form-label {
        font-weight: 500;
        margin-bottom: 0.5rem;
    }
    @media (max-width: 768px) {
        .form-control, .form-select {
            font-size: 0.9rem;
            padding: 0.4rem 0.6rem;
        }
        .form-label {
            font-size: 0.9rem;
            margin-bottom: 0.25rem;
        }
        .form-group {
            margin-bottom: 0.75rem;
        }
    }

    /* Custom container untuk layout lebih lebar, rapi, dan selalu di tengah */
    .custom-container {
        max-width: 1250px;
        width: 100%;
        margin-left: auto;
        margin-right: auto;
        padding-left: 1rem;
        padding-right: 1rem;
    }

    /* Ensure footer stays at bottom */
    .footer {
        flex-shrink: 0;
        margin-top: auto;
    }

    /* Center menu items without badges */
    #sidebar .nav-item > .nav-link:not(:has(.badge)) {
        justify-content: center;
    }
</style>

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<!-- Bootstrap Icons -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
<!-- Custom CSS -->
<link href="assets/css/style.css" rel="stylesheet">