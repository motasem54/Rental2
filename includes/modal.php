<?php
// includes/modal.php - Common Modals Used Across the System
?>

<!-- Confirm Delete Modal -->
<div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="confirmDeleteModalLabel">
                    <i class="fas fa-exclamation-triangle text-danger me-2"></i>
                    *#CJ/ 'D-0A
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p id="deleteModalMessage">GD #F* E*#C/ EF 1:(*C AJ -0A G0' 'D9F51 D' JECF 'D*1',9 9F G0' 'D%,1'!.</p>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>%D:'!
                </button>
                <button type="button" class="btn btn-danger" id="confirmDeleteBtn">
                    <i class="fas fa-trash me-2"></i>-0A
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Image Preview Modal -->
<div class="modal fade" id="imagePreviewModal" tabindex="-1" aria-labelledby="imagePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="imagePreviewModalLabel">
                    <i class="fas fa-image me-2"></i>E9'JF) 'D5H1)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <img src="" id="previewImage" class="img-fluid rounded" alt="Preview" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>

<!-- Quick View Modal (Generic) -->
<div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="quickViewModalLabel">
                    <i class="fas fa-eye me-2"></i>E9'JF) 31J9)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="quickViewContent">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">,'1J 'D*-EJD...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>%:D'B
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Print Preview Modal -->
<div class="modal fade" id="printPreviewModal" tabindex="-1" aria-labelledby="printPreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="printPreviewModalLabel">
                    <i class="fas fa-print me-2"></i>E9'JF) B(D 'D7('9)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="printPreviewContent" style="background: white; color: black; padding: 20px;">
                <!-- Print content will be loaded here -->
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>%:D'B
                </button>
                <button type="button" class="btn btn-primary" onclick="printContent()">
                    <i class="fas fa-print me-2"></i>7('9)
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Status Change Modal -->
<div class="modal fade" id="statusChangeModal" tabindex="-1" aria-labelledby="statusChangeModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="statusChangeModalLabel">
                    <i class="fas fa-exchange-alt me-2"></i>*:JJ1 'D-'D)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="statusChangeForm">
                    <input type="hidden" id="statusChangeId" name="id">
                    <div class="mb-3">
                        <label for="newStatus" class="form-label">'D-'D) 'D,/J/)</label>
                        <select class="form-select glass-input" id="newStatus" name="status" required>
                            <!-- Options will be populated dynamically -->
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="statusChangeNotes" class="form-label">ED'-8'* ('.*J'1J)</label>
                        <textarea class="form-control glass-input" id="statusChangeNotes" name="notes" rows="3"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>%D:'!
                </button>
                <button type="button" class="btn btn-primary" onclick="submitStatusChange()">
                    <i class="fas fa-check me-2"></i>-A8
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Notes Modal -->
<div class="modal fade" id="notesModal" tabindex="-1" aria-labelledby="notesModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content glass-card" style="background: rgba(18, 18, 18, 0.95);">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title" id="notesModalLabel">
                    <i class="fas fa-sticky-note me-2"></i>'DED'-8'*
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="notesForm">
                    <input type="hidden" id="notesItemId" name="item_id">
                    <div class="mb-3">
                        <label for="notesText" class="form-label">'DED'-8)</label>
                        <textarea class="form-control glass-input" id="notesText" name="notes" rows="5" required></textarea>
                    </div>
                </form>
                <div id="previousNotes" class="mt-3">
                    <!-- Previous notes will be loaded here -->
                </div>
            </div>
            <div class="modal-footer border-top border-secondary">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-2"></i>%:D'B
                </button>
                <button type="button" class="btn btn-primary" onclick="saveNotes()">
                    <i class="fas fa-save me-2"></i>-A8 'DED'-8)
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Modal Helper Functions

// Confirm Delete
function confirmDelete(itemId, itemName, callback) {
    const modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
    document.getElementById('deleteModalMessage').textContent =
        `GD #F* E*#C/ EF 1:(*C AJ -0A "${itemName}"? D' JECF 'D*1',9 9F G0' 'D%,1'!.`;

    document.getElementById('confirmDeleteBtn').onclick = function() {
        if (callback) callback(itemId);
        modal.hide();
    };

    modal.show();
}

