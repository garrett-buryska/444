// on load, fetch activities and populate the page with input fields for max values
window.onload = function() {
            fetch('../backend/get-activities.php')
                .then(response => response.json())
                .then(activities => {
                    const container = document.getElementById('activity-body');
                    // Loop through activities and create input fields for max values
                    activities.forEach(act => {
                        let label = act.set_type;
                        if (act.set_type === 'reps') label = 'lbs';
                        if (act.set_type === 'body') label = 'reps';

                        const savedValue = act.max_value !== null ? act.max_value : '';

                        const div = document.createElement('div');
                        div.className = 'activity-item';
                        div.setAttribute('data-name', act.activity_name.toLowerCase());
                        // Create input field with saved max value if it exists
                        div.innerHTML = `
                            <span>${act.activity_name}</span>
                            <div class="input-group">
                                <input type="number" 
                                       class="max-input" 
                                       data-activity="${act.activity_name}" 
                                       value="${savedValue}" 
                                       placeholder="0">
                                <span class="type-label">${label}</span>
                            </div>
                        `;
                        container.appendChild(div);
                    });
                    
                    attachListeners();
                })
                .catch(err => console.error("Error loading activities:", err));
        };

        function filterActivities() {
            const query = document.getElementById('search-bar').value.toLowerCase();
            const items = document.querySelectorAll('.activity-item');

            items.forEach(item => {
                const name = item.getAttribute('data-name');
                if (name.includes(query)) {
                    item.classList.remove('hidden');
                } else {
                    item.classList.add('hidden');
                }
            });
        }
        // Attach event listeners to input fields for saving max values
        function attachListeners() {
            document.querySelectorAll('.max-input').forEach(input => {
                input.addEventListener('input', function() {
                    const element = this;
                    const activity = element.dataset.activity;
                    const value = element.value;
                    // Send POST request to backend to save max value for the activity
                    fetch('../backend/save-max.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ activity: activity, max_value: value })
                    })
                    .then(response => response.json())
                    .then(data => {
                        element.style.borderColor = data.success ? 'green' : 'red';
                    })
                    .catch(err => {
                        element.style.borderColor = 'red';
                    });
                });
            });
        }