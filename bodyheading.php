<?php
ob_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'db.php';
$perPage = 8; 
$countSql = "SELECT COUNT(*) AS total FROM job_info";
$countResult = mysqli_query($conn, $countSql);
$countRow = mysqli_fetch_assoc($countResult);
$totalJobs = (int)$countRow['total'];
$currentPage = (isset($_GET['page']) && is_numeric($_GET['page'])) ? (int)$_GET['page'] : 1;
if ($currentPage < 1) {
    $currentPage = 1;
}
$totalPages = ceil($totalJobs / $perPage);
if ($currentPage > $totalPages && $totalPages > 0) {
    $currentPage = $totalPages;
}
$offset = ($currentPage - 1) * $perPage;
$sql = "SELECT * FROM job_info LIMIT " . $offset . ", " . $perPage;
$result = mysqli_query($conn, $sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part Time Job</title>
    <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>">
</head>
<body>
<header>
    <div class="head">
        <div class="text2">
        <img src="#" alt="">
        <h1>Part-Time Job</h1>
        </div>
        <div class="text3">
            <h1>Part-Time Job</h1>
        </div>
    </div>
    <div class="nav-container">
        <nav>
            <a href="#">Home</a>
            <a href="#">Contact</a>
            <a href="#">Login</a>
            <a href="#">Register</a>
        </nav>
    </div>
</header>
<div class="text">
    <nav>
        <p>Find The Right Job</p>
        <p>🟢<?= $totalJobs ?> Jobs</p>
    </nav>   
    <form action="#" method="GET" class="search-form">
        <input type="text" name="keyword" placeholder="Search jobs...">
        <button type="submit">Search</button>
    </form>
    </div>
<div class="job-container">
    <div class=text1>
        <p><b>Recent Jobs</b> </p>
    </div>
    <?php 
    if ($result && mysqli_num_rows($result) > 0) {
        while($row = mysqli_fetch_assoc($result)) { 
        ?>

            
            <div class="job-card">
                <div class="logo">
                    <?php if (!empty($row['img'])): ?>
                        <img src="<?php echo htmlspecialchars($row['img']); ?>" alt="">
                    <?php else: ?>
                        <span style="color: #94a3b8; font-size: 11px; font-weight: bold;">No Logo</span>
                    <?php endif; ?>
                </div>
                <a href="<?php echo htmlspecialchars($row['link']); ?>" target="_blank">
                    <?php echo htmlspecialchars($row['job_title']); ?>
                </a>
            </div>
        <?php 
        } 
    } else {
        echo "<p style='text-align:center; color:#64748b;'>No jobs found.</p>";
    }
    ?>
    
    <div class="pagination">
        <?php 
        $self = basename($_SERVER['PHP_SELF']); 
        ?>

        <?php if($currentPage > 1): ?>
            <a href="<?php echo $self; ?>?page=1">First</a>
            <a href="<?php echo $self; ?>?page=<?php echo $currentPage - 1; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php 
        $stage = 2; 
        $start = (($currentPage - $stage) > 0) ? ($currentPage - $stage) : 1;
        $end = (($currentPage + $stage) < $totalPages) ? ($currentPage + $stage) : $totalPages;

        for ($i = $start; $i <= $end; $i++): ?>
            <a href="<?php echo $self; ?>?page=<?php echo $i; ?>" class="<?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if($currentPage < $totalPages): ?>
            <a href="<?php echo $self; ?>?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
            <a href="<?php echo $self; ?>?page=<?php echo $totalPages; ?>">Last</a>
        <?php endif; ?>
    </div>

</div>
        </div>

</body>
</html>