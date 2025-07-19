<?php
session_start();
$page_title = "Edit Health Record";

// POST 처리를 먼저 수행
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
    
    try {
        include "../../../system/includes/config.php";
        $db = Database::getInstance()->getConnection();
        
        $year = $_POST['year'];
        $month = $_POST['month'];
        $day = $_POST['day'];
        $dayofweek = $_POST['dayofweek'];
        $running_time = $_POST['running_time'];
        $running_speed_start = $_POST['running_speed_start'];

        $stmt = $db->prepare("UPDATE myhealth SET year = ?, month = ?, day = ?, dayofweek = ?, running_time = ?, running_speed_start = ? WHERE no = ?");
        
        if ($stmt->execute([$year, $month, $day, $dayofweek, $running_time, $running_speed_start, $id])) {
            header("Location: health_list.php");
            exit;
        } else {
            throw new Exception("Failed to update record");
        }
    } catch (Exception $e) {
        $error_message = $e->getMessage();
    } finally {
        if (isset($stmt)) {
            $stmt->closeCursor();
        }
    }
}

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

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// GET 요청일 때만 데이터 조회
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    try {
        $db = Database::getInstance()->getConnection();
        
        $stmt = $db->prepare("SELECT * FROM myhealth WHERE no = ?");
        $stmt->execute([$id]);
        $record = $stmt->fetch();

        if (!$record) {
            throw new Exception("Record not found");
        }
    } catch (Exception $e) {
        echo '<div class="container mx-auto px-4 py-8">';
        echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4">';
        echo "Error: " . $e->getMessage();
        echo '</div></div>';
        exit;
    } finally {
        if (isset($stmt)) {
            $stmt->closeCursor();
        }
    }
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <?php if (isset($error_message)): ?>
            <div class="bg-destructive/15 text-destructive rounded-lg p-4 mb-6">
                Error: <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        
        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="p-6 space-y-6">
                <div class="space-y-2">
                    <h1 class="text-2xl font-bold">Edit Health Record</h1>
                    <p class="text-sm text-muted-foreground">Update your health activity details</p>
                </div>

                <form action="edit_health.php?id=<?php echo $id; ?>" method="post" class="space-y-4">
                    <div class="grid grid-cols-3 gap-4">
                        <div class="space-y-2">
                            <label for="year" class="text-sm font-medium">Year</label>
                            <input type="number" id="year" name="year" required min="2000" max="2100"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                   value="<?php echo htmlspecialchars($record['year']); ?>">
                        </div>

                        <div class="space-y-2">
                            <label for="month" class="text-sm font-medium">Month</label>
                            <input type="number" id="month" name="month" required min="1" max="12"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                   value="<?php echo htmlspecialchars($record['month']); ?>">
                        </div>

                        <div class="space-y-2">
                            <label for="day" class="text-sm font-medium">Day</label>
                            <input type="number" id="day" name="day" required min="1" max="31"
                                   class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                                   value="<?php echo htmlspecialchars($record['day']); ?>">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label for="dayofweek" class="text-sm font-medium">Day of Week</label>
                        <select id="dayofweek" name="dayofweek" required
                                class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            foreach ($days as $day) {
                                $selected = ($day === $record['dayofweek']) ? 'selected' : '';
                                echo "<option value=\"$day\" $selected>$day</option>";
                            }
                            ?>
                        </select>
                    </div>

                    <div class="space-y-2">
                        <label for="running_time" class="text-sm font-medium">Running Time (minutes)</label>
                        <input type="number" id="running_time" name="running_time" required min="1" step="0.1"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                               value="<?php echo htmlspecialchars($record['running_time']); ?>">
                    </div>

                    <div class="space-y-2">
                        <label for="running_speed_start" class="text-sm font-medium">Starting Speed (km/h)</label>
                        <input type="number" id="running_speed_start" name="running_speed_start" required min="0" step="0.1"
                               class="flex h-10 w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background file:border-0 file:bg-transparent file:text-sm file:font-medium placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2"
                               value="<?php echo htmlspecialchars($record['running_speed_start']); ?>">
                    </div>

                    <div class="flex items-center gap-4">
                        <button type="submit"
                                class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Update Record
                        </button>
                        <a href="health_list.php"
                           class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include "../../../system/includes/footer.php"; ?> 