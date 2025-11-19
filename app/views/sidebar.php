<?php
$current_url = $_GET['url'] ?? 'user/dashboard'; // Default to dashboard if no 'url' param

// Detect if the All Documents menu should be expanded
$isDocumentsActive = ($current_url === URL_DISPLAY_LEARNING_MATERIALS || isset($_GET['folder_id']));

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
<div class="sidebar-wrapper d-flex flex-column flex-shrink-0 p-3 text-dark bg-white">
  <a href="<?= DASHBOARD ?>" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-dark text-decoration-none">
    <div class="logo-box"><img src="<?= IMG_LOGO ?>" alt="StudyAids Logo"></div>
    <strong class="fs-4">StudyAid</strong>
  </a>
  <hr>
  <ul class="nav nav-pills flex-column mb-auto sidebar-nav">
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
    <li class="sidebarlist">
      <div class="d-flex justify-content-between align-items-center w-100">
        <a href="<?= DISPLAY_LEARNING_MATERIALS ?>" class="nav-link text-dark flex-grow-1 <?php echo $isDocumentsActive ? 'active' : ''; ?>" id="allDocumentsLink">
          All Documents
        </a>
        <button class="nav-link text-dark border-0 bg-transparent p-0 ms-2" 
                type="button"
                data-bs-toggle="collapse" 
                data-bs-target="#document-collapse" 
                aria-expanded="<?= $isDocumentsActive ? 'true' : 'false'; ?>"
                style="width: auto; min-width: 30px;">
          <i class="bi bi-chevron-down transition"></i>
        </button>
      </div>
      
      <div class="collapse <?= $isDocumentsActive ? 'show' : '' ?> document-collapse-scrollable" id="document-collapse">
        <?php foreach ($allUserFolders as $folder): ?>
          <a href="<?= DISPLAY_LEARNING_MATERIALS ?>?folder_id=<?= $folder['folderID'] ?>" 
             class="nav-link text-dark ms-4 small py-1 <?php echo (isset($_GET['folder_id']) && $_GET['folder_id'] == $folder['folderID']) ? 'active' : ''; ?>">
             <i class="bi bi-folder2-open"></i> <?= $folder['name'] ?>
          </a>
        <?php endforeach; ?>
      </div>
    </li>
    <li class="sidebarlist <?php echo isActive(URL_DOCUMENT_HUB, $current_url); ?>">
      <a href="<?= DOCUMENT_HUB ?>" class="nav-link text-dark <?php echo isActive(URL_DOCUMENT_HUB, $current_url); ?>">
        Document Hub
      </a>
    </li>
  </ul>
  <hr>
  <div class="sidebar-footer">
    <div class="d-flex align-items-center">
      <strong><?= $user['username']?></strong>
      <div class="dropup ms-auto">
        <button class="btn btn-toggle" type="button" id="settingsDropdown" data-bs-toggle="dropdown" aria-expanded="false" style="border: none; background: none; padding: 0;">
          <img src="<?= IMG_SETTING ?>" alt="settings" width="28" height="28">
        </button>
        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="settingsDropdown" style="min-width: 150px;">
          <li><a class="dropdown-item" href="<?= LOGOUT ?>">Logout</a></li>
          <li><a class="dropdown-item" href="<?= PROFILE ?>">Profile</a></li>
        </ul>
      </div>
    </div>
  </div>
</div>

