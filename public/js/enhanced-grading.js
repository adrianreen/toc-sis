/**
 * Enhanced Grading Interface JavaScript
 * 
 * Provides smooth interactions, clear visual feedback, and production-ready
 * functionality for the improved grading interface.
 */

class EnhancedGradingInterface {
    constructor() {
        this.currentEditCell = null;
        this.currentGradeRecordId = null;
        this.isLoading = false;
        this.notifications = [];
        
        this.init();
    }
    
    init() {
        this.setupEventListeners();
        this.addCSRFTokenIfMissing();
        this.showWelcomeGuidance();
        this.initializeKeyboardShortcuts();
    }
    
    setupEventListeners() {
        // Global click handler for closing menus
        document.addEventListener('click', (e) => {
            this.handleGlobalClick(e);
        });
        
        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            this.handleKeyboardNavigation(e);
        });
        
        // Form submission prevention during loading
        document.addEventListener('submit', (e) => {
            if (this.isLoading) {
                e.preventDefault();
                this.showNotification('Please wait for the current operation to complete', 'warning');
            }
        });
    }
    
    /**
     * Grade Editing Functions
     */
    enableGradeEdit(displayElement) {
        if (this.currentEditCell) {
            this.cancelEdit();
        }
        
        const cell = displayElement.closest('.grade-cell-container');
        const input = cell.querySelector('.grade-input-field');
        const display = cell.querySelector('.grade-display-main');
        
        if (!cell || !input || !display) {
            console.error('Grade cell components not found');
            return;
        }
        
        this.currentEditCell = cell;
        this.currentGradeRecordId = cell.dataset.gradeRecordId;
        
        // Add editing class for styling
        cell.classList.add('editing');
        
        // Smooth transition to edit mode
        this.animateToEditMode(display, input);
    }
    
    animateToEditMode(display, input) {
        // Fade out display
        display.style.opacity = '0';
        display.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            display.classList.add('hidden');
            input.classList.remove('hidden');
            
            // Reset input styles
            input.style.opacity = '0';
            input.style.transform = 'scale(0.95)';
            
            // Animate input appearance
            requestAnimationFrame(() => {
                input.style.opacity = '1';
                input.style.transform = 'scale(1)';
                input.focus();
                input.select();
            });
        }, 150);
    }
    
    cancelEdit() {
        if (!this.currentEditCell) return;
        
        const display = this.currentEditCell.querySelector('.grade-display-main');
        const input = this.currentEditCell.querySelector('.grade-input-field');
        
        if (!display || !input) return;
        
        // Remove editing class
        this.currentEditCell.classList.remove('editing');
        
        // Animate out input
        input.style.opacity = '0';
        input.style.transform = 'scale(0.95)';
        
        setTimeout(() => {
            input.classList.add('hidden');
            display.classList.remove('hidden');
            display.style.opacity = '0';
            display.style.transform = 'scale(0.95)';
            
            // Animate in display
            requestAnimationFrame(() => {
                display.style.opacity = '1';
                display.style.transform = 'scale(1)';
            });
        }, 150);
        
        this.currentEditCell = null;
        this.currentGradeRecordId = null;
    }
    
    saveGrade(input) {
        if (!this.currentEditCell || this.isLoading) return;
        
        const grade = parseFloat(input.value);
        const maxGrade = parseFloat(input.getAttribute('max'));
        const studentId = this.currentEditCell.dataset.studentId;
        const component = this.currentEditCell.dataset.component;
        
        // Validation
        if (input.value.trim() === '') {
            this.cancelEdit();
            return;
        }
        
        if (!this.validateGrade(input, grade, maxGrade)) {
            return;
        }
        
        // Show saving state
        this.showSavingState();
        
        // Save via AJAX
        this.updateGradeAPI(studentId, component, grade, maxGrade)
            .then(response => {
                if (response.success) {
                    this.handleSaveSuccess(response);
                } else {
                    this.handleSaveError(response.message || 'Unknown error occurred');
                }
            })
            .catch(error => {
                console.error('Grade save error:', error);
                this.handleSaveError('Network error occurred while saving grade');
            })
            .finally(() => {
                this.hideSavingState();
                this.cancelEdit();
            });
    }
    
    validateGrade(input, grade, maxGrade) {
        if (isNaN(grade) || grade < 0 || grade > maxGrade) {
            // Enhanced error feedback
            input.style.borderColor = '#ef4444';
            input.style.backgroundColor = '#fef2f2';
            input.style.boxShadow = '0 0 0 3px rgba(239, 68, 68, 0.2)';
            
            this.showNotification(
                `Please enter a valid grade between 0 and ${maxGrade}`, 
                'error'
            );
            
            // Reset styles after delay
            setTimeout(() => {
                input.style.borderColor = '#3b82f6';
                input.style.backgroundColor = 'white';
                input.style.boxShadow = '0 0 0 3px rgba(59, 130, 246, 0.2)';
            }, 2000);
            
            input.focus();
            input.select();
            return false;
        }
        return true;
    }
    
    showSavingState() {
        const display = this.currentEditCell.querySelector('.grade-display-main');
        display.classList.add('saving');
        this.isLoading = true;
    }
    
    hideSavingState() {
        if (this.currentEditCell) {
            const display = this.currentEditCell.querySelector('.grade-display-main');
            display.classList.remove('saving');
        }
        this.isLoading = false;
    }
    
    handleSaveSuccess(response) {
        this.showNotification('Grade saved successfully', 'success');
        
        // Add success animation
        const display = this.currentEditCell.querySelector('.grade-display-main');
        display.classList.add('save-success');
        
        // Update display without full page reload
        this.updateGradeDisplay(response.gradeRecord);
        
        // Remove success class after animation
        setTimeout(() => {
            display.classList.remove('save-success');
        }, 600);
    }
    
    handleSaveError(message) {
        this.showNotification(`Error saving grade: ${message}`, 'error');
    }
    
    updateGradeDisplay(gradeRecord) {
        // Implementation to update the grade display without page reload
        // This would update the percentage, status indicators, etc.
        // For now, we'll reload the page for simplicity
        setTimeout(() => {
            window.location.reload();
        }, 1000);
    }
    
    handleGradeKeydown(event, input) {
        switch(event.key) {
            case 'Enter':
                event.preventDefault();
                this.saveGrade(input);
                break;
            case 'Escape':
                event.preventDefault();
                this.cancelEdit();
                break;
            case 'Tab':
                // Save current grade and move to next cell
                this.saveGrade(input);
                break;
        }
    }
    
    /**
     * API Functions
     */
    async updateGradeAPI(studentId, component, grade, maxGrade) {
        const gradeRecordId = this.currentGradeRecordId;
        
        if (!gradeRecordId) {
            throw new Error('No grade record found');
        }
        
        const response = await fetch(`/grade-records/${gradeRecordId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken()
            },
            body: JSON.stringify({
                grade: grade,
                max_grade: maxGrade,
                submission_date: new Date().toISOString().split('T')[0],
                is_visible_to_student: false,
                release_date: null
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    async toggleVisibilityAPI(gradeRecordId, visible) {
        const response = await fetch(`/grade-records/${gradeRecordId}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.getCSRFToken()
            },
            body: JSON.stringify({
                is_visible_to_student: visible
            })
        });
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        return await response.json();
    }
    
    /**
     * Visibility Functions
     */
    toggleVisibility(gradeRecordId) {
        if (this.isLoading) {
            this.showNotification('Please wait for current operation to complete', 'warning');
            return;
        }
        
        this.isLoading = true;
        
        this.toggleVisibilityAPI(gradeRecordId, true) // Server will toggle
            .then(response => {
                if (response.success) {
                    this.showNotification('Visibility updated successfully', 'success');
                    // Update UI to reflect change
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    this.showNotification('Error updating visibility', 'error');
                }
            })
            .catch(error => {
                console.error('Visibility toggle error:', error);
                this.showNotification('Network error occurred', 'error');
            })
            .finally(() => {
                this.isLoading = false;
            });
    }
    
    /**
     * Bulk Actions
     */
    showBulkActions() {
        const modal = document.getElementById('bulkActionsModal');
        if (modal) {
            modal.classList.remove('hidden');
            this.trapFocus(modal);
        }
    }
    
    hideBulkActions() {
        const modal = document.getElementById('bulkActionsModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    }
    
    async bulkShowGrades(moduleInstanceId) {
        if (!confirm('Show all graded assessments to students?')) return;
        
        this.isLoading = true;
        
        try {
            const response = await fetch(`/grade-records/${moduleInstanceId}/toggle-visibility`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({ visible: true })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('All grades made visible to students', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Error updating visibility', 'error');
            }
        } catch (error) {
            console.error('Bulk visibility error:', error);
            this.showNotification('Network error occurred', 'error');
        } finally {
            this.isLoading = false;
            this.hideBulkActions();
        }
    }
    
    async bulkHideGrades(moduleInstanceId) {
        if (!confirm('Hide all grades from students?')) return;
        
        this.isLoading = true;
        
        try {
            const response = await fetch(`/grade-records/${moduleInstanceId}/toggle-visibility`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.getCSRFToken()
                },
                body: JSON.stringify({ visible: false })
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.showNotification('All grades hidden from students', 'success');
                setTimeout(() => window.location.reload(), 1500);
            } else {
                this.showNotification('Error updating visibility', 'error');
            }
        } catch (error) {
            console.error('Bulk visibility error:', error);
            this.showNotification('Network error occurred', 'error');
        } finally {
            this.isLoading = false;
            this.hideBulkActions();
        }
    }
    
    /**
     * Feedback Modal Functions
     */
    showFeedbackModal(gradeRecordId, studentName, component) {
        const modal = document.getElementById('feedbackModal');
        const title = document.getElementById('feedbackModalTitle');
        const textarea = document.getElementById('feedbackText');
        
        if (modal && title && textarea) {
            title.textContent = `Feedback for ${studentName} - ${component}`;
            modal.classList.remove('hidden');
            textarea.focus();
            this.trapFocus(modal);
            
            // Load existing feedback if available
            if (gradeRecordId && gradeRecordId !== 'null') {
                this.loadExistingFeedback(gradeRecordId, textarea);
            }
        }
    }
    
    hideFeedbackModal() {
        const modal = document.getElementById('feedbackModal');
        const textarea = document.getElementById('feedbackText');
        
        if (modal && textarea) {
            modal.classList.add('hidden');
            textarea.value = '';
        }
    }
    
    async loadExistingFeedback(gradeRecordId, textarea) {
        try {
            const response = await fetch(`/grade-records/${gradeRecordId}`);
            const data = await response.json();
            
            if (data.gradeRecord && data.gradeRecord.feedback) {
                textarea.value = data.gradeRecord.feedback;
            }
        } catch (error) {
            console.error('Error loading feedback:', error);
        }
    }
    
    /**
     * Notification System
     */
    showNotification(message, type = 'info', duration = 4000) {
        const notification = this.createNotification(message, type);
        document.body.appendChild(notification);
        
        // Animate in
        requestAnimationFrame(() => {
            notification.style.transform = 'translate(0)';
        });
        
        // Auto-remove
        setTimeout(() => {
            this.removeNotification(notification);
        }, duration);
        
        // Store reference
        this.notifications.push(notification);
        
        // Limit concurrent notifications
        if (this.notifications.length > 3) {
            this.removeNotification(this.notifications.shift());
        }
    }
    
    createNotification(message, type) {
        const icons = {
            success: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path></svg>',
            error: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path></svg>',
            warning: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path></svg>',
            info: '<svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"></path></svg>'
        };
        
        const colors = {
            success: 'bg-green-500 text-white',
            error: 'bg-red-500 text-white',
            warning: 'bg-yellow-500 text-white',
            info: 'bg-blue-500 text-white'
        };
        
        const notification = document.createElement('div');
        notification.className = `fixed top-4 right-4 p-4 rounded-xl shadow-2xl z-50 flex items-center space-x-3 transform translate-x-full transition-all duration-500 ease-out ${colors[type]}`;
        
        notification.innerHTML = `
            <div class="flex-shrink-0">${icons[type]}</div>
            <div class="flex-1">
                <p class="font-medium">${message}</p>
            </div>
            <button onclick="this.parentElement.remove()" class="flex-shrink-0 ml-2 opacity-70 hover:opacity-100 transition-opacity">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                </svg>
            </button>
        `;
        
        return notification;
    }
    
    removeNotification(notification) {
        if (notification && notification.parentElement) {
            notification.style.transform = 'translate(100%)';
            setTimeout(() => {
                if (notification.parentElement) {
                    notification.remove();
                }
            }, 500);
            
            // Remove from tracking array
            const index = this.notifications.indexOf(notification);
            if (index > -1) {
                this.notifications.splice(index, 1);
            }
        }
    }
    
    /**
     * Utility Functions
     */
    getCSRFToken() {
        const token = document.querySelector('meta[name="csrf-token"]');
        return token ? token.getAttribute('content') : '';
    }
    
    addCSRFTokenIfMissing() {
        if (!document.querySelector('meta[name="csrf-token"]')) {
            const meta = document.createElement('meta');
            meta.name = 'csrf-token';
            meta.content = window.Laravel ? window.Laravel.csrfToken : '';
            document.head.appendChild(meta);
        }
    }
    
    showWelcomeGuidance() {
        const hasSeenTutorial = localStorage.getItem('grading-tutorial-seen');
        if (!hasSeenTutorial) {
            setTimeout(() => {
                this.showNotification(
                    'Welcome! Click any grade cell to start grading. Hover over cells for quick actions.',
                    'info',
                    8000
                );
                localStorage.setItem('grading-tutorial-seen', 'true');
            }, 1000);
        }
    }
    
    initializeKeyboardShortcuts() {
        // Add helpful keyboard shortcuts
        this.showNotification(
            'Tip: Use Enter to save, Escape to cancel, Tab to move to next cell',
            'info',
            5000
        );
    }
    
    handleKeyboardNavigation(event) {
        if (event.ctrlKey || event.metaKey) {
            switch(event.key) {
                case 's':
                    event.preventDefault();
                    if (this.currentEditCell) {
                        const input = this.currentEditCell.querySelector('.grade-input-field');
                        this.saveGrade(input);
                    }
                    break;
                case 'Escape':
                    if (this.currentEditCell) {
                        this.cancelEdit();
                    }
                    break;
            }
        }
    }
    
    handleGlobalClick(event) {
        // Close dropdowns when clicking outside
        if (!event.target.closest('.student-actions-dropdown')) {
            document.querySelectorAll('.student-actions-menu').forEach(menu => {
                menu.classList.add('hidden');
            });
        }
    }
    
    trapFocus(element) {
        const focusableElements = element.querySelectorAll(
            'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
        );
        
        const firstElement = focusableElements[0];
        const lastElement = focusableElements[focusableElements.length - 1];
        
        element.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                if (e.shiftKey) {
                    if (document.activeElement === firstElement) {
                        lastElement.focus();
                        e.preventDefault();
                    }
                } else {
                    if (document.activeElement === lastElement) {
                        firstElement.focus();
                        e.preventDefault();
                    }
                }
            }
        });
    }
    
    /**
     * Export Functions
     */
    exportGrades(moduleInstanceId) {
        window.open(`/grade-records/${moduleInstanceId}/export`, '_blank');
    }
}

// Initialize the enhanced grading interface when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.gradingInterface = new EnhancedGradingInterface();
    
    // Make functions globally available for onclick handlers
    window.enableGradeEdit = (element) => window.gradingInterface.enableGradeEdit(element);
    window.saveGrade = (input) => window.gradingInterface.saveGrade(input);
    window.handleGradeKeydown = (event, input) => window.gradingInterface.handleGradeKeydown(event, input);
    window.toggleVisibility = (gradeRecordId) => window.gradingInterface.toggleVisibility(gradeRecordId);
    window.showFeedbackModal = (gradeRecordId, studentName, component) => window.gradingInterface.showFeedbackModal(gradeRecordId, studentName, component);
    window.hideFeedbackModal = () => window.gradingInterface.hideFeedbackModal();
    window.showBulkActions = () => window.gradingInterface.showBulkActions();
    window.hideBulkActions = () => window.gradingInterface.hideBulkActions();
    window.exportGrades = (moduleInstanceId) => window.gradingInterface.exportGrades(moduleInstanceId);
    
    console.log('Enhanced grading interface initialized successfully');
});