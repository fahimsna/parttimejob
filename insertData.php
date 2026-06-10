<?php
$host = 'localhost';
$db   = 'parttimejob'; 
$user = 'root'; 
$password = ''; 
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully.\n";
    $pdo->exec("TRUNCATE TABLE job_info");
    echo "Database cleared for fresh data.\n";
    $lines = file('elements.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $links = file('job_link.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    if (count($lines) !== count($links)) {
        die("Error: Lines and links count mismatch.\n");
    }

    $sql = "INSERT INTO job_info (job_id, job_title, link) VALUES (:job_id, :job_title, :link)";
    $stmt = $pdo->prepare($sql);

    $inserted_count = 0;
    for ($i = 0; $i < count($lines); $i++) {
        $current_line = trim($lines[$i]);
        $raw_link_line = trim($links[$i]);
        $parts = explode('|', $current_line);
        $link_parts = explode('|', $raw_link_line);

        if (count($parts) >= 2) {
            $job_title = trim($parts[1]);
            $job_id  = $i + 1; 
            $link = (count($link_parts) >= 2) ? trim($link_parts[1]) : $raw_link_line;
            $stmt->execute([
                ':job_id'    => $job_id,
                ':job_title' => $job_title,
                ':link'      => $link
            ]);
            
            $inserted_count++;
        }
    }

    echo "Inserted " . $inserted_count . " clean rows.\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>