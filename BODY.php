<?php
ob_start();
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once 'db.php';
$perPage = 12; 
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
    <h1>Part-Time Job</h1>
    <div class="nav-container">
        <nav>
            <a href="#">Home</a>
            <a href="#">Contact</a>
            <a href="#">Login</a>
            <a href="#">Register</a>
        </nav>
        <!-- <form action="#" method="GET" class="search-form">
            <input type="text" name="keyword" placeholder="Search jobs...">
            <button type="submit">Search</button>
        </form> -->
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
    <!-- <nav id="butoon1">

    </nav > -->
</div>
<!-- <div class="job">
    <div>
        <details>
            <summary>Quick Filter</summary>
            <div class="subitem1">
                <lebel for="Salary">Salary Range:৳<span>1000000</span></lebel>  
                <input 
                    type="range" 
                    id="salary" 
                    min="30000" 
                    max="200000" 
                    step="5000" 
                    value="100000"
                >          

                </div>
            
                <div class="subitem2">
                <lebel for="Age">Age Range:<span>64</span></lebel>  
                <input 
                    type="range" 
                    id="age" 
                    min="18" 
                    max="64" 
                    step="1" 
                    value="64"
                >          

                </div>  
                 <div class="subitem3">
                <lebel for="experience">Experience:<span>46</span></lebel>  
                <input 
                    type="range" 
                    id="years" 
                    min="0" 
                    max="46" 
                    step="1" 
                    value="46"
                >          

                </div>                 
                
                


            </div>
        </details>
        <details>
            <summary>Category</summary>


        </details>
        <details>
            <summary>Location</summary>
        </details>
        <details>
            <summary>Deadline</summary>
            
        </details>
            
        <details>
            <summary>Deadline</summary>
        </details>              
    </div>
     -->






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
                    <img src="#" alt="abcd">
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
        <?php if($currentPage > 1): ?>
            <a href="index2.php?page=1">First</a>
            <a href="index2.php?page=<?php echo $currentPage - 1; ?>">&laquo; Prev</a>
        <?php endif; ?>

        <?php 
        $stage = 2; 
        $start = (($currentPage - $stage) > 0) ? ($currentPage - $stage) : 1;
        $end = (($currentPage + $stage) < $totalPages) ? ($currentPage + $stage) : $totalPages;

        for ($i = $start; $i <= $end; $i++): ?>
            <a href="index2.php?page=<?php echo $i; ?>" class="<?php echo ($i === $currentPage) ? 'active' : ''; ?>">
                <?php echo $i; ?>
            </a>
        <?php endfor; ?>

        <?php if($currentPage < $totalPages): ?>
            <a href="index2.php?page=<?php echo $currentPage + 1; ?>">Next &raquo;</a>
            <a href="index2.php?page=<?php echo $totalPages; ?>">Last</a>
        <?php endif; ?>
    </div>

</div>
        </div>

</body>
</html>