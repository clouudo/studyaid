<?php
/**
 * Confirmation Modal Component
 * 
 * Behavior: Provides a reusable confirmation modal dialog for user confirmations.
 * Can be triggered via JavaScript with custom messages, titles, and callback functions.
 * Supports both confirm and cancel actions with customizable button text.
 */
?>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content confirm-modal-content">
            <div class="modal-header confirm-modal-header">
                <h5 class="modal-title confirm-modal-title" id="confirmModalLabel">Confirm Action</h5>
                <button type="button" class="modal-close-btn" data-bs-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x"></i>
                </button>
            </div>
            <div class="modal-body confirm-modal-body">
                <div class="confirm-icon-wrapper">
                    <i class="bi bi-question-circle-fill confirm-icon"></i>
                </div>
                <p class="confirm-message" id="confirmMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="modal-footer confirm-modal-footer">
                <button type="button" class="btn btn-cancel-confirm" data-bs-dismiss="modal" id="confirmCancelBtn">
                    Cancel
                </button>
                <button type="button" class="btn btn-confirm-action" id="confirmActionBtn">
                    Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<style>
    /* Confirmation Modal Styles */
    .confirm-modal-content {
        border-radius: 16px;
        border: none;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
    }

    .confirm-modal-header {
        border-bottom: 1px solid #e9ecef;
        padding: 20px 24px;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .confirm-modal-title {
        font-weight: 600;
        color: #212529;
        font-size: 1.25rem;
        margin: 0;
    }

    .confirm-modal-body {
        padding: 30px 24px;
        text-align: center;
    }

    .confirm-icon-wrapper {
        margin-bottom: 20px;
    }

    .confirm-icon {
        font-size: 4rem;
        color: #6f42c1;
    }

    .confirm-message {
        color: #212529;
        font-size: 1.1rem;
        margin: 0;
        line-height: 1.6;
    }

    .confirm-modal-footer {
        border-top: 1px solid #e9ecef;
        padding: 16px 24px;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
    }

    .btn-cancel-confirm {
        background-color: #e7d5ff;
        border: none;
        color: #6f42c1;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-cancel-confirm:hover {
        background-color: #d4b5ff;
        color: #5a32a3;
    }

    .btn-confirm-action {
        background-color: #6f42c1;
        border: none;
        color: white;
        padding: 10px 24px;
        border-radius: 8px;
        font-weight: 600;
        transition: all 0.3s;
    }

    .btn-confirm-action:hover {
        background-color: #5a32a3;
        color: white;
    }

    .btn-confirm-action.danger {
        background-color: #dc3545;
    }

    .btn-confirm-action.danger:hover {
        background-color: #c82333;
    }

    .modal-close-btn {
        background-color: transparent;
        border: none;
        color: #6f42c1;
        padding: 8px 12px;
        border-radius: 8px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: all 0.3s;
        font-size: 1.5rem;
        cursor: pointer;
    }

    .modal-close-btn:hover {
        background-color: #6f42c1;
        color: white;
    }
</style>

<script>
    /**
     * Confirmation Modal Manager
     * 
     * Behavior: Provides functions to show confirmation modals with custom messages,
     * titles, and callback functions. Supports different modal types (default, danger).
     */

    let confirmCallback = null;
    let cancelCallback = null;

    /**
     * Shows confirmation modal with custom options
     * 
     * Behavior: Displays modal with specified message, title, and button labels.
     * Executes callback function when user confirms. Supports danger variant for
     * destructive actions.
     * 
     * @param {Object} options Configuration object
     * @param {string} options.message Confirmation message to display
     * @param {string} [options.title='Confirm Action'] Modal title
     * @param {string} [options.confirmText='Confirm'] Confirm button text
     * @param {string} [options.cancelText='Cancel'] Cancel button text
     * @param {Function} [options.onConfirm] Callback function when confirmed
     * @param {Function} [options.onCancel] Callback function when cancelled
     * @param {boolean} [options.danger=false] Use danger styling for confirm button
     */
    function showConfirmModal(options) {
        const modal = document.getElementById('confirmModal');
        const modalTitle = document.getElementById('confirmModalLabel');
        const modalMessage = document.getElementById('confirmMessage');
        const confirmBtn = document.getElementById('confirmActionBtn');
        const cancelBtn = document.getElementById('confirmCancelBtn');

        // Set modal content
        modalTitle.textContent = options.title || 'Confirm Action';
        modalMessage.textContent = options.message || 'Are you sure you want to proceed?';
        confirmBtn.textContent = options.confirmText || 'Confirm';
        cancelBtn.textContent = options.cancelText || 'Cancel';

        // Set danger styling if specified
        if (options.danger) {
            confirmBtn.classList.add('danger');
        } else {
            confirmBtn.classList.remove('danger');
        }

        // Store callbacks
        confirmCallback = options.onConfirm || null;
        cancelCallback = options.onCancel || null;

        // Show modal
        const bsModal = new bootstrap.Modal(modal);
        bsModal.show();
    }

    /**
     * Handles confirm button click
     * 
     * Behavior: Executes stored confirm callback if exists, then closes modal.
     */
    document.getElementById('confirmActionBtn').addEventListener('click', function() {
        if (confirmCallback && typeof confirmCallback === 'function') {
            confirmCallback();
        }
        const modal = bootstrap.Modal.getInstance(document.getElementById('confirmModal'));
        if (modal) {
            modal.hide();
        }
        // Clear callbacks
        confirmCallback = null;
        cancelCallback = null;
    });

    /**
     * Handles cancel button click
     * 
     * Behavior: Executes stored cancel callback if exists, then closes modal.
     */
    document.getElementById('confirmCancelBtn').addEventListener('click', function() {
        if (cancelCallback && typeof cancelCallback === 'function') {
            cancelCallback();
        }
        // Clear callbacks
        confirmCallback = null;
        cancelCallback = null;
    });

    /**
     * Clears callbacks when modal is closed via backdrop or ESC key
     * 
     * Behavior: Resets callbacks to prevent memory leaks when modal is dismissed
     * without clicking action buttons.
     */
    document.getElementById('confirmModal').addEventListener('hidden.bs.modal', function() {
        confirmCallback = null;
        cancelCallback = null;
    });

    /**
     * Quick confirmation function for simple use cases
     * 
     * Behavior: Simplified function for basic confirmations with just message and callback.
     * 
     * @param {string} message Confirmation message
     * @param {Function} onConfirm Callback function when confirmed
     * @param {boolean} [danger=false] Use danger styling
     */
    function confirm(message, onConfirm, danger = false) {
        showConfirmModal({
            message: message,
            onConfirm: onConfirm,
            danger: danger
        });
    }
</script>

