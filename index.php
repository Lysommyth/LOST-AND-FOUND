<?php
session_start();
// If the user is already logged in, don't show the login form, just redirect
/*if (isset($_SESSION['user_id'])) {
    header("Location: dashboard.php");
    exit();
}*/
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SU Lost & Found - Welcome</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: linear-gradient(135deg, #003366 0%, #0056b3 100%); min-height: 100vh; }
        .auth-card { border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); background: white; }
        .btn-su { background: #003366; color: white; }
        .btn-su:hover { background: #002244; color: white; }
    </style>
</head>
<body class="d-flex align-items-center">
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card auth-card p-4">
                <div class="text-center mb-4">
                    <h2 class="fw-bold" style="color: #003366;">SU Lost & Found</h2>
                    <p class="text-muted" id="form-subtitle">Sign in to your account</p>
                </div>
            <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger py-2" style="font-size: 13px;">
        <?php 
            if ($_GET['error'] == 'invalid') echo "Invalid email or password.";
            elseif ($_GET['error'] == 'exists') echo "This email is already registered.";
            elseif ($_GET['error'] == 'unverified') echo "Please verify your email to continue.";
        ?>
    </div>
<?php endif; ?>

<?php if (isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
    <div class="alert alert-success py-2" style="font-size: 13px;">
        Account created successfully! Please sign in.
    </div>
<?php endif; ?>
                <!-- One form that can handle both based on a hidden input -->
                <form id="authForm" action="access_logic.php" method="POST">
                    <input type="hidden" name="action" id="authAction" value="login">
                    
                    <div class="mb-3" id="nameField" style="display: none;">
                        <label class="form-label">Full Name</label>
                        <input type="text" name="username" class="form-control">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Strathmore Email</label>
                        <input type="email" name="email" class="form-control" placeholder="name@strathmore.edu" required>
                    </div>

                    <div class="mb-3" id="courseField" style="display: none;">
                        <label class="form-label">Course & Year</label>
                        <input type="text" name="course_year" class="form-control" placeholder="e.g., BICS 3.1">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <button type="submit" class="btn btn-su w-100 btn-lg" id="submitBtn">Sign In</button>
                </form>

                <div class="text-center mt-3">
                    <button class="btn btn-link text-decoration-none" id="toggleBtn" onclick="toggleAuth()">
                        Don't have an account? Register here
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function toggleAuth() {
        const action = document.getElementById('authAction');
        const nameField = document.getElementById('nameField');
        const submitBtn = document.getElementById('submitBtn');
        const subtitle = document.getElementById('form-subtitle');
        const toggleBtn = document.getElementById('toggleBtn');
        const form = document.getElementById('authForm');
        const courseField = document.getElementById('courseField');

        if (action.value === 'login') {
            action.value = 'register';
            form.action = 'register_process.php';
            nameField.style.display = 'block';
            submitBtn.innerText = 'Create Account';
            subtitle.innerText = 'Register your student account';
            toggleBtn.innerText = 'Already have an account? Sign in';
            courseField.style.display = 'block';
        } else {
            action.value = 'login';
            form.action = 'access_logic.php';
            nameField.style.display = 'none';
            submitBtn.innerText = 'Sign In';
            subtitle.innerText = 'Sign in to your account';
            toggleBtn.innerText = "Don't have an account? Register here";
            courseField.style.display = 'none';
        }
    }
</script>
</body>
</html>