<?php
$current_url = $_GET['url'] ?? 'user/dashboard'; // Default to dashboard if no 'url' param

// Function to check if a link is active
function isActive($link_url, $current_url)
{
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
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
<div class="d-flex flex-column flex-shrink-0 p-3 text-dark bg-white" style="width: 280px;">
  <a href="<?= BASE_PATH ?>auth/home" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
    <strong class="fs-4">StudyAid</strong>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="sidebarlist <?php echo isActive('index.php?url=user/dashboard', $current_url); ?>">
      <a href="<?= BASE_PATH ?>user/dashboard" class="nav-link text-dark <?php echo isActive('index.php?url=user/dashboard', $current_url); ?>" aria-current="page">
        Dashboard
      </a>
    </li>
    <li class="sidebarlist <?php echo isActive('index.php?url=lm/newDocument', $current_url); ?>">
      <a href="<?= BASE_PATH ?>lm/newDocument" class="nav-link text-dark <?php echo isActive('index.php?url=lm/newDocument', $current_url); ?>">
        New Document
      </a>
    </li>
    <li class="sidebarlist <?php echo isActive('index.php?url=lm/newFolder', $current_url); ?>">
      <a href="<?= BASE_PATH ?>lm/newFolder" class="nav-link text-dark <?php echo isActive('index.php?url=lm/newFolder', $current_url); ?>">
        New Folder
      </a>
    </li>
    <li class="d-flex align-items-center <?php echo isActive('index.php?url=lm/displayLearningMaterials', $current_url); ?>">
      <a href="<?= BASE_PATH ?>lm/displayLearningMaterials" class="nav-link text-dark <?php echo isActive('index.php?url=lm/displayLearningMaterials', $current_url); ?>">
        All Documents
      </a>
      <button type="button" class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse" data-bs-target="#document-collapse" aria-expanded="false">
        <i class="bi bi-chevron-down"></i>
      </button>
    </li>
    <div class="collapse" id="document-collapse">
      <?php foreach ($allUserFolders as $folder): ?>
        <li class="sidebarlist">
          <a href="<?= BASE_PATH ?>lm/displayLearningMaterials?folder_id=<?= $folder['folderID'] ?>" class="nav-link text-dark fst-italic fw-medium ms-4">
            <?= $folder['name'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </div>
    <li class="sidebarlist <?php echo isActive('index.php?url=lm/createSummary', $current_url); ?>">
      <a href="<?= BASE_PATH ?>lm/createSummary" class="nav-link text-dark <?php echo isActive('index.php?url=lm/createSummary', $current_url); ?>">
        Create Summary
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
  .sidebarlist.active {
    background-color: #A855F7 !important;
  }

  .d-flex.align-items-center.active {
    background-color: #A855F7 !important;
  }

  .nav-link.active {
    background-color: #A855F7 !important;
    color: white !important;
  }

  .d-flex.align-items-center.active .bi-caret-down-square {
    color: white !important;
  }
</style>