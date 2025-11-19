<nav class="navbar navbar-expand-sm">
    <ul class="navbar-nav ps-0 mb-2">
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_DISPLAY_DOCUMENT, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= DISPLAY_DOCUMENT ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_DISPLAY_DOCUMENT, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Source</strong></button>
            </form>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_SUMMARY, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= SUMMARY ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_SUMMARY, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Summary</strong></button>
            </form>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_NOTE, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= NOTE ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_NOTE, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Note</strong></button>
            </form>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_MINDMAP, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= MINDMAP ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_MINDMAP, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Mindmap</strong></button>
            </form>
        </li>

        <li class="nav-item badge rounded-pill <?php echo isActive(URL_CHATBOT, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= CHATBOT ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_CHATBOT, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Chatbot</strong></button>
            </form>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_FLASHCARD, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= FLASHCARD ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_FLASHCARD, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Flashcard</strong></button>
            </form>
        </li>
        <li class="nav-item badge rounded-pill <?php echo isActive(URL_QUIZ, $current_url); ?>" style="background-color:rgb(217, 213, 221); margin-right: 10px; padding: 10px 20px; font-size: 1rem;">
            <form method="POST" action="<?= QUIZ ?>" style="display: inline;">
                <input type="hidden" name="file_id" value="<?php echo $fileId ?>">
                <button type="submit" class="nav-link <?php echo isActive(URL_QUIZ, $current_url); ?>" style="color: black; border: none; background: none; padding: 0;"><strong>Quiz</strong></button>
            </form>
        </li>
    </ul>
</nav>

<style>
    .nav-item.active {
        background-color: #e7d5ff !important;
    }

    .nav-item.active .nav-link {
        background-color: #e7d5ff !important;
        color: #6f42c1 !important;
    }

    .nav-item.active {
        background-color: #A855F7 !important;
    }

    .nav-item.active .nav-link {
        background-color: #A855F7 !important;
        color: white !important;
    }
</style>