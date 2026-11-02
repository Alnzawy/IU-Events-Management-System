<?php
// Common Header for Student Portal
$pageTitle = $pageTitle ?? 'Student Portal - IU Events';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?></title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.min.css">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&family=Outfit:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/common.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/sidebar.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../assets/css/student.css?v=<?php echo time(); ?>">
    <?php if (!empty($pageCss)): ?>
        <?php foreach ((array)$pageCss as $css): ?>
            <link rel="stylesheet" href="../assets/css/<?php echo htmlspecialchars($css); ?>?v=<?php echo time(); ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>
<body class="portal-body">
    <?php include __DIR__ . '/../sidebar.php'; ?>

    <div class="main-content">
        <div class="container-fluid">
