<?php
$current_url = $_GET['url'] ?? 'user/dashboard'; // Default to dashboard if no 'url' param

// Function to check if a link is active
function isActive($link_url, $current_url) {
    // Extract the 'url=' part from the link's href
    $link_param = null;
    $parts = parse_url($link_url);
    if (isset($parts['query'])) {
        parse_str($parts['query'], $query_params);
        if (isset($query_params['url'])) {
            $link_param = $query_params['url'];
        }
    }
    return ($link_param === $current_url) ? 'active' : '';
}
?>
<div class="d-flex flex-column flex-shrink-0 p-3 text-dark bg-white" style="width: 280px;">
  <a href="<?= BASE_PATH ?>auth/home" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
    <svg class="bi me-2" width="40" height="32">
      <use xlink:href="#bootstrap"></use>
    </svg>
    <strong class="fs-4">StudyAid</strong>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="nav-item">
      <a href="<?= BASE_PATH ?>user/dashboard" class="nav-link <?php echo isActive('index.php?url=user/dashboard', $current_url); ?> text-dark" aria-current="page">
        <svg class="bi me-2" width="16" height="16">
          <use xlink:href="#home"></use>
        </svg>
        Dashboard
      </a>
    </li>
    <li>
      <a href="<?= BASE_PATH ?>lm/newDocument" class="nav-link <?php echo isActive('index.php?url=lm/newDocument', $current_url); ?> text-dark">
        <svg class="bi me-2" width="16" height="16">
          <use xlink:href="#speedometer2"></use>
        </svg>
        New Document
      </a>
    </li>
    <li>
      <a href="<?= BASE_PATH ?>lm/newFolder" class="nav-link <?php echo isActive('index.php?url=lm/newFolder', $current_url); ?> text-dark">
        <svg class="bi me-2" width="16" height="16">
          <use xlink:href="#table"></use>
        </svg>
        New Folder
      </a>
    </li>
     <li>
      <a href="<?= BASE_PATH ?>lm/displayLearningMaterials" class="nav-link <?php echo isActive('index.php?url=lm/displayLearningMaterials', $current_url); ?> text-dark">
        <svg class="bi me-2" width="16" height="16">
          <use xlink:href="#speedometer2"></use>
        </svg>
        All Documents
      </a>
    </li>
  </ul>
  <hr>
  <div class="container">
    <img src="https://github.com/mdo.png" alt="" width="32" height="32" class="rounded-circle me-2">
    <strong>Username</strong>
    <img src="<?= IMG_SETTING ?>" alt="settings" width="28" height="28">
  </div>
</div>

<style>
  .nav-link.active {
    background-color: #A855F7 !important;
    color: white !important;
  }
</style>