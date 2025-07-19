<?php
// 세션 시작
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Production config 사용
require_once '../includes/config_production.php';
$page_title = "Login";
include '../includes/header.php';
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-md mx-auto">
        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold">Welcome Back</h1>
                    <p class="text-sm text-muted-foreground">Enter your credentials to access your account.</p>
                </div>

                <?php if(isset($_SESSION['login_error'])): ?>
                    <div class="bg-destructive/15 text-destructive p-4 rounded-lg">
                        <?php 
                        echo htmlspecialchars($_SESSION['login_error']);
                        unset($_SESSION['login_error']);
                        ?>
                    </div>
                <?php endif; ?>

                <form action="./login_check.php" method="post" class="space-y-4">
                    <div class="space-y-2">
                        <label for="id" class="text-sm font-medium">ID</label>
                        <input type="text" id="id" name="id" required
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <div class="space-y-2">
                        <label for="password" class="text-sm font-medium">Password</label>
                        <input type="password" id="password" name="password" required
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 w-full">
                        Login
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?> 