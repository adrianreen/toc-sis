<!DOCTYPE html>
<html>
<head>
    <title>Test Modal</title>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 p-8">
    <div x-data="{ 
        showModal: false,
        columns: {
            'name': { label: 'Name', type: 'text' },
            'email': { label: 'Email', type: 'text' },
            'status': { label: 'Status', type: 'badge' }
        },
        visibleColumns: ['name', 'email']
    }">
        <button @click="showModal = true" 
                class="bg-purple-600 text-white px-4 py-2 rounded hover:bg-purple-700">
            Open Modal Test
        </button>

        <!-- Modal -->
        <div x-show="showModal" 
             class="fixed inset-0 z-50 overflow-y-auto"
             @click.self="showModal = false">
            <div class="flex items-center justify-center min-h-screen px-4">
                <div class="fixed inset-0 bg-gray-500 bg-opacity-75"></div>
                
                <div class="bg-white rounded-lg shadow-xl p-6 max-w-lg w-full relative">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Test Modal</h3>
                    
                    <div class="space-y-3">
                        <template x-for="(column, key) in columns" :key="key">
                            <div class="flex items-center justify-between p-2 border rounded">
                                <div class="flex items-center space-x-3">
                                    <input type="checkbox" 
                                           :checked="visibleColumns.includes(key)"
                                           @change="
                                               if(visibleColumns.includes(key)) {
                                                   visibleColumns = visibleColumns.filter(c => c !== key)
                                               } else {
                                                   visibleColumns.push(key)
                                               }
                                           "
                                           class="rounded border-gray-300">
                                    <label class="text-sm font-medium text-gray-900" x-text="column.label"></label>
                                </div>
                                <span class="text-xs text-gray-500" x-text="column.type"></span>
                            </div>
                        </template>
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
                </div>
            </div>
        </div>
        
        <div class="mt-4">
            <p class="text-sm text-gray-600">Visible columns: <span x-text="visibleColumns.join(', ')"></span></p>
        </div>
    </div>
</body>
</html>