// Image Preview
function previewImage(imageUrl, title = 'E9'JF) 'D5H1)') {
    const modal = new bootstrap.Modal(document.getElementById('imagePreviewModal'));
    document.getElementById('imagePreviewModalLabel').textContent = title;
    document.getElementById('previewImage').src = imageUrl;
    modal.show();
}

// Quick View
function quickView(url, title = 'E9'JF) 31J9)') {
    const modal = new bootstrap.Modal(document.getElementById('quickViewModal'));
    document.getElementById('quickViewModalLabel').innerHTML = '<i class="fas fa-eye me-2"></i>' + title;

    fetch(url)
        .then(response => response.text())
        .then(html => {
            document.getElementById('quickViewContent').innerHTML = html;
        })
        .catch(error => {
            document.getElementById('quickViewContent').innerHTML =
                '<div class="alert alert-danger">-/+ .7# AJ *-EJD 'DE-*HI</div>';
        });

    modal.show();
}

// Print Preview
function showPrintPreview(content, title = 'E9'JF) B(D 'D7('9)') {
    const modal = new bootstrap.Modal(document.getElementById('printPreviewModal'));
    document.getElementById('printPreviewModalLabel').innerHTML = '<i class="fas fa-print me-2"></i>' + title;
    document.getElementById('printPreviewContent').innerHTML = content;
    modal.show();
}

function printContent() {
    const content = document.getElementById('printPreviewContent').innerHTML;
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html lang="ar" dir="rtl">
        <head>
            <meta charset="UTF-8">
            <title>7('9)</title>
            <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
            <style>
                body { padding: 20px; }
                @media print {
                    body { -webkit-print-color-adjust: exact; }
                }
            </style>
        </head>
        <body onload="window.print(); window.close();">
            ${content}
        </body>
        </html>
    `);
    printWindow.document.close();
}

// Status Change
function changeStatus(itemId, currentStatus, availableStatuses) {
    const modal = new bootstrap.Modal(document.getElementById('statusChangeModal'));
    document.getElementById('statusChangeId').value = itemId;

    const statusSelect = document.getElementById('newStatus');
    statusSelect.innerHTML = '';

    availableStatuses.forEach(status => {
        const option = document.createElement('option');
        option.value = status.value;
        option.textContent = status.label;
        if (status.value === currentStatus) option.selected = true;
        statusSelect.appendChild(option);
    });

    modal.show();
}

function submitStatusChange() {
    const form = document.getElementById('statusChangeForm');
    const formData = new FormData(form);

    // This should be implemented based on your API
    console.log('Status change submitted:', Object.fromEntries(formData));

    bootstrap.Modal.getInstance(document.getElementById('statusChangeModal')).hide();
}

// Notes
function showNotes(itemId, previousNotes = []) {
    const modal = new bootstrap.Modal(document.getElementById('notesModal'));
    document.getElementById('notesItemId').value = itemId;
    document.getElementById('notesText').value = '';

    const previousNotesDiv = document.getElementById('previousNotes');
    if (previousNotes.length > 0) {
        let html = '<h6 class="mb-2">'DED'-8'* 'D3'(B):</h6>';
        previousNotes.forEach(note => {
            html += `
                <div class="glass-card p-2 mb-2">
                    <small class="text-muted">${note.date} - ${note.user}</small>
                    <p class="mb-0">${note.text}</p>
                </div>
            `;
        });
        previousNotesDiv.innerHTML = html;
    } else {
        previousNotesDiv.innerHTML = '<p class="text-muted">D' *H,/ ED'-8'* 3'(B)</p>';
    }

    modal.show();
}

function saveNotes() {
    const form = document.getElementById('notesForm');
    const formData = new FormData(form);

    // This should be implemented based on your API
    console.log('Notes saved:', Object.fromEntries(formData));

    bootstrap.Modal.getInstance(document.getElementById('notesModal')).hide();
}
</script>
