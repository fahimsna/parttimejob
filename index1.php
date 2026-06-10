<?php
require_once 'db.php';
$sql = "SELECT * FROM job_info LIMIT 20";
$result = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Part Time Job Board</title>
    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
</head>
<body class="bg-gray-50 text-gray-900 font-sans antialiased">

    <header class="bg-indigo-600 text-white shadow-md py-6 mb-10">
        <div class="max-w-4xl mx-auto px-4 flex justify-between items-center">
            <h1 class="text-2xl font-bold tracking-tight">💼 Part-Time Job Openings</h1>
            <span class="bg-indigo-500 text-xs font-semibold px-3 py-1 rounded-full border border-indigo-400">
                    Total Jobs: <?php echo mysqli_num_rows($result); ?>
            </span>
        </div>
    </header >

    <main class="max-w-4xl mx-auto px-4 pb-12">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <?php while($row = mysqli_fetch_assoc($result)): 
                $job_link = !empty($row['link']) ? htmlspecialchars($row['link']) : '#';
            ?>
                <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm hover:shadow-md hover:border-indigo-300 transition-all duration-200 flex flex-col justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-gray-900 leading-snug mb-3">
                            <?php echo htmlspecialchars($row['job_title']); ?>
                        </h3>
                    </div>

                    <div class="mt-4 pt-4 border-t border-gray-100 flex justify-end">
                        <a href="<?php echo $job_link; ?>" target="_blank" class="inline-flex items-center text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition-colors">
                            View Details 
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            <?php endwhile; ?>

        </div>
    </main>
    <footer class="bg-white border-t border-gray-200 mt-16 py-6 text-center text-xs text-gray-500 font-medium">
        <div class="max-w-4xl mx-auto px-4">
            <p>&copy; <?php echo date('Y'); ?> Part-Time Job Board. All rights reserved.</p>
            <p class="text-gray-400 mt-1">FSNA&bull; Powered by PHP</p>
        </div>
    </footer>            
</body>
</html>