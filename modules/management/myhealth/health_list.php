<?php

session_start(); // 세션 시작
$page_title = "My Health";
include "../../../system/includes/header.php";
include "../../../system/includes/config.php";

if(!isset($_SESSION['id'])){
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4 text-center">';
    echo "Please log in to access this page.";
    echo '</div></div>';
    echo '<script>setTimeout(function(){ window.location.href = "../../../system/auth/login.php"; }, 2000);</script>';
    echo '</body></html>';
    exit;
}

// 데이터베이스 연결
// include "../../../system/includes/Database.php"; // 중복 선언 방지 위해 제거

try {
    $db = Database::getInstance()->getConnection();
    
    // 행 개수를 가져오는 쿼리
    $totalPostsQuery = "SELECT COUNT(*) as total FROM myhealth";
    $totalPostsResult = $db->query($totalPostsQuery);
    $totalPostsRow = $totalPostsResult->fetch();
    $totalPosts = $totalPostsRow['total'];

    $postsPerPage = 10;
    $totalPages = ceil($totalPosts / $postsPerPage);

    $currentPage = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    $currentPage = max(1, min($currentPage, $totalPages)); // 페이지 범위 제한
    $startPost = ($currentPage - 1) * $postsPerPage;

    // 게시물 데이터를 가져오는 쿼리
    $boardDataQuery = "SELECT * FROM myhealth ORDER BY year DESC, month DESC, day DESC LIMIT ?, ?";
    $stmt = $db->prepare($boardDataQuery);
    $stmt->execute([$startPost, $postsPerPage]);
    $boardDataResult = $stmt->fetchAll();
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto space-y-8">
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-2xl font-bold">My Health Records</h1>
                <p class="text-sm text-muted-foreground">Track your daily health activities</p>
                <p class="text-xs text-muted-foreground">
                    Showing <?php echo count($boardDataResult); ?> of <?php echo $totalPosts; ?> records 
                    (Page <?php echo $currentPage; ?> of <?php echo $totalPages; ?>)
                </p>
            </div>
            <div class="flex items-center gap-2">
                <a href="health_stats.php" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    📊 Statistics
                </a>
                <a href="add_health.php" 
                   class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Add New Record
                </a>
            </div>
        </div>

        <div class="rounded-md border">
            <div class="relative w-full overflow-auto">
                <table class="w-full caption-bottom text-sm">
                    <thead class="[&_tr]:border-b">
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">No</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Date</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Day</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Running Time</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Speed</th>
                            <th class="h-12 px-4 text-left align-middle font-medium text-muted-foreground">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="[&_tr:last-child]:border-0">
                        <?php foreach($boardDataResult as $row): ?>
                        <tr class="border-b transition-colors hover:bg-muted/50 data-[state=selected]:bg-muted">
                            <td class="p-4 align-middle"><?php echo htmlspecialchars($row["no"]); ?></td>
                            <td class="p-4 align-middle">
                                <?php echo htmlspecialchars($row["year"] . "-" . $row["month"] . "-" . $row["day"]); ?>
                            </td>
                            <td class="p-4 align-middle"><?php echo htmlspecialchars($row["dayofweek"]); ?></td>
                            <td class="p-4 align-middle"><?php echo htmlspecialchars($row["running_time"]); ?> min</td>
                            <td class="p-4 align-middle"><?php echo htmlspecialchars($row["running_speed_start"]); ?> km/h</td>
                            <td class="p-4 align-middle">
                                <div class="flex items-center gap-2">
                                    <a href="edit_health.php?id=<?php echo $row['no']; ?>" 
                                       class="text-primary hover:text-primary/90 transition-colors">
                                        Edit
                                    </a>
                                    <a href="delete_health.php?id=<?php echo $row['no']; ?>" 
                                       class="text-destructive hover:text-destructive/90 transition-colors"
                                       onclick="return confirm('Are you sure you want to delete this record?')">
                                        Delete
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <?php if ($totalPages > 1): ?>
        <div class="flex items-center justify-center space-x-2">
            <?php if ($currentPage > 1): ?>
                <a href="?page=<?php echo $currentPage - 1; ?>" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    ← Previous
                </a>
            <?php endif; ?>

            <?php 
            // 페이지 번호 표시 로직 개선 (너무 많으면 일부만 표시)
            $startPage = max(1, $currentPage - 2);
            $endPage = min($totalPages, $currentPage + 2);
            
            if ($startPage > 1): ?>
                <a href="?page=1" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    1
                </a>
                <?php if ($startPage > 2): ?>
                    <span class="px-2 text-muted-foreground">...</span>
                <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                <a href="?page=<?php echo $i; ?>" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 <?php echo $i === $currentPage ? 'bg-accent text-accent-foreground' : ''; ?>">
                    <?php echo $i; ?>
                </a>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
                <?php if ($endPage < $totalPages - 1): ?>
                    <span class="px-2 text-muted-foreground">...</span>
                <?php endif; ?>
                <a href="?page=<?php echo $totalPages; ?>" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    <?php echo $totalPages; ?>
                </a>
            <?php endif; ?>

            <?php if ($currentPage < $totalPages): ?>
                <a href="?page=<?php echo $currentPage + 1; ?>" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Next →
                </a>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php
} catch (Exception $e) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4">';
    echo "Error: " . $e->getMessage();
    echo '</div></div>';
} finally {
    if (isset($stmt)) {
        $stmt->closeCursor();
    }
    // Database 클래스는 singleton으로 자동으로 연결을 관리합니다
}

include "../../../system/includes/footer.php";
?>
