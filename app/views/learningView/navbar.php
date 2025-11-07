<nav class="navbar navbar-expand-sm">
    <ul class="navbar-nav ps-0 mb-2">
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/summary', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/summary', $current_url); ?>" href="<?= BASE_PATH ?>lm/summary?fileID=<?php echo $_GET['fileID']; ?>" style="color: black;">Summary</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/note', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/note', $current_url); ?>" href="<?= BASE_PATH ?>lm/note?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Note</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/mindmap', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/mindmap', $current_url); ?>" href="<?= BASE_PATH ?>lm/mindmap?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Mindmap</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/chatbot', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/chatbot', $current_url); ?>" href="<?= BASE_PATH ?>lm/chatbot?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Chatbot</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/flashcard', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/flashcard', $current_url); ?>" href="<?= BASE_PATH ?>lm/flashcard?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Flashcard</a>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive('index.php?url=lm/quiz', $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px;">
            <a class="nav-link <?php echo isActive('index.php?url=lm/quiz', $current_url); ?>" href="<?= BASE_PATH ?>lm/quiz?fileID=<?php echo $_GET['fileID'] ?>" style="color: black;">Quiz</a>
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

