/* =============================================
   PORTFOLIO PROJECT SWITCHING
   ============================================= */
(function() {
    'use strict';

    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        console.log('Portfolio JS initialized');

        const projectRows = document.querySelectorAll('.project-row');
        const projectDetailsContainer = document.getElementById('projectDetails');

        console.log('Found project rows:', projectRows.length);
        console.log('Project details container:', projectDetailsContainer);

        if (!projectRows.length || !projectDetailsContainer) {
            console.error('Missing required elements');
            return;
        }

        // Handle project row clicks
        projectRows.forEach(row => {
            row.addEventListener('click', function() {
                console.log('Project row clicked:', this);

                const projectId = this.getAttribute('data-project-id');
                console.log('Project ID:', projectId);

                // Remove active class from all rows
                projectRows.forEach(r => r.classList.remove('active'));

                // Add active class to clicked row
                this.classList.add('active');

                // Load project details via AJAX
                loadProjectDetails(projectId);
            });
        });

        function loadProjectDetails(projectId) {
            console.log('Loading project details for ID:', projectId);

            // Show loading state
            projectDetailsContainer.innerHTML = '<p style="padding: 2rem; opacity: 0.5;">Loading...</p>';

            // Check if portfolioAjax is defined
            if (typeof portfolioAjax === 'undefined') {
                console.error('portfolioAjax is not defined');
                projectDetailsContainer.innerHTML = '<p style="padding: 2rem; color: red;">Configuration error</p>';
                return;
            }

            // AJAX request to get project details
            const formData = new FormData();
            formData.append('action', 'get_project_details');
            formData.append('project_id', projectId);

            fetch(portfolioAjax.ajaxurl, {
                method: 'POST',
                body: formData
            })
            .then(response => {
                console.log('Response received:', response);
                return response.text();
            })
            .then(data => {
                console.log('Data received, length:', data.length);
                projectDetailsContainer.innerHTML = data;
            })
            .catch(error => {
                console.error('Error loading project:', error);
                projectDetailsContainer.innerHTML = '<p style="padding: 2rem; color: red;">Error loading project</p>';
            });
        }
    }
})();
