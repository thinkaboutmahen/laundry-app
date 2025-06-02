<?php
session_start();

function isLoggedIn() {
        return isset($_SESSION['id_user']);
    }

    function isAdmin() {
        return isset($_SESSION['level']) && $_SESSION['level'] === 'Admin';
    }

function isCashier() {
    return isset($_SESSION['level']) && $_SESSION['level'] === 'Kasir';
}

function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit();
    }
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: index.php');
        exit();
    }
}
?> 