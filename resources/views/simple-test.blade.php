<!DOCTYPE html>
<html>
<head>
    <title>Simple Test</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <style>
        .modal-bg { background: rgba(0,0,0,0.5); }
        .modal-content { background: white; padding: 20px; margin: 50px auto; width: 400px; border-radius: 8px; }
        .btn { padding: 10px 20px; margin: 5px; border: 1px solid #ccc; cursor: pointer; }
        .btn-primary { background: #6366f1; color: white; }
    </style>
</head>
<body style="font-family: Arial, sans-serif; padding: 20px;">
    <h1>Super Simple Modal Test</h1>
    
    <div x-data="{ open: false }">
        <button @click="open = true" class="btn btn-primary">Open Modal</button>
        
        <div x-show="open" class="modal-bg" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%;">
            <div class="modal-content">
                <h2>Test Modal</h2>
                <p>This is some test text.</p>
                <p>Can you see this text?</p>
                
                <div style="margin: 20px 0;">
                    <label style="display: block; margin: 10px 0;">
                        <input type="checkbox"> Option 1
                    </label>
                    <label style="display: block; margin: 10px 0;">
                        <input type="checkbox"> Option 2
                    </label>
                </div>
                
                <button @click="open = false" class="btn">Close</button>
            </div>
        </div>
    </div>
    
    <p>If you can't see text in the modal, the issue is with Alpine.js or CSS.</p>
</body>
</html>