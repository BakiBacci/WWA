// assets/js/search.js
document.querySelector('.search-form input').addEventListener('input', async (e) => {
    const query = e.target.value;
    const response = await fetch(e.target.dataset.autocompleteUrl + '?q=' + query);
    const results = await response.json();
    
    // Affichez les suggestions (ex: avec Alpine.js ou vanilla JS)
});