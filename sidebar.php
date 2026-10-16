<?php $menuConfig = loadMenuConfig(); ?>
<aside class="sidebar" id="sidebar">
<div class="sidebar-header">
<a href="#" class="sidebar-logo">
<i class="bi bi-grid-3x3-gap-fill"></i>
<span>AdminPanel</span>
</a>
<button class="sidebar-toggle" id="sidebarToggle">
<i class="bi bi-chevron-left"></i>
</button>
</div>
<nav class="sidebar-menu">
<div class="sidebar-item">
<a href="#" class="sidebar-link active">
<i class="bi bi-speedometer2"></i>
<span>Dashboard</span>
</a>
</div>
<div class="sidebar-item">
<a href="#" class="sidebar-link">
<i class="bi bi-people"></i>
<span>Users</span>
</a>
</div>
<div class="sidebar-item">
<a href="#" class="sidebar-link">
<i class="bi bi-file-earmark-bar-graph"></i>
<span>Reports</span>
</a>
</div>
<div class="sidebar-item">
<a href="#" class="sidebar-link">
<i class="bi bi-gear"></i>
<span>Settings</span>
</a>
</div>
<div class="sidebar-item">
<a href="<?= base_url('logout.php') ?>" class="sidebar-link">
<i class="bi bi-box-arrow-right"></i>
<span>Logout</span>
</a>
</div>
</nav>
</aside>
