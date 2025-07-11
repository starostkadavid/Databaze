document.addEventListener('DOMContentLoaded', function() {

    document.querySelectorAll('table tr').forEach(function(row, idx) {
        if (idx === 0) return;
        row.addEventListener('click', function() {
            
            document.querySelectorAll('table tr').forEach(function(r) {
                r.classList.remove('selected-row');
            });
            
            row.classList.add('selected-row');
        });
    });
});