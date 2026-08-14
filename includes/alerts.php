<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['flash_message'])) {
    $flash = $_SESSION['flash_message'];
    $type = htmlspecialchars($flash['type']);
    $message = $flash['message'];
    
    $icon = 'info-circle';
    if ($type === 'success') $icon = 'check-circle';
    if ($type === 'danger') $icon = 'exclamation-triangle';
    if ($type === 'warning') $icon = 'exclamation-circle';
    
    echo "
    <div class='alert alert-{$type} alert-dismissible fade show shadow-sm d-flex align-items-center' role='alert'>
        <i class='bi bi-{$icon} fs-4 me-2'></i>
        <div class='flex-grow-1'>
            {$message}
        </div>
        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
    </div>
    ";
    
    unset($_SESSION['flash_message']);
}
