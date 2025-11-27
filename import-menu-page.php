<!DOCTYPE html>
<html>
<head>
    <title>Import Menu Items</title>
    <style>
        body { font-family: Arial; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; }
        .error { color: red; }
        button { background: #C92A2A; color: white; border: none; padding: 15px 30px; font-size: 16px; cursor: pointer; border-radius: 5px; }
        button:hover { background: #A10E0E; }
        .result { margin-top: 20px; padding: 15px; background: #f5f5f5; border-radius: 5px; }
    </style>
</head>
<body>
    <h1>Import Landing Page Menu Items</h1>
    <p>Click the button below to import all menu items from the landing page into the database.</p>
    <button onclick="importMenuItems()">Import Menu Items Now</button>
    <div id="result" class="result" style="display:none;"></div>

    <script>
    async function importMenuItems() {
        const button = document.querySelector('button');
        button.disabled = true;
        button.textContent = 'Importing...';
        
        const result = document.getElementById('result');
        result.style.display = 'block';
        result.innerHTML = 'Starting import...';
        
        try {
            const response = await fetch('<?php echo admin_url('admin-ajax.php'); ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'action=ucfc_batch_import_menu_items'
            });
            
            const data = await response.json();
            
            if (data.success) {
                result.innerHTML = `
                    <h2 class="success">✅ Import Successful!</h2>
                    <p><strong>${data.data.imported}</strong> items imported</p>
                    <p><strong>${data.data.skipped}</strong> items skipped (already exist)</p>
                    <p><strong>${data.data.categories}</strong> categories created</p>
                    <p><a href="<?php echo admin_url('admin.php?page=ucfc-menu-dashboard'); ?>">View Menu Dashboard</a></p>
                `;
            } else {
                result.innerHTML = `<h2 class="error">❌ Import Failed</h2><p>${data.data}</p>`;
            }
        } catch (error) {
            result.innerHTML = `<h2 class="error">❌ Error</h2><p>${error.message}</p>`;
        }
        
        button.disabled = false;
        button.textContent = 'Import Menu Items Now';
    }
    </script>
</body>
</html>
