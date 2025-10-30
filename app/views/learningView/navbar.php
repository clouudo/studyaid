<nav class="navbar navbar-expand-sm">
    <ul class="navbar-nav ps-0">
        <li class="nav-item badge rounded-pill" style="background-color: #A855F7; margin-right: 10px;">
            <a class="nav-link" href="<?= BASE_PATH ?>lm/summary?fileID=<?php echo $_GET['fileID']; ?>" style="color: white;">Summarize</a>
        </li>
        <li class="nav-item badge rounded-pill" style="background-color: #A855F7; margin-right: 10px;">
            <a class="nav-link" href="<?= BASE_PATH ?>lm/note?fileID=<?php echo $_GET['fileID'] ?>" style="color: white;">Note</a>
        </li>
        <li class="nav-item badge rounded-pill" style="background-color: #A855F7; margin-right: 10px;">
            <a class="nav-link" href="<?= BASE_PATH ?>lm/mindmap?fileID=<?php echo $_GET['fileID'] ?>" style="color: white;">Mindmap</a>
        </li>
    </ul>
</nav>