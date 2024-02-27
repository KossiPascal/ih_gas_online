
document.addEventListener('DOMContentLoaded', function() {
    // Get the language select dropdown element
    var languageSelect = document.getElementById('languageSelect');

    // Add event listener for change event
    languageSelect.addEventListener('change', function() {
        // Get the selected language option value
        var selectedLanguage = languageSelect.value;

        // Perform an AJAX request to update the user's locale on the server-side
        fetch('/ihgas/set-locale', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest' // Optional header to indicate AJAX request
            },
            credentials: 'same-origin', 
            // Include cookies (including CSRF token)
            body: JSON.stringify({ 
                locale: selectedLanguage,
                //_token: document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            })
        })
        .then(response => {
            if (response.ok) {
                // Locale updated successfully
                location.reload();
            } else {
                console.error('Failed to update locale');
            }
            console.log(response);
        })
        .catch(error => {
            console.error('Error:', error);
        });
    });
});
