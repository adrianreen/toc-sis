<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Compose Email for {{ $student->full_name }}
            </h2>
            <a href="{{ route('students.show', $student) }}" 
               class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out">
                Back to Student
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6">
                    <h3 class="text-lg font-semibold mb-6">Email Composition</h3>
                    
                    <div class="mb-6 p-4 bg-gray-50 rounded-lg">
                        <h4 class="text-sm font-medium text-gray-700 mb-2">Sending to:</h4>
                        <div class="text-sm text-gray-600">
                            <strong>{{ $student->full_name }}</strong> ({{ $student->student_number }})<br>
                            Email: {{ $student->email }}
                        </div>
                    </div>

                    <form method="POST" action="{{ route('student-emails.send', $student) }}" id="email-form">
                        @csrf
                        
                        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                            <!-- Template Selection -->
                            <div>
                                <label for="template_id" class="block text-sm font-medium text-gray-700 mb-2">
                                    Select Template
                                </label>
                                <select id="template_id" 
                                        name="template_id" 
                                        required
                                        class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">
                                    <option value="">Choose a template...</option>
                                    @foreach($availableTemplates as $category => $templates)
                                        <optgroup label="{{ ucfirst($category) }}">
                                            @foreach($templates as $tmpl)
                                                <option value="{{ $tmpl->id }}" 
                                                        {{ old('template_id', $template?->id) == $tmpl->id ? 'selected' : '' }}>
                                                    {{ $tmpl->name }}
                                                </option>
                                            @endforeach
                                        </optgroup>
                                    @endforeach
                                </select>
                                @error('template_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Options -->
                            <div class="space-y-4">
                                <div>
                                    <label class="flex items-center">
                                        <input type="checkbox" 
                                               id="include_transcript" 
                                               name="include_transcript" 
                                               value="1"
                                               {{ old('include_transcript') ? 'checked' : '' }}
                                               class="rounded border-gray-300 text-indigo-600 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                                        <span class="ml-2 text-sm text-gray-700">Include transcript as attachment</span>
                                    </label>
                                </div>

                                <div>
                                    <label for="custom_message" class="block text-sm font-medium text-gray-700 mb-1">
                                        Custom Message (Optional)
                                    </label>
                                    <textarea id="custom_message" 
                                              name="custom_message" 
                                              rows="3"
                                              placeholder="Add any personal message here..."
                                              class="w-full border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500">{{ old('custom_message') }}</textarea>
                                    <p class="mt-1 text-xs text-gray-500">This will be added to the template as {{ '{{custom_message}}' }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Preview Section -->
                        <div id="preview-section" class="mt-8 hidden">
                            <div class="border border-gray-200 rounded-lg">
                                <div class="bg-gray-50 px-4 py-3 border-b border-gray-200 rounded-t-lg">
                                    <h4 class="text-sm font-medium text-gray-700">Email Preview</h4>
                                </div>
                                <div class="p-4">
                                    <div class="mb-4">
                                        <div class="text-sm text-gray-600 mb-1">Subject:</div>
                                        <div id="preview-subject" class="font-medium"></div>
                                    </div>
                                    <div id="preview-attachment" class="mb-4 hidden">
                                        <div class="text-sm text-gray-600 mb-1">Attachment:</div>
                                        <div class="flex items-center text-sm text-blue-600">
                                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13"></path>
                                            </svg>
                                            <span id="attachment-name"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-sm text-gray-600 mb-2">Content:</div>
                                        <div id="preview-content" class="prose prose-sm max-w-none border rounded p-3 bg-gray-50"></div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-between items-center">
                            <button type="button" 
                                    id="preview-btn"
                                    class="inline-flex items-center px-4 py-2 bg-gray-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed">
                                Preview Email
                            </button>
                            
                            <button type="submit" 
                                    id="send-btn"
                                    class="inline-flex items-center px-6 py-2 bg-indigo-600 border border-transparent rounded-md font-medium text-sm text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition duration-150 ease-in-out disabled:opacity-50 disabled:cursor-not-allowed"
                                    disabled>
                                Send Email
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const templateSelect = document.getElementById('template_id');
            const includeTranscript = document.getElementById('include_transcript');
            const previewBtn = document.getElementById('preview-btn');
            const sendBtn = document.getElementById('send-btn');
            const previewSection = document.getElementById('preview-section');

            function updatePreviewButton() {
                const hasTemplate = templateSelect.value !== '';
                previewBtn.disabled = !hasTemplate;
                sendBtn.disabled = !hasTemplate;
            }

            templateSelect.addEventListener('change', updatePreviewButton);
            updatePreviewButton();

            previewBtn.addEventListener('click', async function() {
                if (!templateSelect.value) return;

                try {
                    const formData = new FormData();
                    formData.append('template_id', templateSelect.value);
                    formData.append('include_transcript', includeTranscript.checked ? '1' : '0');
                    formData.append('_token', document.querySelector('[name="_token"]').value);

                    const response = await fetch(`{{ route('student-emails.preview', $student) }}`, {
                        method: 'POST',
                        body: formData
                    });

                    if (response.ok) {
                        const data = await response.json();
                        
                        document.getElementById('preview-subject').textContent = data.subject;
                        document.getElementById('preview-content').innerHTML = data.body_html;
                        
                        const attachmentDiv = document.getElementById('preview-attachment');
                        if (data.attachment) {
                            document.getElementById('attachment-name').textContent = data.attachment.name;
                            attachmentDiv.classList.remove('hidden');
                        } else {
                            attachmentDiv.classList.add('hidden');
                        }
                        
                        previewSection.classList.remove('hidden');
                        previewSection.scrollIntoView({ behavior: 'smooth' });
                    } else {
                        alert('Failed to generate preview. Please try again.');
                    }
                } catch (error) {
                    console.error('Preview error:', error);
                    alert('Failed to generate preview. Please try again.');
                }
            });
        });
    </script>
</x-app-layout>