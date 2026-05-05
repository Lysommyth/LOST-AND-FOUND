<?php
session_start();
require 'config/db.php';

// 1. Check if the student is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit();
}

// 2. Fetch the logged-in student's details
$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$currentUser = $stmt->fetch();

// 3. Fetch all found items from the database
$itemStmt = $pdo->query("SELECT * FROM items ORDER BY created_at DESC");
$dbItems = $itemStmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SU Lost & Found - Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">