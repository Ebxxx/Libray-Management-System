  // Function to automatically close alerts after 7 seconds
  function setupAlertTimeout() {
    const alerts = document.querySelectorAll('.alert-dismissible');
    
    alerts.forEach(alert => {
        // Create a new bootstrap alert instance
        const bsAlert = new bootstrap.Alert(alert);
        
        // Set timeout to close the alert after 7 seconds
        setTimeout(() => {
            bsAlert.close();
        }, 5000);
    });
}

// Run the alert timeout setup when the document is fully loaded
document.addEventListener('DOMContentLoaded', setupAlertTimeout);

// Edit book event listener
document.querySelectorAll('.edit-book').forEach(button => {
    button.addEventListener('click', function() {
        // Set form to edit mode
        document.getElementById('form-action').value = 'edit';
        document.querySelector('.modal-title').textContent = 'Edit Book';
        document.getElementById('modal-submit-btn').textContent = 'Update Book';

        // Populate form with book data
        document.getElementById('resource-id').value = this.dataset.id;
        document.getElementById('title').value = this.dataset.title;
        document.getElementById('author').value = this.dataset.author;
        document.getElementById('isbn').value = this.dataset.isbn;
        document.getElementById('category').value = this.dataset.category;
        document.getElementById('publisher').value = this.dataset.publisher;
        document.getElementById('edition').value = this.dataset.edition;
        document.getElementById('publication_date').value = this.dataset.publicationDate;
    });
});

// Delete book event listener
document.querySelectorAll('.delete-book').forEach(button => {
    button.addEventListener('click', function() {
        document.getElementById('delete-resource-id').value = this.dataset.id;
        document.getElementById('delete-book-title').textContent = this.dataset.title;
    });
});

// Reset modal when closed
document.getElementById('bookModal').addEventListener('hidden.bs.modal', function () {
    // Reset form to add mode
    document.getElementById('form-action').value = 'add';
    document.querySelector('.modal-title').textContent = 'Add New Book';
    document.getElementById('modal-submit-btn').textContent = 'Add Book';
    this.querySelector('form').reset();
});

