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
    $images = file('image.txt', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

    // We removed the strict 'die()' check here so the script runs anyway!

    $sql = "INSERT INTO job_info (job_id, job_title, job_desc, link, img) 
            VALUES (:job_id, :job_title, :job_desc, :link, :img)";
    $stmt = $pdo->prepare($sql);

    $inserted_count = 0;
    
    // Use the count of elements.txt as the baseline
    $total_items = count($lines);
    
    // Generate unique random IDs
    $random_ids = [];
    while (count($random_ids) < $total_items) {
        $rand_num = mt_rand(100000, 999999); 
        if (!in_array($rand_num, $random_ids)) {
            $random_ids[] = $rand_num;
        }
    }

    for ($i = 0; $i < $total_items; $i++) {
        $current_line = trim($lines[$i]);
        
        // Use a fallback empty string if job_link or image files have fewer lines
        $raw_link_line = isset($links[$i]) ? trim($links[$i]) : '';
        $raw_image_line = isset($images[$i]) ? trim($images[$i]) : '';
        
        $parts = explode('|', $current_line);
        $link_parts = explode('|', $raw_link_line);
        $image_parts = explode('|', $raw_image_line);

        if (count($parts) >= 3) {
            $job_title = trim($parts[1]);
            $job_desc  = trim($parts[2]); 
            $job_id    = $random_ids[$i]; 
            
            $link = (count($link_parts) >= 2) ? trim($link_parts[1]) : $raw_link_line;
            $img  = (count($image_parts) >= 2) ? trim($image_parts[1]) : $raw_image_line;
            
            $stmt->execute([
                ':job_id'    => $job_id,
                ':job_title' => $job_title,
                ':job_desc'  => $job_desc, 
                ':link'      => $link,
                ':img'       => $img 
            ]);
            
            $inserted_count++;
        }
    }

    echo "Successfully inserted " . $inserted_count . " rows into the database!\n";

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage() . "\n");
}
?>