<?php
session_start();
$page_title = "Health Statistics";
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

try {
    $db = Database::getInstance()->getConnection();
    
    // 기간 필터 처리
    $startDate = isset($_GET['start_date']) ? $_GET['start_date'] : '';
    $endDate = isset($_GET['end_date']) ? $_GET['end_date'] : '';
    
    // date 파싱
function parseDate(
    $dateStr
) {
    // 예: 2024-06-13
    $dt = DateTime::createFromFormat('Y-m-d', $dateStr);
    if ($dt === false) return null;
    return [
        'year' => (int)$dt->format('Y'),
        'month' => (int)$dt->format('m'),
        'day' => (int)$dt->format('d'),
        'date' => $dt
    ];
}

    $whereClause = "";
    $params = [];
    
    if ($startDate && $endDate) {
    $start = parseDate($startDate);
    $end = parseDate($endDate);
    if ($start && $end) {
        // 날짜 비교 (year, month, day)
        $whereClause = "WHERE (year > ? OR (year = ? AND month > ?) OR (year = ? AND month = ? AND day >= ?))
        AND (year < ? OR (year = ? AND month < ?) OR (year = ? AND month = ? AND day <= ?))";
        $params = [
            $start['year'], $start['year'], $start['month'], $start['year'], $start['month'], $start['day'],
            $end['year'], $end['year'], $end['month'], $end['year'], $end['month'], $end['day']
        ];
    }
}
    
    // 기본 통계 데이터 가져오기
    $statsQuery = "SELECT 
        COUNT(*) as total_records,
        SUM(running_time) as total_time,
        AVG(running_time) as avg_time,
        AVG(running_speed_start) as avg_speed,
        MAX(running_time) as max_time,
        MAX(running_speed_start) as max_speed,
        MIN(running_time) as min_time,
        MIN(running_speed_start) as min_speed,
        SUM(running_time * running_speed_start / 60) as total_distance
        FROM myhealth " . $whereClause;
    
    $stmt = $db->prepare($statsQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $stats = $stmt->fetch();
    
    // 월별 통계 (차트용) - 거리 계산 포함
    $monthlyQuery = "SELECT 
        year, month, 
        COUNT(*) as count,
        AVG(running_time) as avg_time,
        AVG(running_speed_start) as avg_speed,
        SUM(running_time) as total_time,
        SUM(running_time * running_speed_start / 60) as total_distance
        FROM myhealth 
        " . $whereClause . "
        GROUP BY year, month 
        ORDER BY year ASC, month ASC";
    
    $stmt = $db->prepare($monthlyQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $monthlyData = $stmt->fetchAll();
    
    // 요일별 통계 (차트용)
    $weeklyQuery = "SELECT 
        dayofweek,
        COUNT(*) as count,
        AVG(running_time) as avg_time,
        AVG(running_speed_start) as avg_speed
        FROM myhealth 
        " . $whereClause . "
        GROUP BY dayofweek 
        ORDER BY 
        CASE dayofweek 
            WHEN 'Monday' THEN 1
            WHEN 'Tuesday' THEN 2
            WHEN 'Wednesday' THEN 3
            WHEN 'Thursday' THEN 4
            WHEN 'Friday' THEN 5
            WHEN 'Saturday' THEN 6
            WHEN 'Sunday' THEN 7
        END";
    
    $stmt = $db->prepare($weeklyQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $weeklyData = $stmt->fetchAll();
    
    // 속도 분포 (히스토그램용)
    $speedQuery = "SELECT 
        CASE 
            WHEN running_speed_start < 8 THEN '7-8 km/h'
            WHEN running_speed_start < 9 THEN '8-9 km/h'
            WHEN running_speed_start < 10 THEN '9-10 km/h'
            WHEN running_speed_start < 11 THEN '10-11 km/h'
            WHEN running_speed_start < 12 THEN '11-12 km/h'
            ELSE '12+ km/h'
        END as speed_range,
        COUNT(*) as count
        FROM myhealth 
        " . $whereClause . "
        GROUP BY speed_range
        ORDER BY 
        CASE speed_range
            WHEN '7-8 km/h' THEN 1
            WHEN '8-9 km/h' THEN 2
            WHEN '9-10 km/h' THEN 3
            WHEN '10-11 km/h' THEN 4
            WHEN '11-12 km/h' THEN 5
            ELSE 6
        END";
    
    $stmt = $db->prepare($speedQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $speedData = $stmt->fetchAll();
    
    // 운동 시간 분포 (파이 차트용)
    $timeQuery = "SELECT 
        CASE 
            WHEN running_time < 30 THEN 'Under 30 min'
            WHEN running_time < 45 THEN '30-45 min'
            WHEN running_time < 60 THEN '45-60 min'
            WHEN running_time < 90 THEN '60-90 min'
            ELSE 'Over 90 min'
        END as time_range,
        COUNT(*) as count
        FROM myhealth 
        " . $whereClause . "
        GROUP BY time_range
        ORDER BY 
        CASE time_range
            WHEN 'Under 30 min' THEN 1
            WHEN '30-45 min' THEN 2
            WHEN '45-60 min' THEN 3
            WHEN '60-90 min' THEN 4
            ELSE 5
        END";
    
    $stmt = $db->prepare($timeQuery);
    if (!empty($params)) {
        $stmt->execute($params);
    } else {
        $stmt->execute();
    }
    $timeData = $stmt->fetchAll();
    
    // 최근 30일 연속 운동 확인 (SQLite 호환)
    $streakQuery = "SELECT 
        year, month, day
        FROM myhealth 
        ORDER BY year DESC, month DESC, day DESC";
    $streakResult = $db->query($streakQuery);
    $streakData = $streakResult->fetchAll();
    
    // 연속 운동 일수 계산
    $currentStreak = 0;
    $maxStreak = 0;
    $tempStreak = 0;
    
    // 오늘 날짜
    $today = new DateTime();
    $todayStr = $today->format('Y-m-d');
    
    // 연속 운동 계산
    $expectedDate = clone $today;
    $consecutiveDays = 0;
    
    foreach ($streakData as $record) {
        $recordDate = new DateTime($record['year'] . '-' . $record['month'] . '-' . $record['day']);
        $recordDateStr = $recordDate->format('Y-m-d');
        
        if ($recordDateStr == $expectedDate->format('Y-m-d')) {
            $consecutiveDays++;
            $expectedDate->modify('-1 day');
        } else {
            break;
        }
    }
    
    $currentStreak = $consecutiveDays;
    
    // 최대 연속 운동 계산
    $tempStreak = 0;
    for ($i = 0; $i < count($streakData) - 1; $i++) {
        $current = $streakData[$i];
        $next = $streakData[$i + 1];
        
        $currentDate = new DateTime($current['year'] . '-' . $current['month'] . '-' . $current['day']);
        $nextDate = new DateTime($next['year'] . '-' . $next['month'] . '-' . $next['day']);
        $diff = $currentDate->diff($nextDate)->days;
        
        if ($diff == 1) {
            $tempStreak++;
        } else {
            if ($tempStreak > $maxStreak) $maxStreak = $tempStreak;
            $tempStreak = 0;
        }
    }
    if ($tempStreak > $maxStreak) $maxStreak = $tempStreak;
    
} catch (Exception $e) {
    echo '<div class="container mx-auto px-4 py-8">';
    echo '<div class="bg-destructive/15 text-destructive rounded-lg p-4">';
    echo "Error: " . $e->getMessage();
    echo '</div></div>';
    exit;
}
?>

<div class="container mx-auto px-4 py-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <!-- 헤더 -->
        <div class="flex items-center justify-between">
            <div class="space-y-1">
                <h1 class="text-3xl font-bold">Health Statistics</h1>
                <p class="text-sm text-muted-foreground">Analyze your health activity patterns</p>
            </div>
            <a href="health_list.php" 
               class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                ← Back to List
            </a>
        </div>

        <!-- 기간 검색 폼 -->
        <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
            <h3 class="text-base font-semibold mb-3">Filter by Date Range</h3>
            <form method="GET" class="flex items-center gap-4">
                <div class="flex items-center gap-2">
                    <label for="start_date" class="text-sm font-medium">From:</label>
                    <input type="date" id="start_date" name="start_date" 
                           value="<?php echo htmlspecialchars($startDate); ?>"
                           style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; pointer-events: auto;">
                </div>
                <div class="flex items-center gap-2">
                    <label for="end_date" class="text-sm font-medium">To:</label>
                    <input type="date" id="end_date" name="end_date" 
                           value="<?php echo htmlspecialchars($endDate); ?>"
                           style="padding: 5px; border: 1px solid #ccc; border-radius: 4px; cursor: pointer; pointer-events: auto;">
                </div>
                <button type="submit" 
                        class="inline-flex items-center justify-center rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground ring-offset-background transition-colors hover:bg-primary/90 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Filter
                </button>
                <?php if ($startDate || $endDate): ?>
                <a href="health_stats.php" 
                   class="inline-flex items-center justify-center rounded-md border border-input bg-background px-4 py-2 text-sm font-medium ring-offset-background transition-colors hover:bg-accent hover:text-accent-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2">
                    Clear Filter
                </a>
                <?php endif; ?>
            </form>
            <?php if ($startDate && $endDate): ?>
            <p class="text-sm text-muted-foreground mt-2">
                Showing data from <?php echo htmlspecialchars($startDate); ?> to <?php echo htmlspecialchars($endDate); ?>
            </p>
            <?php endif; ?>
        </div>

        <!-- 통합 통계 카드 -->
        <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-6">
            <h3 class="text-lg font-semibold mb-4 text-center">Health Summary</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600"><?php echo number_format($stats['total_records']); ?></p>
                    <p class="text-xs text-muted-foreground">Records</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600"><?php echo number_format($stats['total_time']); ?></p>
                    <p class="text-xs text-muted-foreground">Total Min</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['avg_speed'], 1); ?></p>
                    <p class="text-xs text-muted-foreground">Avg km/h</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-orange-600"><?php echo number_format($stats['max_time']); ?></p>
                    <p class="text-xs text-muted-foreground">Max Min</p>
                </div>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-4">
                <div class="text-center">
                    <p class="text-2xl font-bold text-red-600"><?php echo number_format($stats['total_distance'], 1); ?></p>
                    <p class="text-xs text-muted-foreground">Total km</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-green-600"><?php echo $currentStreak; ?></p>
                    <p class="text-xs text-muted-foreground">Current Streak</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-blue-600"><?php echo $maxStreak; ?></p>
                    <p class="text-xs text-muted-foreground">Best Streak</p>
                </div>
                <div class="text-center">
                    <p class="text-2xl font-bold text-purple-600"><?php echo number_format($stats['avg_time'], 1); ?></p>
                    <p class="text-xs text-muted-foreground">Avg Min</p>
                </div>
            </div>
        </div>

        <!-- 차트 섹션 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 월별 운동 시간 트렌드 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Monthly Activity Trend</h3>
                <div style="height: 250px;">
                    <canvas id="monthlyChart"></canvas>
                </div>
            </div>

            <!-- 요일별 패턴 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Weekly Pattern</h3>
                <div style="height: 250px;">
                    <canvas id="weeklyChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 추가 차트 섹션 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 월별 총 거리 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Monthly Distance</h3>
                <div style="height: 250px;">
                    <canvas id="distanceChart"></canvas>
                </div>
            </div>

            <!-- 속도 분포 히스토그램 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Speed Distribution</h3>
                <div style="height: 250px;">
                    <canvas id="speedChart"></canvas>
                </div>
            </div>
        </div>

        <!-- 세 번째 차트 섹션 -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 운동 시간 분포 파이 차트 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Exercise Duration Distribution</h3>
                <div style="height: 250px;">
                    <canvas id="timeChart"></canvas>
                </div>
            </div>

            <!-- 빈 공간 (향후 추가 차트용) -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Future Chart</h3>
                <div style="height: 250px;" class="flex items-center justify-center text-muted-foreground">
                    <p>Additional chart coming soon...</p>
                </div>
            </div>
        </div>

        <!-- 기존 차트 섹션 (간단한 바 차트) -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <!-- 월별 운동 시간 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Monthly Activity (Simple)</h3>
                <div class="space-y-2">
                    <?php foreach (array_slice($monthlyData, -6) as $month): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm"><?php echo $month['year'] . '-' . str_pad($month['month'], 2, '0', STR_PAD_LEFT); ?></span>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-blue-600 h-2 rounded-full" style="width: <?php echo min(100, ($month['total_time'] / max(array_column($monthlyData, 'total_time'))) * 100); ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $month['count']; ?> times</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 요일별 패턴 -->
            <div class="bg-card text-card-foreground rounded-lg border shadow-sm p-4">
                <h3 class="text-base font-semibold mb-3">Weekly Pattern (Simple)</h3>
                <div class="space-y-2">
                    <?php foreach ($weeklyData as $day): ?>
                    <div class="flex items-center justify-between">
                        <span class="text-sm w-20"><?php echo $day['dayofweek']; ?></span>
                        <div class="flex items-center space-x-2">
                            <div class="w-32 bg-gray-200 dark:bg-gray-700 rounded-full h-2">
                                <div class="bg-green-600 h-2 rounded-full" style="width: <?php echo min(100, ($day['count'] / max(array_column($weeklyData, 'count'))) * 100); ?>%"></div>
                            </div>
                            <span class="text-sm font-medium"><?php echo $day['count']; ?> times</span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- 상세 통계 테이블 -->
        <div class="bg-card text-card-foreground rounded-lg border shadow-sm">
            <div class="p-6">
                <h3 class="text-lg font-semibold mb-4">Detailed Statistics</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b">
                                <th class="text-left p-2">Metric</th>
                                <th class="text-left p-2">Value</th>
                                <th class="text-left p-2">Description</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Total Records</td>
                                <td class="p-2"><?php echo number_format($stats['total_records']); ?></td>
                                <td class="p-2 text-muted-foreground">Total number of health records</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Total Exercise Time</td>
                                <td class="p-2"><?php echo number_format($stats['total_time']); ?> minutes</td>
                                <td class="p-2 text-muted-foreground">Cumulative exercise time</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Total Distance</td>
                                <td class="p-2"><?php echo number_format($stats['total_distance'], 1); ?> km</td>
                                <td class="p-2 text-muted-foreground">Total distance covered</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Average Exercise Time</td>
                                <td class="p-2"><?php echo number_format($stats['avg_time'], 1); ?> minutes</td>
                                <td class="p-2 text-muted-foreground">Average time per session</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Average Speed</td>
                                <td class="p-2"><?php echo number_format($stats['avg_speed'], 1); ?> km/h</td>
                                <td class="p-2 text-muted-foreground">Average running speed</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Longest Session</td>
                                <td class="p-2"><?php echo number_format($stats['max_time']); ?> minutes</td>
                                <td class="p-2 text-muted-foreground">Longest single exercise session</td>
                            </tr>
                            <tr class="border-b">
                                <td class="p-2 font-medium">Fastest Speed</td>
                                <td class="p-2"><?php echo number_format($stats['max_speed'], 1); ?> km/h</td>
                                <td class="p-2 text-muted-foreground">Highest recorded speed</td>
                            </tr>
                            <tr>
                                <td class="p-2 font-medium">Current Streak</td>
                                <td class="p-2"><?php echo $currentStreak; ?> days</td>
                                <td class="p-2 text-muted-foreground">Consecutive days of exercise</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
// 다크모드 감지
function isDarkMode() {
    return document.documentElement.classList.contains('dark');
}

// 차트 색상 설정
const chartColors = {
    light: {
        primary: '#2563eb',
        secondary: '#059669',
        accent: '#7c3aed',
        background: '#ffffff',
        text: '#374151'
    },
    dark: {
        primary: '#3b82f6',
        secondary: '#10b981',
        accent: '#8b5cf6',
        background: '#1f2937',
        text: '#f9fafb'
    }
};

function getChartColors() {
    return isDarkMode() ? chartColors.dark : chartColors.light;
}

// 월별 차트 데이터
const monthlyData = <?php echo json_encode($monthlyData); ?>;
const monthlyLabels = monthlyData.map(item => `${item.year}-${String(item.month).padStart(2, '0')}`);
const monthlyCounts = monthlyData.map(item => item.count);
const monthlyTimes = monthlyData.map(item => item.total_time);
const monthlyDistances = monthlyData.map(item => parseFloat(item.total_distance || 0));

// 요일별 차트 데이터
const weeklyData = <?php echo json_encode($weeklyData); ?>;
const weeklyLabels = weeklyData.map(item => item.dayofweek);
const weeklyCounts = weeklyData.map(item => item.count);

// 속도 분포 차트 데이터
const speedData = <?php echo json_encode($speedData); ?>;
const speedLabels = speedData.map(item => item.speed_range);
const speedCounts = speedData.map(item => item.count);

// 시간 분포 차트 데이터
const timeData = <?php echo json_encode($timeData); ?>;
const timeLabels = timeData.map(item => item.time_range);
const timeCounts = timeData.map(item => item.count);

// 차트 생성
document.addEventListener('DOMContentLoaded', function() {
    const colors = getChartColors();
    
    // 월별 활동 트렌드 (선 그래프)
    new Chart(document.getElementById('monthlyChart'), {
        type: 'line',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Exercise Count',
                data: monthlyCounts,
                borderColor: colors.primary,
                backgroundColor: colors.primary + '20',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: colors.text
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                },
                x: {
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                }
            }
        }
    });

    // 요일별 패턴 (막대 그래프)
    new Chart(document.getElementById('weeklyChart'), {
        type: 'bar',
        data: {
            labels: weeklyLabels,
            datasets: [{
                label: 'Exercise Count',
                data: weeklyCounts,
                backgroundColor: colors.secondary,
                borderColor: colors.secondary,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: colors.text
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                },
                x: {
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                }
            }
        }
    });

    // 월별 총 거리 (막대 그래프)
    new Chart(document.getElementById('distanceChart'), {
        type: 'bar',
        data: {
            labels: monthlyLabels,
            datasets: [{
                label: 'Total Distance (km)',
                data: monthlyDistances,
                backgroundColor: '#ef4444',
                borderColor: '#ef4444',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: colors.text
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text,
                        callback: function(value) {
                            return value.toFixed(1) + ' km';
                        }
                    }
                },
                x: {
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                }
            }
        }
    });

    // 속도 분포 (막대 그래프)
    new Chart(document.getElementById('speedChart'), {
        type: 'bar',
        data: {
            labels: speedLabels,
            datasets: [{
                label: 'Number of Sessions',
                data: speedCounts,
                backgroundColor: colors.accent,
                borderColor: colors.accent,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    labels: {
                        color: colors.text
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                },
                x: {
                    grid: {
                        color: isDarkMode() ? '#374151' : '#e5e7eb'
                    },
                    ticks: {
                        color: colors.text
                    }
                }
            }
        }
    });

    // 운동 시간 분포 (파이 차트)
    new Chart(document.getElementById('timeChart'), {
        type: 'doughnut',
        data: {
            labels: timeLabels,
            datasets: [{
                data: timeCounts,
                backgroundColor: [
                    colors.primary,
                    colors.secondary,
                    colors.accent,
                    '#f59e0b',
                    '#ef4444'
                ],
                borderWidth: 2,
                borderColor: isDarkMode() ? '#1f2937' : '#ffffff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        color: colors.text,
                        padding: 20
                    }
                }
            }
        }
    });
});

// 다크모드 변경 시 차트 업데이트
const observer = new MutationObserver(function(mutations) {
    mutations.forEach(function(mutation) {
        if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
            // 페이지 새로고침으로 차트 다시 그리기
            setTimeout(() => {
                location.reload();
            }, 100);
        }
    });
});

observer.observe(document.documentElement, {
    attributes: true,
    attributeFilter: ['class']
});
</script>

<?php include "../../../system/includes/footer.php"; ?> 