<style>
  .sidebar-wrapper {
    width: 280px;
    height: 100vh;
    position: fixed;
    top: 0;
    left: 0;
    z-index: 1000;
    overflow: hidden;
    display: flex;
    flex-direction: column;
  }

  /* Add margin to main content when sidebar is present */
  body > .d-flex.flex-grow-1 > main,
  body > .d-flex > main {
    margin-left: 280px;
    width: calc(100% - 280px);
    min-width: 0;
  }
  
  /* Ensure body doesn't have horizontal scroll */
  body {
    overflow-x: hidden;
  }

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

  .sidebar-nav {
    flex: 1;
    overflow: visible;
  }

  .sidebarlist.active {
    background-color: #6f42c1 !important;
  }

  .nav-link.active {
    background-color: #6f42c1 !important;
    color: white !important;
  }

  .nav-link {
    cursor: pointer;
    border-radius: 6px;
    padding: 8px 12px;
  }

  .nav-link:hover {
    background-color: #e8dcff;
  }

  .nav-link:focus {
    outline: none;
    box-shadow: none;
  }

  .document-collapse-scrollable {
    max-height: 300px;
    overflow-y: auto;
    overflow-x: hidden;
  }

  .document-collapse-scrollable::-webkit-scrollbar {
    width: 6px;
  }

  .document-collapse-scrollable::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
  }

  .document-collapse-scrollable::-webkit-scrollbar-thumb {
    background: #d4b5ff;
    border-radius: 10px;
  }

  .document-collapse-scrollable::-webkit-scrollbar-thumb:hover {
    background: #6f42c1;
  }

  .collapse a {
    background-color: white !important;
  }

  .collapse a.active {
    background-color: #d4b5ff !important;
    color: #212529 !important;
    border-radius: 6px;
  }

  .collapse a:hover {
    background-color: #e7d5ff !important;
  }

  /* Dropdown menu styles */
  .dropdown-menu {
    background-color: white !important;
    border: 1px solid #e9ecef;
    border-radius: 8px;
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  .dropdown-item {
    background-color: white;
    color: #212529;
    padding: 10px 16px;
    border-radius: 6px;
  }

  .dropdown-item:hover {
    background-color: #e7d5ff !important;
    color: #212529;
  }

  .dropdown-item.active,
  .dropdown-item:active {
    background-color: #d4b5ff !important;
    color: #212529;
  }

  /* Remove dropdown toggle triangle */
  .dropdown-toggle::after {
    display: none !important;
  }

  .bi-chevron-down {
    transition: transform 0.3s ease;
  }

  button[aria-expanded="true"] .bi-chevron-down {
    transform: rotate(180deg);
  }

  .sidebar-footer {
    margin-top: auto;
    padding-top: 1rem;
  }

  .sidebar-footer .d-flex {
    padding: 0;
  }

  /* Settings button hover and active states */
  .btn-toggle {
    border-radius: 8px;
    transition: all 0.2s;
    padding: 4px !important;
  }

  .btn-toggle:hover {
    background-color: #e7d5ff !important;
  }

  .btn-toggle.show,
  .btn-toggle[aria-expanded="true"] {
    background-color: #e7d5ff !important;
  }

  .btn-toggle:focus {
    outline: none;
    box-shadow: none;
  }
</style>
<script>
  document.addEventListener('DOMContentLoaded', function() {
    // Auto-expand dropdown when on All Documents page
    const isDocumentsActive = <?php echo $isDocumentsActive ? 'true' : 'false'; ?>;
    if (isDocumentsActive) {
      const collapseElement = document.getElementById('document-collapse');
      if (collapseElement && !collapseElement.classList.contains('show')) {
        const bsCollapse = new bootstrap.Collapse(collapseElement, {
          show: true
        });
      }
    }

    // Handle click on "All Documents" link
    const allDocumentsLink = document.getElementById('allDocumentsLink');
    if (allDocumentsLink) {
      allDocumentsLink.addEventListener('click', function(e) {
        const currentUrl = window.location.href;
        const targetUrl = this.href;
        
        // Check if we're already on the All Documents page
        if (currentUrl.includes('displayLearningMaterials') && !currentUrl.includes('folder_id')) {
          // Already on the page, toggle dropdown and prevent navigation
          e.preventDefault();
          const collapseElement = document.getElementById('document-collapse');
          if (collapseElement) {
            const bsCollapse = bootstrap.Collapse.getInstance(collapseElement) || new bootstrap.Collapse(collapseElement);
            bsCollapse.toggle();
          }
        }
        // Otherwise, let the link navigate normally (dropdown will auto-expand on page load)
      });
    }
  });
</script>