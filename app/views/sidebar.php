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
  <a href="<?= DASHBOARD ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
    <div class="logo-box"><img src="<?= IMG_LOGO ?>" alt="StudyAids Logo"></div>
    <strong class="fs-4">StudyAid</strong>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto">
    <li class="sidebarlist <?php echo isActive(URL_DASHBOARD, $current_url); ?>">
      <a href="<?= DASHBOARD ?>" class="nav-link text-dark <?php echo isActive(URL_DASHBOARD, $current_url); ?>" aria-current="page">
        Dashboard
      </a>
    </li>
    <li class="sidebarlist <?php echo isActive(URL_NEW_DOCUMENT, $current_url); ?>">
      <a href="<?= NEW_DOCUMENT ?>" class="nav-link text-dark <?php echo isActive(URL_NEW_DOCUMENT, $current_url); ?>">
        New Document
      </a>
    </li>
    <li class="sidebarlist <?php echo isActive(URL_NEW_FOLDER, $current_url); ?>">
      <a href="<?= NEW_FOLDER ?>" class="nav-link text-dark <?php echo isActive(URL_NEW_FOLDER, $current_url); ?>">
        New Folder
      </a>
    </li>
    <li class="d-flex align-items-center <?php echo isActive(URL_DISPLAY_LEARNING_MATERIALS, $current_url); ?>">
      <a href="<?= DISPLAY_LEARNING_MATERIALS ?>" class="nav-link text-dark <?php echo isActive(URL_DISPLAY_LEARNING_MATERIALS, $current_url); ?>">
        All Documents
      </a>
      <button type="button" class="btn btn-toggle align-items-center rounded" data-bs-toggle="collapse" data-bs-target="#document-collapse" aria-expanded="false">
        <i class="bi bi-chevron-down"></i>
      </button>
    </li>
    <div class="collapse" id="document-collapse">
      <?php foreach ($allUserFolders as $folder): ?>
        <li class="sidebarlist">
          <a href="<?= DISPLAY_LEARNING_MATERIALS ?>?folder_id=<?= $folder['folderID'] ?>" class="nav-link text-dark fst-italic fw-medium ms-4">
            <?= $folder['name'] ?>
          </a>
        </li>
      <?php endforeach; ?>
    </div>
  </ul>
  <hr>
  <div class="container position-relative">
    <div class="d-flex align-items-center">
      <strong><?= $user['username']?></strong>
      <div class="dropdown ms-auto">
        <button class="btn btn-toggle dropdown-toggle" type="button" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: none; padding: 0;">
          <img src="<?= IMG_SETTING ?>" alt="settings" width="28" height="28">
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown" style="min-width: 150px;">
          <li><a class="dropdown-item" href="<?= LOGOUT ?>">Logout</a></li>
          <li><a class="dropdown-item" href="<?= PROFILE ?>">Manage Profile</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<style>
  .logo-box {
            background-color: #00000000;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-right: 5px;
            font-size: 14px;
        }
        .logo-box img {
            width: 100px;
            height: 100px;
            object-fit: contain;
        }
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