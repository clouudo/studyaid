<nav class="navbar navbar-expand-sm">
    <ul class="navbar-nav ps-0">
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/summary', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/summary', $current_url); ?>" href="<?= BASE_PATH ?>lm/summary?fileID=<?php echo $_GET['fileID']; ?>" style="color: black;">Summary</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/note', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/note', $current_url); ?>" href="<?= BASE_PATH ?>lm/note?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Note</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/mindmap', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/mindmap', $current_url); ?>" href="<?= BASE_PATH ?>lm/mindmap?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Mindmap</a>
        </li>
    </ul>
</nav>

<style>
    .nav-item.active{
        background-color: #A855F7 !important;
    }
    
    .nav-item.active .nav-link{
        background-color: #A855F7 !important;
        color: white !important;
    }
</style>

