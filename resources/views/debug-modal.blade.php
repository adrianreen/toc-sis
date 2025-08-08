<!DOCTYPE html>
<html>
<head>
    <title>Debug Modal</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div x-data="{ 
        showModal: false,
        visibleColumns: ['status', 'email'],
        
        toggleColumn(col) {
            console.log('Toggling column:', col);
            const index = this.visibleColumns.indexOf(col);
            if (index > -1) {
                this.visibleColumns.splice(index, 1);
            } else {
                this.visibleColumns.push(col);
            }
            console.log('Visible columns now:', this.visibleColumns);
        }
    }">
        <h1 class="text-2xl font-bold mb-4">Debug Modal Test</h1>
        
        <button @click="showModal = true" 
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
            Open Debug Modal
        </button>
        
        <p class="mt-4">
            Current visible columns: <span x-text="visibleColumns.join(', ')"></span>
        </p>

        <!-- Modal -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.self="showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                
                <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full relative">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Debug Modal</h3>
                    
                    <div class="space-y-3 border border-gray-200 p-4">
                        <p class="text-sm text-gray-600">This should show visible text:</p>
                        
                        <!-- Status Column -->
                        <div class="flex items-center justify-between p-2 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       id="debug-status"
                                       :checked="visibleColumns.includes('status')"
                                       @change="toggleColumn('status')"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="debug-status" class="text-sm text-gray-900 cursor-pointer">
                                    Status Column
                                </label>
                            </div>
                            <span class="text-xs text-gray-500">status_badge</span>
                        </div>
                        
                        <!-- Email Column -->
                        <div class="flex items-center justify-between p-2 border-b border-gray-100">
                            <div class="flex items-center space-x-3">
                                <input type="checkbox" 
                                       id="debug-email"
                                       :checked="visibleColumns.includes('email')"
                                       @change="toggleColumn('email')"
                                       class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                                <label for="debug-email" class="text-sm text-gray-900 cursor-pointer">
                                    Email Column
                                </label>
                            </div>
                            <span class="text-xs text-gray-500">text</span>
                        </div>
                        
                        <p class="text-xs text-green-600">If you can see this text, the modal structure works</p>
                    </div>
                    
                    <div class="mt-6 flex justify-end space-x-3">
                        <button @click="showModal = false" 
                                class="px-4 py-2 text-gray-600 border border-gray-300 rounded hover:bg-gray-50">
                            Cancel
                        </button>
                        <button @click="showModal = false" 
                                class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700">
                            Save
                        </button>
                    </div>
                    
                    <div class="mt-4 p-2 bg-gray-100 text-xs">
                        Debug info: <span x-text="JSON.stringify(visibleColumns)"></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>