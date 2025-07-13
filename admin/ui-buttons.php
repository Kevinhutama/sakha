<?php
$page_title = "Buttons - MaterialM Admin Template";

ob_start();
?>

<div class="card">
  <div class="card-body">
    <h5 class="card-title fw-semibold mb-4">Buttons</h5>
    <div class="card">
      <div class="card-body p-4">
        <button type="button" class="btn btn-primary m-1">Primary</button>
        <button type="button" class="btn btn-secondary m-1">Secondary</button>
        <button type="button" class="btn btn-success m-1">Success</button>
        <button type="button" class="btn btn-danger m-1">Danger</button>
        <button type="button" class="btn btn-warning m-1">Warning</button>
        <button type="button" class="btn btn-info m-1">Info</button>
        <button type="button" class="btn btn-light m-1">Light</button>
        <button type="button" class="btn btn-dark m-1">Dark</button>
        <button type="button" class="btn btn-link m-1">Link</button>
      </div>
    </div>
    
    <h5 class="card-title fw-semibold mb-4 mt-4">Outline buttons</h5>
    <div class="card">
      <div class="card-body p-4">
        <button type="button" class="btn btn-outline-primary m-1">Primary</button>
        <button type="button" class="btn btn-outline-secondary m-1">Secondary</button>
        <button type="button" class="btn btn-outline-success m-1">Success</button>
        <button type="button" class="btn btn-outline-danger m-1">Danger</button>
        <button type="button" class="btn btn-outline-warning m-1">Warning</button>
        <button type="button" class="btn btn-outline-info m-1">Info</button>
        <button type="button" class="btn btn-outline-light m-1">Light</button>
        <button type="button" class="btn btn-outline-dark m-1">Dark</button>
        <button type="button" class="btn btn-outline-link m-1">Link</button>
      </div>
    </div>
    
    <h5 class="card-title fw-semibold mb-4 mt-4">Button Sizes</h5>
    <div class="card">
      <div class="card-body p-4">
        <button type="button" class="btn btn-primary btn-lg m-1">Large button</button>
        <button type="button" class="btn btn-secondary btn-lg m-1">Large button</button>
        <button type="button" class="btn btn-primary m-1">Default button</button>
        <button type="button" class="btn btn-secondary m-1">Default button</button>
        <button type="button" class="btn btn-primary btn-sm m-1">Small button</button>
        <button type="button" class="btn btn-secondary btn-sm m-1">Small button</button>
      </div>
    </div>
    
    <h5 class="card-title fw-semibold mb-4 mt-4">Button States</h5>
    <div class="card">
      <div class="card-body p-4">
        <button type="button" class="btn btn-primary m-1">Normal</button>
        <button type="button" class="btn btn-primary m-1" disabled>Disabled</button>
        <button type="button" class="btn btn-outline-primary m-1">Normal Outline</button>
        <button type="button" class="btn btn-outline-primary m-1" disabled>Disabled Outline</button>
      </div>
    </div>
    
    <h5 class="card-title fw-semibold mb-4 mt-4">Button Groups</h5>
    <div class="card">
      <div class="card-body p-4">
        <div class="btn-group" role="group" aria-label="Basic example">
          <button type="button" class="btn btn-primary">Left</button>
          <button type="button" class="btn btn-primary">Middle</button>
          <button type="button" class="btn btn-primary">Right</button>
        </div>
        
        <div class="btn-group mt-3" role="group" aria-label="Basic outlined example">
          <button type="button" class="btn btn-outline-primary">Left</button>
          <button type="button" class="btn btn-outline-primary">Middle</button>
          <button type="button" class="btn btn-outline-primary">Right</button>
        </div>
      </div>
    </div>
    
    <h5 class="card-title fw-semibold mb-4 mt-4">Icon Buttons</h5>
    <div class="card mb-0">
      <div class="card-body p-4">
        <button type="button" class="btn btn-primary m-1">
          <iconify-icon icon="solar:user-bold-duotone" class="me-2"></iconify-icon>
          User
        </button>
        <button type="button" class="btn btn-success m-1">
          <iconify-icon icon="solar:check-circle-bold-duotone" class="me-2"></iconify-icon>
          Success
        </button>
        <button type="button" class="btn btn-danger m-1">
          <iconify-icon icon="solar:trash-bin-minimalistic-bold-duotone" class="me-2"></iconify-icon>
          Delete
        </button>
        <button type="button" class="btn btn-warning m-1">
          <iconify-icon icon="solar:settings-bold-duotone" class="me-2"></iconify-icon>
          Settings
        </button>
        
        <div class="mt-3">
          <button type="button" class="btn btn-outline-primary m-1">
            <iconify-icon icon="solar:download-bold-duotone"></iconify-icon>
          </button>
          <button type="button" class="btn btn-outline-success m-1">
            <iconify-icon icon="solar:heart-bold-duotone"></iconify-icon>
          </button>
          <button type="button" class="btn btn-outline-danger m-1">
            <iconify-icon icon="solar:bookmark-bold-duotone"></iconify-icon>
          </button>
          <button type="button" class="btn btn-outline-warning m-1">
            <iconify-icon icon="solar:star-bold-duotone"></iconify-icon>
          </button>
        </div>
      </div>
    </div>
  </div>
</div>

<?php
$content = ob_get_clean();
include 'includes/layout.php';
?> 